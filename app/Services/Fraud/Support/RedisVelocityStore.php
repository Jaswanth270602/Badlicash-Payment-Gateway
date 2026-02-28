<?php

namespace App\Services\Fraud\Support;

use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\Log;

/**
 * Redis-backed velocity and device state for fraud rules.
 * All operations are atomic where required; failures are fail-open (log and return safe default).
 */
class RedisVelocityStore
{
    public function __construct(
        private readonly RedisFactory $redis,
        private readonly ConfigRepository $config
    ) {
    }

    /**
     * @return \Illuminate\Redis\Connections\Connection
     */
    private function connection()
    {
        $name = $this->config->get('fraud.redis_connection') ?? 'default';
        return $this->redis->connection($name);
    }

    private function failOpen(): bool
    {
        return (bool) $this->config->get('fraud.fail_open_on_redis', true);
    }

    /**
     * Add a timestamp to a sorted set, trim by window, return count. Atomic.
     * Key is used for velocity (e.g. fraud:ip:{ip}:txns). TTL set to 2× window to avoid unbounded keys.
     *
     * @return int Count of entries in window after add, or 0 on Redis failure (fail-open).
     */
    public function addAndCountInWindow(string $key, int $windowSeconds): int
    {
        try {
            $conn = $this->connection();
            $now = (int) floor(microtime(true));

            $results = $conn->pipeline(function ($pipe) use ($key, $now, $windowSeconds) {
                $pipe->zadd($key, $now, (string) $now);
                $pipe->zremrangebyscore($key, 0, $now - $windowSeconds);
                $pipe->expire($key, $windowSeconds * 2);
                $pipe->zcard($key);
            });

            return (int) ($results[3] ?? 0);
        } catch (\Throwable $e) {
            $this->logRedisFailure('addAndCountInWindow', $key, $e);
            return $this->failOpen() ? 0 : throw $e;
        }
    }

    /**
     * Get previous value for key and set new value with TTL (e.g. last device_id).
     * Not atomic across get+set; acceptable for device-change rule. Returns null on failure (fail-open).
     */
    public function getAndSet(string $key, string $value, int $ttlSeconds): ?string
    {
        try {
            $conn = $this->connection();
            $previous = $conn->get($key);
            $conn->setex($key, $ttlSeconds, $value);
            return $previous !== false && $previous !== null ? (string) $previous : null;
        } catch (\Throwable $e) {
            $this->logRedisFailure('getAndSet', $key, $e);
            if ($this->failOpen()) {
                return null;
            }
            throw $e;
        }
    }

    private function logRedisFailure(string $operation, string $key, \Throwable $e): void
    {
        Log::channel('single')->warning('Fraud Redis operation failed (fail-open)', [
            'operation' => $operation,
            'key_prefix' => substr($key, 0, 50),
            'message' => $e->getMessage(),
        ]);
    }
}

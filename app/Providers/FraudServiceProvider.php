<?php

namespace App\Providers;

use App\Services\Fraud\DecisionService;
use App\Services\Fraud\FraudEngine;
use App\Services\Fraud\FraudPersistenceService;
use App\Services\Fraud\Rules\AmountAnomalyRule;
use App\Services\Fraud\Rules\CardVelocityRule;
use App\Services\Fraud\Rules\DeviceChangeRule;
use App\Services\Fraud\Rules\GeoMismatchRule;
use App\Services\Fraud\Rules\IpVelocityRule;
use App\Services\Fraud\Support\RedisVelocityStore;
use Illuminate\Support\ServiceProvider;

class FraudServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RedisVelocityStore::class);

        $this->app->singleton(DecisionService::class);

        $this->app->singleton(FraudEngine::class, function ($app) {
            $config = $app->make('config');
            $rules = [];

            $redisStore = $app->make(RedisVelocityStore::class);

            $r = $config->get('fraud.rules', []);
            if ($r['ip_velocity']['enabled'] ?? true) {
                $rules[] = new IpVelocityRule(
                    $redisStore,
                    $r['ip_velocity']['window_seconds'],
                    $r['ip_velocity']['max_transactions_in_window'],
                    $r['ip_velocity']['weight']
                );
            }
            if ($r['card_velocity']['enabled'] ?? true) {
                $rules[] = new CardVelocityRule(
                    $redisStore,
                    $r['card_velocity']['window_seconds'],
                    $r['card_velocity']['max_transactions_in_window'],
                    $r['card_velocity']['weight']
                );
            }
            if ($r['amount_anomaly']['enabled'] ?? true) {
                $rules[] = new AmountAnomalyRule(
                    $r['amount_anomaly']['high_amount_threshold'],
                    $r['amount_anomaly']['very_high_amount_threshold'],
                    $r['amount_anomaly']['weight_high'],
                    $r['amount_anomaly']['weight_very_high']
                );
            }
            if ($r['geo_mismatch']['enabled'] ?? true) {
                $rules[] = new GeoMismatchRule(
                    $r['geo_mismatch']['weight_mismatch'],
                    $r['geo_mismatch']['weight_high_risk'],
                    $r['geo_mismatch']['high_risk_countries'] ?? []
                );
            }
            if ($r['device_change']['enabled'] ?? true) {
                $rules[] = new DeviceChangeRule(
                    $redisStore,
                    $r['device_change']['weight_new_device'],
                    $r['device_change']['weight_device_change'],
                    $r['device_change']['ttl_seconds']
                );
            }

            return new FraudEngine($app->make(DecisionService::class), $rules);
        });

        $this->app->singleton(FraudPersistenceService::class);
    }
}

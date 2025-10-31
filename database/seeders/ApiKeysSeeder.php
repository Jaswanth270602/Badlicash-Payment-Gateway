<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Merchant;
use Illuminate\Database\Seeder;

class ApiKeysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $merchants = Merchant::all();

        foreach ($merchants as $merchant) {
            // Create test mode API key
            ApiKey::generate($merchant->id, 'test', 'Test API Key');

            // Create live mode API key for non-test merchants
            if (!$merchant->test_mode) {
                ApiKey::generate($merchant->id, 'live', 'Live API Key');
            }
        }

        $this->command->info('API keys seeded successfully.');
        $this->command->info('Check api_keys table for generated keys and secrets.');
    }
}


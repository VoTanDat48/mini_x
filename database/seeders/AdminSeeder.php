<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Grant admin role to demo@example.com (demouser).
 * Run: php artisan db:seed --class=AdminSeeder
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::where('email', 'demo@example.com')->update(['role' => 'admin']);
        $this->command->info('✅ demo@example.com đã được nâng cấp lên Admin.');
    }
}


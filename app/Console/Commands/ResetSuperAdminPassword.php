<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('sitera:reset-super-admin-password {--force : Skip the confirmation prompt after account checks pass}')]
#[Description('Reset the sole active Super Admin password to SITERA_DEFAULT_PASSWORD without displaying the secret.')]
class ResetSuperAdminPassword extends Command
{
    public function handle(): int
    {
        $defaultPassword = config('sitera.default_password');
        if (! is_string($defaultPassword) || $defaultPassword === '') {
            $this->error('SITERA_DEFAULT_PASSWORD is not configured. No account was changed.');

            return self::FAILURE;
        }

        $admins = User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('code', 'super_admin'));

        if ($admins->count() !== 1) {
            $this->error('Expected exactly one active Super Admin account. No account was changed.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Reset password for the sole active Super Admin account to the configured default?')) {
            $this->warn('Password reset cancelled.');

            return self::SUCCESS;
        }

        $admins->firstOrFail()->forceFill(['password' => Hash::make($defaultPassword)])->save();

        $this->info('Password reset completed. Secret value was not displayed.');

        return self::SUCCESS;
    }
}

// Deliberately never print the configured default password or account email.
// The account must be exactly one active Super Admin to prevent ambiguous resets.
// No session or user record is changed when config or account checks fail.

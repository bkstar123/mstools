<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Bkstar123\BksCMS\AdminPanel\Role;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            return ($user->hasRole(Role::SUPERADMINS) || $user->hasRole(Role::SUPERADMINS)) ? true : null;
        });

        Gate::define('domains.ssl.check', function ($user) {
            return $user->hasPermission('domains.ssl.check');
        });

        Gate::define('cfzones.customssl.check', function ($user) {
            return $user->hasPermission('cfzones.customssl.check');
        });

        Gate::define('certificate.decode', function ($user) {
            return $user->hasPermission('certificate.decode');
        });

        Gate::define('cfzone.certificate.update', function ($user) {
            return $user->hasPermission('cfzone.certificate.update');
        });

        Gate::define('key.certificate.matching', function ($user) {
            return $user->hasPermission('key.certificate.matching');
        });
    }
}

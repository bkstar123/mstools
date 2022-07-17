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

        // SSL
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

        Gate::define('certificate.pre.replacement.validation.bypass', function ($user) {
            return $user->hasPermission('certificate.pre.replacement.validation.bypass');
        });

        // CF FW rule
        Gate::define('cffwrule.create', function ($user) {
            return $user->hasPermission('cffwrule.create');
        });

        Gate::define('cffwrule.update', function ($user) {
            return $user->hasPermission('cffwrule.update');
        });

        Gate::define('cffwrule.delete', function ($user) {
            return $user->hasPermission('cffwrule.delete');
        });

        // DXP Sites Go-live tracking
        Gate::define('trackings.create', function ($user) {
            return $user->hasPermission('trackings.create');
        });

        Gate::define('trackings.on', function ($user, $track) {
            return $user->hasPermission('trackings.on') || $user->id == $track->admin_id;
        });

        Gate::define('trackings.off', function ($user, $track) {
            return $user->hasPermission('trackings.off') || $user->id == $track->admin_id;
        });

        Gate::define('trackings.destroy', function ($user, $track) {
            return $user->hasPermission('trackings.destroy') || $user->id == $track->admin_id;
        });

        Gate::define('trackings.update', function ($user, $track) {
            return $user->hasPermission('trackings.update') || $user->id == $track->admin_id;
        });

        Gate::define('trackings.massiveDestroy', function ($user) {
            return $user->hasRole(Role::SUPERADMINS);
        });

        Gate::define('aboutpage.create', function ($user) {
            return $user->hasRole(Role::SUPERADMINS);
        });
    }
}

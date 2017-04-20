<?php

namespace Wishginee\Providers;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Wishginee\Campaign;
use Wishginee\Event;
use Wishginee\Follow;
use Wishginee\Foundation\JWTGuard;
use Wishginee\Foundation\JWTManager;
use Wishginee\Policies\CampaignPolicy;
use Wishginee\Policies\EventPolicy;
use Wishginee\Policies\FollowPolicy;
use Wishginee\Policies\UserPolicy;
use Wishginee\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'Wishginee\Model' => 'Wishginee\Policies\ModelPolicy',
        User::class => UserPolicy::class,
        Campaign::class => CampaignPolicy::class,
        Event::class => EventPolicy::class,
        Follow::class => FollowPolicy::class
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        /*
         * JWT Auth Service Definition
         */
        Auth::extend('jwt', function (Container $app, $name, array $config){
            return new JWTGuard(Auth::createUserProvider($config['provider']), $app->make(Repository::class), $app->make(Request::class), $app->make(JWTManager::class));
        });

        $this->registerPolicies($gate);
    }
}

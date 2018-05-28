<?php

namespace yedincisenol\UserProvider;

use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use League\OAuth2\Server\AuthorizationServer;

abstract class UserProviderServiceProviderAbstract extends PassportServiceProvider
{

    /**
     * Provider configuration
     * @var
     */
    protected $config;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->config = app()['config']['userprovider'];
        app(AuthorizationServer::class)->enableGrantType($this->makeUserProviderGrant(), Passport::tokensExpireIn());
    }


    /**
     * Create and configure a Password grant instance.
     *
     * @return PasswordGrant
     */
    abstract protected function makeUserProviderGrant();

}
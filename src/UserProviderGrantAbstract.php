<?php

namespace yedincisenol\UserProvider;

use Laravel\Passport\Bridge\User;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\RequestEvent;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use yedincisenol\UserProvider\Exceptions\ConfigNotFoundException;
use Illuminate\Http\Request;

abstract class UserProviderGrantAbstract extends AbstractGrant
{

    /**
     * User provider config
     * @var
     */
    protected $config;

    /**
     * @param $config
     * @param UserRepositoryInterface $userRepository
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     * @throws ConfigNotFoundException
     */
    public function __construct(
        $config,
        UserRepositoryInterface $userRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository
    ) {
        if (!isset($config[$this->getIdentifier()])) {
            throw new ConfigNotFoundException('Config array not found in: userprovider.' . $this->getIdentifier(), 500);
        } 

        $this->config = $config[$this->getIdentifier()];
        $this->setUserRepository($userRepository);
        $this->setRefreshTokenRepository($refreshTokenRepository);
        $this->refreshTokenTTL = new \DateInterval('P1M');
    }
    /**
     * {@inheritdoc}
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        \DateInterval $accessTokenTTL
    ) {
        $scope = $this->getRequestParameter('scope', $request);
        // Validate request
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($scope);
        $user = $this->validateUser($request);
        // Finalize the requested scopes
        // $scopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client, $user->getIdentifier());
        // Issue and persist new tokens
        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $user->getIdentifier(), $scopes);
        $refreshToken = $this->issueRefreshToken($accessToken);
        // Inject tokens into response
        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);
        return $responseType;
    }
    /**
     * {@inheritdoc}
     */
    abstract  public function getIdentifier();
    /**
     * @param ServerRequestInterface $request
     *
     * @throws OAuthServerException
     *
     * @return UserEntityInterface
     */
    protected function validateUser(ServerRequestInterface $request)
    {
        $laravelRequest = new Request($request->getParsedBody());
        $user = $this->getUser($laravelRequest);
        if ($user instanceof UserEntityInterface === false) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::USER_AUTHENTICATION_FAILED, $request));
            throw OAuthServerException::invalidCredentials();
        }
        return $user;
    }

    /**
     * Retrieve user by request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \League\OAuth2\Server\Exception\OAuthServerException
     *
     * @return \Laravel\Passport\Bridge\User|null
     */
    protected function getUser(Request $request)
    {
        $controllerClass = $this->config['controller'];
        $method     = $this->config['method'];

        if (!class_exists($controllerClass)) {
            throw OAuthServerException::serverError("Unable to find  $controllerClass class.");
        } else {
            $controller = app($controllerClass);
        }

        if (method_exists($controller, $method)) {
            $user = (new $controller())->$method($request);
        } else {
            throw OAuthServerException::serverError("Unable to find  $method method on $controllerClass.");
        }

        return ($user) ? new User($user->id) : null;
    }
}

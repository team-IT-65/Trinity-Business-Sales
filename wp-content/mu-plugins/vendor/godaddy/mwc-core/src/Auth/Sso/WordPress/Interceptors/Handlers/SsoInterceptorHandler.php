<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Interceptors\Handlers;

use Exception;
use GoDaddy\WordPress\MWC\Common\Auth\Exceptions\JwtAuthServiceException;
use GoDaddy\WordPress\MWC\Common\Auth\JWT\Contracts\TokenContract;
use GoDaddy\WordPress\MWC\Common\Auth\JWT\JwtAuthFactory;
use GoDaddy\WordPress\MWC\Common\Events\Events;
use GoDaddy\WordPress\MWC\Common\Exceptions\JwtDecoderException;
use GoDaddy\WordPress\MWC\Common\Exceptions\SentryException;
use GoDaddy\WordPress\MWC\Common\Exceptions\UserLogInException;
use GoDaddy\WordPress\MWC\Common\Exceptions\ValidationException;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Common\Http\Redirect;
use GoDaddy\WordPress\MWC\Common\Http\Url;
use GoDaddy\WordPress\MWC\Common\Models\User;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Contracts\CanCreateUserContract;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Events\SsoFailedEvent;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Events\SsoSucceededEvent;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Events\SsoUserCreatedEvent;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Exceptions\SsoFailedException;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Exceptions\UserCreateFailedException;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Exceptions\UserNotFoundException;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Interceptors\SsoInterceptor;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\Cache\CacheAuthJwt;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\Contracts\SsoTokenContract;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services\Contracts\UserAssociationStrategyContract;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services\UserAssociationStrategyFactory;
use GoDaddy\WordPress\MWC\Core\Interceptors\Handlers\AbstractInterceptorHandler;
use ReflectionClass;

/**
 * Handler for {@see SsoInterceptor}.
 */
class SsoInterceptorHandler extends AbstractInterceptorHandler
{
    /**
     * Checks if the query args are set and attempts to sign the user on.
     *
     * @return void
     */
    public function run(...$args)
    {
        $jwt = TypeHelper::string(ArrayHelper::get($_GET, 'sso'), '');

        if (empty($jwt)) {
            // no query arg provided, bail
            return;
        }

        try {
            $ssoType = TypeHelper::string(ArrayHelper::get($_GET, 'ssotypeid'), '');
            if (empty($ssoType)) {
                throw new JwtAuthServiceException('SSO type query argument not provided.');
            }

            $token = $this->getDecodedToken($jwt, $ssoType);

            if (! $token instanceof SsoTokenContract) {
                throw new JwtAuthServiceException('Token is not an SsoTokenContract.');
            }

            $this->handleValidToken($token);
        } catch (Exception $exception) {
            $this->handleError($exception);
        }
    }

    /**
     * Gets the decoded token from the encoded JWT.
     *
     * @param string $jwt
     * @param string $ssoType
     * @return TokenContract
     * @throws JwtAuthServiceException|ValidationException|JwtDecoderException
     */
    protected function getDecodedToken(string $jwt, string $ssoType) : TokenContract
    {
        return JwtAuthFactory::getNewInstance()
            ->getServiceByType($ssoType)
            ->decodeToken($jwt);
    }

    /**
     * Creates the user, logs any failures, and dies if the account could not be created.
     *
     * @param CanCreateUserContract $strategy
     * @return User|void exits on failure
     */
    protected function createUserOrDie(CanCreateUserContract $strategy)
    {
        try {
            $user = $strategy->createUser();

            Events::broadcast(SsoUserCreatedEvent::getNewInstance($user));

            return $user;
        } catch (UserCreateFailedException $exception) {
            $this->triggerSsoException($exception);

            wp_die(__('Something went wrong when creating the user account.', 'mwc-core'));
        }
    }

    /**
     * Handles a valid token.
     *
     * Signs the user on and removes the query args.
     *
     * @param SsoTokenContract $token
     * @return void
     * @throws UserLogInException|Exception
     */
    protected function handleValidToken(SsoTokenContract $token) : void
    {
        $strategy = UserAssociationStrategyFactory::getNewInstance()->getAssociationStrategy($token);

        try {
            // cache the valid JWT so that it cannot be used again.
            CacheAuthJwt::getNewInstance($token->getJti())->set(true);

            // tries to get an existing user first
            $user = $strategy->getLocalUser();
        } catch(UserNotFoundException $exception) {
            // as fallback, tries to create a new one
            $user = $this->maybeCreateUser($strategy);
        } catch(Exception $exception) {
            /* translators: Placeholder: %s account username */
            throw new UserLogInException(sprintf(__('User %s could not be logged in.', 'mwc-core'), $token->getUsername()), $exception);
        }

        $this->logInAndRedirectUser($user);
    }

    /**
     * Logs in and redirects the Care Agent user after handling a token successfully.
     *
     * @see SsoInterceptorHandler::handleValidToken()
     *
     * @param User $user
     * @return void
     * @throws UserLogInException|Exception
     */
    protected function logInAndRedirectUser(User $user)
    {
        if (! $user->isLoggedIn()) {
            $user->logIn();
        }

        Events::broadcast(SsoSucceededEvent::getNewInstance());

        $redirectUrl = Url::fromString($_SERVER['REQUEST_URI'])
            ->removeQueryParameters(['sso', 'ssotypeid'])
            ->toString();

        Redirect::to($redirectUrl)->execute();
    }

    /**
     * Maybe creates a user from a strategy implementing {@see CanCreateUserContract} when successfully handling a token.
     *
     * @see SsoInterceptorHandler::handleValidToken()
     * @see SsoInterceptorHandler::createUserOrDie()
     *
     * @param UserAssociationStrategyContract $strategy
     * @return User
     * @throws UserCreateFailedException|UserNotFoundException
     */
    protected function maybeCreateUser(UserAssociationStrategyContract $strategy) : User
    {
        if (! $strategy instanceof CanCreateUserContract) {
            throw new UserNotFoundException(sprintf('User not found and %s strategy does not implement %s.', get_class($strategy), CanCreateUserContract::class));
        }

        $user = $this->createUserOrDie($strategy);

        if (! $user instanceof User) {
            throw new UserCreateFailedException(sprintf('User not found and %s strategy did not return a user.', get_class($strategy)));
        }

        return $user;
    }

    /**
     * Handles an error.
     *
     * Redirects to the WordPress login page.
     *
     * @param Exception $exception
     * @return void
     */
    protected function handleError(Exception $exception) : void
    {
        $this->triggerSsoException($exception);

        // remove the query args
        $redirectTo = remove_query_arg(['sso', 'ssotypeid']);

        // set this so that WP does not add the sso query args back when redirecting
        $_SERVER['REQUEST_URI'] = $redirectTo;

        try {
            // redirect to login page
            Redirect::to(wp_login_url(home_url($redirectTo)))->execute();
        } catch (SentryException $exception) {
            // the error will be automatically reported to sentry
        } catch (Exception $exception) {
            new SentryException('Failed to redirect to log in page', $exception);
        }
    }

    /**
     * Triggers a new {@see SsoFailedException}, which will report the failure to Sentry.
     * Also dispatches an {@see SsoFailedEvent}.
     *
     * @param Exception $exception
     * @return void
     */
    protected function triggerSsoException(Exception $exception) : void
    {
        // We don't actually _throw_ the exception because we're in a hook callback and want to avoid fatal errors.
        SsoFailedException::getNewInstance(
            $exception->getMessage(),
            TypeHelper::int($exception->getCode() ?: 500, 500),
            $exception
        );

        $failureReason = (new ReflectionClass($exception))->getShortName();

        Events::broadcast(
            SsoFailedEvent::getNewInstance($failureReason)
        );
    }
}

<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\SanitizationHelper;
use GoDaddy\WordPress\MWC\Common\Http\Redirect;
use GoDaddy\WordPress\MWC\Common\Http\Url;
use GoDaddy\WordPress\MWC\Common\Http\Url\Exceptions\InvalidUrlException;
use GoDaddy\WordPress\MWC\Common\Http\Url\Exceptions\InvalidUrlSchemeException;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Common\Repositories\WordPress\SiteRepository;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Exceptions\RedirectToShippingDashboardFailedException;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Contracts\ShippingAccountServiceContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\ShippingAccountService;
use GoDaddy\WordPress\MWC\Shipping\Exceptions\Contracts\ShippingExceptionContract;
use GoDaddy\WordPress\MWC\Shipping\Models\Account\Statuses\ConnectedStatus;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\AccountContract;
use GoDaddy\WordPress\MWC\Shipping\Operations\GetDashboardUrlOperation;
use Throwable;

class RedirectToShippingDashboardInterceptor extends AbstractInterceptor
{
    protected const ACTION_REDIRECT_TO_SHIPPING_DASHBOARD = 'mwc_redirect_to_shipping_dashboard';

    /** @var ?ShippingAccountService */
    protected $shippingAccountService;

    /**
     * Registers the action and filter hooks for this interceptor.
     *
     * @throws Exception
     */
    public function addHooks() : void
    {
        Register::action()
            ->setGroup('admin_action_'.static::ACTION_REDIRECT_TO_SHIPPING_DASHBOARD)
            ->setHandler([$this, 'maybeRedirectToShippingDashboard'])
            ->execute();
    }

    /**
     * Handler for the mwc_redirect_to_shipping_dashboard admin action.
     *
     * @internal
     *
     * @return void
     */
    public function maybeRedirectToShippingDashboard() : void
    {
        try {
            if (! current_user_can('manage_woocommerce')) {
                throw new Exception('You are not authorized.');
            }

            $this->redirectToShippingDashboard($this->getConnectedAccount());
        } catch (ShippingExceptionContract|Exception $exception) {
            /* {@see RedirectToShippingDashboardFailedException} will be reported to Sentry automatically */
            $this->redirectBackToReferer(
                new RedirectToShippingDashboardFailedException($exception->getMessage(), $exception)
            );
        }
    }

    /**
     * Gets the shipping account for this site.
     *
     * If the account is not connected yet, the method will attempt to connect first.
     *
     * @return AccountContract
     * @throws ShippingExceptionContract
     */
    protected function getConnectedAccount() : AccountContract
    {
        $account = $this->getShippingAccountService()->getAccount();

        if (! $account->getStatus() instanceof ConnectedStatus) {
            $account = $this->getShippingAccountService()->connectAccount($account);
        }

        return $account;
    }

    /**
     * Gets an instance of {@see ShippingAccountServiceContract}.
     *
     * @return ShippingAccountServiceContract
     */
    protected function getShippingAccountService() : ShippingAccountServiceContract
    {
        if (! $this->shippingAccountService) {
            $this->shippingAccountService = ShippingAccountService::getNewInstance();
        }

        return $this->shippingAccountService;
    }

    /**
     * Redirects to the URL of the Shipping Dashboard of the given account.
     *
     * @param AccountContract $account
     * @return void
     * @throws InvalidUrlException
     * @throws InvalidUrlSchemeException
     * @throws ShippingExceptionContract
     * @throws Exception
     */
    protected function redirectToShippingDashboard(AccountContract $account) : void
    {
        Redirect::to($this->buildDashboardUrl($account))->setSafe(false)->execute();
    }

    /**
     * Builds a URL for the Shipping Dashboard of the given account.
     *
     * @param AccountContract $account
     * @return string
     * @throws InvalidUrlException
     * @throws InvalidUrlSchemeException
     * @throws ShippingExceptionContract
     */
    protected function buildDashboardUrl(AccountContract $account) : string
    {
        return Url::fromString($this->requestDashboardUrl($account))
            ->addQueryParameter('brand', $this->getBrandName($account))
            ->toString();
    }

    /**
     * Issues a request to get the URL for the Shipping Dashboard of the given account.
     *
     * @param AccountContract $account
     * @return string
     * @throws ShippingExceptionContract
     */
    protected function requestDashboardUrl(AccountContract $account) : string
    {
        $operation = $this->getShippingAccountService()->getDashboardUrl(
            (new GetDashboardUrlOperation())
                ->setAccount($account)
                ->setReturnUrl($this->getReturnUrl())
        );

        return $operation->getDashboardUrl();
    }

    /**
     * Gets the brand name that should be included as a parameter of the Shipping Dashboard URL.
     *
     * @param AccountContract $account
     * @return string
     */
    protected function getBrandName(AccountContract $account) : string
    {
        return _x('GoDaddy', 'GoDaddy name sent as brand name for the ShipEngine integration', 'mwc-core');
    }

    /**
     * Gets the return URL specified as a query parameter.
     *
     * If no return URL is given, we will use the URL of the Shipping Labels settings admin page as fallback.
     *
     * @return string
     */
    protected function getReturnUrl() : string
    {
        $input = SanitizationHelper::input(ArrayHelper::get($_GET, 'return_url', ''));

        if (empty($input)) {
            return $this->getSettingsUrl();
        }

        try {
            $siteUrl = Url::fromString(SiteRepository::getSiteUrl());
            $returnUrl = Url::fromString($input);

            if (empty($returnUrl->getHost())) {
                $returnUrl->setHost($siteUrl->getHost());
                $returnUrl->setScheme($siteUrl->getScheme());
            }

            if ($siteUrl->getHost() !== $returnUrl->getHost()) {
                return $this->getSettingsUrl();
            }

            return (string) $returnUrl;
        } catch (InvalidUrlException|InvalidUrlSchemeException $e) {
            return $this->getSettingsUrl();
        }
    }

    /**
     * Gets the URL for the Shipping Labels settings admin page.
     *
     * @return string
     */
    protected function getSettingsUrl() : string
    {
        return admin_url('/admin.php?page=wc-settings&tab=shipping&section=labels');
    }

    /**
     * Redirects users back to the page where the request initiated.
     *
     * @return void
     */
    protected function redirectBackToReferer(?Throwable $throwable = null) : void
    {
        try {
            Redirect::to($this->getRefererUrlWithErrorMessage($throwable))->execute();
        } catch (Exception $exception) {
            // if everything fails, a wp_die() error is better than a white screen -- {wvega 2022-08-01}
            wp_die($exception->getMessage());
        }
    }

    /**
     * Gets the referrer URL for this request including an error_message parameter.
     *
     * @param Throwable $throwable
     * @return string
     */
    protected function getRefererUrlWithErrorMessage(?Throwable $throwable) : string
    {
        $refererUrl = $this->getRefererUrl();

        if (! $throwable) {
            return $refererUrl;
        }

        try {
            $url = Url::fromString($refererUrl);
        } catch (InvalidUrlSchemeException|InvalidUrlException $e) {
            return $refererUrl;
        }

        return $url->addQueryParameter('error_message', $throwable->getMessage())->toString();
    }

    /**
     * Gets the referrer URL for this request.
     *
     * If a request URL cannot be retrieved, we will use the URL of the Shipping Labels settings admin page as fallback.
     *
     * @return string
     */
    protected function getRefererUrl() : string
    {
        return wp_get_referer() ?: $this->getSettingsUrl();
    }
}

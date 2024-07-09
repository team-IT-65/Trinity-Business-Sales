<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Stripe\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Events\Events;
use GoDaddy\WordPress\MWC\Common\Exceptions\SentryException;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\SanitizationHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\StringHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Common\Http\GoDaddyRequest;
use GoDaddy\WordPress\MWC\Common\Http\Redirect;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Models\User;
use GoDaddy\WordPress\MWC\Common\Platforms\PlatformRepositoryFactory;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Common\Repositories\ManagedWooCommerceRepository;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerce\OrdersRepository;
use GoDaddy\WordPress\MWC\Common\Repositories\WordPress\SiteRepository;
use GoDaddy\WordPress\MWC\Core\Auth\Providers\Platform\Cache\Types\ErrorResponseCache;
use GoDaddy\WordPress\MWC\Core\Events\ButtonClickedEvent;
use GoDaddy\WordPress\MWC\Core\Features\Stripe\Exceptions\FailedOnboardingFinishException;
use GoDaddy\WordPress\MWC\Core\Features\Stripe\Exceptions\FailedOnboardingStartException;
use GoDaddy\WordPress\MWC\Core\Features\Stripe\Exceptions\FailedOnboardingWebhookException;
use GoDaddy\WordPress\MWC\Core\Features\Stripe\Stripe as StripeFeature;
use GoDaddy\WordPress\MWC\Core\Payments\DataStores\WooCommerce\OrderPaymentTransactionDataStore;
use GoDaddy\WordPress\MWC\Core\Payments\DataStores\WooCommerce\PaymentMethodDataStore;
use GoDaddy\WordPress\MWC\Core\Payments\Events\ProviderAccountOnboardingRedirectEvent;
use GoDaddy\WordPress\MWC\Core\Payments\Exceptions\InvalidPaymentMethodException;
use GoDaddy\WordPress\MWC\Core\Payments\Exceptions\MissingPaymentIntentException;
use GoDaddy\WordPress\MWC\Core\Payments\Exceptions\MissingPaymentMethodException;
use GoDaddy\WordPress\MWC\Core\Payments\Models\Transactions\PaymentTransaction;
use GoDaddy\WordPress\MWC\Core\Payments\Stripe;
use GoDaddy\WordPress\MWC\Core\Payments\Stripe\Adapters\TransactionPaymentIntentAdapter;
use GoDaddy\WordPress\MWC\Core\Payments\Stripe\DataStores\WooCommerce\SessionPaymentIntentDataStore;
use GoDaddy\WordPress\MWC\Core\Payments\Stripe\Exceptions\MissingSetupIntentException;
use GoDaddy\WordPress\MWC\Core\Payments\Stripe\Gateways\PaymentIntentGateway;
use GoDaddy\WordPress\MWC\Core\Payments\Stripe\Gateways\SetupIntentGateway;
use GoDaddy\WordPress\MWC\Core\Payments\Stripe\Models\SetupIntent;
use GoDaddy\WordPress\MWC\Core\Payments\Stripe\Onboarding;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Adapters\OrderAdapter;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Exceptions\ConnectionFailedException;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Exceptions\FailedTransactionException;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Exceptions\InvalidNonceException;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Exceptions\InvalidPermissionsException;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Exceptions\InvalidSignatureException;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Exceptions\MissingRequestBodyException;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Exceptions\MissingSignatureException;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Models\Orders\Order;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Payments\CorePaymentGateways;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Payments\Events\PaymentGatewayConnectedEvent;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Payments\Events\PaymentGatewayEnabledEvent;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Payments\Integrations\Contracts\IntegrationContract;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Payments\Integrations\SubscriptionsIntegration;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Payments\Stripe\Frontend\PaymentForm;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Payments\StripeGateway;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\OrderNotFoundException;
use GoDaddy\WordPress\MWC\Payments\Events\PaymentTransactionEvent;
use GoDaddy\WordPress\MWC\Payments\Models\PaymentMethods\AbstractPaymentMethod;
use GoDaddy\WordPress\MWC\Payments\Models\Transactions\Statuses\ApprovedTransactionStatus;
use GoDaddy\WordPress\MWC\Payments\Models\Transactions\Statuses\HeldTransactionStatus;
use WC_Order;
use WC_Payment_Gateway;
use WC_Subscriptions_Change_Payment_Gateway;

class RedirectInterceptor extends AbstractInterceptor
{
    /**
     * Adds hooks.
     *
     * @return void
     * @throws Exception
     */
    public function addHooks()
    {
        // handle starting the Stripe onboarding
        Register::action()
            ->setGroup('admin_action_'.Onboarding::ACTION_START)
            ->setHandler([$this, 'handleOnboardingStart'])
            ->execute();

        // handle the redirect back from the MWC API after onboarding
        Register::action()
            ->setGroup('woocommerce_api_'.Onboarding::ACTION_FINISH)
            ->setHandler([$this, 'handleOnboardingFinish'])
            ->execute();

        // handle the onboarding webhook request
        Register::action()
            ->setGroup('woocommerce_api_mwc_oauth_connection_webhook')
            ->setHandler([$this, 'handleOnboardingWebhook'])
            ->execute();

        // handle starting the Stripe disconnection
        Register::action()
                ->setGroup('admin_action_'.Onboarding::ACTION_DISCONNECT)
                ->setHandler([$this, 'handleDisconnect'])
                ->execute();

        // set the current session's cookie when a guest customer is logged in during checkout
        Register::action()
            ->setGroup('woocommerce_checkout_process')
            ->setHandler([$this, 'queueSetSessionCookie'])
            ->execute();

        // modify the redirect URL after a Stripe payment is complete
        Register::action()
                ->setGroup('mwc_payments_stripe_after_process_payment')
                ->setHandler([$this, 'modifyRedirectUrl'])
                ->setArgumentsCount(3)
                ->execute();

        // handle payment completion
        Register::action()
                ->setGroup('woocommerce_api_mwc_payments_stripe_complete_payment')
                ->setHandler([$this, 'completePayment'])
                ->execute();

        // handle payment setup completion
        Register::action()
            ->setGroup('woocommerce_api_mwc_payments_stripe_complete_setup')
            ->setHandler([$this, 'completePaymentSetup'])
            ->execute();
    }

    /**
     * Determines whether the component should be loaded or not.
     *
     * @return bool
     */
    public static function shouldLoad() : bool
    {
        return StripeFeature::shouldLoad();
    }

    /**
     * Handles starting the Stripe onboarding.
     */
    public function handleOnboardingStart() : void
    {
        ErrorResponseCache::getInstance()->clear();

        Events::broadcast(new ButtonClickedEvent('stripe_setup'));

        try {
            check_admin_referer(Onboarding::ACTION_START);

            if (! current_user_can('manage_woocommerce')) {
                throw new InvalidPermissionsException(__('User does not have permission to manage WooCommerce', 'mwc-core'));
            }

            $request = GoDaddyRequest::withAuth()
                ->setUrl(StringHelper::trailingSlash(ManagedWooCommerceRepository::getApiUrl()).'oauth/stripe/start')
                ->setBody([
                    'siteId'        => PlatformRepositoryFactory::getNewInstance()->getPlatformRepository()->getPlatformSiteId(),
                    'siteUrl'       => SiteRepository::getHomeUrl(),
                    'webhookSecret' => Onboarding::getWebhookSecret(),
                ])->setMethod('POST');

            $response = $request->send();

            if ($response->isError()) {
                throw new ConnectionFailedException(sprintf(
                    /* translators: Placeholders: %s - error message from the MWC API */
                    __('Could not create connection: %s', 'mwc-core'),
                    $response->getErrorMessage() ?? 'Unknown error'
                ));
            }

            if (! $redirectUri = ArrayHelper::get($response->getBody(), 'redirectUri')) {
                throw new ConnectionFailedException(__('No redirect available', 'mwc-core'));
            }

            Onboarding::setStatus('');

            Events::broadcast(new ProviderAccountOnboardingRedirectEvent('stripe'));

            Redirect::to(Onboarding::getConnectionUrl($redirectUri))
                ->setSafe(false)
                ->execute();
        } catch (Exception $exception) {
            new FailedOnboardingStartException($exception->getMessage());
            wp_die($exception->getMessage());
        }
    }

    /**
     * Handles the redirect back from the MWC API after onboarding.
     */
    public function handleOnboardingFinish()
    {
        if (ArrayHelper::get($_GET, 'serviceName', '') !== 'stripe') {
            return;
        }

        try {
            $state = json_decode(stripslashes(TypeHelper::string(ArrayHelper::get($_GET, 'state'), '')), true);

            if (! wp_verify_nonce(ArrayHelper::get($state, 'nonce', ''), Onboarding::ACTION_FINISH)) {
                throw new InvalidNonceException('Invalid nonce');
            }

            if ($status = Onboarding::getStatus() === Onboarding::STATUS_CONNECTED) {
                $this->broadcastPaymentGatewayEnabledEvent();
                $this->maybeAutoEnablePaymentGateway();
            }

            if (! $status) {
                Onboarding::setStatus(Onboarding::STATUS_PENDING);
            }
        } catch (Exception $exception) {
        }

        try {
            Redirect::to(add_query_arg([
                'page'    => 'wc-settings',
                'tab'     => 'checkout',
                'section' => 'stripe',
            ], admin_url('admin.php')))->execute();
        } catch (Exception $exception) {
            // we shouldn't be throwing an exception at this point since we are in a WordPress hook callback context
            new FailedOnboardingFinishException($exception->getMessage());
        }
    }

    /**
     * Broadcasts the payment gateway enabled event.
     */
    protected function broadcastPaymentGatewayEnabledEvent() : void
    {
        Events::broadcast(new PaymentGatewayEnabledEvent('stripe'));
    }

    /**
     * Auto-Enables Stripe payment method if the onboarding process is completed.
     */
    protected function maybeAutoEnablePaymentGateway() : void
    {
        if (! $gateway = $this->getWooCommerceGateway('stripe')) {
            return;
        }

        $gateway->update_option('enabled', 'yes');
    }

    /**
     * Gets the Stripe gateway.
     *
     * @return WC_Payment_Gateway|null
     */
    protected function getWooCommerceGateway(string $id)
    {
        /* @phpstan-ignore-next-line */
        if (! $woocommerce = WC()) {
            return null;
        }

        /* @phpstan-ignore-next-line */
        if (! $gateways = $woocommerce->payment_gateways()) {
            return null;
        }

        return ArrayHelper::get($gateways->payment_gateways(), $id);
    }

    /**
     * Handles the onboarding webhook request.
     *
     * @internal
     */
    public function handleOnboardingWebhook()
    {
        $payload = $this->getWebhookPayload();
        $payloadData = json_decode($payload, true);

        // only handle requests for the Stripe OAuth service
        if ('stripe' !== ArrayHelper::get($payloadData, 'serviceName')) {
            return;
        }

        try {
            $this->validateWebhookSignature($payload);

            if ($accountId = SanitizationHelper::input(ArrayHelper::get($payloadData, 'externalAccountId'))) {
                Stripe::setAccountId($accountId);
            }

            if ($publicKey = SanitizationHelper::input(ArrayHelper::get($payloadData, 'publicKey'))) {
                Stripe::setApiPublicKey($publicKey);
            }

            if ($secretKey = SanitizationHelper::input(ArrayHelper::get($payloadData, 'accessToken'))) {
                Stripe::setApiSecretKey($secretKey);
            }

            Events::broadcast(new PaymentGatewayConnectedEvent('stripe'));

            Onboarding::setStatus(Onboarding::STATUS_CONNECTED);
            $this->broadcastPaymentGatewayEnabledEvent();
            $this->maybeAutoEnablePaymentGateway();

            status_header(200);
        } catch (Exception $exception) {
            status_header($exception->getCode());

            new FailedOnboardingWebhookException($exception->getMessage());

            echo $exception->getMessage();
        }
    }

    /**
     * Gets the webhook payload from the request.
     *
     * @return array|null
     */
    protected function getWebhookPayload() : ?string
    {
        return file_get_contents('php://input');
    }

    /**
     * Validates the webhook request signature.
     *
     * @param mixed $payload
     *
     * @throws InvalidSignatureException|MissingRequestBodyException|MissingSignatureException
     */
    protected function validateWebhookSignature($payload)
    {
        if (empty($payload)) {
            throw new MissingRequestBodyException('Missing request body');
        }

        if (! $signature = $_SERVER['HTTP_MWC_WEBHOOK_SIGNATURE']) {
            throw new MissingSignatureException('Missing signature');
        }

        $secret = Onboarding::getWebhookSecret();
        $hash = hash_hmac('sha512', $payload, $secret);

        if (! hash_equals($signature, $hash)) {
            throw new InvalidSignatureException('Invalid signature');
        }
    }

    /**
     * Handles the disconnection from Stripe and redirects back to settings page.
     *
     * @throws Exception
     */
    public function handleDisconnect()
    {
        try {
            check_admin_referer(Onboarding::ACTION_DISCONNECT);

            if (! current_user_can('manage_woocommerce')) {
                throw new InvalidPermissionsException(__('User does not have permission to manage WooCommerce', 'mwc-core'));
            }

            Stripe::setAccountId('');
            Stripe::setApiPublicKey('');
            Stripe::setApiSecretKey('');

            Onboarding::setStatus(Onboarding::STATUS_DISCONNECTED);

            Redirect::to(add_query_arg([
                'page'    => 'wc-settings',
                'tab'     => 'checkout',
                'section' => 'stripe',
            ], admin_url('admin.php')))->execute();
        } catch (Exception $exception) {
            wp_die($exception->getMessage());
        }
    }

    /**
     * Queues setting the session cookie on checking processing.
     *
     * @internal
     *
     * @throws Exception
     */
    public function queueSetSessionCookie() : void
    {
        Register::action()
            ->setGroup('set_logged_in_cookie')
            ->setHandler([$this, 'setSessionCookie'])
            ->execute();
    }

    /**
     * Sets the current session's cookie when a guest customer is logged in during checkout.
     *
     * This ensures the redirect nonce that's generated is valid for the same user after they've logged in. The action
     * is only registered during the woocommerce_checkout_process action to ensure we don't alter any regular WordPress
     * cookie behavior outside the checkout flow.
     *
     * @internal
     *
     * @param mixed $cookie
     */
    public function setSessionCookie($cookie) : void
    {
        $_COOKIE[LOGGED_IN_COOKIE] = $cookie;
    }

    /**
     * Modifies the redirect URL after a Stripe payment is complete.
     *
     * @param mixed $result
     * @param WC_Order $wooOrder
     * @param PaymentTransaction $transaction
     * @return mixed
     * @throws Exception
     */
    public function modifyRedirectUrl($result, WC_Order $wooOrder, PaymentTransaction $transaction)
    {
        if ('success' !== ArrayHelper::get($result, 'result')) {
            return $result;
        }

        // only pass the completion URL if not using a saved payment method
        if ((! $paymentMethod = $transaction->getPaymentMethod()) || ! $paymentMethod->getRemoteId()) {
            ArrayHelper::set($result, 'redirect', PaymentForm::getPaymentRedirectUrl($transaction));
        }

        /** @var Order $order */
        if ($order = $transaction->getOrder()) {
            ArrayHelper::set($result, 'billingDetails', PaymentForm::getOrderBillingDetails($order));
        }

        return $result;
    }

    /**
     * Handles completing the payment via Stripe.
     *
     * @return void
     * @throws OrderNotFoundException
     */
    public function completePayment() : void
    {
        if (! ($wooOrder = OrdersRepository::get((int) ArrayHelper::get($_GET, 'orderId')))) {
            throw new OrderNotFoundException('Order not found');
        }

        try {
            if (! wp_verify_nonce(ArrayHelper::get($_GET, '_wpnonce', ''), 'mwc_payments_stripe_complete_payment')) {
                throw new InvalidNonceException('Invalid nonce');
            }

            if (! ($paymentIntentId = ArrayHelper::get($_GET, 'payment_intent'))) {
                throw new MissingPaymentIntentException('Payment Intent is missing');
            }

            $transactionDataStore = OrderPaymentTransactionDataStore::getNewInstance('stripe');
            $storedTransaction = $transactionDataStore->read($wooOrder->get_id(), 'payment');

            if ($storedTransaction->getRemoteId() !== $paymentIntentId) {
                throw new MissingPaymentIntentException('Payment Intent does not belong to this order');
            }

            // this will throw if the payment intent is not found
            $paymentIntent = PaymentIntentGateway::getNewInstance()->get($paymentIntentId);
            $transaction = TransactionPaymentIntentAdapter::getNewInstance($storedTransaction)->convertToSource($paymentIntent);

            if ($paymentIntent->getStatus() === 'requires_capture') {
                $transaction->setAuthOnly(true);
            }

            if (ArrayHelper::get($_GET, 'shouldTokenize', false) && $paymentMethod = $transaction->getPaymentMethod()) {
                $transaction->setPaymentMethod($this->handlePaymentMethod($paymentMethod, $wooOrder->get_customer_id()));
            }

            $transaction->setOrder(OrderAdapter::getNewInstance($wooOrder)->convertFromSource());

            $transactionDataStore->save($transaction);

            // ensure subscribers are notified
            Events::broadcast(new PaymentTransactionEvent($transaction));

            if (! $stripeGateway = CorePaymentGateways::getManagedPaymentGatewayInstance('stripe')) {
                throw new InvalidPaymentMethodException('Stripe payment gateway not found');
            }

            $stripeGateway->processPaymentResult($transaction, $wooOrder);

            $this->maybeHandleIntegrations($stripeGateway->getIntegrations(), $wooOrder);

            // clear payment intent from session to ensure a fresh payment intent is created for future carts/orders
            SessionPaymentIntentDataStore::getNewInstance()->delete($paymentIntent);

            wp_redirect($stripeGateway->get_return_url($wooOrder));
        } catch (InvalidNonceException | MissingPaymentIntentException $exception) {
            wc_add_notice(__('An error occurred, please try again or try an alternate form of payment.', 'mwc-core'), 'error');

            wp_redirect(wc_get_checkout_url());
        } catch (FailedTransactionException $exception) {
            wp_redirect($wooOrder->get_checkout_payment_url());
        } catch (Exception $exception) {
            SentryException::getNewInstance($exception->getMessage(), $exception);

            $wooOrder->add_order_note(__('An error occurred while processing the payment: '.$exception->getMessage(), 'mwc-core'));

            wc_add_notice(__('An error occurred, please try again or try an alternate form of payment.', 'mwc-core'), 'error');
            wp_redirect($wooOrder->get_checkout_payment_url());
        }
    }

    /**
     * Handles completing the payment setup via Stripe.
     *
     * @throws Exception
     */
    public function completePaymentSetup() : void
    {
        try {
            if (! wp_verify_nonce(ArrayHelper::get($_GET, '_wpnonce', ''), 'mwc_payments_stripe_complete_setup')) {
                throw new InvalidNonceException('Invalid nonce');
            }

            if (! ($setupIntentId = ArrayHelper::get($_GET, 'setup_intent'))) {
                throw new MissingSetupIntentException('Setup Intent is missing');
            }

            // this will throw if the setup intent is not found
            $setupIntent = SetupIntentGateway::getNewInstance()->get($setupIntentId);

            // if this setup is for an order, route it differently
            if ($wooOrder = OrdersRepository::get((int) ArrayHelper::get($_GET, 'orderId'))) {
                $this->handleOrderSetup($wooOrder, $setupIntent);

                return;
            }

            if (! $paymentMethod = $setupIntent->getPaymentMethod()) {
                throw new MissingPaymentMethodException('No payment method available for the setup intent.');
            }

            $currentUser = User::getCurrent();

            $this->handlePaymentMethod($paymentMethod, $currentUser ? (int) $currentUser->getId() : 0);

            wc_add_notice(__('Payment method successfully added.', 'mwc-core'));
        } catch (Exception $exception) {
            wc_add_notice(__('Unable to add payment method to your account. Please try again.', 'mwc-core'), 'error');
        }

        Redirect::to(wc_get_account_endpoint_url('payment-methods'))
            ->setSafe(true)
            ->execute();
    }

    /**
     * Handles an order when processing a setup intent.
     *
     * @param WC_Order $wooOrder
     * @param SetupIntent $setupIntent
     *
     * @throws Exception
     */
    protected function handleOrderSetup(WC_Order $wooOrder, SetupIntent $setupIntent) : void
    {
        /** @var StripeGateway $paymentGateway */
        $paymentGateway = CorePaymentGateways::getManagedPaymentGatewayInstance('stripe');
        $transaction = PaymentTransaction::getNewInstance();

        try {
            $transaction->setOrder(OrderAdapter::getNewInstance($wooOrder)->convertFromSource());

            if (! $paymentMethod = $setupIntent->getPaymentMethod()) {
                throw new MissingPaymentMethodException('No payment method available from the setup intent.');
            }

            $paymentMethod = $this->handlePaymentMethod($paymentMethod, $wooOrder->get_customer_id());

            $transaction->setPaymentMethod($paymentMethod)
                ->setStatus(new ApprovedTransactionStatus());
        } catch (Exception $exception) {
            $transaction->setStatus(new HeldTransactionStatus());

            $wooOrder->add_order_note(sprintf(
                __('Payment method could not be saved. %s', 'mwc-core'),
                $exception->getMessage()
            ));
        } finally {
            OrderPaymentTransactionDataStore::getNewInstance('stripe')->save($transaction);

            $paymentGateway->processPaymentResult($transaction, $wooOrder);

            $this->maybeHandleIntegrations($paymentGateway->getIntegrations(), $wooOrder);
        }

        Redirect::to($paymentGateway->get_return_url($wooOrder))
            ->setSafe(true)
            ->execute();
    }

    /**
     * Handles saving the payment method, if requested.
     *
     * @param AbstractPaymentMethod $paymentMethod
     * @param int $customerId
     *
     * @return AbstractPaymentMethod
     * @throws Exception
     */
    protected function handlePaymentMethod(AbstractPaymentMethod $paymentMethod, int $customerId) : AbstractPaymentMethod
    {
        $paymentMethod->setCustomerId((string) $customerId);
        $paymentMethod->setProviderName('stripe');

        return (new PaymentMethodDataStore('stripe'))->save($paymentMethod);
    }

    /**
     * Handles any integration actions.
     *
     * @param array<string, IntegrationContract> $integrations
     * @param WC_Order $wooOrder
     *
     * @throws Exception
     */
    protected function maybeHandleIntegrations(array $integrations, WC_Order $wooOrder) : void
    {
        if (class_exists('WC_Subscriptions') && $subscriptionsIntegration = ArrayHelper::get($integrations, 'subscriptions')) {
            /* @var SubscriptionsIntegration $subscriptionsIntegration */
            $subscriptionsIntegration->saveSubscriptionMetaData([], $wooOrder);

            // if changing the payment method, set some properties & redirect to the subscription page
            if (ArrayHelper::get($_GET, 'change_payment_method') && $subscription = wcs_get_subscription($wooOrder)) {
                WC_Subscriptions_Change_Payment_Gateway::update_payment_method($subscription, 'stripe');

                $subscription->set_requires_manual_renewal(false);
                $subscription->save();

                wc_add_notice(__('Payment method updated.', 'mwc-core'));

                Redirect::to($subscription->get_view_order_url())
                    ->setSafe(true)
                    ->execute();
            }
        }
    }
}

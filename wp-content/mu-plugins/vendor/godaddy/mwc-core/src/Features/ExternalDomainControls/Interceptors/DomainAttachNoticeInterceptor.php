<?php

namespace GoDaddy\WordPress\MWC\Core\Features\ExternalDomainControls\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Enqueue\Enqueue;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Common\Http\Url;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Platforms\PlatformRepositoryFactory;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Common\Repositories\WordPressRepository;

/**
 * Enqueues JavaScript for displaying domain attachment flow notices in Settings > General and Settings > Reading.
 */
class DomainAttachNoticeInterceptor extends AbstractInterceptor
{
    /**
     * {@inheritDoc}
     * @throws Exception
     */
    public function addHooks() : void
    {
        Register::action()
            ->setGroup('admin_enqueue_scripts')
            ->setHandler([$this, 'maybeEnqueueJs'])
            ->execute();
    }

    /**
     * Enqueues the JavaScript on the Settings > General and Settings > Reading pages.
     *
     * @internal
     *
     * @param string|mixed $hook
     * @return void
     * @throws Exception
     */
    public function maybeEnqueueJs($hook) : void
    {
        if (! in_array($hook, ['options-general.php', 'options-reading.php'], true)) {
            return;
        }

        Enqueue::script()
            ->setHandle('gd-domain-attach-notice')
            ->setSource(WordPressRepository::getAssetsUrl('js/domain-attach-notice.js'))
            ->setVersion(TypeHelper::string(Configuration::get('mwc.version'), '1.0'))
            ->setCondition([$this, 'shouldEnqueueJs'])
            ->attachInlineScriptObject('gdDomainAttachNotice')
            ->attachInlineScriptVariables($this->getVariables())
            ->setDeferred(true)
            ->execute();
    }

    /**
     * Determines whether we should enqueue the JS.
     *
     * @internal
     *
     * @return bool
     */
    public function shouldEnqueueJs() : bool
    {
        if (! Configuration::get('features.external_domain_controls.domainAttachFlow.showNotices')) {
            return false;
        }

        try {
            $platformRepository = PlatformRepositoryFactory::getNewInstance()->getPlatformRepository();

            return $platformRepository->isTemporaryDomain() && $platformRepository->getPlatformEnvironment()->isProduction();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Gets the inline script variables.
     *
     * @return array<string, string>
     */
    protected function getVariables() : array
    {
        $domainAttachUrl = $this->getAttachDomainUrl();

        return [
            'seoVisibilityText' => sprintf(
                /* translators: %1$s opening strong tag; %2$s closing strong tag; %3$s opening anchor tag; %4$s closing anchor tag; %5$s opening anchor tag; %6$s closing anchor tag */
                __('%1$sNote:%2$s Your site is using a temporary domain that cannot be indexed by search engines. Change your primary domain via the %3$sWoo store management area%4$s. Learn more about %5$sconnecting a domain%6$s.', 'mwc-core'),
                '<strong>',
                '</strong>',
                '<a href="'.esc_url($domainAttachUrl).'" target="_blank">',
                '</a>',
                '<a href="https://www.godaddy.com/help/a-41377" target="_blank">',
                '</a>'
            ),
            'siteAddressText' => sprintf(
                /* translators: %1$s opening anchor tag; %2$s closing anchor tag; %3$s opening anchor tag; %4$s closing anchor tag */
                __('Change your primary domain via the %1$sWoo store management area%2$s. Learn more about %3$sconnecting a domain%4$s.', 'mwc-core'),
                '<a href="'.esc_url($domainAttachUrl).'" target="_blank">',
                '</a>',
                '<a href="https://www.godaddy.com/help/a-41377" target="_blank">',
                '</a>'
            ),
        ];
    }

    /**
     * Gets the URL for the domain attach flow.
     *
     * @return string
     */
    protected function getAttachDomainUrl() : string
    {
        $url = TypeHelper::string(Configuration::get('features.external_domain_controls.domainAttachFlow.attachmentUrl'), '');

        try {
            $url = Url::fromString($url);
            $storeId = PlatformRepositoryFactory::getNewInstance()->getPlatformRepository()->getStoreRepository()->getStoreId();

            if ($storeId) {
                $url->addQueryParameter('storeId', $storeId);
            }

            return $url->toString();
        } catch (Exception $e) {
            // will return the base URL without any query args
            return $url;
        }
    }
}

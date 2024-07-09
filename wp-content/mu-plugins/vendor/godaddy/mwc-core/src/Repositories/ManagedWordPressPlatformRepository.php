<?php

namespace GoDaddy\WordPress\MWC\Core\Repositories;

use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\StringHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Common\HostingPlans\Enums\HostingPlanNamesEnum;
use GoDaddy\WordPress\MWC\Common\Models\Contracts\GoDaddyCustomerContract;
use GoDaddy\WordPress\MWC\Common\Models\Contracts\HostingPlanContract;
use GoDaddy\WordPress\MWC\Common\Models\GoDaddyCustomer;
use GoDaddy\WordPress\MWC\Common\Platforms\Contracts\PlatformEnvironmentContract;
use GoDaddy\WordPress\MWC\Common\Platforms\Repositories\AbstractPlatformRepository;
use GoDaddy\WordPress\MWC\Common\Repositories\WordPress\SiteRepository;
use GoDaddy\WordPress\MWC\Common\Repositories\WordPressRepository;
use GoDaddy\WordPress\MWC\Common\Stores\Contracts\StoreRepositoryContract;
use GoDaddy\WordPress\MWC\Core\HostingPlans\Builders\ManagedWordPress\HostingPlanBuilder;
use GoDaddy\WordPress\MWC\Core\Platforms\Builders\PlatformEnvironmentBuilder;
use GoDaddy\WordPress\MWC\Core\Stores\Repositories\StoreRepository;

/**
 * Managed WordPress platform repository class.
 */
class ManagedWordPressPlatformRepository extends AbstractPlatformRepository
{
    /**
     * {@inheritDoc}
     */
    public function getPlatformName() : string
    {
        return 'mwp';
    }

    /** {@inheritDoc} */
    public function getPlatformEnvironment() : PlatformEnvironmentContract
    {
        return PlatformEnvironmentBuilder::getNewInstance()->build();
    }

    /**
     * {@inheritDoc}
     */
    public function getPlatformSiteId() : string
    {
        $siteXId = ArrayHelper::exists($_SERVER, 'XID')
            ? (int) ArrayHelper::get($_SERVER, 'XID', 0)
            : (int) ArrayHelper::get($_SERVER, 'WPAAS_SITE_ID', 0);

        return $siteXId > 1000000 ? (string) $siteXId : '0';
    }

    /**
     * {@inheritDoc}
     */
    public function getSiteId() : string
    {
        $siteId = Configuration::get('godaddy.site.id', '');

        if (! is_string($siteId)) {
            $siteId = '';
        }

        if (empty($siteId) && ! empty($siteXid = $this->getPlatformSiteId())) {
            // use XID instead
            $siteId = $siteXid;

            // update configuration
            Configuration::set('godaddy.site.id', $siteId);

            if (WordPressRepository::hasWordPressInstance()) {
                update_option('gd_mwc_site_id', $siteId);
            }
        }

        return $siteId;
    }

    /**
     * Determines if the current site has an eCommerce plan.
     *
     * @return bool
     */
    public function hasEcommercePlan() : bool
    {
        return Configuration::get('godaddy.account.plan.name') === Configuration::get('mwc.plan_name') || $this->isEssentialsPlan();
    }

    /**
     * Determines if the current site is part of an essentials plan.
     *
     * @return bool
     */
    protected function isEssentialsPlan() : bool
    {
        return HostingPlanNamesEnum::isEssentialsPlan($this->getPlan()->getName());
    }

    /**
     * Gets the configured reseller account ID, if present.
     *
     * @return int|null
     */
    public function getResellerId() : ?int
    {
        $resellerId = Configuration::get('godaddy.reseller');

        return is_numeric($resellerId) ? (int) $resellerId : null;
    }

    /**
     * Determines if the current site is a staging site.
     *
     * @return bool
     */
    public function isStagingSite() : bool
    {
        return (bool) Configuration::get('godaddy.is_staging_site');
    }

    /**
     * Determines if we have the data that we expect a valid Managed WordPress site to have.
     *
     * @return bool
     */
    public function hasPlatformData() : bool
    {
        return (bool) Configuration::get('godaddy.account.uid');
    }

    /**
     * Returns an instance of {@see HostingPlanContract} for the Managed WordPress hosting plan used by this site.
     *
     * @return HostingPlanContract
     */
    public function getPlan() : HostingPlanContract
    {
        return HostingPlanBuilder::getNewInstance()->build();
    }

    /**
     * Gets the GoDaddy customer ID.
     *
     * @return string
     */
    public function getGoDaddyCustomerId() : string
    {
        return (string) Configuration::get('godaddy.customerId', '');
    }

    /**
     * {@inheritDoc}
     */
    public function getGoDaddyCustomer() : GoDaddyCustomerContract
    {
        return GoDaddyCustomer::seed(['id' => $this->getGoDaddyCustomerId()]);
    }

    /**
     * Gets the venture ID.
     *
     * @return string
     */
    public function getVentureId() : string
    {
        return (string) Configuration::get('godaddy.ventureId', '');
    }

    /**
     * Determines if the host represents a temporary domain.
     *
     * @return bool
     */
    public function isTemporaryDomain() : bool
    {
        $domain = Configuration::get('godaddy.temporary_domain');
        $homeUrl = parse_url(SiteRepository::getHomeUrl(), PHP_URL_HOST);

        return $this->hasPlatformData() && is_string($domain) && is_string($homeUrl) && StringHelper::trailingSlash($domain) === StringHelper::trailingSlash($homeUrl);
    }

    /**
     * Gets the store repository for the Managed WordPress platform.
     *
     * @return StoreRepositoryContract
     */
    public function getStoreRepository() : StoreRepositoryContract
    {
        return new StoreRepository();
    }

    /** {@inheritDoc} */
    public function getChannelId() : string
    {
        return TypeHelper::string(Configuration::get('godaddy.store.channelId', ''), '');
    }
}

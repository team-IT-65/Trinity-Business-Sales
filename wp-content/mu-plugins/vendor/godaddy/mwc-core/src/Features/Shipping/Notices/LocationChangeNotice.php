<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Notices;

use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Admin\Notices\Notice;

class LocationChangeNotice extends Notice
{
    use CanGetNewInstanceTrait;

    /** @var string the admin notice ID */
    protected $id = 'mwc_shipping_location_change';

    /** @var bool determines whether the notice is dismissible or not, defaults to true */
    protected $dismissible = true;

    /** @var string the notice's type, defaults to info */
    protected $type = self::TYPE_INFO;

    /** @var string[] the list of restricted user capabilities that can see this notice */
    protected $restrictedUserCapabilities = ['manage_woocommerce'];

    /**
     * Gets the notice title.
     *
     * @return string
     */
    public function getTitle() : ?string
    {
        if (! $this->title) {
            $this->title = esc_html__("ShipEngine isn't available in your region", 'mwc-core');
        }

        return $this->title;
    }

    /**
     * Gets the notice's content.
     *
     * @return string
     */
    public function getContent() : ?string
    {
        if (! $this->content) {
            $this->content = __('To benefit from streamlined shipping and label printing, please ensure your store is located in the US, UK, Canada, or Australia.', 'mwc-core');
        }

        return $this->content;
    }
}

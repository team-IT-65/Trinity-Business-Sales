<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Providers\Marketplaces\Webhook\Models;

use GoDaddy\WordPress\MWC\Common\Auth\Contracts\AuthCredentialsContract;
use GoDaddy\WordPress\MWC\Common\Models\AbstractModel;

/**
 * Credentials used to validate incoming webhook requests from GDM.
 */
class Credentials extends AbstractModel implements AuthCredentialsContract
{
    /** @var string channel ID */
    protected $channelId = '';

    /** @var string venture ID */
    protected $ventureId = '';

    /**
     * @return string
     */
    public function getChannelId() : string
    {
        return $this->channelId;
    }

    /**
     * Gets the venture ID.
     *
     * @return string
     */
    public function getVentureId() : string
    {
        return $this->ventureId;
    }

    /**
     * @param string $channelId
     * @return Credentials
     */
    public function setChannelId(string $channelId) : Credentials
    {
        $this->channelId = $channelId;

        return $this;
    }

    /**
     * Sets the venture ID.
     *
     * @param string $value
     * @return Credentials
     */
    public function setVentureId(string $value) : Credentials
    {
        $this->ventureId = $value;

        return $this;
    }

    /**
     * Gets the composite key used to generate the signature header.
     */
    public function getKey() : string
    {
        return $this->getChannelId().$this->getVentureId();
    }
}

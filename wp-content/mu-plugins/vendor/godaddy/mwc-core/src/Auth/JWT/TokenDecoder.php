<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\JWT;

use DomainException;
use Exception;
use GoDaddy\WordPress\MWC\Common\Auth\JWT\Contracts\JwtDecoderContract;
use GoDaddy\WordPress\MWC\Common\Exceptions\JwtDecoderException;
use GoDaddy\WordPress\MWC\Core\Vendor\Firebase\JWT\JWK;
use GoDaddy\WordPress\MWC\Core\Vendor\Firebase\JWT\JWT;
use InvalidArgumentException;
use stdClass;
use UnexpectedValueException;

/**
 * JWT Token Decoder.
 */
class TokenDecoder implements JwtDecoderContract
{
    /** @var mixed[] */
    protected array $keySet;

    /** @var string */
    protected string $defaultAlgorithm = 'RS256';

    /**
     * Sets the keyset.
     *
     * @param mixed[] $value
     * @return $this
     */
    public function setKeySet(array $value) : JwtDecoderContract
    {
        $this->keySet = $value;

        return $this;
    }

    /**
     * Sets the default algorithm that'll be used to decode the JWT if the key (JWK) doesn't specify an alg value.
     *
     * @param string $value
     * @return $this
     */
    public function setDefaultAlgorithm(string $value) : JwtDecoderContract
    {
        $this->defaultAlgorithm = $value;

        return $this;
    }

    /**
     * Decodes the token.
     *
     * @param string $token
     *
     * @return stdClass
     * @throws JwtDecoderException
     */
    public function decode(string $token) : stdclass
    {
        try {
            return JWT::decode($token, JWK::parseKeySet($this->keySet, $this->defaultAlgorithm));
        } catch(DomainException|InvalidArgumentException|UnexpectedValueException|Exception $exception) {
            throw new JwtDecoderException($exception->getMessage(), $exception);
        }
    }
}

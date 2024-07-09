<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Providers\EmailsService;

use GoDaddy\WordPress\MWC\Common\Auth\AuthProviderFactory;
use GoDaddy\WordPress\MWC\Common\Auth\Exceptions\AuthProviderException;
use GoDaddy\WordPress\MWC\Common\Auth\Exceptions\CredentialsCreateFailedException;
use GoDaddy\WordPress\MWC\Common\Auth\Providers\AbstractAuthProvider as BaseAbstractAuthProvider;
use GoDaddy\WordPress\MWC\Common\Auth\Providers\EmailsService\AbstractAuthProvider;
use GoDaddy\WordPress\MWC\Common\Auth\Providers\Models\Token;
use GoDaddy\WordPress\MWC\Common\Contracts\GraphQLOperationContract;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Http\Contracts\ResponseContract;
use GoDaddy\WordPress\MWC\Common\Platforms\Exceptions\PlatformRepositoryException;
use GoDaddy\WordPress\MWC\Core\Email\Http\GraphQL\Mutations\IssueTokenForSiteJwtMutation;
use GoDaddy\WordPress\MWC\Core\Email\Repositories\EmailServiceRepository;

class JwtAuthProvider extends AbstractAuthProvider
{
    /**
     * @throws AuthProviderException
     * @throws CredentialsCreateFailedException
     * @throws PlatformRepositoryException
     */
    protected function getIssueTokenForSiteMutation() : GraphQLOperationContract
    {
        /** @var Token $credentials */
        $credentials = AuthProviderFactory::getNewInstance()->getManagedWooCommerceAuthProvider()->getCredentials();

        return (new IssueTokenForSiteJwtMutation())
            ->setVariables([
                'accessToken'    => $credentials->getAccessToken(),
                'siteIdentifier' => EmailServiceRepository::getSiteId(),
            ]);
    }

    /**
     * @param ResponseContract $response
     * @return array<mixed>
     * @throws AuthProviderException
     */
    protected function getCredentialsData(ResponseContract $response) : array
    {
        if (! $accessToken = ArrayHelper::get(BaseAbstractAuthProvider::getCredentialsData($response), 'data.issueTokenForSiteJwt')) {
            throw new AuthProviderException('The response does not include a token for the site.');
        }

        return ['accessToken' => $accessToken];
    }
}

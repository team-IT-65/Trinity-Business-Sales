<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Providers\EmailsService;

use Exception;
use GoDaddy\WordPress\MWC\Common\Auth\Providers\EmailsService\AbstractAuthProvider;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Contracts\GraphQLOperationContract;
use GoDaddy\WordPress\MWC\Common\Platforms\PlatformRepositoryFactory;
use GoDaddy\WordPress\MWC\Core\Email\Http\GraphQL\Mutations\IssueTokenForSiteMutation;

/**
 * @deprecated
 */
class AuthProvider extends AbstractAuthProvider
{
    /**
     * Gets an issue site token GraphQL mutation operation.
     *
     * @return GraphQLOperationContract
     * @throws Exception
     */
    protected function getIssueTokenForSiteMutation() : GraphQLOperationContract
    {
        return (new IssueTokenForSiteMutation())
            ->setVariables([
                'siteId'    => PlatformRepositoryFactory::getNewInstance()->getPlatformRepository()->getSiteId(),
                'uid'       => Configuration::get('godaddy.account.uid'),
                'siteToken' => Configuration::get('godaddy.site.token', 'empty'),
                'platform'  => strtoupper(PlatformRepositoryFactory::getNewInstance()->getPlatformRepository()->getPlatformName()),
            ]);
    }
}

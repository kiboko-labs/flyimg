<?php

namespace Flyimg\Http\Security;

class AllowedDomainsSecurityRule implements DomainBasedRuleInterface
{
    use DomainListSecurityRuleTrait;

    public function __construct(array $domains)
    {
        $this->domains = $domains;
    }

    public function isAllowedSourceUrl(string $url): bool
    {
        return $this->isDomainInList($url);
    }
}

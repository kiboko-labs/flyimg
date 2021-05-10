<?php

namespace Flyimg\Http\Security;

trait DomainListSecurityRuleTrait
{
    private $domains = [];

    public function addDomain(string $domain): void
    {
        $this->domains[] = $domain;
    }

    public function hasDomains(): bool
    {
        return !empty($this->domains);
    }

    public function countDomains(): int
    {
        return count($this->domains);
    }

    public function walkDomains(): iterable
    {
        yield from $this->domains;
    }

    private function isDomainInList(string $url): bool
    {
        $domain = parse_url($url, PHP_URL_HOST);
        foreach ($this->walkDomains() as $listedDomain) {
            if ($listedDomain === $domain) {
                return true;
            }
        }

        return false;
    }
}

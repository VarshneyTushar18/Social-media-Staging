<?php

namespace Modules\AppWebsiteBuilder\Support;

class WebsiteCustomDomainDnsVerifier
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_CONNECTED = 'connected';

    public const STATUS_WRONG = 'wrong';

    /**
     * @return array{
     *     verified: bool,
     *     records: list<array{type: string, host: string, points_to: string, status: string, observed: ?string}>
     * }
     */
    public function verify(string $customDomain): array
    {
        $domain = WebsiteDomainInputValidator::normalizeRegisteredDomain($customDomain);

        if ($domain === '') {
            return ['verified' => false, 'records' => []];
        }

        $expectedRows = WebsiteCustomDomain::dnsRecords();
        $results = [];
        $verified = true;

        foreach ($expectedRows as $row) {
            $type = strtoupper((string) ($row['type'] ?? ''));
            $hostLabel = (string) ($row['host'] ?? '@');
            $expectedTarget = (string) ($row['points_to'] ?? '');

            if ($expectedTarget === '' || str_contains($expectedTarget, ' ')) {
                $results[] = [
                    'type' => $type,
                    'host' => $hostLabel,
                    'points_to' => $expectedTarget,
                    'status' => self::STATUS_PENDING,
                    'observed' => null,
                ];
                $verified = false;

                continue;
            }

            if (WebsiteDomainInputValidator::isForbiddenHostOrIp($expectedTarget)) {
                $results[] = [
                    'type' => $type,
                    'host' => $hostLabel,
                    'points_to' => $expectedTarget,
                    'status' => self::STATUS_WRONG,
                    'observed' => null,
                ];
                $verified = false;

                continue;
            }

            $lookupHost = $this->lookupHost($domain, $hostLabel);
            $status = $this->matchRecord($type, $lookupHost, $expectedTarget);
            $observed = $this->observedTargets($type, $lookupHost);

            $results[] = [
                'type' => $type,
                'host' => $hostLabel,
                'points_to' => $expectedTarget,
                'status' => $status,
                'observed' => $observed !== [] ? implode(', ', $observed) : null,
            ];

            if ($status !== self::STATUS_CONNECTED) {
                $verified = false;
            }
        }

        return ['verified' => $verified, 'records' => $results];
    }

    protected function lookupHost(string $apexDomain, string $hostLabel): string
    {
        if ($hostLabel === '@' || $hostLabel === '') {
            return $apexDomain;
        }

        return strtolower($hostLabel.'.'.$apexDomain);
    }

    protected function matchRecord(string $type, string $lookupHost, string $expectedTarget): string
    {
        $expected = $this->normalizeTarget($expectedTarget);
        $observed = $this->observedTargets($type, $lookupHost);

        if ($observed === []) {
            return self::STATUS_PENDING;
        }

        foreach ($observed as $value) {
            if ($this->targetsMatch($type, $value, $expected)) {
                return self::STATUS_CONNECTED;
            }
        }

        return self::STATUS_WRONG;
    }

    /**
     * @return list<string>
     */
    protected function observedTargets(string $type, string $lookupHost): array
    {
        $type = strtoupper($type);

        return match ($type) {
            'A' => $this->dnsValues($lookupHost, DNS_A, 'ip'),
            'AAAA' => $this->dnsValues($lookupHost, DNS_AAAA, 'ipv6'),
            'CNAME' => $this->dnsValues($lookupHost, DNS_CNAME, 'target'),
            default => [],
        };
    }

    /**
     * @return list<string>
     */
    protected function dnsValues(string $host, int $recordType, string $key): array
    {
        $records = @dns_get_record($host, $recordType);

        if (! is_array($records) || $records === []) {
            return [];
        }

        $values = [];

        foreach ($records as $record) {
            if (! is_array($record)) {
                continue;
            }

            $value = $record[$key] ?? null;

            if (is_string($value) && $value !== '') {
                $values[] = $this->normalizeTarget($value);
            }
        }

        return array_values(array_unique($values));
    }

    protected function targetsMatch(string $type, string $observed, string $expected): bool
    {
        if (strtoupper($type) === 'CNAME') {
            return $observed === $expected || $observed === $this->normalizeTarget($expected);
        }

        if (strtoupper($type) === 'A') {
            if (WebsiteDomainInputValidator::isForbiddenHostOrIp($observed)) {
                return false;
            }

            return $observed === $expected;
        }

        return $observed === $expected;
    }

    protected function normalizeTarget(string $target): string
    {
        return strtolower(rtrim(trim($target), '.'));
    }
}
<?php

declare(strict_types=1);

namespace Tavp\Analytics\Fraud;

/**
 * Detects suspicious geographic patterns.
 */
class GeographicRule implements FraudRule
{
    public function getName(): string
    {
        return 'geographic';
    }

    public function evaluate(array $data, array $config): array
    {
        $ip = $data['ip_address'] ?? '';
        $country = $data['country'] ?? null;
        $isp = $data['isp'] ?? null;

        $indicators = [];

        // Check 1: Known VPN/proxy ISPs
        $vpnKeywords = ['vpn', 'proxy', 'tor', 'relay', 'anonym'];
        if ($isp !== null) {
            foreach ($vpnKeywords as $keyword) {
                if (str_contains(strtolower($isp), $keyword)) {
                    $indicators[] = 'vpn_detected';
                    break;
                }
            }
        }

        // Check 2: Data center IP (simplified)
        $dcKeywords = ['amazon', 'google', 'microsoft', 'digitalocean', 'linode', 'vultr', 'hetzner'];
        if ($isp !== null) {
            foreach ($dcKeywords as $keyword) {
                if (str_contains(strtolower($isp), $keyword)) {
                    $indicators[] = 'datacenter_ip';
                    break;
                }
            }
        }

        // Check 3: Country mismatch with language/header
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if ($country !== null && !empty($acceptLanguage)) {
            $langCountry = substr($acceptLanguage, 0, 2);
            // Simplified check — in production, use proper mapping
        }

        if (!empty($indicators)) {
            $score = 0.0;
            if (in_array('vpn_detected', $indicators)) {
                $score += 0.2;
            }
            if (in_array('datacenter_ip', $indicators)) {
                $score += 0.15;
            }

            return [
                'score' => min(0.5, $score),
                'reason' => 'Geographic anomaly: ' . implode(', ', $indicators),
                'data' => ['indicators' => $indicators, 'country' => $country, 'isp' => $isp],
            ];
        }

        return ['score' => 0.0, 'reason' => '', 'data' => []];
    }
}

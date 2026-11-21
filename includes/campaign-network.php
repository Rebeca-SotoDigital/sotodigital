<?php
function campaign_ip_in_cidr(string $ip, string $cidr): bool {
    $parts = explode('/', trim($cidr), 2);
    $network = @inet_pton($parts[0]); $address = @inet_pton($ip);
    if ($network === false || $address === false || strlen($network) !== strlen($address)) return false;
    $max = strlen($network) * 8;
    if (isset($parts[1]) && !ctype_digit($parts[1])) return false;
    $bits = isset($parts[1]) ? (int)$parts[1] : $max;
    if ($bits < 0 || $bits > $max) return false;
    $bytes = intdiv($bits, 8); $remainder = $bits % 8;
    if (substr($network, 0, $bytes) !== substr($address, 0, $bytes)) return false;
    return $remainder === 0 || ((ord($network[$bytes]) ^ ord($address[$bytes])) & (255 << (8 - $remainder))) === 0;
}
function campaign_client_ip(array $server, ?array $trusted = null): string {
    $remote = $server['REMOTE_ADDR'] ?? '';
    if (!is_string($remote) || !filter_var($remote, FILTER_VALIDATE_IP)) return 'unknown';
    $trusted = $trusted ?? array_filter(array_map('trim', explode(',', campaign_config('CAMPAIGN_TRUSTED_PROXIES'))));
    if (!array_filter($trusted, static function ($cidr) use ($remote) { return campaign_ip_in_cidr($remote, $cidr); })) return $remote;
    $cf = $server['HTTP_CF_CONNECTING_IP'] ?? '';
    if (is_string($cf) && filter_var(trim($cf), FILTER_VALIDATE_IP)) return trim($cf);
    $forwarded = $server['HTTP_X_FORWARDED_FOR'] ?? '';
    if (is_string($forwarded) && strlen($forwarded) < 4096) {
        foreach (explode(',', $forwarded) as $candidate) if (filter_var(trim($candidate), FILTER_VALIDATE_IP)) return trim($candidate);
    }
    return $remote;
}

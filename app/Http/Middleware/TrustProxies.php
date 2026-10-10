<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Trusted proxies, resolved from the TRUSTED_PROXIES environment value
     * (comma-separated IPs/CIDRs, or `*` to trust all).
     */
    protected $proxies;

    public function __construct()
    {
        $raw = trim((string) env('TRUSTED_PROXIES', ''));

        if ($raw === '' || strtolower($raw) === 'none') {
            $this->proxies = null;
        } elseif ($raw === '*') {
            $this->proxies = '*';
        } else {
            $this->proxies = array_values(array_filter(array_map(
                fn ($v) => trim($v),
                explode(',', $raw)
            )));
        }
    }

    /**
     * Use forwarded headers (only honored for requests coming through a
     * trusted proxy, see above).
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO;
}

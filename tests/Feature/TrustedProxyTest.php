<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Le site passe par un relais Vercel : les limites par IP doivent viser le visiteur,
 * sans que le relais puisse changer l'hôte utilisé pour les URL générées par le backend.
 * Aucune base de données n'est utilisée par ce test.
 */
class TrustedProxyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/__proxy-probe', fn (Request $request) => response()->json([
            'ip' => $request->ip(),
            'secure' => $request->secure(),
            'host' => $request->getHost(),
        ]));
    }

    public function test_client_ip_comes_from_the_forwarded_header(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '76.76.21.21'])
            ->withHeader('X-Forwarded-For', '203.0.113.9')
            ->getJson('/__proxy-probe')
            ->assertJsonPath('ip', '203.0.113.9');
    }

    public function test_two_visitors_behind_the_relay_keep_distinct_ips(): void
    {
        $first = $this->withServerVariables(['REMOTE_ADDR' => '76.76.21.21'])
            ->withHeader('X-Forwarded-For', '203.0.113.9')
            ->getJson('/__proxy-probe')->json('ip');
        $second = $this->withServerVariables(['REMOTE_ADDR' => '76.76.21.21'])
            ->withHeader('X-Forwarded-For', '198.51.100.7')
            ->getJson('/__proxy-probe')->json('ip');

        $this->assertNotSame($first, $second);
    }

    public function test_forwarded_https_is_honoured(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '76.76.21.21'])
            ->withHeader('X-Forwarded-Proto', 'https')
            ->getJson('/__proxy-probe')
            ->assertJsonPath('secure', true);
    }

    public function test_forwarded_host_is_ignored(): void
    {
        $host = $this->withServerVariables(['REMOTE_ADDR' => '76.76.21.21'])
            ->getJson('/__proxy-probe')->json('host');

        $this->withServerVariables(['REMOTE_ADDR' => '76.76.21.21'])
            ->withHeader('X-Forwarded-Host', 'nlcrdc.org')
            ->getJson('/__proxy-probe')
            ->assertJsonPath('host', $host);
    }
}

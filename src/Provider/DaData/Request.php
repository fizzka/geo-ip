<?php

declare(strict_types=1);

namespace Fizz\GeoIp\Provider\DaData;

use Fizz\GeoIp\IpAddr;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Request
{
    private string $url;
    private string $token;

    /**
     * @param string[] $config
     */
    public function __construct(array $config)
    {
        $this->url = $config['url'];
        $this->token = $config['token'];
    }

    private function uri(IpAddr $ip): UriInterface
    {
        return Psr7\uri_for($this->url)
            ->withQuery(Psr7\build_query([
                'ip' => $ip,
            ]));
    }

    public function __invoke(IpAddr $ip): RequestInterface
    {
        return new Psr7\Request('GET', $this->uri($ip), [
            'Authorization' => 'Token ' . $this->token,
        ]);
    }
}

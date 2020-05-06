<?php

declare(strict_types=1);

namespace Fizz\GeoIp\Provider\IpGeoBase;

use Fizz\GeoIp\IpAddr;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7;

class Request
{
    private const REQUEST_BODY_TPL = '<ipquery><fields><all/></fields><ip-list><ip>%s</ip></ip-list></ipquery>';

    private string $url;

    /**
     * @param string[] $config
     */
    public function __construct(array $config)
    {
        $this->url = $config['url'];
    }

    private function body(IpAddr $ip): string
    {
        return sprintf(self::REQUEST_BODY_TPL, (string)$ip);
    }

    public function __invoke(IpAddr $ip): RequestInterface
    {
        return (new Psr7\Request('POST', $this->url))
            ->withBody(Psr7\stream_for($this->body($ip)))
        ;
    }
}

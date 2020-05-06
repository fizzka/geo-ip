<?php

declare(strict_types=1);

namespace Fizz\GeoIp\Provider;

use Fizz\GeoIp\IpAddr;
use Fizz\GeoIp\Utils\FilterResult;
use Fizz\GeoIp\Utils\ParseJsonResponse;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use League\Pipeline\Pipeline;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Tightenco\Collect\Support\Collection;

final class IpWhois extends Provider
{
    /**
     * @param string[] $config
     */
    public function __construct(ClientInterface $client, array $config)
    {
        $this->pipeline = (new Pipeline())
            ->pipe(fn(IpAddr $ip) => new Request('GET', $config['url'] . $ip . '?lang=ru'))
            ->pipe(fn(RequestInterface $request): StreamInterface => $client->send($request)->getBody())
            ->pipe(new ParseJsonResponse())
            ->pipe('collect')
            ->pipe(new IpWhois\Validate())
            ->pipe(new FilterResult())
        ;
    }
}

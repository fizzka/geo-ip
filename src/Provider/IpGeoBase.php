<?php

declare(strict_types=1);

namespace Fizz\GeoIp\Provider;

use Fizz\GeoIp\IpAddr;
use Fizz\GeoIp\Utils\FilterResult;
use Fizz\GeoIp\Utils\ParseJsonResponse;
use Fizz\GeoIp\Utils\Xml2Json;
use GuzzleHttp\ClientInterface;
use League\Pipeline\Pipeline;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Tightenco\Collect\Support\Collection;

final class IpGeoBase extends Provider
{
    /**
     * @param string[] $config
     */
    public function __construct(ClientInterface $client, array $config)
    {
        $fetch = (new Pipeline())
            ->pipe(new IpGeoBase\Request($config))
            ->pipe(fn(RequestInterface $request): StreamInterface => $client->send($request)->getBody())
            ->pipe(new Xml2Json())
            ->pipe(new ParseJsonResponse())
        ;

        $this->pipeline = (new Pipeline())
            ->pipe($fetch)
            ->pipe('collect')
            ->pipe(new IpGeoBase\Validate())
            ->pipe(fn(Collection $response) => $this->transform($response))
            ->pipe(new FilterResult())
        ;
    }

    private function transform(Collection $response): Collection
    {
        return $response->merge([
            'ip' => $response['@attributes']['value'],
            'latitude' => $response['lat'],
            'longitude' => $response['lng'],
        ]);
    }
}

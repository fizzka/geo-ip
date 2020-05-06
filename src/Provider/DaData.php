<?php

declare(strict_types=1);

namespace Fizz\GeoIp\Provider;

use Fizz\GeoIp\IpAddr;
use Fizz\GeoIp\Utils\FilterResult;
use Fizz\GeoIp\Utils\ParseJsonResponse;
use GuzzleHttp\ClientInterface;
use League\Pipeline\Pipeline;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Tightenco\Collect\Support\Collection;

final class DaData extends Provider
{
    /**
     * @param string[] $config
     */
    public function __construct(ClientInterface $client, array $config)
    {
        $fetch = (new Pipeline())
            ->pipe(new DaData\Request($config))
            ->pipe(fn(RequestInterface $request): StreamInterface => $client->send($request)->getBody())
            ->pipe(new ParseJsonResponse())
        ;

        $this->pipeline = (new Pipeline())
            ->pipe($fetch)
            ->pipe('collect')
            ->pipe(new DaData\Validate())
            ->pipe(fn(Collection $response) => $this->transform($response))
            ->pipe(new FilterResult())
        ;
    }

    private function transform(Collection $response): Collection
    {
        $response = collect($response['location']['data']);
        return $response->merge([
            'latitude' => $response['geo_lat'],
            'longitude' => $response['geo_lon'],
        ]);
    }
}

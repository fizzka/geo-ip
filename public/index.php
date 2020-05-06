<?php

declare(strict_types=1);

use Fizz\GeoIp\IpAddr;
use Fizz\GeoIp\Provider\DaData;
use Fizz\GeoIp\Provider\IpGeoBase;
use Fizz\GeoIp\Provider\IpWhois;
use Fizz\GeoIp\Provider\Provider;
use GuzzleHttp\Client;
use Phalcon\Mvc\Micro;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Phalcon\Config;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream;

require __DIR__ . '/../vendor/autoload.php';

$_ENV['DEBUG'] = true;

$app = new Micro();

$errorHandler = fn($code, $message) => fn() => $app->response
    ->setStatusCode($code, $message)
    ->setJsonContent([
        'error' => $message
    ]);

$app->notFound($errorHandler(404, 'Not Found'));

$app->error(function (Exception $e) use ($errorHandler, $app) {
    $app->logger->error($e);
    return $errorHandler(500, 'Internal Server Error')();
});

$app->delete('/ip/{ip}', function ($ip) {
    $this->cache->delete((string)$ip);
    return $this->response->setStatusCode(204);
})->convert('ip', fn($ip) => IpAddr::make($ip));

$app->get('/ip/{ip}', function ($ip) {
    $this->response->setHeader('Cache-Status', 'HIT');

    $fetch = collect([
        new DaData($this->client, $this->config->dadata->toArray()),
        new IpGeoBase($this->client, $this->config->ipgeobase->toArray()),
        new IpWhois($this->client, $this->config->ipwhois->toArray()),
    ]);

    $value = $this->cache->get((string)$ip, function (ItemInterface $item) use ($fetch) {
        $this->response->setHeader('Cache-Status', 'MISSED');

        if ($_ENV['DEBUG'] ?? false) {
            $this->response->setHeader('X-Provider', get_class($fetch));
        }
        return $fetch->map(fn(Provider $p) => $p($item->getKey()));
    });

    return $this->response
        ->setJsonContent($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
})->convert('ip', fn($ip) => IpAddr::make($ip));

$config = new Config([
    'redis' => [
        'url'  => getenv('REDIS_URL'),
        'prefix' => getenv('REDIS_PREFIX'),
        // 'lifetime' => getenv('CACHE_LIFETIME') ?: 30,
        'lifetime' => 1,
    ],
    'dadata' => [
        'url' => 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/iplocate/address',
        'token' => getenv('DADATA_TOKEN'),
    ],
    'ipwhois' => [
        'url' => 'http://free.ipwhois.io/json/',
    ],
    'ipgeobase' => [
        'url' => 'http://194.85.91.253:8090/geo/geo.html'
    ],
]);

$app->setService('config', $config);

$app->setService('cache', function () use ($config) {
    $config = $config->redis;

    $client = RedisAdapter::createConnection(
        $config->url
    );

    return new RedisAdapter(
        $client,
        $config->prefix,
        $config->lifetime
    );
});

$app->setService('logger', function () {
    $adapter = new Stream('php://stderr');

    return new Logger('messages', [
        'main' => $adapter,
    ]);
});

$app->setService('client', fn() => new Client([
    //'debug' => 'stderr'
]));

$app->handle(
    $_SERVER['REQUEST_URI']
);

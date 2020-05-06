<?php

declare(strict_types=1);

namespace Fizz\GeoIp\Provider\IpGeoBase;

use Exception;
use Tightenco\Collect\Support\Collection;

class Validate
{
    public function __invoke(Collection $response): Collection
    {
        if ($response->get('message') == 'Not found') {
            throw new Exception('info not found');
        }

        return $response;
    }
}

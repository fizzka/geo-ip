<?php

declare(strict_types=1);

namespace Fizz\GeoIp\Provider\DaData;

use Exception;
use Tightenco\Collect\Support\Collection;

class Validate
{
    public function __invoke(Collection $response): Collection
    {
        if (!$response->get('location')) {
            throw new Exception('got failed response');
        }

        return $response;
    }
}

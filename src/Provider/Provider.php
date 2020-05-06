<?php

declare(strict_types=1);

namespace Fizz\GeoIp\Provider;

use Fizz\GeoIp\IpAddr;
use League\Pipeline\Pipeline;
use Tightenco\Collect\Support\Collection;

abstract class Provider
{
    protected Pipeline $pipeline;

    final public function __invoke(string $ip): Collection
    {
        $ip = IpAddr::make($ip);

        return $this->pipeline
            ->process($ip);
    }
}

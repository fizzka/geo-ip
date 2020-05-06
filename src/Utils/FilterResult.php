<?php

declare(strict_types=1);

namespace Fizz\GeoIp\Utils;

use Tightenco\Collect\Support\Collection;

class FilterResult
{
    /** @psalm-immutable */
    public function __invoke(Collection $result): Collection
    {
        return $result
            ->only($this->filterFields())
            ->sortKeys()
        ;
    }

    /**
     * @return string[]
     */
    protected static function filterFields(): array
    {
        // model
        return [
            'ip',
            'city',
            'region',
            'latitude',
            'longitude',
        ];
    }
}

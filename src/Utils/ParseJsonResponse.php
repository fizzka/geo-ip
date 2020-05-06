<?php

declare(strict_types=1);

namespace Fizz\GeoIp\Utils;

class ParseJsonResponse
{
    /**
     * @param string $responseBody
     * @return mixed
     */
    public function __invoke($responseBody)
    {
        return json_decode((string)$responseBody, true);
    }
}

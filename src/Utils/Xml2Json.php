<?php

declare(strict_types=1);

namespace Fizz\GeoIp\Utils;

use Exception;

class Xml2Json
{
    /**
     * @param string $responseBody
     */
    public function __invoke($responseBody): string
    {
        $xml = simplexml_load_string((string)$responseBody);

        /** @todo fix ->ip */
        if (empty($xml->ip)) {
            throw new Exception('bad response');
        }
        return json_encode($xml->ip) ?: '';
    }
}

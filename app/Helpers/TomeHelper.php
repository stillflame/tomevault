<?php

namespace App\Helpers;

class TomeHelper
{

    public static function resourceUrl(string $objectId, string $resourceName = 'tomes')
    {
        return url('/') . config('api.api_prefix') . '/' . $resourceName . '/' . $objectId;
    }

}

<?php

namespace App\Support\Api;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ApiPaginationHelper
{
    public static function fromResourceCollection(AnonymousResourceCollection $collection): array
    {
        return $collection->response()->getData(true);
    }
}

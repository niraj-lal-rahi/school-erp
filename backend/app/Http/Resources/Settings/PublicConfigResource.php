<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'app_name' => data_get($this->resource, 'app_name'),
            'branding' => data_get($this->resource, 'branding', []),
            'localization' => data_get($this->resource, 'localization', []),
            'features' => data_get($this->resource, 'features', []),
            'settings' => data_get($this->resource, 'settings', []),
        ];
    }
}

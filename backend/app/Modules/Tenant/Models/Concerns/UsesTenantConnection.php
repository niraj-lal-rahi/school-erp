<?php

namespace App\Modules\Tenant\Models\Concerns;

trait UsesTenantConnection
{
    protected $connection = 'tenant';
}

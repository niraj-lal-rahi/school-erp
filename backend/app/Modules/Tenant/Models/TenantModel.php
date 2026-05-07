<?php

namespace App\Modules\Tenant\Models;

use App\Modules\Tenant\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

abstract class TenantModel extends Model
{
    use UsesTenantConnection;
}

<?php

namespace App\Http\Requests\Transport;

use App\Support\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;

abstract class TransportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function tenantId(): ?int
    {
        return app(TenantContext::class)->id() ?? $this->user()?->school_id;
    }
}

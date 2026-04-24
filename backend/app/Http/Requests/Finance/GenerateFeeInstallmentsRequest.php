<?php

namespace App\Http\Requests\Finance;

use App\Models\Finance\StudentFeeAssignment;
use Illuminate\Foundation\Http\FormRequest;

class GenerateFeeInstallmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $assignment = $this->route('studentFeeAssignment');

        return $assignment instanceof StudentFeeAssignment
            ? ($this->user()?->can('update', $assignment) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [];
    }
}

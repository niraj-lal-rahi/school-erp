<?php

namespace App\Http\Requests\Reports;

use Closure;
use Illuminate\Validation\Rule;

class UpdateDashboardLayoutRequest extends ReportsRequest
{
    public function rules(): array
    {
        return [
            'user_type' => ['required', 'string', Rule::in(['staff', 'admin'])],
            'user_id' => ['required', 'integer'],
            'layout' => ['required', 'array', 'min:1', $this->validLayout()],
            'layout.*.widget_id' => ['required', 'integer', $this->existsInTenant('dashboard_widgets')],
            'layout.*.x' => ['required', 'integer', 'min:0'],
            'layout.*.y' => ['required', 'integer', 'min:0'],
            'layout.*.w' => ['required', 'integer', 'min:1'],
            'layout.*.h' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function validLayout(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_array($value)) {
                $fail('The layout field must be a valid list of widget positions.');
                return;
            }

            $seenWidgetIds = [];

            foreach ($value as $index => $item) {
                if (! is_array($item)) {
                    $fail("The layout item at index {$index} must be a valid object payload.");
                    return;
                }

                $widgetId = $item['widget_id'] ?? null;
                if ($widgetId !== null) {
                    if (in_array($widgetId, $seenWidgetIds, true)) {
                        $fail('Each widget can appear only once in the dashboard layout.');
                        return;
                    }

                    $seenWidgetIds[] = $widgetId;
                }
            }
        };
    }
}

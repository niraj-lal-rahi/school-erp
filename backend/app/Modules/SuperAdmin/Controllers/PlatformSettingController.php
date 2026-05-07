<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Resources\PlatformSettingResource;
use App\Modules\SuperAdmin\Services\PlatformSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PlatformSettingController extends Controller
{
    public function __construct(
        protected PlatformSettingService $settings,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $group = $request->query('group');

        if (is_string($group) && $group !== '') {
            return response()->json([
                'data' => PlatformSettingResource::collection($this->settings->getGroup($group)),
            ]);
        }

        $groups = $this->settings->list();

        return response()->json([
            'data' => $groups->map(
                fn ($items, $settingGroup): array => [
                    'group' => $settingGroup,
                    'settings' => PlatformSettingResource::collection($items),
                ]
            )->values(),
        ]);
    }

    public function show(string $group): JsonResponse
    {
        return response()->json([
            'data' => PlatformSettingResource::collection($this->settings->getGroup($group)),
        ]);
    }

    public function update(Request $request, string $group): JsonResponse
    {
        $payload = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string', 'max:191'],
            'settings.*.value' => ['nullable'],
            'settings.*.value_type' => ['nullable', 'in:string,integer,boolean,json,encrypted,file'],
            'settings.*.is_sensitive' => ['nullable', 'boolean'],
            'settings.*.is_public' => ['nullable', 'boolean'],
            'settings.*.description' => ['nullable', 'string'],
            'settings.*.metadata' => ['nullable', 'array'],
        ]);

        try {
            $settings = $this->settings->updateGroup(
                $group,
                $payload['settings'],
                $request->user()?->id,
                $request->ip(),
                $request->userAgent(),
            );
        } catch (InvalidArgumentException $exception) {
            abort(422, $exception->getMessage());
        }

        return response()->json([
            'message' => 'Platform settings updated successfully.',
            'data' => PlatformSettingResource::collection($settings),
        ]);
    }
}

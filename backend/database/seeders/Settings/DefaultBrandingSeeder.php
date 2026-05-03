<?php

namespace Database\Seeders\Settings;

use App\Models\School;
use App\Models\Settings\BrandingSetting;
use Illuminate\Database\Seeder;

class DefaultBrandingSeeder extends Seeder
{
    public function run(): void
    {
        School::withoutGlobalScopes()->get()->each(function (School $school): void {
            BrandingSetting::withoutGlobalScopes()->updateOrCreate(
                ['school_id' => $school->id],
                [
                    'school_name' => $school->name,
                    'primary_color' => '#0F4C81',
                    'secondary_color' => '#1C7C54',
                    'accent_color' => '#F4B400',
                    'footer_text' => sprintf('%s | Learning with confidence', $school->name),
                    'custom_css' => null,
                ],
            );
        });
    }
}

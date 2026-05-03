<?php

namespace Database\Seeders\Settings;

use App\Models\School;
use App\Models\Settings\LocalizationSetting;
use Illuminate\Database\Seeder;

class DefaultLocalizationSeeder extends Seeder
{
    public function run(): void
    {
        School::withoutGlobalScopes()->get()->each(function (School $school): void {
            LocalizationSetting::withoutGlobalScopes()->updateOrCreate(
                ['school_id' => $school->id],
                [
                    'timezone' => $school->timezone ?? 'Asia/Kolkata',
                    'locale' => $school->locale ?? 'en',
                    'date_format' => 'd-m-Y',
                    'time_format' => 'h:i A',
                    'currency' => $school->currency ?? 'INR',
                    'currency_symbol' => '₹',
                    'first_day_of_week' => 'monday',
                ],
            );
        });
    }
}

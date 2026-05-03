<?php

namespace App\Models\Settings;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class LocalizationSetting extends Model
{
    use BelongsToSchool;

    protected $table = 'localization_settings';

    protected $fillable = [
        'school_id',
        'timezone',
        'locale',
        'date_format',
        'time_format',
        'currency',
        'currency_symbol',
        'first_day_of_week',
    ];
}

<?php

namespace App\Models\Settings;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrandingSetting extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'branding_settings';

    protected $fillable = [
        'school_id',
        'school_name',
        'logo_path',
        'favicon_path',
        'primary_color',
        'secondary_color',
        'accent_color',
        'footer_text',
        'custom_css',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }
}

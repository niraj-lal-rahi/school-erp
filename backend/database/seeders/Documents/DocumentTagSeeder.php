<?php

namespace Database\Seeders\Documents;

use App\Models\Documents\DocumentTag;
use App\Models\School;
use Illuminate\Database\Seeder;

class DocumentTagSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();

        $tags = [
            ['name' => 'Verified', 'code' => 'VERIFIED'],
            ['name' => 'Admission', 'code' => 'ADMISSION'],
            ['name' => 'Compliance', 'code' => 'COMPLIANCE'],
            ['name' => 'Finance', 'code' => 'FINANCE'],
            ['name' => 'HR', 'code' => 'HR'],
            ['name' => 'Identity', 'code' => 'IDENTITY'],
        ];

        foreach ($tags as $tag) {
            DocumentTag::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'code' => $tag['code'],
                ],
                [
                    'school_id' => $school->id,
                    'name' => $tag['name'],
                    'code' => $tag['code'],
                ]
            );
        }
    }
}

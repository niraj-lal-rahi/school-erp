<?php

namespace Database\Seeders\Documents;

use App\Models\Documents\DocumentCategory;
use App\Models\School;
use Illuminate\Database\Seeder;

class DocumentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();

        $categories = [
            [
                'name' => 'Student Birth Certificate',
                'code' => 'STUDENT-BIRTH-CERTIFICATE',
                'description' => 'Birth certificate copies for student identity verification.',
                'applies_to' => 'student',
                'requires_verification' => true,
                'has_expiry' => false,
            ],
            [
                'name' => 'Transfer Certificate',
                'code' => 'TRANSFER-CERTIFICATE',
                'description' => 'Transfer certificate documents for admissions and exits.',
                'applies_to' => 'student',
                'requires_verification' => true,
                'has_expiry' => false,
            ],
            [
                'name' => 'Student ID Proof',
                'code' => 'STUDENT-ID-PROOF',
                'description' => 'Identity proof submitted by students or guardians.',
                'applies_to' => 'student',
                'requires_verification' => true,
                'has_expiry' => true,
            ],
            [
                'name' => 'Staff ID Proof',
                'code' => 'STAFF-ID-PROOF',
                'description' => 'Identity proof submitted by staff members.',
                'applies_to' => 'staff',
                'requires_verification' => true,
                'has_expiry' => true,
            ],
            [
                'name' => 'Staff Qualification Certificate',
                'code' => 'STAFF-QUALIFICATION-CERTIFICATE',
                'description' => 'Qualification and certification records for staff.',
                'applies_to' => 'staff',
                'requires_verification' => true,
                'has_expiry' => false,
            ],
            [
                'name' => 'Fee Receipt',
                'code' => 'FEE-RECEIPT',
                'description' => 'Generated fee receipts and supporting payment documents.',
                'applies_to' => 'finance',
                'requires_verification' => false,
                'has_expiry' => false,
            ],
            [
                'name' => 'School Registration Document',
                'code' => 'SCHOOL-REGISTRATION-DOCUMENT',
                'description' => 'School registration and statutory tenant documents.',
                'applies_to' => 'tenant',
                'requires_verification' => true,
                'has_expiry' => true,
            ],
        ];

        foreach ($categories as $category) {
            DocumentCategory::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'code' => $category['code'],
                ],
                [
                    ...$category,
                    'school_id' => $school->id,
                    'status' => 'active',
                ]
            );
        }
    }
}

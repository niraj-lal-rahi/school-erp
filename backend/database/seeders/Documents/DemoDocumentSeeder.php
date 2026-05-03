<?php

namespace Database\Seeders\Documents;

use App\Models\Documents\Document;
use App\Models\Documents\DocumentAuditLog;
use App\Models\Documents\DocumentCategory;
use App\Models\Documents\DocumentFile;
use App\Models\Documents\DocumentFolder;
use App\Models\Documents\DocumentPermission;
use App\Models\Documents\DocumentTag;
use App\Models\Documents\DocumentVerification;
use App\Models\HR\Staff;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $admin = User::withoutGlobalScopes()->where('school_id', $school->id)->where('email', 'admin@greenwood.edu')->firstOrFail();
        $student = Student::withoutGlobalScopes()->where('school_id', $school->id)->orderBy('id')->firstOrFail();
        $staff = Staff::withoutGlobalScopes()->where('school_id', $school->id)->orderBy('id')->first();

        $studentFolder = DocumentFolder::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'code' => 'STUDENT-RECORDS',
            ],
            [
                'parent_id' => null,
                'name' => 'Student Records',
                'description' => 'Student-owned documents and identity records.',
                'visibility' => 'private',
                'created_by' => $admin->id,
            ]
        );

        $financeFolder = DocumentFolder::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'code' => 'FINANCE-RECEIPTS',
            ],
            [
                'parent_id' => null,
                'name' => 'Finance Receipts',
                'description' => 'Fee receipts and payment support documents.',
                'visibility' => 'internal',
                'created_by' => $admin->id,
            ]
        );

        $studentCategory = DocumentCategory::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('code', 'STUDENT-ID-PROOF')
            ->firstOrFail();
        $receiptCategory = DocumentCategory::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('code', 'FEE-RECEIPT')
            ->firstOrFail();

        $studentDocument = Document::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'document_no' => 'DOC-STU-0001',
            ],
            [
                'category_id' => $studentCategory->id,
                'folder_id' => $studentFolder->id,
                'owner_type' => 'student',
                'owner_id' => $student->id,
                'title' => 'Riya Student Aadhaar Copy',
                'description' => 'Identity proof document for student records.',
                'issue_date' => now()->subYear()->toDateString(),
                'expiry_date' => now()->addMonths(6)->toDateString(),
                'verification_status' => 'verified',
                'status' => 'active',
                'created_by' => $admin->id,
            ]
        );

        DocumentFile::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'document_id' => $studentDocument->id,
                'version_no' => 1,
            ],
            [
                'file_name' => 'riya-id-proof-v1.pdf',
                'original_file_name' => 'riya-id-proof.pdf',
                'file_path' => 'documents/'.$school->id.'/student/'.$student->id.'/'.$studentDocument->id.'/riya-id-proof-v1.pdf',
                'disk' => 'local',
                'mime_type' => 'application/pdf',
                'file_size' => 128400,
                'checksum' => hash('sha256', 'riya-id-proof-v1'),
                'uploaded_by' => $admin->id,
                'is_current' => true,
            ]
        );

        DocumentPermission::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'document_id' => $studentDocument->id,
                'permission_type' => 'owner',
                'permission_id' => null,
            ],
            [
                'can_view' => true,
                'can_download' => true,
                'can_update' => false,
                'can_delete' => false,
                'can_verify' => false,
            ]
        );

        DocumentVerification::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'document_id' => $studentDocument->id,
                'status' => 'verified',
            ],
            [
                'verified_by' => $admin->id,
                'remarks' => 'Verified during student onboarding.',
                'verified_at' => now()->subMonths(3),
            ]
        );

        $receiptDocument = Document::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'document_no' => 'DOC-FEE-0001',
            ],
            [
                'category_id' => $receiptCategory->id,
                'folder_id' => $financeFolder->id,
                'owner_type' => 'general',
                'owner_id' => null,
                'title' => 'April Fee Receipt',
                'description' => 'Generated receipt document for April collection.',
                'issue_date' => now()->subDays(15)->toDateString(),
                'expiry_date' => null,
                'verification_status' => 'verified',
                'status' => 'active',
                'created_by' => $admin->id,
            ]
        );

        DocumentFile::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'document_id' => $receiptDocument->id,
                'version_no' => 1,
            ],
            [
                'file_name' => 'april-fee-receipt.pdf',
                'original_file_name' => 'april-fee-receipt.pdf',
                'file_path' => 'documents/'.$school->id.'/general/0/'.$receiptDocument->id.'/april-fee-receipt.pdf',
                'disk' => 'local',
                'mime_type' => 'application/pdf',
                'file_size' => 98400,
                'checksum' => hash('sha256', 'april-fee-receipt'),
                'uploaded_by' => $admin->id,
                'is_current' => true,
            ]
        );

        foreach (['IDENTITY', 'VERIFIED'] as $code) {
            $tag = DocumentTag::withoutGlobalScopes()->where('school_id', $school->id)->where('code', $code)->first();

            if ($tag) {
                $studentDocument->tags()->syncWithoutDetaching([$tag->id => ['school_id' => $school->id]]);
            }
        }

        if ($staff) {
            $staffCategory = DocumentCategory::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->where('code', 'STAFF-QUALIFICATION-CERTIFICATE')
                ->firstOrFail();

            $staffDocument = Document::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'document_no' => 'DOC-STF-0001',
                ],
                [
                    'category_id' => $staffCategory->id,
                    'folder_id' => null,
                    'owner_type' => 'staff',
                    'owner_id' => $staff->id,
                    'title' => 'Staff Qualification Record',
                    'description' => 'Qualification certificate document for staff profile.',
                    'issue_date' => now()->subYears(2)->toDateString(),
                    'expiry_date' => null,
                    'verification_status' => 'pending',
                    'status' => 'active',
                    'created_by' => $admin->id,
                ]
            );

            DocumentFile::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'document_id' => $staffDocument->id,
                    'version_no' => 1,
                ],
                [
                    'file_name' => 'staff-qualification.pdf',
                    'original_file_name' => 'staff-qualification.pdf',
                    'file_path' => 'documents/'.$school->id.'/staff/'.$staff->id.'/'.$staffDocument->id.'/staff-qualification.pdf',
                    'disk' => 'local',
                    'mime_type' => 'application/pdf',
                    'file_size' => 143200,
                    'checksum' => hash('sha256', 'staff-qualification-v1'),
                    'uploaded_by' => $admin->id,
                    'is_current' => true,
                ]
            );
        }

        DocumentAuditLog::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'document_id' => $studentDocument->id,
                'action' => 'uploaded',
                'performed_by' => $admin->id,
            ],
            [
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder',
                'metadata' => ['seeded' => true],
            ]
        );
    }
}

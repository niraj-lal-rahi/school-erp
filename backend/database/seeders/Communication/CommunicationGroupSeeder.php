<?php

namespace Database\Seeders\Communication;

use App\Models\Communication\CommunicationGroup;
use App\Models\Guardian;
use App\Models\HR\Staff;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Seeder;

class CommunicationGroupSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $class = SchoolClass::withoutGlobalScopes()->where('school_id', $school->id)->orderBy('id')->first();
        $section = $class
            ? Section::withoutGlobalScopes()->where('school_id', $school->id)->where('school_class_id', $class->id)->orderBy('id')->first()
            : null;

        $staffGroup = CommunicationGroup::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'ALL-STAFF'],
            [
                'name' => 'All Staff',
                'group_type' => 'staff',
                'class_id' => null,
                'section_id' => null,
                'description' => 'Seeded staff communication group.',
                'status' => 'active',
            ]
        );

        $parentsGroup = CommunicationGroup::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'ALL-PARENTS'],
            [
                'name' => 'All Parents',
                'group_type' => 'custom',
                'class_id' => null,
                'section_id' => null,
                'description' => 'Seeded guardian communication group.',
                'status' => 'active',
            ]
        );

        $classGroup = CommunicationGroup::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'CLASS-GROUP-1'],
            [
                'name' => 'Class Group',
                'group_type' => 'class',
                'class_id' => $class?->id,
                'section_id' => $section?->id,
                'description' => 'Seeded class communication group.',
                'status' => 'active',
            ]
        );

        $this->syncMembers($staffGroup, Staff::withoutGlobalScopes()->where('school_id', $school->id)->get()->map(fn ($staff) => [
            'member_type' => 'staff',
            'member_id' => $staff->id,
        ])->all());

        $this->syncMembers($parentsGroup, Guardian::withoutGlobalScopes()->where('school_id', $school->id)->get()->map(fn ($guardian) => [
            'member_type' => 'guardian',
            'member_id' => $guardian->id,
        ])->all());

        $studentMembers = Student::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->when($class, fn ($query) => $query->whereHas('enrollments', fn ($enrollmentQuery) => $enrollmentQuery->where('is_current', true)->where('school_class_id', $class->id)))
            ->get()
            ->map(fn ($student) => [
                'member_type' => 'student',
                'member_id' => $student->id,
            ])
            ->all();

        $this->syncMembers($classGroup, $studentMembers);
    }

    protected function syncMembers(CommunicationGroup $group, array $members): void
    {
        $group->members()->delete();

        foreach ($members as $member) {
            $group->members()->create([
                'school_id' => $group->school_id,
                'member_type' => $member['member_type'],
                'member_id' => $member['member_id'],
                'joined_at' => now(),
            ]);
        }
    }
}

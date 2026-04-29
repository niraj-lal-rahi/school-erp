<?php

namespace App\Services\Examination;

use App\Events\Examination\ResultsPublished;
use App\Models\Examination\Exam;
use App\Models\Examination\ResultPublication;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResultPublicationService
{
    public function publish(Exam $exam, ?int $publishedBy, array $options = []): ResultPublication
    {
        if ($exam->result_status === 'archived') {
            throw ValidationException::withMessages([
                'exam' => 'Archived exams cannot be published.',
            ]);
        }

        return DB::transaction(function () use ($exam, $publishedBy, $options): ResultPublication {
            $exam->loadMissing(['studentResults.student.guardians']);

            if ($exam->studentResults->isEmpty()) {
                throw ValidationException::withMessages([
                    'results' => 'Compute results before publishing them.',
                ]);
            }

            $publication = ResultPublication::query()->updateOrCreate(
                [
                    'school_id' => $exam->school_id,
                    'exam_id' => $exam->id,
                ],
                [
                    'published_by' => $publishedBy,
                    'published_at' => now(),
                    'is_public' => (bool) ($options['is_public'] ?? false),
                    'notify_users' => (bool) ($options['notify_users'] ?? true),
                ]
            );

            $exam->update([
                'result_status' => 'published',
            ]);

            $publication = $publication->fresh(['exam', 'publisher']);
            event(new ResultsPublished($publication, $options));

            return $publication;
        });
    }
}

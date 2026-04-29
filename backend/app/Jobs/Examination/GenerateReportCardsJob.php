<?php

namespace App\Jobs\Examination;

use App\Models\Examination\StudentResult;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateReportCardsJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $examId,
        public ?int $studentId = null,
    ) {
    }

    public function handle(): void
    {
        $results = StudentResult::query()
            ->with(['exam.schoolClass', 'exam.section', 'student', 'resultSubjectDetails.subject'])
            ->where('exam_id', $this->examId)
            ->when($this->studentId, fn ($query) => $query->where('student_id', $this->studentId))
            ->get();

        foreach ($results as $result) {
            $path = sprintf(
                'report-cards/school-%d/exam-%d/student-%d.html',
                $result->school_id,
                $result->exam_id,
                $result->student_id
            );

            Storage::disk(config('filesystems.default'))->put($path, $this->buildHtml($result));
        }
    }

    protected function buildHtml(StudentResult $result): string
    {
        $subjectRows = $result->resultSubjectDetails->map(function ($detail): string {
            return sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                e($detail->subject?->name ?? 'Subject'),
                number_format((float) $detail->max_marks, 2),
                number_format((float) $detail->obtained_marks, 2),
                e((string) ($detail->grade ?? '-')),
                $detail->is_pass ? 'Pass' : 'Fail'
            );
        })->implode('');

        return sprintf(
            '<html><head><meta charset="utf-8"><title>Report Card</title></head><body><h1>%s</h1><p>Student: %s</p><p>Exam: %s</p><p>Percentage: %s%%</p><p>Grade: %s</p><p>Rank: %s</p><table border="1" cellspacing="0" cellpadding="6"><thead><tr><th>Subject</th><th>Max Marks</th><th>Obtained</th><th>Grade</th><th>Status</th></tr></thead><tbody>%s</tbody></table></body></html>',
            e($result->exam?->schoolClass?->name ? $result->exam->schoolClass->name.' Report Card' : 'Report Card'),
            e($result->student?->full_name ?? 'Student'),
            e($result->exam?->name ?? 'Exam'),
            number_format((float) $result->percentage, 2),
            e((string) ($result->grade ?? '-')),
            e((string) ($result->rank ?? '-')),
            $subjectRows
        );
    }
}

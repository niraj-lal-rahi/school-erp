<?php

namespace App\Services\Examination;

use App\Models\Examination\Exam;
use App\Models\Examination\StudentResult;
use App\Repositories\Eloquent\Examination\StudentResultRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MeritListService
{
    public function __construct(
        protected StudentResultRepository $results,
    ) {
    }

    public function assignRanks(Exam $exam): Collection
    {
        return DB::transaction(function () use ($exam): Collection {
            $results = $this->results->byExam($exam->id)
                ->sort(function (StudentResult $left, StudentResult $right): int {
                    $comparison = (float) $right->obtained_marks <=> (float) $left->obtained_marks;
                    if ($comparison !== 0) {
                        return $comparison;
                    }

                    $comparison = (float) $right->percentage <=> (float) $left->percentage;
                    if ($comparison !== 0) {
                        return $comparison;
                    }

                    return (int) $left->student_id <=> (int) $right->student_id;
                })
                ->values();

            $eligibleIndex = 0;
            $currentRank = 0;
            $lastScoreKey = null;

            foreach ($results as $result) {
                if (in_array($result->result_status, ['absent', 'withheld'], true)) {
                    $result->update(['rank' => null]);
                    continue;
                }

                $eligibleIndex++;
                $scoreKey = sprintf('%.2f|%.2f', (float) $result->obtained_marks, (float) $result->percentage);

                if ($scoreKey !== $lastScoreKey) {
                    $currentRank = $eligibleIndex;
                    $lastScoreKey = $scoreKey;
                }

                $result->update(['rank' => $currentRank]);
            }

            return $this->results->byExam($exam->id);
        });
    }
}

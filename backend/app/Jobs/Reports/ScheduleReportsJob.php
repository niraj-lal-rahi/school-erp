<?php

namespace App\Jobs\Reports;

use App\Models\Reports\ReportSchedule;
use App\Repositories\Contracts\Reports\ReportScheduleRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScheduleReportsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ?int $schoolId = null,
    ) {
    }

    public function handle(ReportScheduleRepositoryInterface $schedules): void
    {
        $dueSchedules = $schedules->dueSchedules();

        foreach ($dueSchedules as $schedule) {
            if ($this->schoolId !== null && $schedule->school_id !== $this->schoolId) {
                continue;
            }

            $context = [
                'schedule_id' => $schedule->id,
                'channel' => $schedule->channel,
                'recipients' => $schedule->recipients ?? [],
                'report_name' => $schedule->reportDefinition?->name,
                'report_code' => $schedule->reportDefinition?->code,
            ];

            RunReportJob::dispatch(
                $schedule->report_definition_id,
                $schedule->reportDefinition?->default_filters ?? [],
                'pdf',
                'scheduled',
                $schedule->created_by,
                $context,
            );

            $schedules->update($schedule, $this->scheduleUpdatePayload($schedule));
        }
    }

    protected function scheduleUpdatePayload(ReportSchedule $schedule): array
    {
        $nextRunAt = $this->computeNextRunAt($schedule);

        return [
            'last_run_at' => now(),
            'next_run_at' => $nextRunAt,
            'status' => $schedule->schedule_type === 'once' ? 'paused' : $schedule->status,
        ];
    }

    protected function computeNextRunAt(ReportSchedule $schedule): ?Carbon
    {
        $config = $schedule->schedule_config ?? [];
        $time = (string) ($config['time'] ?? '08:00');
        $base = $schedule->next_run_at instanceof Carbon
            ? $schedule->next_run_at->copy()
            : now()->copy();

        if ($schedule->schedule_type === 'once') {
            return null;
        }

        if ($schedule->schedule_type === 'daily') {
            return $base->copy()->addDay()->setTimeFromTimeString($time);
        }

        if ($schedule->schedule_type === 'weekly') {
            $targetDay = strtolower((string) ($config['day_of_week'] ?? 'monday'));
            $dayMap = [
                'sunday' => Carbon::SUNDAY,
                'monday' => Carbon::MONDAY,
                'tuesday' => Carbon::TUESDAY,
                'wednesday' => Carbon::WEDNESDAY,
                'thursday' => Carbon::THURSDAY,
                'friday' => Carbon::FRIDAY,
                'saturday' => Carbon::SATURDAY,
            ];

            $candidate = $base->copy()->next($dayMap[$targetDay] ?? Carbon::MONDAY);

            return $candidate->setTimeFromTimeString($time);
        }

        $targetDayOfMonth = max(1, min(28, (int) ($config['day_of_month'] ?? 1)));
        $candidate = $base->copy()->addMonthNoOverflow()->startOfMonth()->setDay($targetDayOfMonth);

        return $candidate->setTimeFromTimeString($time);
    }
}

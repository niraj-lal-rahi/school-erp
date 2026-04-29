<?php

namespace App\Services\Reports;

use App\Models\Communication\AnnouncementRecipient;
use App\Models\Communication\CommunicationMessage;
use App\Models\Communication\NotificationLog;
use Illuminate\Database\Eloquent\Builder;

class CommunicationReportService
{
    public function deliveryRates(array $filters = []): array
    {
        $query = $this->notificationQuery($filters);
        $total = (clone $query)->count();
        $sent = (clone $query)->whereIn('status', ['sent', 'delivered', 'read'])->count();
        $delivered = (clone $query)->whereIn('status', ['delivered', 'read'])->count();
        $read = (clone $query)->where('status', 'read')->count();
        $failed = (clone $query)->whereIn('status', ['failed', 'bounced'])->count();

        return [
            'summary' => [
                'total_notifications' => $total,
                'sent_count' => $sent,
                'delivered_count' => $delivered,
                'read_count' => $read,
                'failed_count' => $failed,
                'delivery_rate' => $total > 0 ? round(($delivered / $total) * 100, 2) : 0.0,
                'read_rate' => $total > 0 ? round(($read / $total) * 100, 2) : 0.0,
            ],
        ];
    }

    public function channelPerformance(array $filters = []): array
    {
        $rows = $this->notificationQuery($filters)
            ->selectRaw('channel, COUNT(*) as total_count, SUM(CASE WHEN status IN ("delivered", "read") THEN 1 ELSE 0 END) as delivered_count, SUM(CASE WHEN status = "read" THEN 1 ELSE 0 END) as read_count, SUM(CASE WHEN status IN ("failed", "bounced") THEN 1 ELSE 0 END) as failed_count')
            ->groupBy('channel')
            ->orderBy('channel')
            ->get()
            ->map(fn ($row) => [
                'channel' => $row->channel,
                'total_count' => (int) $row->total_count,
                'delivered_count' => (int) $row->delivered_count,
                'read_count' => (int) $row->read_count,
                'failed_count' => (int) $row->failed_count,
                'delivery_rate' => (int) $row->total_count > 0 ? round(((int) $row->delivered_count / (int) $row->total_count) * 100, 2) : 0.0,
                'read_rate' => (int) $row->total_count > 0 ? round(((int) $row->read_count / (int) $row->total_count) * 100, 2) : 0.0,
            ])
            ->values()
            ->all();

        return ['rows' => $rows];
    }

    public function announcementEngagement(array $filters = []): array
    {
        $query = $this->announcementRecipientQuery($filters);
        $total = (clone $query)->count();
        $read = (clone $query)->whereNotNull('read_at')->count();
        $acknowledged = (clone $query)->whereNotNull('acknowledged_at')->count();

        return [
            'summary' => [
                'total_recipients' => $total,
                'read_count' => $read,
                'acknowledged_count' => $acknowledged,
                'read_rate' => $total > 0 ? round(($read / $total) * 100, 2) : 0.0,
                'acknowledgement_rate' => $total > 0 ? round(($acknowledged / $total) * 100, 2) : 0.0,
            ],
            'by_recipient_type' => $this->announcementRecipientQuery($filters)
                ->selectRaw('recipient_type, COUNT(*) as total_count, SUM(CASE WHEN read_at IS NOT NULL THEN 1 ELSE 0 END) as read_count, SUM(CASE WHEN acknowledged_at IS NOT NULL THEN 1 ELSE 0 END) as acknowledged_count')
                ->groupBy('recipient_type')
                ->orderBy('recipient_type')
                ->get()
                ->map(fn ($row) => [
                    'recipient_type' => $row->recipient_type,
                    'total_count' => (int) $row->total_count,
                    'read_count' => (int) $row->read_count,
                    'acknowledged_count' => (int) $row->acknowledged_count,
                ])
                ->values()
                ->all(),
        ];
    }

    public function messageVolume(array $filters = []): array
    {
        $rows = $this->messageQuery($filters)
            ->selectRaw('DATE(created_at) as activity_date, message_type, COUNT(*) as total_count')
            ->groupBy('activity_date', 'message_type')
            ->orderBy('activity_date')
            ->get()
            ->map(fn ($row) => [
                'activity_date' => $row->activity_date,
                'message_type' => $row->message_type,
                'total_count' => (int) $row->total_count,
            ])
            ->values()
            ->all();

        return ['rows' => $rows];
    }

    public function overview(array $filters = []): array
    {
        return [
            'delivery' => $this->deliveryRates($filters),
            'channels' => $this->channelPerformance($filters),
            'announcement_engagement' => $this->announcementEngagement($filters),
            'message_volume' => $this->messageVolume($filters),
        ];
    }

    protected function notificationQuery(array $filters = []): Builder
    {
        return NotificationLog::query()
            ->when($filters['channel'] ?? null, fn (Builder $query, string $value) => $query->where('channel', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value));
    }

    protected function announcementRecipientQuery(array $filters = []): Builder
    {
        return AnnouncementRecipient::query()
            ->when($filters['recipient_type'] ?? null, fn (Builder $query, string $value) => $query->where('recipient_type', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value));
    }

    protected function messageQuery(array $filters = []): Builder
    {
        return CommunicationMessage::query()
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['recipient_type'] ?? null, fn (Builder $query, string $value) => $query->where('recipient_type', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value));
    }
}

<?php

namespace App\Services\Communication;

use App\Events\Communication\ScheduledMessageProcessed;
use App\Models\Communication\ScheduledMessage;
use App\Repositories\Contracts\Communication\ScheduledMessageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ScheduledMessageService
{
    public function __construct(
        protected ScheduledMessageRepositoryInterface $scheduledMessages,
        protected RecipientResolverService $recipientResolver,
        protected NotificationService $notifications,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->scheduledMessages->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): ScheduledMessage
    {
        return $this->scheduledMessages->findOrFail($id);
    }

    public function create(array $attributes): ScheduledMessage
    {
        return DB::transaction(function () use ($attributes): ScheduledMessage {
            $scheduledMessage = $this->scheduledMessages->create([
                ...$attributes,
                'status' => $attributes['status'] ?? 'pending',
            ]);

            $this->queueMessageDelivery($scheduledMessage);

            return $this->scheduledMessages->findOrFail($scheduledMessage->id);
        });
    }

    public function update(ScheduledMessage $scheduledMessage, array $attributes): ScheduledMessage
    {
        return DB::transaction(fn (): ScheduledMessage => $this->scheduledMessages->update($scheduledMessage, $attributes));
    }

    public function delete(ScheduledMessage $scheduledMessage): void
    {
        DB::transaction(function () use ($scheduledMessage): void {
            $this->scheduledMessages->delete($scheduledMessage);
        });
    }

    public function cancel(ScheduledMessage $scheduledMessage): ScheduledMessage
    {
        return DB::transaction(fn (): ScheduledMessage => $this->scheduledMessages->update($scheduledMessage, [
            'status' => 'cancelled',
        ]));
    }

    public function processDueScheduledMessages(?int $schoolId = null): Collection
    {
        $messages = ScheduledMessage::query()
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->get();

        return $messages->map(fn (ScheduledMessage $message) => $this->processSingleScheduledMessage($message));
    }

    public function processSingleScheduledMessage(ScheduledMessage $scheduledMessage): ScheduledMessage
    {
        return DB::transaction(function () use ($scheduledMessage): ScheduledMessage {
            $scheduledMessage = $this->scheduledMessages->update($scheduledMessage, [
                'status' => 'processing',
            ]);

            $recipients = $this->recipientResolver->resolve($scheduledMessage->audience_type, [
                'class_id' => $scheduledMessage->class_id,
                'section_id' => $scheduledMessage->section_id,
            ]);

            $this->notifications->sendToRecipients($recipients, [
                'school_id' => $scheduledMessage->school_id,
                'template_id' => $scheduledMessage->template_id,
                'subject' => $scheduledMessage->title,
                'message' => $scheduledMessage->message,
                'channel' => $scheduledMessage->channel,
            ]);

            $processed = $this->scheduledMessages->update($scheduledMessage, [
                'status' => 'sent',
                'processed_at' => now(),
            ]);

            event(new ScheduledMessageProcessed($processed->refresh()));

            return $processed;
        });
    }

    public function queueMessageDelivery(ScheduledMessage $scheduledMessage): void
    {
        $jobClass = 'App\\Jobs\\Communication\\ProcessScheduledMessagesJob';

        if (class_exists($jobClass)) {
            dispatch(new $jobClass($scheduledMessage->school_id));
        }
    }
}

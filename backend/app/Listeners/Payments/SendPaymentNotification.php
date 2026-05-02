<?php

namespace App\Listeners\Payments;

use App\Events\Payments\PaymentFailed;
use App\Events\Payments\PaymentInitiated;
use App\Events\Payments\PaymentRefunded;
use App\Events\Payments\PaymentSuccessful;
use App\Events\Payments\UpiPaymentManuallyVerified;
use App\Events\Payments\UpiPaymentPendingVerification;
use App\Models\Payments\PaymentTransaction;
use App\Models\User;
use App\Services\Communication\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class SendPaymentNotification implements ShouldQueue
{
    public function __construct(
        protected NotificationService $notifications,
    ) {
    }

    public function handle(object $event): void
    {
        [$transaction, $subject, $message] = $this->buildNotification($event);

        if (! $transaction || ! $message) {
            return;
        }

        $recipients = $this->resolveRecipients($transaction);

        if ($recipients->isEmpty()) {
            return;
        }

        $this->notifications->sendToRecipients($recipients, [
            'school_id' => $transaction->school_id,
            'subject' => $subject,
            'message' => $message,
            'channel' => 'in_app',
            'channels' => ['in_app'],
            'provider' => 'payments',
        ]);
    }

    protected function buildNotification(object $event): array
    {
        return match (true) {
            $event instanceof PaymentInitiated => [
                $event->transaction,
                'Payment initiated',
                sprintf('Payment %s for %s %.2f has been initiated.', $event->transaction->transaction_no, $event->transaction->currency, (float) $event->transaction->amount),
            ],
            $event instanceof PaymentSuccessful => [
                $event->transaction,
                'Payment successful',
                sprintf('Payment %s was completed successfully.', $event->transaction->transaction_no),
            ],
            $event instanceof PaymentFailed => [
                $event->transaction,
                'Payment failed',
                sprintf('Payment %s failed%s.', $event->transaction->transaction_no, $event->reason ? ': '.$event->reason : ''),
            ],
            $event instanceof PaymentRefunded => [
                $event->transaction,
                'Refund processed',
                sprintf('Refund %s for payment %s has been processed.', $event->refund->refund_no, $event->transaction->transaction_no),
            ],
            $event instanceof UpiPaymentPendingVerification => [
                $event->transaction,
                'UPI payment pending verification',
                sprintf('UPI payment %s is pending verification. Reference submission may be required.', $event->transaction->transaction_no),
            ],
            $event instanceof UpiPaymentManuallyVerified => [
                $event->transaction,
                'UPI payment verified',
                sprintf('UPI payment %s has been manually verified.', $event->transaction->transaction_no),
            ],
            default => [null, null, null],
        };
    }

    protected function resolveRecipients(PaymentTransaction $transaction): Collection
    {
        $recipients = collect();

        if ($transaction->student?->user) {
            $recipients->push($this->makeRecipient($transaction->student->user));
        }

        $tenantAdmins = User::query()
            ->withoutGlobalScopes()
            ->where('school_id', $transaction->school_id)
            ->whereHas('roles', fn ($query) => $query->whereIn('roles.code', ['tenant_admin', 'super_admin'])->orWhereIn('roles.slug', ['school-admin', 'super_admin']))
            ->get();

        foreach ($tenantAdmins as $user) {
            $recipients->push($this->makeRecipient($user));
        }

        return $recipients
            ->unique(fn (array $recipient) => $recipient['recipient_type'].'-'.$recipient['recipient_id'])
            ->values();
    }

    protected function makeRecipient(User $user): array
    {
        return [
            'recipient_type' => User::class,
            'recipient_id' => $user->id,
            'recipient' => $user,
        ];
    }
}

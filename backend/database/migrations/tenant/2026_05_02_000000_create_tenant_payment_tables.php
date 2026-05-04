<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        $schema->create('payment_gateways', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('name');
            $table->string('code');
            $table->enum('provider', ['razorpay', 'stripe', 'upi_manual', 'offline']);
            $table->enum('mode', ['test', 'live'])->default('test');
            $table->json('config')->nullable();
            $table->boolean('supports_upi')->default(false);
            $table->boolean('supports_card')->default(false);
            $table->boolean('supports_netbanking')->default(false);
            $table->boolean('supports_wallet')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'payment_gateways_school_code_unique');
            $table->index(['school_id', 'status'], 'payment_gateways_school_status_idx');
            $table->index(['provider', 'mode'], 'payment_gateways_provider_mode_idx');
        });

        $schema->create('payment_gateway_credentials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('payment_gateway_id')->constrained('payment_gateways')->cascadeOnDelete();
            $table->string('key_name');
            $table->longText('key_value');
            $table->boolean('is_encrypted')->default(true);
            $table->timestamps();

            $table->unique(['payment_gateway_id', 'key_name'], 'payment_gateway_credentials_gateway_key_unique');
            $table->index(['school_id', 'payment_gateway_id'], 'payment_gateway_credentials_school_gateway_idx');
        });

        $schema->create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('transaction_no');
            $table->enum('payable_type', ['school_fee', 'saas_subscription', 'other']);
            $table->unsignedBigInteger('payable_id')->nullable();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->unsignedBigInteger('tenant_subscription_id')->nullable();
            $table->foreignId('gateway_id')->nullable()->constrained('payment_gateways')->nullOnDelete();
            $table->enum('provider', ['razorpay', 'stripe', 'upi_manual', 'offline']);
            $table->enum('payment_method', ['upi', 'card', 'netbanking', 'wallet', 'cash', 'bank_transfer', 'cheque', 'other']);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('INR');
            $table->string('gateway_order_id')->nullable();
            $table->string('gateway_payment_id')->nullable();
            $table->text('gateway_signature')->nullable();
            $table->string('upi_vpa')->nullable();
            $table->string('upi_reference_no')->nullable();
            $table->text('upi_qr_payload')->nullable();
            $table->enum('status', ['pending', 'initiated', 'successful', 'failed', 'cancelled', 'refunded', 'manually_verified'])->default('pending');
            $table->enum('verification_status', ['pending', 'verified', 'failed', 'manual_review'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'transaction_no'], 'payment_transactions_school_no_unique');
            $table->index(['school_id', 'student_id'], 'payment_transactions_school_student_idx');
            $table->index(['payable_type', 'payable_id'], 'payment_transactions_payable_idx');
            $table->index(['school_id', 'status'], 'payment_transactions_school_status_idx');
            $table->index(['school_id', 'provider'], 'payment_transactions_school_provider_idx');
            $table->index(['school_id', 'payment_method'], 'payment_transactions_school_method_idx');
            $table->index(['school_id', 'verification_status'], 'payment_transactions_school_verification_idx');
            $table->index(['school_id', 'tenant_subscription_id'], 'payment_transactions_school_subscription_idx');
            $table->index('gateway_order_id', 'payment_transactions_gateway_order_idx');
            $table->index('gateway_payment_id', 'payment_transactions_gateway_payment_idx');
            $table->index('upi_reference_no', 'payment_transactions_upi_reference_idx');
        });

        $schema->create('payment_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->enum('provider', ['razorpay', 'stripe']);
            $table->string('event_type');
            $table->string('event_id')->nullable();
            $table->longText('payload');
            $table->text('signature')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'provider'], 'payment_webhook_events_school_provider_idx');
            $table->index(['provider', 'processed'], 'payment_webhook_events_provider_processed_idx');
            $table->index('event_id', 'payment_webhook_events_event_id_idx');
        });

        $schema->create('payment_refunds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained('payment_transactions')->cascadeOnDelete();
            $table->string('refund_no');
            $table->string('gateway_refund_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->text('reason')->nullable();
            $table->enum('status', ['requested', 'processing', 'successful', 'failed', 'cancelled'])->default('requested');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'refund_no'], 'payment_refunds_school_no_unique');
            $table->index(['school_id', 'transaction_id'], 'payment_refunds_school_transaction_idx');
            $table->index(['school_id', 'status'], 'payment_refunds_school_status_idx');
            $table->index('gateway_refund_id', 'payment_refunds_gateway_refund_idx');
        });

        $schema->create('payment_reconciliations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained('payment_transactions')->cascadeOnDelete();
            $table->enum('source', ['webhook', 'manual', 'gateway_api', 'bank_statement']);
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reconciled_at')->nullable();
            $table->text('remarks')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'transaction_id'], 'payment_reconciliations_school_transaction_idx');
            $table->index(['school_id', 'source'], 'payment_reconciliations_school_source_idx');
            $table->index(['school_id', 'new_status'], 'payment_reconciliations_school_status_idx');
        });

        $schema->create('upi_payment_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained('payment_transactions')->cascadeOnDelete();
            $table->string('upi_vpa');
            $table->string('payee_name')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('INR');
            $table->text('qr_payload')->nullable();
            $table->string('qr_image_path')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->enum('status', ['pending', 'paid', 'expired', 'cancelled', 'verified'])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['transaction_id'], 'upi_payment_requests_transaction_unique');
            $table->index(['school_id', 'status'], 'upi_payment_requests_school_status_idx');
            $table->index(['school_id', 'expires_at'], 'upi_payment_requests_school_expiry_idx');
            $table->index(['school_id', 'upi_vpa'], 'upi_payment_requests_school_vpa_idx');
        });
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        $schema->dropIfExists('upi_payment_requests');
        $schema->dropIfExists('payment_reconciliations');
        $schema->dropIfExists('payment_refunds');
        $schema->dropIfExists('payment_webhook_events');
        $schema->dropIfExists('payment_transactions');
        $schema->dropIfExists('payment_gateway_credentials');
        $schema->dropIfExists('payment_gateways');
    }
};

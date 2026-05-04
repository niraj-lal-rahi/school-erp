<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        $this->ensureIndexExists('schools', ['status', 'trial_ends_at'], 'schools_status_trial_ends_at_idx');

        $this->ensureIndexExists('users', ['school_id', 'status'], 'users_school_status_idx');
        $this->ensureIndexExists('users', ['school_id', 'created_at'], 'users_school_created_at_idx');

        $this->ensureIndexExists('students', ['school_id', 'admission_date'], 'students_school_admission_date_idx');
        $this->ensureIndexExists('students', ['school_id', 'created_at'], 'students_school_created_at_idx');

        $this->ensureIndexExists('guardians', ['school_id', 'created_at'], 'guardians_school_created_at_idx');

        $this->ensureIndexExists('staff', ['school_id', 'department_id', 'current_status'], 'staff_school_department_status_idx');
        $this->ensureIndexExists('staff', ['school_id', 'designation_id', 'current_status'], 'staff_school_designation_status_idx');
        $this->ensureIndexExists('staff', ['school_id', 'joining_date'], 'staff_school_joining_date_idx');

        $this->ensureIndexExists('attendance_student_sessions', ['school_id', 'status', 'attendance_date'], 'attendance_sessions_school_status_date_idx');
        $this->ensureIndexExists('staff_attendance', ['school_id', 'attendance_date', 'attendance_status'], 'staff_attendance_school_date_status_idx');

        $this->ensureIndexExists('finance_fee_invoices', ['school_id', 'status', 'due_date'], 'finance_invoices_school_status_due_idx');
        $this->ensureIndexExists('finance_fee_invoices', ['school_id', 'created_at'], 'finance_invoices_school_created_idx');
        $this->ensureIndexExists('finance_payments', ['school_id', 'status', 'payment_date'], 'finance_payments_school_status_date_idx');
        $this->ensureIndexExists('finance_payments', ['school_id', 'created_at'], 'finance_payments_school_created_idx');

        $this->ensureIndexExists('exam_marks', ['school_id', 'exam_id', 'student_id'], 'exam_marks_school_exam_student_ix');
        $this->ensureIndexExists('student_results', ['school_id', 'exam_id', 'result_status'], 'student_results_school_exam_status_ix');

        $this->ensureIndexExists('communication_messages', ['school_id', 'status', 'created_at'], 'comm_messages_school_status_created_ix');
        $this->ensureIndexExists('notification_logs', ['school_id', 'status', 'created_at'], 'notification_logs_school_status_created_ix');

        $this->ensureIndexExists('payment_transactions', ['school_id', 'payable_type', 'payable_id'], 'payment_transactions_school_payable_idx');
        $this->ensureIndexExists('payment_transactions', ['school_id', 'created_at'], 'payment_transactions_school_created_idx');

        $this->ensureIndexExists('documents', ['school_id', 'status'], 'documents_school_status_index');
        $this->ensureIndexExists('documents', ['school_id', 'created_at'], 'documents_school_created_at_index');
        $this->ensureIndexExists('document_audit_logs', ['school_id', 'action', 'created_at'], 'document_audit_logs_school_action_created_index');

        $this->ensureIndexExists('workflow_instances', ['school_id', 'reference_type', 'reference_id'], 'workflow_instances_school_reference_idx');
        $this->ensureIndexExists('workflow_instances', ['school_id', 'status', 'created_at'], 'workflow_instances_school_status_created_idx');
        $this->ensureIndexExists('approval_requests', ['school_id', 'approver_id', 'status'], 'approval_requests_school_approver_status_idx');
        $this->ensureIndexExists('automation_runs', ['school_id', 'automation_rule_id', 'status'], 'automation_runs_school_rule_status_idx');

        $this->ensureIndexExists('report_runs', ['school_id', 'report_definition_id', 'status'], 'report_runs_school_definition_status_ix');
        $this->ensureIndexExists('report_exports', ['school_id', 'created_at'], 'report_exports_school_created_ix');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('report_exports', 'report_exports_school_created_ix');
        $this->dropIndexIfExists('report_runs', 'report_runs_school_definition_status_ix');

        $this->dropIndexIfExists('automation_runs', 'automation_runs_school_rule_status_idx');
        $this->dropIndexIfExists('approval_requests', 'approval_requests_school_approver_status_idx');
        $this->dropIndexIfExists('workflow_instances', 'workflow_instances_school_status_created_idx');
        $this->dropIndexIfExists('workflow_instances', 'workflow_instances_school_reference_idx');

        $this->dropIndexIfExists('document_audit_logs', 'document_audit_logs_school_action_created_index');
        $this->dropIndexIfExists('documents', 'documents_school_created_at_index');
        $this->dropIndexIfExists('documents', 'documents_school_status_index');

        $this->dropIndexIfExists('payment_transactions', 'payment_transactions_school_created_idx');
        $this->dropIndexIfExists('payment_transactions', 'payment_transactions_school_payable_idx');

        $this->dropIndexIfExists('notification_logs', 'notification_logs_school_status_created_ix');
        $this->dropIndexIfExists('communication_messages', 'comm_messages_school_status_created_ix');

        $this->dropIndexIfExists('student_results', 'student_results_school_exam_status_ix');
        $this->dropIndexIfExists('exam_marks', 'exam_marks_school_exam_student_ix');

        $this->dropIndexIfExists('finance_payments', 'finance_payments_school_created_idx');
        $this->dropIndexIfExists('finance_payments', 'finance_payments_school_status_date_idx');
        $this->dropIndexIfExists('finance_fee_invoices', 'finance_invoices_school_created_idx');
        $this->dropIndexIfExists('finance_fee_invoices', 'finance_invoices_school_status_due_idx');

        $this->dropIndexIfExists('staff_attendance', 'staff_attendance_school_date_status_idx');
        $this->dropIndexIfExists('attendance_student_sessions', 'attendance_sessions_school_status_date_idx');

        $this->dropIndexIfExists('staff', 'staff_school_joining_date_idx');
        $this->dropIndexIfExists('staff', 'staff_school_designation_status_idx');
        $this->dropIndexIfExists('staff', 'staff_school_department_status_idx');

        $this->dropIndexIfExists('guardians', 'guardians_school_created_at_idx');

        $this->dropIndexIfExists('students', 'students_school_created_at_idx');
        $this->dropIndexIfExists('students', 'students_school_admission_date_idx');

        $this->dropIndexIfExists('users', 'users_school_created_at_idx');
        $this->dropIndexIfExists('users', 'users_school_status_idx');

        $this->dropIndexIfExists('schools', 'schools_status_trial_ends_at_idx');
    }

    protected function ensureIndexExists(string $tableName, array $columns, string $indexName): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::connection($this->connection)->table($tableName, function (Blueprint $table) use ($columns, $indexName): void {
            $table->index($columns, $indexName);
        });
    }

    protected function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (! $this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::connection($this->connection)->table($tableName, function (Blueprint $table) use ($indexName): void {
            $table->dropIndex($indexName);
        });
    }

    protected function indexExists(string $tableName, string $indexName): bool
    {
        $db = DB::connection($this->connection);

        if ($db->getDriverName() === 'sqlite') {
            return false;
        }

        return $db->table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', $db->getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }
};

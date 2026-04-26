<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_channels', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name');
            $table->string('code', 50);
            $table->string('channel_type', 20);
            $table->string('provider')->nullable();
            $table->json('configuration')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->index(['school_id'], 'comm_channels_school_ix');
            $table->index(['school_id', 'channel_type'], 'comm_channels_type_ix');
            $table->index(['school_id', 'status'], 'comm_channels_status_ix');
        });

        Schema::create('message_templates', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name');
            $table->string('code', 80);
            $table->string('template_type', 20);
            $table->string('subject')->nullable();
            $table->text('body');
            $table->json('variables')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->unique(['school_id', 'code'], 'msg_templates_school_code_uq');
            $table->index(['school_id'], 'msg_templates_school_ix');
            $table->index(['school_id', 'template_type'], 'msg_templates_type_ix');
            $table->index(['school_id', 'status'], 'msg_templates_status_ix');
        });

        Schema::create('announcements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('title');
            $table->text('content');
            $table->string('announcement_type', 30)->default('general');
            $table->string('audience_type', 30)->default('all');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('priority', 20)->default('normal');
            $table->string('status', 20)->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
            $table->foreign('class_id')->references('id')->on('school_classes')->nullOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('published_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['school_id'], 'announcements_school_ix');
            $table->index(['school_id', 'status'], 'announcements_status_ix');
            $table->index(['school_id', 'announcement_type'], 'announcements_type_ix');
            $table->index(['school_id', 'audience_type'], 'announcements_audience_ix');
            $table->index(['school_id', 'academic_year_id'], 'announcements_year_ix');
            $table->index(['school_id', 'class_id', 'section_id'], 'announcements_class_section_ix');
            $table->index(['school_id', 'publish_at', 'expires_at'], 'announcements_publish_ix');
        });

        Schema::create('announcement_recipients', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('announcement_id');
            $table->string('recipient_type', 20);
            $table->unsignedBigInteger('recipient_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('announcement_id')->references('id')->on('announcements')->cascadeOnDelete();
            $table->index(['school_id'], 'ann_recipients_school_ix');
            $table->index(['school_id', 'announcement_id'], 'ann_recipients_announcement_ix');
            $table->index(['school_id', 'recipient_type', 'recipient_id'], 'ann_recipients_target_ix');
            $table->index(['school_id', 'read_at'], 'ann_recipients_read_ix');
            $table->unique(['announcement_id', 'recipient_type', 'recipient_id'], 'ann_recipients_unique');
        });

        Schema::create('communication_conversations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('conversation_type', 30)->default('direct');
            $table->string('title')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['school_id'], 'comm_conversations_school_ix');
            $table->index(['school_id', 'conversation_type'], 'comm_conversations_type_ix');
            $table->index(['school_id', 'status'], 'comm_conversations_status_ix');
        });

        Schema::create('communication_messages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->string('sender_type', 20);
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('recipient_type', 20);
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('message_type', 20)->default('direct');
            $table->string('priority', 20)->default('normal');
            $table->string('status', 20)->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('conversation_id')->references('id')->on('communication_conversations')->nullOnDelete();
            $table->index(['school_id'], 'comm_messages_school_ix');
            $table->index(['school_id', 'conversation_id'], 'comm_messages_conversation_ix');
            $table->index(['school_id', 'sender_type', 'sender_id'], 'comm_messages_sender_ix');
            $table->index(['school_id', 'recipient_type', 'recipient_id'], 'comm_messages_recipient_ix');
            $table->index(['school_id', 'message_type'], 'comm_messages_type_ix');
            $table->index(['school_id', 'status'], 'comm_messages_status_ix');
            $table->index(['school_id', 'priority'], 'comm_messages_priority_ix');
            $table->index(['school_id', 'sent_at'], 'comm_messages_sent_ix');
        });

        Schema::create('conversation_participants', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('conversation_id');
            $table->string('participant_type', 20);
            $table->unsignedBigInteger('participant_id');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_muted')->default(false);
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('conversation_id')->references('id')->on('communication_conversations')->cascadeOnDelete();
            $table->index(['school_id'], 'conv_participants_school_ix');
            $table->index(['school_id', 'conversation_id'], 'conv_participants_conversation_ix');
            $table->index(['school_id', 'participant_type', 'participant_id'], 'conv_participants_member_ix');
            $table->unique(['conversation_id', 'participant_type', 'participant_id'], 'conv_participants_unique');
        });

        Schema::create('notification_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('notifiable_type', 20);
            $table->unsignedBigInteger('notifiable_id');
            $table->string('channel', 20);
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('provider')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('template_id')->references('id')->on('message_templates')->nullOnDelete();
            $table->index(['school_id'], 'notification_logs_school_ix');
            $table->index(['school_id', 'notifiable_type', 'notifiable_id'], 'notification_logs_notifiable_ix');
            $table->index(['school_id', 'channel'], 'notification_logs_channel_ix');
            $table->index(['school_id', 'status'], 'notification_logs_status_ix');
            $table->index(['school_id', 'sent_at'], 'notification_logs_sent_ix');
        });

        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('user_type', 20);
            $table->unsignedBigInteger('user_id');
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('push_enabled')->default(true);
            $table->boolean('in_app_enabled')->default(true);
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->index(['school_id'], 'notification_preferences_school_ix');
            $table->index(['school_id', 'user_type', 'user_id'], 'notification_preferences_user_ix');
            $table->unique(['school_id', 'user_type', 'user_id'], 'notification_preferences_unique');
        });

        Schema::create('scheduled_messages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('title');
            $table->text('message');
            $table->string('audience_type', 30)->default('all');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->string('channel', 20)->default('in_app');
            $table->timestamp('scheduled_at');
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('template_id')->references('id')->on('message_templates')->nullOnDelete();
            $table->foreign('class_id')->references('id')->on('school_classes')->nullOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['school_id'], 'scheduled_messages_school_ix');
            $table->index(['school_id', 'scheduled_at', 'status'], 'scheduled_messages_due_ix');
            $table->index(['school_id', 'audience_type'], 'scheduled_messages_audience_ix');
            $table->index(['school_id', 'channel'], 'scheduled_messages_channel_ix');
            $table->index(['school_id', 'class_id', 'section_id'], 'scheduled_messages_class_section_ix');
        });

        Schema::create('message_attachments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('message_id')->nullable();
            $table->unsignedBigInteger('announcement_id')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('message_id')->references('id')->on('communication_messages')->nullOnDelete();
            $table->foreign('announcement_id')->references('id')->on('announcements')->nullOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['school_id'], 'message_attachments_school_ix');
            $table->index(['school_id', 'message_id'], 'message_attachments_message_ix');
            $table->index(['school_id', 'announcement_id'], 'message_attachments_announcement_ix');
        });

        Schema::create('communication_groups', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name');
            $table->string('code', 80);
            $table->string('group_type', 20)->default('custom');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('class_id')->references('id')->on('school_classes')->nullOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->nullOnDelete();
            $table->unique(['school_id', 'code'], 'comm_groups_school_code_uq');
            $table->index(['school_id'], 'comm_groups_school_ix');
            $table->index(['school_id', 'group_type'], 'comm_groups_type_ix');
            $table->index(['school_id', 'status'], 'comm_groups_status_ix');
            $table->index(['school_id', 'class_id', 'section_id'], 'comm_groups_class_section_ix');
        });

        Schema::create('communication_group_members', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('group_id');
            $table->string('member_type', 20);
            $table->unsignedBigInteger('member_id');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('group_id')->references('id')->on('communication_groups')->cascadeOnDelete();
            $table->index(['school_id'], 'comm_group_members_school_ix');
            $table->index(['school_id', 'group_id'], 'comm_group_members_group_ix');
            $table->index(['school_id', 'member_type', 'member_id'], 'comm_group_members_member_ix');
            $table->unique(['group_id', 'member_type', 'member_id'], 'comm_group_members_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_group_members');
        Schema::dropIfExists('communication_groups');
        Schema::dropIfExists('message_attachments');
        Schema::dropIfExists('scheduled_messages');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('communication_messages');
        Schema::dropIfExists('communication_conversations');
        Schema::dropIfExists('announcement_recipients');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('communication_channels');
    }
};

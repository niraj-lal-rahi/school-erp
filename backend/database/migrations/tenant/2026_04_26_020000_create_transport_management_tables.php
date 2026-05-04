<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_vehicles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('vehicle_no', 50);
            $table->string('registration_no', 50);
            $table->string('name')->nullable();
            $table->string('vehicle_type', 40)->default('bus');
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('color', 40)->nullable();
            $table->unsignedInteger('seat_capacity')->nullable();
            $table->string('fuel_type', 30)->nullable();
            $table->string('ownership_type', 30)->default('owned');
            $table->string('gps_device_code', 80)->nullable();
            $table->date('insurance_expiry_date')->nullable();
            $table->date('permit_expiry_date')->nullable();
            $table->date('fitness_expiry_date')->nullable();
            $table->unsignedInteger('odometer_reading')->nullable();
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->unique(['school_id', 'vehicle_no'], 'tr_vehicles_no_uq');
            $table->unique(['school_id', 'registration_no'], 'tr_vehicles_reg_uq');
            $table->index(['school_id', 'status'], 'tr_vehicles_status_ix');
            $table->index(['school_id', 'vehicle_type'], 'tr_vehicles_type_ix');
        });

        Schema::create('transport_drivers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('driver_code', 50)->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name');
            $table->string('phone', 30);
            $table->string('alternate_phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('license_no', 80);
            $table->date('license_expiry_date')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('status', 30)->default('active');
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('staff_id')->references('id')->on('staff')->nullOnDelete();
            $table->unique(['school_id', 'license_no'], 'tr_drivers_license_uq');
            $table->unique(['school_id', 'driver_code'], 'tr_drivers_code_uq');
            $table->index(['school_id', 'staff_id'], 'tr_drivers_staff_ix');
            $table->index(['school_id', 'status'], 'tr_drivers_status_ix');
        });

        Schema::create('transport_routes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name');
            $table->string('code', 50);
            $table->string('route_type', 30)->default('regular');
            $table->string('start_location')->nullable();
            $table->string('end_location')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->unsignedInteger('estimated_duration_minutes')->nullable();
            $table->string('status', 30)->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->unique(['school_id', 'code'], 'tr_routes_code_uq');
            $table->index(['school_id', 'status'], 'tr_routes_status_ix');
            $table->index(['school_id', 'route_type'], 'tr_routes_type_ix');
        });

        Schema::create('transport_route_stops', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('route_id');
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->unsignedSmallInteger('stop_order');
            $table->time('pickup_time')->nullable();
            $table->time('drop_time')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();
            $table->decimal('distance_from_start_km', 8, 2)->nullable();
            $table->text('address')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('route_id')->references('id')->on('transport_routes')->cascadeOnDelete();
            $table->unique(['route_id', 'stop_order'], 'tr_route_stops_order_uq');
            $table->unique(['school_id', 'code'], 'tr_route_stops_code_uq');
            $table->index(['school_id', 'route_id'], 'tr_route_stops_route_ix');
            $table->index(['school_id', 'status'], 'tr_route_stops_status_ix');
        });

        Schema::create('transport_route_vehicle_assignments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->date('assigned_from');
            $table->date('assigned_to')->nullable();
            $table->string('shift_type', 30)->default('both');
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('route_id')->references('id')->on('transport_routes')->cascadeOnDelete();
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->cascadeOnDelete();
            $table->foreign('driver_id')->references('id')->on('transport_drivers')->nullOnDelete();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
            $table->index(['school_id', 'route_id'], 'tr_rva_route_ix');
            $table->index(['school_id', 'vehicle_id'], 'tr_rva_vehicle_ix');
            $table->index(['school_id', 'driver_id'], 'tr_rva_driver_ix');
            $table->index(['school_id', 'status'], 'tr_rva_status_ix');
            $table->index(['school_id', 'academic_year_id'], 'tr_rva_year_ix');
        });

        Schema::create('student_transport_allocations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('route_vehicle_assignment_id')->nullable();
            $table->unsignedBigInteger('pickup_stop_id')->nullable();
            $table->unsignedBigInteger('drop_stop_id')->nullable();
            $table->date('allocated_from');
            $table->date('allocated_to')->nullable();
            $table->decimal('fare_amount', 12, 2)->nullable();
            $table->string('status', 30)->default('active');
            $table->string('active_scope_key', 20)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
            $table->foreign('route_id')->references('id')->on('transport_routes')->cascadeOnDelete();
            $table->foreign('route_vehicle_assignment_id', 'stud_tr_alloc_rva_fk')
                ->references('id')->on('transport_route_vehicle_assignments')->nullOnDelete();
            $table->foreign('pickup_stop_id', 'stud_tr_alloc_pickup_fk')
                ->references('id')->on('transport_route_stops')->nullOnDelete();
            $table->foreign('drop_stop_id', 'stud_tr_alloc_drop_fk')
                ->references('id')->on('transport_route_stops')->nullOnDelete();
            $table->unique(
                ['school_id', 'student_id', 'academic_year_id', 'active_scope_key'],
                'stud_tr_alloc_active_uq'
            );
            $table->index(['school_id', 'route_id'], 'stud_tr_alloc_route_ix');
            $table->index(['school_id', 'status'], 'stud_tr_alloc_status_ix');
            $table->index(['school_id', 'pickup_stop_id'], 'stud_tr_alloc_pickup_ix');
            $table->index(['school_id', 'drop_stop_id'], 'stud_tr_alloc_drop_ix');
        });

        Schema::create('staff_transport_allocations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('route_vehicle_assignment_id')->nullable();
            $table->unsignedBigInteger('pickup_stop_id')->nullable();
            $table->unsignedBigInteger('drop_stop_id')->nullable();
            $table->date('allocated_from');
            $table->date('allocated_to')->nullable();
            $table->decimal('fare_amount', 12, 2)->nullable();
            $table->string('status', 30)->default('active');
            $table->string('active_scope_key', 20)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('staff_id')->references('id')->on('staff')->cascadeOnDelete();
            $table->foreign('route_id')->references('id')->on('transport_routes')->cascadeOnDelete();
            $table->foreign('route_vehicle_assignment_id', 'staff_tr_alloc_rva_fk')
                ->references('id')->on('transport_route_vehicle_assignments')->nullOnDelete();
            $table->foreign('pickup_stop_id', 'staff_tr_alloc_pickup_fk')
                ->references('id')->on('transport_route_stops')->nullOnDelete();
            $table->foreign('drop_stop_id', 'staff_tr_alloc_drop_fk')
                ->references('id')->on('transport_route_stops')->nullOnDelete();
            $table->unique(['school_id', 'staff_id', 'active_scope_key'], 'staff_tr_alloc_active_uq');
            $table->index(['school_id', 'route_id'], 'staff_tr_alloc_route_ix');
            $table->index(['school_id', 'status'], 'staff_tr_alloc_status_ix');
        });

        Schema::create('transport_trips', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('route_vehicle_assignment_id');
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->date('trip_date');
            $table->string('trip_type', 30)->default('pickup');
            $table->time('scheduled_start_time')->nullable();
            $table->time('scheduled_end_time')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('status', 30)->default('scheduled');
            $table->unsignedInteger('total_boarded')->default(0);
            $table->unsignedInteger('total_dropped')->default(0);
            $table->unsignedBigInteger('started_by')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('route_vehicle_assignment_id', 'transport_trips_rva_fk')
                ->references('id')->on('transport_route_vehicle_assignments')->cascadeOnDelete();
            $table->foreign('route_id')->references('id')->on('transport_routes')->cascadeOnDelete();
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->cascadeOnDelete();
            $table->foreign('driver_id')->references('id')->on('transport_drivers')->nullOnDelete();
            $table->foreign('started_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('completed_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['school_id', 'trip_date'], 'transport_trips_date_ix');
            $table->index(['school_id', 'route_id'], 'transport_trips_route_ix');
            $table->index(['school_id', 'vehicle_id'], 'transport_trips_vehicle_ix');
            $table->index(['school_id', 'driver_id'], 'transport_trips_driver_ix');
            $table->index(['school_id', 'status'], 'transport_trips_status_ix');
        });

        Schema::create('transport_trip_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('transport_trip_id');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->unsignedBigInteger('route_stop_id')->nullable();
            $table->string('user_type', 20);
            $table->string('event_type', 30);
            $table->timestamp('event_time');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();
            $table->unsignedBigInteger('marked_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('transport_trip_id')->references('id')->on('transport_trips')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
            $table->foreign('staff_id')->references('id')->on('staff')->nullOnDelete();
            $table->foreign('route_stop_id')->references('id')->on('transport_route_stops')->nullOnDelete();
            $table->foreign('marked_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['school_id', 'transport_trip_id'], 'transport_trip_logs_trip_ix');
            $table->index(['school_id', 'student_id'], 'transport_trip_logs_stud_ix');
            $table->index(['school_id', 'staff_id'], 'transport_trip_logs_staff_ix');
            $table->index(['school_id', 'event_type'], 'transport_trip_logs_event_ix');
            $table->index(['school_id', 'event_time'], 'transport_trip_logs_time_ix');
        });

        Schema::create('vehicle_maintenance_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->string('maintenance_type', 40);
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('maintenance_date');
            $table->date('next_due_date')->nullable();
            $table->unsignedInteger('odometer_reading')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('vendor_name')->nullable();
            $table->string('status', 30)->default('scheduled');
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->cascadeOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['school_id', 'vehicle_id'], 'veh_maint_vehicle_ix');
            $table->index(['school_id', 'maintenance_date'], 'veh_maint_date_ix');
            $table->index(['school_id', 'status'], 'veh_maint_status_ix');
        });

        Schema::create('vehicle_fuel_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->date('fuel_date');
            $table->decimal('quantity_liters', 10, 2);
            $table->decimal('cost_per_unit', 10, 2)->nullable();
            $table->decimal('total_cost', 12, 2);
            $table->unsignedInteger('odometer_reading')->nullable();
            $table->string('fuel_station')->nullable();
            $table->string('reference_no', 80)->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->cascadeOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['school_id', 'vehicle_id'], 'veh_fuel_vehicle_ix');
            $table->index(['school_id', 'fuel_date'], 'veh_fuel_date_ix');
        });

        Schema::create('transport_gps_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->unsignedBigInteger('transport_trip_id')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 11, 7);
            $table->decimal('speed_kmph', 8, 2)->nullable();
            $table->decimal('heading_degree', 8, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->string('engine_status', 20)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('vehicle_id')->references('id')->on('transport_vehicles')->nullOnDelete();
            $table->foreign('transport_trip_id', 'transport_gps_logs_trip_fk')
                ->references('id')->on('transport_trips')->nullOnDelete();
            $table->foreign('driver_id')->references('id')->on('transport_drivers')->nullOnDelete();
            $table->index(['school_id', 'vehicle_id', 'recorded_at'], 'transport_gps_vehicle_ix');
            $table->index(['school_id', 'transport_trip_id'], 'transport_gps_trip_ix');
            $table->index(['school_id', 'driver_id'], 'transport_gps_driver_ix');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_gps_logs');
        Schema::dropIfExists('vehicle_fuel_logs');
        Schema::dropIfExists('vehicle_maintenance_logs');
        Schema::dropIfExists('transport_trip_logs');
        Schema::dropIfExists('transport_trips');
        Schema::dropIfExists('staff_transport_allocations');
        Schema::dropIfExists('student_transport_allocations');
        Schema::dropIfExists('transport_route_vehicle_assignments');
        Schema::dropIfExists('transport_route_stops');
        Schema::dropIfExists('transport_routes');
        Schema::dropIfExists('transport_drivers');
        Schema::dropIfExists('transport_vehicles');
    }
};

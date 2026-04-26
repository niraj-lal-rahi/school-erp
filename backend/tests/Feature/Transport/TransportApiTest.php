<?php

namespace Tests\Feature\Transport;

use App\Models\AcademicYear;
use App\Models\HR\Staff;
use App\Models\Student;
use App\Models\Transport\StudentTransportAllocation;
use App\Models\Transport\TransportDriver;
use App\Models\Transport\TransportRoute;
use App\Models\Transport\TransportRouteStop;
use App\Models\Transport\TransportRouteVehicleAssignment;
use App\Models\Transport\TransportTrip;
use App\Models\Transport\TransportVehicle;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransportApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): array
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ];
    }

    public function test_authorized_user_can_create_vehicle(): void
    {
        $headers = $this->authenticate();

        $this->withHeaders($headers)->postJson('/api/v1/transport/vehicles', [
            'vehicle_no' => 'BUS-099',
            'registration_no' => 'WB-12-TR-9099',
            'name' => 'Greenline Shuttle',
            'vehicle_type' => 'bus',
            'make' => 'Ashok Leyland',
            'model' => 'Falcon',
            'color' => 'White',
            'seat_capacity' => 48,
            'fuel_type' => 'diesel',
            'ownership_type' => 'owned',
            'status' => 'active',
        ])->assertCreated()->assertJsonPath('data.vehicle_no', 'BUS-099');

        $this->assertDatabaseHas('transport_vehicles', [
            'vehicle_no' => 'BUS-099',
            'registration_no' => 'WB-12-TR-9099',
        ]);
    }

    public function test_authorized_user_can_create_route(): void
    {
        $headers = $this->authenticate();

        $this->withHeaders($headers)->postJson('/api/v1/transport/routes', [
            'name' => 'South Route',
            'code' => 'RT-SOUTH',
            'route_type' => 'regular',
            'start_location' => 'South Point',
            'end_location' => 'Campus',
            'distance_km' => 14.5,
            'estimated_duration_minutes' => 40,
            'status' => 'active',
            'description' => 'South zone route.',
        ])->assertCreated()->assertJsonPath('data.code', 'RT-SOUTH');

        $this->assertDatabaseHas('transport_routes', [
            'code' => 'RT-SOUTH',
            'name' => 'South Route',
        ]);
    }

    public function test_duplicate_active_student_allocation_is_rejected(): void
    {
        $headers = $this->authenticate();
        $existing = StudentTransportAllocation::withoutGlobalScopes()->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/transport/student-allocations', [
            'student_id' => $existing->student_id,
            'academic_year_id' => $existing->academic_year_id,
            'route_id' => $existing->route_id,
            'route_vehicle_assignment_id' => $existing->route_vehicle_assignment_id,
            'pickup_stop_id' => $existing->pickup_stop_id,
            'drop_stop_id' => $existing->drop_stop_id,
            'allocated_from' => now()->toDateString(),
            'fare_amount' => 19500,
            'status' => 'active',
            'remarks' => 'Should fail due to duplicate active allocation.',
        ])->assertStatus(422);
    }

    public function test_trip_lifecycle_and_boarding_flow_work(): void
    {
        $headers = $this->authenticate();
        $assignment = TransportRouteVehicleAssignment::withoutGlobalScopes()->firstOrFail();
        $allocation = StudentTransportAllocation::withoutGlobalScopes()->firstOrFail();

        $tripResponse = $this->withHeaders($headers)->postJson('/api/v1/transport/trips', [
            'route_vehicle_assignment_id' => $assignment->id,
            'route_id' => $assignment->route_id,
            'vehicle_id' => $assignment->vehicle_id,
            'driver_id' => $assignment->driver_id,
            'trip_date' => now()->addDay()->toDateString(),
            'trip_type' => 'drop',
            'scheduled_start_time' => '14:45',
            'scheduled_end_time' => '15:45',
            'status' => 'scheduled',
            'notes' => 'Test drop trip.',
        ])->assertCreated();

        $tripId = $tripResponse->json('data.id');

        $this->withHeaders($headers)->postJson("/api/v1/transport/trips/{$tripId}/start", [])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');

        $this->withHeaders($headers)->postJson("/api/v1/transport/trips/{$tripId}/mark-boarded", [
            'transport_trip_id' => $tripId,
            'student_id' => $allocation->student_id,
            'route_stop_id' => $allocation->pickup_stop_id,
            'user_type' => 'student',
            'event_type' => 'boarded',
            'event_time' => now()->addDay()->setTime(14, 50)->toDateTimeString(),
            'remarks' => 'Boarded for test trip.',
        ])->assertCreated()->assertJsonPath('data.event_type', 'boarded');

        $this->withHeaders($headers)->postJson("/api/v1/transport/trips/{$tripId}/mark-dropped", [
            'transport_trip_id' => $tripId,
            'student_id' => $allocation->student_id,
            'route_stop_id' => $allocation->drop_stop_id,
            'user_type' => 'student',
            'event_type' => 'dropped',
            'event_time' => now()->addDay()->setTime(15, 30)->toDateTimeString(),
            'remarks' => 'Dropped for test trip.',
        ])->assertCreated()->assertJsonPath('data.event_type', 'dropped');

        $this->withHeaders($headers)->postJson("/api/v1/transport/trips/{$tripId}/complete", [])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('transport_trips', [
            'id' => $tripId,
            'status' => 'completed',
            'total_boarded' => 1,
            'total_dropped' => 1,
        ]);
    }

    public function test_gps_logs_can_be_recorded_and_latest_vehicle_location_can_be_viewed(): void
    {
        $headers = $this->authenticate();
        $trip = TransportTrip::withoutGlobalScopes()->firstOrFail();
        $vehicle = TransportVehicle::withoutGlobalScopes()->findOrFail($trip->vehicle_id);

        $gpsResponse = $this->withHeaders($headers)->postJson('/api/v1/transport/gps-logs', [
            'vehicle_id' => $trip->vehicle_id,
            'transport_trip_id' => $trip->id,
            'driver_id' => $trip->driver_id,
            'latitude' => 22.5811000,
            'longitude' => 88.3744000,
            'speed_kmph' => 28.5,
            'heading_degree' => 190,
            'recorded_at' => now()->toDateTimeString(),
            'engine_status' => 'on',
            'raw_payload' => ['source' => 'test'],
        ])->assertCreated();

        $gpsId = $gpsResponse->json('data.id');

        $this->withHeaders($headers)->getJson("/api/v1/transport/vehicle-location/{$vehicle->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $gpsId);

        $this->assertDatabaseHas('transport_gps_logs', [
            'id' => $gpsId,
            'vehicle_id' => $vehicle->id,
        ]);
    }

    public function test_maintenance_log_can_be_created(): void
    {
        $headers = $this->authenticate();
        $vehicle = TransportVehicle::withoutGlobalScopes()->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/transport/maintenance', [
            'vehicle_id' => $vehicle->id,
            'maintenance_type' => 'repair',
            'title' => 'Brake Pad Replacement',
            'description' => 'Front brake pad replacement.',
            'maintenance_date' => now()->toDateString(),
            'next_due_date' => now()->addMonths(6)->toDateString(),
            'odometer_reading' => 126000,
            'cost' => 6400,
            'vendor_name' => 'Rapid Motors',
            'status' => 'completed',
            'remarks' => 'Completed successfully.',
        ])->assertCreated()->assertJsonPath('data.title', 'Brake Pad Replacement');

        $this->assertDatabaseHas('vehicle_maintenance_logs', [
            'title' => 'Brake Pad Replacement',
            'maintenance_type' => 'repair',
        ]);
    }
}

<?php

namespace Database\Seeders\Transport;

use App\Models\AcademicYear;
use App\Models\HR\Staff;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Transport\StaffTransportAllocation;
use App\Models\Transport\StudentTransportAllocation;
use App\Models\Transport\TransportDriver;
use App\Models\Transport\TransportGpsLog;
use App\Models\Transport\TransportRoute;
use App\Models\Transport\TransportRouteStop;
use App\Models\Transport\TransportRouteVehicleAssignment;
use App\Models\Transport\TransportTrip;
use App\Models\Transport\TransportVehicle;
use App\Models\Transport\VehicleFuelLog;
use App\Models\Transport\VehicleMaintenanceLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransportSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $admin = User::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('email', 'admin@greenwood.edu')
            ->firstOrFail();

        $academicYear = AcademicYear::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('is_current', true)
            ->firstOrFail();

        $driverStaff = Staff::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('employee_code', 'EMP-0001')
            ->firstOrFail();

        $allocatedStaff = Staff::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('employee_code', 'EMP-0002')
            ->firstOrFail();

        $studentEnrollment = StudentEnrollment::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('is_current', true)
            ->firstOrFail();

        $student = Student::withoutGlobalScopes()->findOrFail($studentEnrollment->student_id);

        $vehicle = TransportVehicle::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'vehicle_no' => 'BUS-001'],
            [
                'registration_no' => 'WB-12-TR-1001',
                'name' => 'Blue Bird 1',
                'vehicle_type' => 'bus',
                'make' => 'Tata',
                'model' => 'School Cruiser',
                'color' => 'Yellow',
                'seat_capacity' => 42,
                'fuel_type' => 'diesel',
                'ownership_type' => 'owned',
                'gps_device_code' => 'GPS-BUS-001',
                'insurance_expiry_date' => now()->addYear()->toDateString(),
                'permit_expiry_date' => now()->addMonths(9)->toDateString(),
                'fitness_expiry_date' => now()->addMonths(6)->toDateString(),
                'odometer_reading' => 125000,
                'status' => 'active',
                'notes' => 'Seeded primary transport vehicle.',
            ]
        );

        $driver = TransportDriver::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'license_no' => 'DL-TR-1001'],
            [
                'staff_id' => $driverStaff->id,
                'driver_code' => 'DRV-001',
                'first_name' => $driverStaff->first_name,
                'middle_name' => $driverStaff->middle_name,
                'last_name' => $driverStaff->last_name,
                'full_name' => $driverStaff->full_name,
                'phone' => $driverStaff->phone ?? '9000000001',
                'alternate_phone' => $driverStaff->alternate_phone,
                'email' => $driverStaff->email,
                'license_expiry_date' => now()->addYears(2)->toDateString(),
                'date_of_birth' => '1985-06-10',
                'joining_date' => now()->subYears(2)->toDateString(),
                'status' => 'active',
                'address' => 'Seeded driver address',
                'emergency_contact_name' => 'Driver Emergency Contact',
                'emergency_contact_phone' => '9000000011',
                'notes' => 'Seeded transport driver.',
            ]
        );

        $route = TransportRoute::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'RT-NORTH'],
            [
                'name' => 'North Corridor Route',
                'route_type' => 'regular',
                'start_location' => 'North Gate',
                'end_location' => 'School Campus',
                'distance_km' => 18.5,
                'estimated_duration_minutes' => 55,
                'status' => 'active',
                'description' => 'Seeded main north-side pickup route.',
            ]
        );

        $pickupStop = TransportRouteStop::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'STOP-N1'],
            [
                'route_id' => $route->id,
                'name' => 'North Market',
                'stop_order' => 1,
                'pickup_time' => '07:15:00',
                'drop_time' => '15:45:00',
                'latitude' => 22.5726000,
                'longitude' => 88.3639000,
                'distance_from_start_km' => 2.5,
                'address' => 'North Market Main Road',
                'status' => 'active',
            ]
        );

        $dropStop = TransportRouteStop::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'STOP-N2'],
            [
                'route_id' => $route->id,
                'name' => 'Lake View',
                'stop_order' => 2,
                'pickup_time' => '07:30:00',
                'drop_time' => '16:00:00',
                'latitude' => 22.5755000,
                'longitude' => 88.3695000,
                'distance_from_start_km' => 6.0,
                'address' => 'Lake View Circle',
                'status' => 'active',
            ]
        );

        $assignment = TransportRouteVehicleAssignment::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'route_id' => $route->id,
                'vehicle_id' => $vehicle->id,
                'assigned_from' => $academicYear->start_date->toDateString(),
            ],
            [
                'driver_id' => $driver->id,
                'academic_year_id' => $academicYear->id,
                'assigned_to' => null,
                'shift_type' => 'both',
                'status' => 'active',
                'notes' => 'Seeded active assignment.',
            ]
        );

        StudentTransportAllocation::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'student_id' => $student->id,
                'academic_year_id' => $academicYear->id,
                'active_scope_key' => 'active',
            ],
            [
                'route_id' => $route->id,
                'route_vehicle_assignment_id' => $assignment->id,
                'pickup_stop_id' => $pickupStop->id,
                'drop_stop_id' => $dropStop->id,
                'allocated_from' => $academicYear->start_date->toDateString(),
                'allocated_to' => null,
                'fare_amount' => 18000,
                'status' => 'active',
                'remarks' => 'Seeded active student transport allocation.',
            ]
        );

        StaffTransportAllocation::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'staff_id' => $allocatedStaff->id,
                'active_scope_key' => 'active',
            ],
            [
                'route_id' => $route->id,
                'route_vehicle_assignment_id' => $assignment->id,
                'pickup_stop_id' => $pickupStop->id,
                'drop_stop_id' => $dropStop->id,
                'allocated_from' => $academicYear->start_date->toDateString(),
                'allocated_to' => null,
                'fare_amount' => 0,
                'status' => 'active',
                'remarks' => 'Seeded active staff transport allocation.',
            ]
        );

        $trip = TransportTrip::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'route_vehicle_assignment_id' => $assignment->id,
                'trip_date' => now()->toDateString(),
                'trip_type' => 'pickup',
            ],
            [
                'route_id' => $route->id,
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'scheduled_start_time' => '07:00:00',
                'scheduled_end_time' => '08:00:00',
                'status' => 'scheduled',
                'total_boarded' => 0,
                'total_dropped' => 0,
                'notes' => 'Seeded scheduled pickup trip.',
            ]
        );

        VehicleMaintenanceLog::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'vehicle_id' => $vehicle->id,
                'title' => 'Quarterly Service',
            ],
            [
                'maintenance_type' => 'service',
                'description' => 'Seeded quarterly service log.',
                'maintenance_date' => now()->subDays(10)->toDateString(),
                'next_due_date' => now()->addMonths(3)->toDateString(),
                'odometer_reading' => 124000,
                'cost' => 8500,
                'vendor_name' => 'City Workshop',
                'status' => 'completed',
                'recorded_by' => $admin->id,
                'remarks' => 'Service completed on time.',
            ]
        );

        VehicleFuelLog::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'vehicle_id' => $vehicle->id,
                'reference_no' => 'FUEL-SEED-001',
            ],
            [
                'fuel_date' => now()->subDays(2)->toDateString(),
                'quantity_liters' => 55.5,
                'cost_per_unit' => 95,
                'total_cost' => 5272.50,
                'odometer_reading' => 124800,
                'fuel_station' => 'North Fuel Station',
                'recorded_by' => $admin->id,
                'remarks' => 'Seeded fuel refill record.',
            ]
        );

        TransportGpsLog::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'vehicle_id' => $vehicle->id,
                'recorded_at' => now()->subMinutes(15)->toDateTimeString(),
            ],
            [
                'transport_trip_id' => $trip->id,
                'driver_id' => $driver->id,
                'latitude' => 22.5801000,
                'longitude' => 88.3722000,
                'speed_kmph' => 32.5,
                'heading_degree' => 180,
                'engine_status' => 'on',
                'raw_payload' => ['seeded' => true],
            ]
        );
    }
}

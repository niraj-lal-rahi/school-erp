<?php

namespace App\Http\Controllers\Api\V1\Transport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transport\CancelTransportTripRequest;
use App\Http\Requests\Transport\CompleteTransportTripRequest;
use App\Http\Requests\Transport\StartTransportTripRequest;
use App\Http\Requests\Transport\StoreTransportTripLogRequest;
use App\Http\Requests\Transport\UpsertTransportTripRequest;
use App\Models\Transport\TransportTrip;
use App\Services\Transport\TransportTripService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function __construct(
        protected TransportTripService $trips,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->trips->paginate(
                $request->only([
                    'route_vehicle_assignment_id',
                    'route_id',
                    'vehicle_id',
                    'driver_id',
                    'trip_type',
                    'status',
                    'date_from',
                    'date_to',
                ]),
                (int) $request->integer('per_page', 15)
            ),
        ]);
    }

    public function store(UpsertTransportTripRequest $request): JsonResponse
    {
        $trip = $this->trips->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Trip created successfully.',
            'data' => $trip,
        ], 201);
    }

    public function show(TransportTrip $trip): JsonResponse
    {
        return response()->json([
            'data' => $this->trips->show($trip),
        ]);
    }

    public function update(UpsertTransportTripRequest $request, TransportTrip $trip): JsonResponse
    {
        $trip = $this->trips->update($trip, $request->validated());

        return response()->json([
            'message' => 'Trip updated successfully.',
            'data' => $trip,
        ]);
    }

    public function destroy(TransportTrip $trip): JsonResponse
    {
        $this->trips->delete($trip);

        return response()->json(null, 204);
    }

    public function start(StartTransportTripRequest $request, TransportTrip $trip): JsonResponse
    {
        $trip = $this->trips->start($trip, [
            ...$request->validated(),
            'started_by' => $request->validated('started_by', $request->user()->id),
        ]);

        return response()->json([
            'message' => 'Trip started successfully.',
            'data' => $trip,
        ]);
    }

    public function complete(CompleteTransportTripRequest $request, TransportTrip $trip): JsonResponse
    {
        $trip = $this->trips->complete($trip, [
            ...$request->validated(),
            'completed_by' => $request->validated('completed_by', $request->user()->id),
        ]);

        return response()->json([
            'message' => 'Trip completed successfully.',
            'data' => $trip,
        ]);
    }

    public function cancel(CancelTransportTripRequest $request, TransportTrip $trip): JsonResponse
    {
        $trip = $this->trips->cancel($trip, $request->validated('reason'));

        return response()->json([
            'message' => 'Trip cancelled successfully.',
            'data' => $trip,
        ]);
    }

    public function markBoarded(StoreTransportTripLogRequest $request, TransportTrip $trip): JsonResponse
    {
        $log = $this->trips->markBoarded($trip, [
            ...$request->validated(),
            'marked_by' => $request->validated('marked_by', $request->user()->id),
        ]);

        return response()->json([
            'message' => 'Passenger marked as boarded successfully.',
            'data' => $log,
        ], 201);
    }

    public function markDropped(StoreTransportTripLogRequest $request, TransportTrip $trip): JsonResponse
    {
        $log = $this->trips->markDropped($trip, [
            ...$request->validated(),
            'marked_by' => $request->validated('marked_by', $request->user()->id),
        ]);

        return response()->json([
            'message' => 'Passenger marked as dropped successfully.',
            'data' => $log,
        ], 201);
    }

    public function logs(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->trips->paginateLogs(
                $request->only([
                    'transport_trip_id',
                    'student_id',
                    'staff_id',
                    'event_type',
                    'user_type',
                    'date_from',
                    'date_to',
                ]),
                (int) $request->integer('per_page', 15)
            ),
        ]);
    }

    public function reports(Request $request): JsonResponse
    {
        $trips = $this->trips->paginate(
            $request->only([
                'route_id',
                'vehicle_id',
                'driver_id',
                'trip_type',
                'status',
                'date_from',
                'date_to',
            ]),
            (int) $request->integer('per_page', 15)
        );

        $collection = collect($trips->items());

        return response()->json([
            'data' => [
                'summary' => [
                    'total_trips' => $trips->total(),
                    'completed_trips' => $collection->where('status', 'completed')->count(),
                    'in_progress_trips' => $collection->where('status', 'in_progress')->count(),
                    'cancelled_trips' => $collection->where('status', 'cancelled')->count(),
                    'total_boarded' => (int) $collection->sum('total_boarded'),
                    'total_dropped' => (int) $collection->sum('total_dropped'),
                ],
                'trips' => $trips,
            ],
        ]);
    }
}

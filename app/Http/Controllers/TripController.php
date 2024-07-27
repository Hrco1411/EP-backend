<?php

namespace App\Http\Controllers;

use App\Events\TripAccepted;
use App\Events\TripCreated;
use App\Events\TripStarted;
use App\Events\TripEnded;
use App\Events\TripLocationUpdated;
use Illuminate\Http\Request;
use App\Models\Trip;

class TripController extends Controller
{
     /**
 * @OA\Post(
 *     path="/api/trip",
 *     summary="Create a new trip",
 *     tags={"Trip"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"origin", "destination", "destination_name"},
 *             @OA\Property(property="origin", type="object",
 *                 @OA\Property(property="lat", type="number", example=37.7749),
 *                 @OA\Property(property="lng", type="number", example=-122.4194)
 *             ),
 *             @OA\Property(property="destination", type="object",
 *                 @OA\Property(property="lat", type="number", example=37.7749),
 *                 @OA\Property(property="lng", type="number", example=-122.4194)
 *             ),
 *             @OA\Property(property="destination_name", type="string", example="San Francisco")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="The new trip",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="user_id", type="integer"),
 *             @OA\Property(property="origin", type="object",
 *                 @OA\Property(property="lat", type="number"),
 *                 @OA\Property(property="lng", type="number")
 *             ),
 *             @OA\Property(property="destination", type="object",
 *                 @OA\Property(property="lat", type="number"),
 *                 @OA\Property(property="lng", type="number")
 *             ),
 *             @OA\Property(property="destination_name", type="string"),
 *             @OA\Property(property="driver_id", type="integer", example=null),
 *             @OA\Property(property="driver_location", type="object", example=null,
 *                 @OA\Property(property="lat", type="number"),
 *                 @OA\Property(property="lng", type="number")
 *             ),
 *             @OA\Property(property="is_started", type="boolean", example=false),
 *             @OA\Property(property="is_complete", type="boolean", example=false),
 *             @OA\Property(property="created_at", type="string"),
 *             @OA\Property(property="updated_at", type="string")
 *         )
 *     )
 * )
 */
    public function store(Request $request)
    {
        $request->validate([
            'origin' => 'required',
            'destination' => 'required',
            'destination_name' => 'required',
        ]);

        $trip = $request->user()->trips()->create($request->only([
            'origin',
            'destination',
            'destination_name',
        ]));

        TripCreated::dispatch($trip, $request->user());

        return $trip;
    }

  
    /**
     * @OA\Get(
     *     path="/api/trip/{trip}",
     *     summary="Get a trip",
     *     tags={"Trip"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="trip",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The trip",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="origin", type="object",
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number")
     *             ),
     *             @OA\Property(property="destination", type="object",
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number")
     *             ),
     *             @OA\Property(property="destination_name", type="string"),
     *             @OA\Property(property="driver_id", type="integer", example=null),
     *             @OA\Property(property="driver_location", type="object", example=null,
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number")
     *             ),
     *             @OA\Property(property="is_started", type="boolean", example=false),
     *             @OA\Property(property="is_complete", type="boolean", example=false),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cannot find this trip.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cannot find this trip.")
     *         )
     *     )
     * )
     */
    public function show(Request $request, Trip $trip)
    {
        // is the trip associated with the authenticated user?
        if($trip->user->id === $request->user()->id) {
            return $trip;
        }

        if($trip->driver && $request->user()->driver) {
            if($trip->driver_id === $request->user()->driver->id) {
                return $trip;
            }
        }

        return response()->json(['message' => 'Cannot find this trip.'], 404);
    }

    /**
     * @OA\Post(
     *     path="/api/trip/{trip}/accept",
     *     summary="Accept a trip",
     *     tags={"Trip"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="trip",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"driver_location"},
     *             @OA\Property(property="driver_location", type="object",
     *                 @OA\Property(property="lat", type="number", example=37.7749),
     *                 @OA\Property(property="lng", type="number", example=-122.4194)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The trip",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="origin", type="object",
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number")
     *             ),
     *             @OA\Property(property="destination", type="object",
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number")
     *             ),
     *             @OA\Property(property="destination_name", type="string"),
     *             @OA\Property(property="driver_id", type="integer", example=null),
     *             @OA\Property(property="driver_location", type="object",
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number")
     *             ),
     *             @OA\Property(property="is_started", type="boolean", example=false),
     *             @OA\Property(property="is_complete", type="boolean", example=false),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string")
     *         )
     *     )
     * )
     */
    public function accept(Request $request, Trip $trip)
    {
        // a driver accepts a trip   
        $request->validate([
            'driver_location' => 'required',
        ]);

        $trip->update([
            'driver_id' => $request->user()->driver->id,
            'driver_location' => $request->driver_location,
        ]);

        $trip->load('driver.user');

        TripAccepted::dispatch($trip, $trip->user);

        return $trip;
    }

    /**
     * @OA\Post(
     *     path="/api/trip/{trip}/start",
     *     summary="Start a trip",
     *     tags={"Trip"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="trip",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The trip",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="origin", type="object",
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number")
     *             ),
     *             @OA\Property(property="destination", type="object",
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number")
     *             ),
     *             @OA\Property(property="destination_name", type="string"),
     *             @OA\Property(property="driver_id", type="integer", example=null),
     *             @OA\Property(property="driver_location", type="object", example=null,
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number")
     *             ),
     *             @OA\Property(property="is_started", type="boolean", example=true),
     *             @OA\Property(property="is_complete", type="boolean", example=false),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string")
     *         )
     *     )
     * )
     */
    public function start(Request $request, Trip $trip)
    {
        // a driver has started taking a passenger to their destination
        $trip->update([
            'is_started' => true,
        ]);

        $trip->load('driver.user');

        TripStarted::dispatch($trip, $request->user());

        return $trip;
    }

    /**
     * @OA\Post(
     *     path="/api/trip/{trip}/end",
     *     summary="End a trip",
     *     tags={"Trip"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="trip",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The trip",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="origin", type="object",
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number")
     *             ),
     *             @OA\Property(property="destination", type="object",
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number")
     *             ),
     *             @OA\Property(property="destination_name", type="string"),
     *             @OA\Property(property="driver_id", type="integer", example=null),
     *             @OA\Property(property="driver_location", type="object", example=null,
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number")
     *             ),
     *             @OA\Property(property="is_started", type="boolean", example=true),
     *             @OA\Property(property="is_complete", type="boolean", example=true),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string")
     *         )
     *     )
     * )
     */
    public function end(Request $request, Trip $trip)
    {
        // a driver has ended a trip
        $trip->update([
            'is_complete' => true,
        ]);

        $trip->load('driver.user');

        TripEnded::dispatch($trip, $request->user());

        return $trip;
    }

    /**
     * @OA\Post(
     *     path="/api/trip/{trip}/location",
     *     summary="Update the driver's current location",
     *     tags={"Trip"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="trip",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"driver_location"},
     *             @OA\Property(property="driver_location", type="object",
     *                 @OA\Property(property="lat", type="number", example=37.7749),
     *                 @OA\Property(property="lng", type="number", example=-122.4194)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The trip",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="origin", type="object",
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number")
     *             ),
     *             @OA\Property(property="destination", type="object",
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number")
     *             ),
     *             @OA\Property(property="destination_name", type="string"),
     *             @OA\Property(property="driver_id", type="integer", example=null),
     *             @OA\Property(property="driver_location", type="object",
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number")
     *             ),
     *             @OA\Property(property="is_started", type="boolean", example=false),
     *             @OA\Property(property="is_complete", type="boolean", example=false),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string")
     *         )
     *     )
     * )
     */
    public function location(Request $request, Trip $trip)
    {
        // update the driver's current location
        $request->validate([
            'driver_location' => 'required',
        ]);

        $trip->update([
            'driver_location' => $request->driver_location,
        ]);

        $trip->load('driver.user');

        TripLocationUpdated::dispatch($trip, $trip->user);

        return $trip;
    }
}
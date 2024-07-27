<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DriverController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/driver",
     *     summary="Get the user and associated driver model",
     *     tags={"Driver"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="The user and associated driver model",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="email_verified_at", type="string"),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string"),
     *             @OA\Property(property="driver", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="year", type="integer"),
     *                 @OA\Property(property="make", type="string"),
     *                 @OA\Property(property="model", type="string"),
     *                 @OA\Property(property="color", type="string"),
     *                 @OA\Property(property="license_plate", type="string"),
     *                 @OA\Property(property="created_at", type="string"),
     *                 @OA\Property(property="updated_at", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function show(Request $request)
    {
        // return back the user and associated driver model
        $user = $request->user();
        $user->load('driver');

        return $user;
    }

    /**
     * @OA\Post(
     *    path="/api/driver",
     *   summary="Update the user and associated driver model",
     *  tags={"Driver"},
     * security={{"bearerAuth": {}}},
     * @OA\RequestBody(
     *    required=true,
     *  @OA\JsonContent(
     *    required={"year", "make", "model", "color", "license_plate", "name"},
     * @OA\Property(property="year", type="integer", example=2010),
     * @OA\Property(property="make", type="string", example="Toyota"),
     * @OA\Property(property="model", type="string", example="Corolla"),
     * @OA\Property(property="color", type="string", example="Red"),
     * @OA\Property(property="license_plate", type="string", example="ABC123"),
     * @OA\Property(property="name", type="string", example="John Doe")
     * )
     * ),
     * @OA\Response(
     *   response=200,
     * description="The user and associated driver model",
     * @OA\JsonContent(
     *  @OA\Property(property="id", type="integer"),
     * @OA\Property(property="name", type="string"),
     * @OA\Property(property="email", type="string"),
     * @OA\Property(property="email_verified_at", type="string"),
     * @OA\Property(property="created_at", type="string"),
     * @OA\Property(property="updated_at", type="string"),
     * @OA\Property(property="driver", type="object",
     *  @OA\Property(property="id", type="integer"),
     * @OA\Property(property="user_id", type="integer"),
     * @OA\Property(property="year", type="integer"),
     * @OA\Property(property="make", type="string"),
     * @OA\Property(property="model", type="string"),
     * @OA\Property(property="color", type="string"),
     * @OA\Property(property="license_plate", type="string"),
     * @OA\Property(property="created_at", type="string"),
     * @OA\Property(property="updated_at", type="string")
     * )
     * )
     * )
     * )
     */
    public function update(Request $request)
    {
        $request->validate([
            'year' => 'required|numeric|between:2010,2024',
            'make' => 'required',
            'model' => 'required',
            'color' => 'required|alpha',
            'license_plate' => 'required',
            'name' => 'required',
        ]);

        $user = $request->user();

        $user->update($request->only('name'));

        // create or update a driver associated with the user
        $user->driver()->updateOrCreate($request->only([
            'year', 
            'make',
            'model',
            'color',
            'license_plate'
        ]));

        $user->load('driver');

        return $user;
    }
}
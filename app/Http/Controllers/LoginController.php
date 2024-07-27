<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\LoginNeedsVerification;

/**
 * @OA\Info(title="TripHarmony API", version="1.0")
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

class LoginController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login/",
     *     summary="Submit phone number for login",
     *     tags={"Login"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string", example="+38763123456")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A login code has been sent to your phone number.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="A login code has been sent to your phone number.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Could not process a user with that phone number.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Could not process a user with that phone number.")
     *         )
     *     )
     * )
     */
    public function submit(Request $request){
        
        // validate the phone number
        $request->validate([
            'phone' => 'required|numeric|min:10'
        ]);

        // find or create a user model 
        $user = User::firstOrCreate([
            'phone' => $request->phone
        ]);

        if(!$user) {
            return response()->json([
                'message' => 'Could not process a user with that phone number.'], 401);
        }

        // send the user a one-time use code
        $user->notify(new LoginNeedsVerification());

        // return back a response
        return response()->json([
            'message' => 'A login code has been sent to your phone number.']);

    }

    /**
     * @OA\Post(
     *    path="/api/login/verify",
     *   summary="Verify the login code",
     *  tags={"Login"},
     * @OA\RequestBody(
     *    required=true,
     *   @OA\JsonContent(
     *     required={"phone", "login_code"},
     *   @OA\Property(property="phone", type="string", example="+38763123456"),
     * @OA\Property(property="login_code", type="integer", example="123456")
     * )
     * ),
     * @OA\Response(
     *   response=200,
     * description="A token has been issued.",
     * @OA\JsonContent(
     *  @OA\Property(property="token", type="string", example="2|3d4f3g5h6j7k8l9")
     * )
     * ),
     * @OA\Response(
     *  response=401,
     * description="Could not verify the login code.",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Could not verify the login code.")
     * )
     * )
     * )
     */
    public function verify(Request $request){
        
        // validate the incoming request
        $request->validate([
            'phone' => 'required|numeric|min:10',
            'login_code' => 'required|numeric|between:111111,999999'
        ]);

        // find the user
        $user = User::where('phone', $request->phone)
            ->where('login_code', $request->login_code)
            ->first();

        // is the code provided the same as the one in the database?
        // if so, return back an auth token
        if($user){
            $user->update([
                'login_code' => null
            ]);
            
            return $user->createToken($request->login_code)->plainTextToken;
        }

        // if not, return back an error message
        return response()->json([
            'message' => 'Could not verify the login code.'], 401);

    }
}
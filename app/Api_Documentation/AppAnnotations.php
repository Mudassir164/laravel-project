<?php

namespace App\Api_Documentation;

class AppAnnotations {
     /**
     * 
     * @OA\Tag(
     *     name="App Authentication",
     *     description="User Authentication Endpoints For App"
     * )
     *  
     * 
     * @OA\Post(
     *     path="/api/sign-up",
     *     summary="User sign up",
     *     description="Registers a new user.",
     *     tags={"App Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User sign-up data",
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User signed up successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *             ),
     *             @OA\Property(property="message", type="string", example="User Signed Up. OTP sent")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     *
    *    @OA\Post(
    *     path="/api/auth/otp/verify",
    *     summary="Verify OTP",
    *     description="Verify OTP for email or password verification.",
    *     tags={"App Authentication"},
    *     security={{ "sanctum": {}, "scope": "email-otp,password-otp,reset-password" }},
    *     @OA\RequestBody(
    *         required=true,
    *         description="OTP verification data",
    *         @OA\JsonContent(
    *          required={"code"},
    *          @OA\Property(property="code", type="integer", example=1234)
    * )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="OTP verified successfully",
    *         @OA\JsonContent(
    *     @OA\Property(property="id", type="integer", example=1),
    *     @OA\Property(property="name", type="string", example="John Doe"),
    *     @OA\Property(property="email", type="string", format="email", example="john@example.com")
    * )
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Bad request",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="message", type="string", example="Error message")
    *         )
    *     )
    * )
     *   @OA\Post(
     *     path="/api/register",
     *     summary="User registration",
     *     description="Registers a new user.",
     *     tags={"App Authentication"},
     *     security={{ "sanctum": {}, "scope": "register" }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User registration data",
     *         @OA\JsonContent(
     *     required={"username", "name", "gender", "password", "country_id", "city_id"},
     *     @OA\Property(property="profile_pic", type="string", format="binary", description="Profile picture"),
     *     @OA\Property(property="username", type="string", example="john_doe"),
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male"),
     *     @OA\Property(property="password", type="string", example="password"),
     *     @OA\Property(property="country_id", type="integer", example=1),
     *     @OA\Property(property="city_id", type="integer", example=1)
     * )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="name", type="string", example="John Doe"),
     *         @OA\Property(property="email", type="string", format="email", example="john@example.com")
     * )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
    *  @OA\Post(
    *     path="/api/login",
    *     summary="User login",
    *     description="Logs in a user with email and password.",
    *     tags={"App Authentication"},
    *     @OA\RequestBody(
    *         required=true,
    *         description="User credentials",
    *         @OA\JsonContent(
    *             required={"email", "password"},
    *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
    *             @OA\Property(property="password", type="string", example="password") 
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="User authenticated successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="data", type="object",
    *                 @OA\Property(property="id", type="integer", example=1),
    *                 @OA\Property(property="name", type="string", example="John Doe"),
    *                 @OA\Property(property="email", type="string", format="email", example="john@example.com")
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Bad request",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="message", type="string", example="Error message")
    *         )
    *     )
    * )
    * @OA\Get(
    *     path="/api/auth/user/profile",
    *     summary="User Profile",
    *     description="Get current user's profile information",
    *     tags={"App Authentication"},
    *     security={{"sanctum": {}}},
    *     @OA\Response(
    *         response=200,
    *         description="User profile retrieved successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="data", type="object", description="User profile data"),
    *             @OA\Property(property="message", type="string", example="current User")
    *         )
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Bad request",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="message", type="string", example="Error message")
    *         )
    *     )
    * )
    * @OA\Post(
*     path="/api/auth/user/update/profile",
*     summary="Update User Profile",
*     description="Update current user's profile information",
*     tags={"App Authentication"},
*     security={{"sanctum": {}}},
*     @OA\RequestBody(
*         required=true,
*         description="User profile data",
*         @OA\JsonContent(
*             @OA\Property(property="profile_pic", type="string", format="binary", description="Profile picture"),
*             @OA\Property(property="username", type="string", description="Username"),
*             @OA\Property(property="name", type="string", description="Name"),
*             @OA\Property(property="country_id", type="integer", description="Country ID"),
*             @OA\Property(property="city_id", type="integer", description="City ID")
*         )
*     ),
*     @OA\Response(
*         response=200,
*         description="User profile updated successfully",
*         @OA\JsonContent(
*             @OA\Property(property="id", type="integer", example=1),
*             @OA\Property(property="name", type="string", example="John Doe"),
*             @OA\Property(property="email", type="string", format="email", example="john@example.com")
*         )
*     ),
*     @OA\Response(
*         response=400,
*         description="Bad request",
*         @OA\JsonContent(
*             type="object",
*             @OA\Property(property="message", type="string", example="Error message")
*         )
*     )
* )

    * @OA\Post(
     *     path="/api/forgot-password",
     *     summary="Forgot password",
     *     description="Sends OTP for resetting password to user's email.",
     *     tags={"App Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User email",
     *         @OA\JsonContent(
     *          required={"email"},
     *          @OA\Property(property="email", type="string", format="email", example="user@example.com"))
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *         @OA\Property(property="token", type="string", example="Generated OTP token"),
     *         @OA\Property(property="message", type="string", example="Password Reset Otp Sent")
     *     )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     *      @OA\Post(
     *     path="/api/reset-password",
     *     summary="Reset password",
     *     description="Reset user's password.",
     *     tags={"App Authentication"},
     *     security={{ "sanctum": {}, "scope": "reset-password" }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User password reset data",
     *         @OA\JsonContent(
     *     required={"password", "password_confirmation"},
     *     @OA\Property(property="password", type="string", example="new_password"),
     *     @OA\Property(property="password_confirmation", type="string", example="new_password")
     * )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="email", type="string", format="email", example="john@example.com")
     * )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
    *  @OA\Post(
    *     path="/api/auth/otp/resend",
    *     summary="Resend OTP",
    *     description="Resend OTP for email or password verification.",
    *     tags={"App Authentication"},
    *     security={{ "sanctum": {}, "scope": "email-otp,password-otp,reset-password" }},
    *     @OA\Response(
    *         response=200,
    *         description="OTP resent successfully",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="message", type="string", example="Otp resent success!")
    *         )
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Bad request",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="message", type="string", example="Error message")
    *         )
    *     )
    * )
    * @OA\Post(
 *     path="/api/auth/user/change-password",
 *     summary="Change User Password",
 *     description="Change current user's password",
 *     tags={"App Authentication"},
 *     security={{"sanctum": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Change password data",
 *         @OA\JsonContent(
 *         @OA\Property(property="current_password", type="string", description="Current password"),
 *         @OA\Property(property="new_password", type="string", description="New password")
 * )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Password changed successfully",
 *         @OA\JsonContent(
 *     @OA\Property(property="data", type="object", description="User data"),
 *     @OA\Property(property="message", type="string", example="User password changed")
 * )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Error message")
 *         )
 *     )
 * )
 *
 * 
 * @OA\Get(
 *     path="/api/auth/logout",
 *     summary="Logout",
 *     description="Logout current user",
 *     tags={"App Authentication"},
 *     security={{"sanctum": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="User logged out successfully",
 *         @OA\JsonContent(
 *     @OA\Property(property="data", type="object", description="Logout data"),
 *     @OA\Property(property="message", type="string", example="User logged out successfully")
 * )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Error message")
 *         )
 *     )
 * )
 */

 public function appAuth(){}

}

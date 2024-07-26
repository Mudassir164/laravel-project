<?php

namespace App\Api_Documentation;
class WebAnnotations {  
    
    /**
     * @OA\Tag(
     *     name="Web Authentication",
     *     description="User Authentication Endpoints For Web"
     * )
     *
     *   
     * @OA\Post(
     *     path="/panel/sign-up/pub-owner",
     *     summary="Pub owner sign up",
     *     description="Registers a new pub owner.",
     *     tags={"Web Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Pub owner sign-up data",
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pub owner signed up successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *             ),
     *             @OA\Property(property="message", type="string", example="Pub Owner Signed Up. OTP sent")
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
     *
     *  
     * @OA\Post(
     *     path="/panel/sign-up/sponsor",
     *     summary="Sponsor sign up",
     *     description="Registers a new sponsor.",
     *     tags={"Web Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Sponsor sign-up data",
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sponsor signed up successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *             ),
     *             @OA\Property(property="message", type="string", example="Sponsor Signed Up. OTP sent")
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
     * 
    * @OA\Post(
    *     path="/panel/otp/verify",
    *     summary="Verify OTP",
    *     description="Verify OTP for email or password verification.",
    *     tags={"Web Authentication"},
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
    *
 * @OA\Post(
 *     path="/panel/register/pub-owner",
 *     summary="Pub owner registration",
 *     description="Registers a new pub owner.",
 *     tags={"Web Authentication"},
 *     security={{"sanctum": {}, "scope": "register-web"}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Pub owner registration data",
 *         @OA\JsonContent(
 *             required={"pub_name", "owner", "address", "phone", "password", "post_code", "country_id", "city_id"},
 *             @OA\Property(property="pub_name", type="string", example="Mechil"),
 *             @OA\Property(property="owner", type="string", example="John Doe"),
 *             @OA\Property(property="address", type="string", example="Street 2 Shop 13 Los Santos"),
 *             @OA\Property(property="phone", type="string", example="092327"),
 *             @OA\Property(property="post_code", type="string", example="BN2 9SP"),
 *             @OA\Property(property="password", type="string", example="password"),
 *             @OA\Property(property="country_id", type="integer", example=1),
 *             @OA\Property(property="city_id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Pub owner registered successfully",
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
 *

     * 
     * 
     * 
     *     @OA\Post(
     *     path="/panel/register/sponsor",
     *     summary="Sponsor registration",
     *     description="Registers a new Sponsor.",
     *     tags={"Web Authentication"},
     *     security={{ "sanctum": {}, "scope": "register-web" }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Sponsor registration data",
     *         @OA\JsonContent(
     *     required={"business_name", "owner", "address", "phone", "password", "country_id", "city_id"},
     *     @OA\Property(property="business_name", type="string", example="Mechil"),
     *     @OA\Property(property="owner", type="string", example="John Doe"),
     *     @OA\Property(property="address", type="string", example="Street 2 Shop 13 Los Santos"),
     *     @OA\Property(property="phone", type="string", example="092327"),
     *     @OA\Property(property="password", type="string", example="password"),
     *     @OA\Property(property="country_id", type="integer", example=1),
     *     @OA\Property(property="city_id", type="integer", example=1)
     * )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sponsor registered successfully",
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
     * 
     * 
     * 
     * 
    * @OA\Post(
    *     path="/panel/login",
    *     summary="User login",
    *     description="Logs in a user with email and password.",
    *     tags={"Web Authentication"},
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
     
     * @OA\Post(
     *     path="/panel/forgot-password",
     *     summary="Forgot password",
     *     description="Sends OTP for resetting password to user's email.",
     *     tags={"Web Authentication"},
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
     * 
     
     * 
     *     @OA\Post(
     *     path="/panel/reset-password",
     *     summary="Reset password",
     *     description="Reset user's password.",
     *     tags={"Web Authentication"},
     *     security={{ "sanctum": {}, "scope": "reset-password-web" }},
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
     * 
     * 
    * @OA\Post(
    *     path="/panel/otp/resend",
    *     summary="Resend OTP",
    *     description="Resend OTP for email or password verification.",
    *     tags={"Web Authentication"},
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
 * @OA\Get(
 *     path="/panel/user/profile",
 *     summary="User Profile",
 *     description="Get current user's profile information",
 *     tags={"Web Authentication"},
 *     security={{"sanctum": {}, "scope": "web"}},
 *     @OA\Response(
 *         response=200,
 *         description="User profile retrieved successfully",
 *         @OA\JsonContent(
 *     @OA\Property(property="data", type="object", description="User profile data"),
 *     @OA\Property(property="message", type="string", example="current User")
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
 * @OA\Post(
 *     path="/panel/user/change-password",
 *     summary="Change User Password",
 *     description="Change current user's password",
 *     tags={"Web Authentication"},
 *     security={{"sanctum": {}, "scope": "web"}},
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
 *     path="/panel/logout",
 *     summary="Logout",
 *     description="Logout current user",
 *     tags={"Web Authentication"},
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
 * 
 */

public function webAuth(){}

}

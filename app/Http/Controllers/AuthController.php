<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessTokenResult;

class AuthController extends Controller
{
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            if (empty($request->fullName)) {
                return response()->json(['success' => false, 'message' => 'fullName is required'], 400);
            }
            if (empty($request->email)) {
                return response()->json(['success' => false, 'message' => 'email is required'], 400);
            }
            if (empty($request->password)) {
                return response()->json(['success' => false, 'message' => 'password is required'], 400);
            }
            if (empty($request->confirm_password)) {
                return response()->json(['success' => false, 'message' => 'confirm_password is required'], 400);
            }
            if ($request->confirm_password != $request->password) {
                return response()->json(['success' => false, 'message' => "Confirm password doesn't match"], 400);
            }

            $hashedPassword = Hash::make($request->password);


            $user = User::create([
                'name' => $request->fullName,
                'email' => $request->email,
                'password' => $hashedPassword,
                'status'=> 'active',
            ]);

            $token = $user->createToken('mobile')->plainTextToken;
            $user->token = $token;
            $user->followers = 0;
            $user->following = 0;

            return response()->json([
                'success' => true, 
                'data'=> $user, 
                'message' => 'Account created successfully'
            ], 
            200);
        } catch (\Exception $e) {
            // dd($e->getMessage());

            if ($e->errorInfo[1] === 1062) { // User already exists
                return response()->json(['success' => false, 'message' => 'User with email already exists'], 400);
            }
            
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
        
    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            if (empty($request->email)) {
                return response()->json(['success' => false, 'message' => 'email is required'], 400);
            }
            if (empty($request->password)) {
                return response()->json(['success' => false, 'message' => 'password is required'], 400);
            }

            $credentials = $request->only('email', 'password');
            

            if (Auth::attempt($credentials)) {
                // Authentication successful
                $user = User::where('email', $request->email)->first();
                
                $tokenResult = $user->createToken('mobile-access');
                $user->token = $tokenResult->plainTextToken;
                $followersCount = $user->followers_count;
                $followingCount = $user->following_count;
                $user->followers = $followersCount;
                $user->following = $followingCount;

                return response()->json([
                    'success' => true, 
                    'data'=> $user, 
                    'message' => 'Logged in successfully'
                ], 
                200);
            } else {
                // Authentication failed
                return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
        
    }

    public function updateProfile(Request $request, $userId): \Illuminate\Http\JsonResponse
    {
        try {
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            if ($request->has('bio')) {
                $user->bio = $request->bio;
            }

            if ($request->has('userName')) {
                $user->userName = $request->userName;
            }

            if ($request->has('dateOfBirth')) {
                $user->dateOfBirth = $request->dateOfBirth;
            }

            if ($request->hasFile('image')) {
                $getImage = $request->file('image');
                $imageName = time() . '.' . $getImage->getClientOriginalExtension();
                $getImage->move(public_path('uploads'), $imageName);
                $user->image = $imageName;
            }

            $user->save();

            $tokenResult = $user->createToken('mobile-access');
            $user->token = $tokenResult->plainTextToken;
            
            $followersCount = $user->followers_count;
            $followingCount = $user->following_count;
            $user->followers = $followersCount;
            $user->following = $followingCount;

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            dd($e->getMessage());
            if ($e->errorInfo[1] === 1062) { // Username is already taken
                return response()->json(['success' => false, 'message' => 'User Name is taken choose different!'], 400);
            }

            return response()->json(['success' => false, 'message' => "An error occurred".  $e->getMessage()], 500);
        }
        }
 
        public function editProfile(Request $request, $userId): \Illuminate\Http\JsonResponse
        {
        try {
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            if ($request->has('fullName')) {
                $user->name = $request->fullName;
            }

            if ($request->has('userName')) {
                $user->userName = $request->userName;
            }

            if ($request->has('email')) {
                $user->email = $request->email;
            }

            // if ($request->has('image')) {
            //     $user->image = $request->image;
            // }
           

            $user->save();

            $tokenResult = $user->createToken('mobile-access');
            $user->token = $tokenResult->plainTextToken;
            
            $followersCount = $user->followers_count;
            $followingCount = $user->following_count;
            $user->followers = $followersCount;
            $user->following = $followingCount;

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            if ($e->errorInfo[1] === 1062) { // Username is already taken
                return response()->json(['success' => false, 'message' => 'User Name is taken choose different!'], 400);
            }

            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }
}
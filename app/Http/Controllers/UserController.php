<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessTokenResult;

class UserController extends Controller
{
    public function getFollowers(Request $request, $userId): \Illuminate\Http\JsonResponse
    {
        try {
            
            $user = User::find($userId);
            if($user) {
                $followers = $user->followers()->where('request_status', 'accepted')->get();
                $followerData = [];
                foreach ($followers as $follower) {
                    $followerData[] = [
                        'id' => $follower->follower->id,
                        'name' => $follower->follower->name,
                        'email' => $follower->follower->email,
                        'bio' => $follower->follower->bio,
                        'userName' => $follower->follower->userName,
                        'image' => $follower->follower->image,
                        'dateOfBirth' => $follower->follower->dateOfBirth,
                        'status' => $follower->follower->status,
                        'email_verified_at' => $follower->follower->email_verified_at,
                        'created_at' => $follower->follower->created_at,
                        'updated_at' => $follower->follower->updated_at,
                    ];
                }

                if(empty($followerData)) {
                    return response()->json([
                        'success' => true,
                        'message' => 'You have no followers',
                        'data' => $followerData,
                    ], 200);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Followers retrieved successfully',
                        'data' => $followerData,
                    ], 200);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'User not found / User deleted',
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function getFollowing(Request $request, $userId): \Illuminate\Http\JsonResponse
    {
        try {
            $user = User::find($userId);
            
            if($user) {
                $following = $user->following()->where('request_status', 'accepted')->get();
                $followingData = [];
                foreach ($following as $followedUser) {
                    $followingData[] = [
                        'id' => $followedUser->user->id,
                        'name' => $followedUser->user->name,
                        'email' => $followedUser->user->email,
                        'bio' => $followedUser->user->bio,
                        'userName' => $followedUser->user->userName,
                        'image' => $followedUser->user->image,
                        'dateOfBirth' => $followedUser->user->dateOfBirth,
                        'status' => $followedUser->user->status,
                        'email_verified_at' => $followedUser->user->email_verified_at,
                        'created_at' => $followedUser->user->created_at,
                        'updated_at' => $followedUser->user->updated_at,
                    ];
                }
    
                if(empty($followingData)) {
                    return response()->json([
                        'success' => true,
                        'message' => "You aren't following anyone",
                        'data' => $followingData,
                    ], 200);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Following retrieved successfully',
                        'data' => $followingData,
                    ], 200);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'User not found / User deleted',
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function getPendingRequests(Request $request, $userId): \Illuminate\Http\JsonResponse
    {
        try {
            $user = User::find($userId);
            if($user) {
                $followers = $user->followers()->where('request_status', 'pending')->get();
                $followerData = [];
                foreach ($followers as $follower) {
                    $followerData[] = [
                        'id' => $follower->follower->id,
                        'name' => $follower->follower->name,
                        'email' => $follower->follower->email,
                        'bio' => $follower->follower->bio,
                        'userName' => $follower->follower->userName,
                        'image' => $follower->follower->image,
                        'dateOfBirth' => $follower->follower->dateOfBirth,
                        'status' => $follower->follower->status,
                        'email_verified_at' => $follower->follower->email_verified_at,
                        'created_at' => $follower->follower->created_at,
                        'updated_at' => $follower->follower->updated_at,
                    ];
                }

                if(empty($followerData)) {
                    return response()->json([
                        'success' => true,
                        'message' => 'You have no pending requests',
                        'data' => $followerData,
                    ], 200);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Requests retrieved successfully',
                        'data' => $followerData,
                    ], 200);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'User not found / User deleted',
            ], 200);
            

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function acceptFollowRequest(Request $request, $userId): \Illuminate\Http\JsonResponse
    {
        try {
            if (empty($request->follower_id)) {
                return response()->json(['success' => false, 'message' => 'follower_id is required'], 400);
            }
            $user = User::find($userId);
    
            if ($user) {
                $follower = $user->followers()->where('follower_id', $request->follower_id)->first();
    
                if ($follower) {
                    $follower->update(['request_status' => 'accepted']);
    
                    return response()->json([
                        'success' => true,
                        'message' => 'Follower request accepted successfully',
                    ], 200);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Follower request not found',
                    ], 404);
                }
            }
    
            return response()->json([
                'success' => true,
                'message' => 'User not found / User deleted',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }
    

    public function deletePendingRequest(Request $request, $userId): \Illuminate\Http\JsonResponse
    {
        try {
            $user = User::find($userId);

            if ($user) {
                $follower = $user->followers()->where('follower_id', $request->follower_id)->first();

                if ($follower) {
                    $follower->delete();

                    return response()->json([
                        'success' => true,
                        'message' => 'Follower request deleted successfully',
                    ], 200);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Follower request not found',
                    ], 404);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'User not found / User deleted',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    // public function blockUser(Request $request, $userId): \Illuminate\Http\JsonResponse
    // {
    //     try {
    //         if (empty($request->user_id)) {
    //             return response()->json(['success' => false, 'message' => 'user_id is required'], 400);
    //         }
    //         $user = User::find($userId);
    
    //         if ($user) {
    //             $follower = $user->followers()->where('follower_id', $request->follower_id)->first();
    
    //             if ($follower) {
    //                 $follower->update(['request_status' => 'accepted']);
    
    //                 return response()->json([
    //                     'success' => true,
    //                     'message' => 'Follower request accepted successfully',
    //                 ], 200);
    //             } else {
    //                 return response()->json([
    //                     'success' => true,
    //                     'message' => 'Follower request not found',
    //                 ], 404);
    //             }
    //         }
    
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'User not found / User deleted',
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
    //     }
    // }


    public function getAllUsers($userId): \Illuminate\Http\JsonResponse
    {
        try {
            $usersData = User::where('id', '!=', $userId)->where('status', 'active')->get();

            if ($usersData->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => "No users found",
                    'data' => [],
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Users retrieved successfully',
                    'data' => $usersData,
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function createPost(Request $request, $userId)
    {
        try {
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            if (empty($request->type)) {
                return response()->json(['success' => false, 'message' => 'type is required'], 400);
            }

            if (empty($request->turnOffComments)) {
                return response()->json(['success' => false, 'message' => 'turnOffComments is required'], 400);
            }

            $post = new Post();
            $post->user_id = $user->id;
            $post->caption = $request->input('caption');
            $post->type = $request->input('type');
            $post->allowComments = $request->input('turnOffComments') == "false" ? "0": "1";
            
            if ($request->has('tagged_users')) {
                $post->tagged_users = $request->input('tagged_users');
            }
            
            if ($request->hasFile('media')) {
                $getMedia = $request->file('media');
                $mediaName = time() . '.' . $getMedia->getClientOriginalExtension();
                $getMedia->move(public_path('uploads'), $mediaName);
                $post->media_url = $mediaName;        
            }

            $post->save();

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $post,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


}

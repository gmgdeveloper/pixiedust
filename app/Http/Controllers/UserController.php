<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Notifications;
use App\Models\Follower;
use App\Models\Blocked;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessTokenResult;
use Illuminate\Support\Facades\DB;
class UserController extends Controller
{
    public function getFollowers(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();

            if ($user) {
                $followers = $user->followers()->where('request_status', 'accepted')->get();
                $followerData = [];

                foreach ($followers as $follower) {
                    // Check if the follower is blocked by the authenticated user
                    $isBlockedByMe = $user->blocked()
                        ->where('user_id', $follower->follower->id)
                        ->where('blocker_id', $user->id)
                        ->exists();

                    // Check if the authenticated user is blocked by the follower
                    $isBlockedMe = $follower->follower->blocked()
                        ->where('user_id', $user->id)
                        ->where('blocker_id', $follower->follower->id)
                        ->exists();

                    // Determine the status based on block status
                    if ($isBlockedByMe) {
                        $status = 'blockedByMe';
                        $blockedDate = $user->blocked()
                        ->where('user_id', $follower->follower->id)
                        ->where('blocker_id', $user->id)
                        ->first();
                    } elseif ($isBlockedMe) {
                        $status = 'blockedMe';
                    } else {
                        $status = $follower->follower->status;
                    }

                    $followerData[] = [
                        'id' => $follower->follower->id,
                        'name' => $follower->follower->name,
                        'email' => $follower->follower->email,
                        'bio' => $follower->follower->bio,
                        'userName' => $follower->follower->userName,
                        'image' => $follower->follower->image,
                        'dateOfBirth' => $follower->follower->dateOfBirth,
                        'status' => $status,
                        'blocked_date' => $isBlockedByMe ? $blockedDate->created_at: null,
                        'is_freelancer' => $follower->follower->is_freelancer,
                        'enable_push_notifications' => $follower->follower->enable_push_notifications,
                        'isFollowingMe' => $follower->follower->isFollowingMe($follower->follower->id, $user->id),
                        'isFollowedByMe' => $follower->follower->isFollowedByMe($follower->follower->id, $user->id),
                        'device_id' => $follower->follower->device_id,
                        'email_verified_at' => $follower->follower->email_verified_at,
                        'followers' => $follower->follower->followers->count(),
                        'following' => $follower->follower->following->count(),
                        'created_at' => $follower->follower->created_at,
                        'updated_at' => $follower->follower->updated_at,
                    ];
                }

                if (empty($followerData)) {
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
            dd($e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }



    public function getFollowing(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();

            if($user) {
                $following = $user->following()->where('request_status', 'accepted')->get();
                $followingData = [];
                foreach ($following as $followedUser) {

                    $isBlockedByMe = $user->blocked()
                    ->where('user_id', $followedUser->user->id)
                    ->where('blocker_id', $user->id)
                    ->exists();

                    $isBlockedMe = $followedUser->user->blocked()
                        ->where('user_id', $user->id)
                        ->where('blocker_id', $followedUser->user->id)
                        ->exists();

                    if ($isBlockedByMe) {
                        $status = 'blockedByMe';
                        $blockedDate = $user->blocked()
                        ->where('user_id', $followedUser->user->id)
                        ->where('blocker_id', $user->id)
                        ->first();
                    } elseif ($isBlockedMe) {
                        $status = 'blockedMe';
                    } else {
                        $status = $followedUser->user->status;
                    }

                    $followingData[] = [
                        'id' => $followedUser->user->id,
                        'name' => $followedUser->user->name,
                        'email' => $followedUser->user->email,
                        'bio' => $followedUser->user->bio,
                        'userName' => $followedUser->user->userName,
                        'image' => $followedUser->user->image,
                        'dateOfBirth' => $followedUser->user->dateOfBirth,
                        'status' => $status,
                        'blocked_date' => $isBlockedByMe ? $blockedDate->created_at: null,
                        'followers' => $followedUser->user->followers->count(),
                        'following' => $followedUser->user->following->count(),
                        'isFollowingMe' => $followedUser->user->isFollowingMe($followedUser->user->id, $user->id),
                        'isFollowedByMe' => $followedUser->user->isFollowedByMe($followedUser->user->id, $user->id),
                        'is_freelancer' => $followedUser->user->is_freelancer,
                        'enable_push_notifications' => $followedUser->user->enable_push_notifications,
                        'device_id' => $followedUser->user->device_id,
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

    public function getPendingRequests(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();

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
                        'followers' => $follower->follower->followers->count(),
                        'following' => $follower->follower->following->count(),
                        'is_freelancer' => $follower->follower->is_freelancer,
                        'enable_push_notifications' => $follower->follower->enable_push_notifications,
                        'device_id' => $follower->follower->device_id,
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

    public function acceptFollowRequest(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            if (empty($request->follower_id)) {
                return response()->json(['success' => false, 'message' => 'follower_id is required'], 400);
            }
            $user = auth()->user();

            if ($user) {
                $follower = $user->followers()->where('follower_id', $request->follower_id)->first();
    
                if ($follower) {
                    $follower->update(['request_status' => 'accepted']);
                    $followerData = User::find($request->follower_id);
                    
                    try{
                        $this->sendPushNotification(
                            "Follow Request", 
                            $user->name. " has accepted your request", 
                            $followerData->device_id
                        );
                    } catch(Exception $e) {}

                    return response()->json([
                        'success' => true,
                        'message' => 'Follower request accepted successfully',
                        'data' => $user,
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

    public function followUser(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            if (empty($request->user_id)) {
                return response()->json(['success' => false, 'message' => 'user_id is required'], 400);
            }
            
            $user = auth()->user();

            if ($user) {
                $userToFollow = User::find($request->user_id);
                
                if ($userToFollow) {
                    // if ($user->followers->contains('user_id', $userToFollow->id))
                    $follower = new Follower();
                    $follower->user_id = $userToFollow->id;
                    $follower->follower_id = $user->id;
                    $follower->save();

                    $userToFollow->load('followers', 'following');
                    $followersCount = $userToFollow->followers->count();
                    $followingCount = $userToFollow->following->count();

                    $responseData = $userToFollow->toArray();
                    $responseData['followers'] = $followersCount;
                    $responseData['following'] = $followingCount;

                    $responseData['isFollowingMe'] = $userToFollow->isFollowingMe($userToFollow->id, $user->id);
                    $responseData['isFollowedByMe'] = $userToFollow->isFollowedByMe($userToFollow->id, $user->id);

                    return response()->json([
                        'success' => true,
                        'message' => 'User followed successfully',
                        'data' => $responseData,
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'User to follow not found',
                    ], 404);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'User not found / User deleted',
            ], 200);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function unfollowUser(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            if (empty($request->followed_user_id)) {
                return response()->json(['success' => false, 'message' => 'followed_user_id is required'], 400);
            }
            
            $user = auth()->user();

            if ($user) {
                $userToUnfollow = User::find($request->followed_user_id);
                
                if ($userToUnfollow) {
                    if ($user->following->contains('user_id', $userToUnfollow->id)) {

                        // $user->following()->where('follower_id', $userToUnfollow->id)->delete();
                        $following = Follower::where('user_id', $userToUnfollow->id)
                            ->where('follower_id', $user->id)
                            ->first();
                        if ($following) {
                            $following->delete();
                        }

                        $user->load('followers', 'following');
                        $followersCount = $user->followers->count();
                        $followingCount = $user->following->count();

                        $responseData = $user->toArray();
                        $responseData['followers'] = $followersCount;
                        $responseData['following'] = $followingCount;
                        $responseData['isFollowingMe'] = $userToUnfollow->isFollowingMe($userToUnfollow->id, $user->id);
                        $responseData['isFollowedByMe'] = $userToUnfollow->isFollowedByMe($userToUnfollow->id, $user->id);
    
                        return response()->json([
                            'success' => true,
                            'message' => 'Unfollowed user successfully',
                            'data' => $responseData,
                        ], 200);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'You are not following this user',
                        ], 400);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'User to unfollow not found',
                    ], 404);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'User not found / User deleted',
            ], 200);
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function deletePendingRequest(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();

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
            // dd($e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function changeBlockStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            if (empty($request->user_id)) {
                return response()->json(['success' => false, 'message' => 'user_id is required'], 400);
            }

            $authUser = auth()->user();
            $userIdToBlock = (int) $request->user_id;
            $userToBlock = User::find($userIdToBlock);

            if ($userToBlock) {
                // Check if a block record already exists
                $existingBlock = Blocked::where('user_id', $userIdToBlock)
                    ->where('blocker_id', $authUser->id)
                    ->first();

                $blockedUser = User::find($userIdToBlock);

                $followersCount = $blockedUser->followers->count();
                $followingCount = $blockedUser->following->count();

                $responseData = $blockedUser->toArray();

                $responseData['followers'] = $followersCount;
                $responseData['following'] = $followingCount;


                if (!$existingBlock) {
                    // Create a new block record
                    $blocked = new Blocked();
                    $blocked->user_id = $userIdToBlock;
                    $blocked->blocker_id = $authUser->id;
                    $blocked->save();

                    // Check if the authenticated user has blocked the follower
                    $isBlockedMe = $blockedUser->blocked()
                    ->where('user_id', $authUser->id)
                    ->where('blocker_id', $userIdToBlock)
                    ->exists();

                    // Check if the authenticated user is blocked by the follower
                    $isBlockedByMe = $authUser->blocked()
                    ->where('user_id', $userIdToBlock)
                    ->where('blocker_id', $authUser->id)
                    ->exists();

                    if ($isBlockedByMe) {
                        $responseData['status'] = 'blockedByMe';
                    } elseif ($isBlockedMe) {
                        $responseData['status'] = 'blockedMe';
                    } else {
                        // Set the original status if no blocking status applies
                        $responseData['status'] = $blockedUser['status'];
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'User blocked successfully.',
                        'data' => $responseData,
                    ], 200);
                } else {
                    // If the block record already exists, delete it to unblock the user
                    $existingBlock->delete();

                    // Check if the authenticated user has blocked the follower
                    $isBlockedMe = $blockedUser->blocked()
                    ->where('user_id', $authUser->id)
                    ->where('blocker_id', $userIdToBlock)
                    ->exists();

                    // Check if the authenticated user is blocked by the follower
                    $isBlockedByMe = $authUser->blocked()
                    ->where('user_id', $userIdToBlock)
                    ->where('blocker_id', $authUser->id)
                    ->exists();

                    if ($isBlockedByMe) {
                        $responseData['status'] = 'blockedByMe';
                    } elseif ($isBlockedMe) {
                        $responseData['status'] = 'blockedMe';
                    } else {
                        // Set the original status if no blocking status applies
                        $responseData['status'] = $blockedUser['status'];
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'User unblocked successfully.',
                        'data' => $responseData,
                    ], 200);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'User not found / User deleted.',
            ], 200);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }


    public function getBlockedUsers(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();

            $blockedUsers = Blocked::where('user_id', '!=', $user->id)
                ->where('blocker_id', $user->id)
                ->get();

            $blockedUserData = [];

            foreach ($blockedUsers as $blockedUser) {
                $userToFetch = User::find($blockedUser->user_id);

                if ($userToFetch) {
                    $userData = [
                        'id' => $userToFetch->id,
                        'name' => $userToFetch->name,
                        'email' => $userToFetch->email,
                        'image' => $userToFetch->image,
                    ];

                    $blockedUserData[] = $userData;
                }
            }

            if (empty($blockedUserData)) {
                return response()->json([
                    'success' => true,
                    'message' => "No users found",
                    'data' => [],
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Users retrieved successfully',
                    'data' => $blockedUserData,
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }


    public function getAllUsers(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();

            $usersData = User::where('id', '!=', $user->id)->where('status', 'active')->get();

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

    public function getUserDataById(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            if (empty($request->userId)) {
                return response()->json(['success' => false, 'message' => 'userId is required'], 400);
            }

            $targetUserId = (int)$request->userId;
            $authUserId = auth()->user()->id;

            $userData = User::where('id', $targetUserId)->get();

            if ($userData->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => "User not found",
                ], 200);
            }

            $user = $userData->first();

            $followersCount = $user->followers->count();
            $followingCount = $user->following->count();

            $responseData = $user->toArray();

            unset($responseData['followers']);
            unset($responseData['following']);

            $responseData['followers'] = $followersCount;
            $responseData['following'] = $followingCount;

            $responseData['isFollowingMe'] = $user->isFollowingMe($targetUserId, $authUserId);
            $responseData['isFollowedByMe'] = $user->isFollowedByMe($targetUserId, $authUserId);

            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => $responseData,
            ], 200);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function getUserNotifications()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            $notifications = Notifications::where('user_id', $user->id)
                ->with('fromUser') 
                ->orderByDesc('created_at')
                ->get();

            if ($notifications->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => "No notifications found",
                    'data' => [],
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Notifications retrieved successfully',
                    'data' => $notifications,
                ], 200);
            }
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function updateNotificationStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            if (empty($request->notification_id)) {
                return response()->json(['success' => false, 'message' => 'notification_id is required'], 400);
            }

            $user = auth()->user();

            if ($user) {
                $notification = Notifications::find($request->notification_id);
                $notification->load('fromUser');

                if ($notification) {
                    if ($notification->user_id === $user->id) {
                        $notification->update(['status' => 1]);

                        return response()->json([
                            'success' => true,
                            'message' => 'Notification status updated successfully',
                            'data' => $notification,
                        ], 200);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'You do not have permission to update this notification',
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Notification not found',
                    ], 404);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'User not found / User deleted',
            ], 200);
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function updateDeviceToken(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            if (empty($request->device_id)) {
                return response()->json(['success' => false, 'message' => 'device_id is required'], 400);
            }

            $user = auth()->user();

            if ($user) {
                $user->update(['device_id' => $request->device_id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Device ID updated successfully',
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'User not found / User deleted',
            ], 200);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function updatePushNotificationStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();

            if ($user) {
                $value = $user->enable_push_notifications;

                $user->update(['enable_push_notifications' => $value == 0 ? 1: 0]);
                
                return response()->json([
                    'success' => true,
                    'message' => $user->enable_push_notifications == 0 ? 
                    "Push notifications turned off you'll not recieve any push notifications":
                    "Wuhoo push notifications enabled you'll recieve all updates",
                    'data' => $user,
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'User not found / User deleted',
            ], 200);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }


    public function sendPushNotification($title, $body, $deviceId)
    {
        $url = "https://fcm.googleapis.com/fcm/send";
        $serverKey = 'AAAAbOfhwxs:APA91bHPFOsFasA5GoSMi56OLVRB5iH_AiVKcyQQd6bUtALW1xy081sMOE14eN--7iohRGvy_4xxvfKj1lxJRRDkvotpjo-eunI3D-IVcJjFTTS3MhoqxJi2Uc5XKwZdRlKJRa29HjYE';
        $notification = array('title' => $title, 'body' => $body, 'sound' => 'default', 'badge' => '1');
        $arrayToSend = array('to' => $deviceId, 'notification' => $notification, 'priority' => 'high');
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key=' . $serverKey;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        // Close request
        if ($response === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);

    }

}

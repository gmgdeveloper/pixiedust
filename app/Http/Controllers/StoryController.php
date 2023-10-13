<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Stories;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Notifications;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessTokenResult;

class StoryController extends Controller
{
    public function createStory(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }
  
            if (empty($request->type)) {
                return response()->json(['success' => false, 'message' => 'type is required'], 400);
            }

            if (empty($request->turnOffComments)) {
                return response()->json(['success' => false, 'message' => 'turnOffComments is required'], 400);
            }

            $story = new Stories();
            $story->user_id = $user->id;
            $story->caption = $request->input('caption');
            $story->type = $request->input('type');
            $story->allowComments = $request->input('turnOffComments') == "false" ? "0" : "1";
            
            $taggedUsers = [];

            if ($request->has('taggedUsers')) {
                $taggedUsers = json_decode($request->input('taggedUsers'), true);
                $story->tagged_users = json_encode($taggedUsers);
            }
            
            if ($request->hasFile('media')) {
                $getMedia = $request->file('media');
                $mediaName = time() . '.' . $getMedia->getClientOriginalExtension();
                $getMedia->move(public_path('uploads'), $mediaName);
                $story->media_url = $mediaName;
            }
            
            $story->save();
            
            $storyId = $story->id;

            foreach ($taggedUsers as $taggedUserId) {
                $notification = new Notifications([
                    'user_id' => $taggedUserId,
                    'notification_type' => 'Story',
                    'story_id' => $storyId,
                    'from_user_id' => $user->id,
                    'content' => 'tagged you in a story.',
                ]);
                $notification->save();

                try {
                    $taggedUser = User::find($taggedUserId);
                    $this->sendPushNotification(
                        "Tagged you", 
                        $user->name. " tagged you in a story", 
                        $taggedUser->device_id
                    );
                } catch (Exception $e) {
                }
            }

        
            return response()->json([
                'success' => true,
                'message' => 'Story created successfully',
                'data' => $story,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    
    public function getFeedStories(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();
    
            $storiesData = Stories::with('user')
                ->whereIn('user_id', function ($query) use ($user) {
                    $query->select('user_id')
                        ->from('followers')
                        ->where('follower_id', $user->id);
                })
                ->get();
    
            $groupedStories = $storiesData->groupBy('user_id');
    
            $groupedStoriesData = [];
    
            foreach ($groupedStories as $userId => $stories) {
                $user = User::find($userId);
                $groupedStoriesData[] = [
                    'user' => $user,
                    'stories' => $stories,
                ];
            }
    
            if (empty($groupedStoriesData)) {
                return response()->json([
                    'success' => true,
                    'message' => "No stories found",
                    'data' => [],
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Stories retrieved successfully',
                    'data' => $groupedStoriesData,
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function getUserStories(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();
    
            $userStories = Stories::where('user_id', $user->id)->get();
    
            if ($userStories->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => "No stories found for this user",
                    'data' => [],
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'User stories retrieved successfully',
                    'data' => $userStories,
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }    

    public function getAllPosts(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();

            $postsData = Post::with('user')->get()->map(function ($post) use ($user) {
                $post->likedByMe = $user ? $post->likes()->where('user_id', $user->id)->exists() : false;
                return $post;
            });

            if ($postsData->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => "No posts found",
                    'data' => [],
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Posts retrieved successfully',
                    'data' => $postsData,
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function getUserPosts(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();

            $postsData = Post::where('user_id', $user->id)->with('user')->get()->map(function ($post) use ($user) {
                $post->likedByMe = $user ? $post->likes()->where('user_id', $user->id)->exists() : false;
                return $post;
            });

            if ($postsData->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => "No posts found",
                    'data' => [],
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Posts retrieved successfully',
                    'data' => $postsData,
                ], 200);
            }
        } catch (\Exception $e) {
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Notifications;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessTokenResult;

class PostController extends Controller
{
    // public function getAllPosts(): \Illuminate\Http\JsonResponse
    // {
    //     try {
    //         $user = auth()->user(); // Get the authenticated user

    //         // Fetch all posts
    //         $postsData = Post::with('user')->get();

    //         if ($postsData->isEmpty()) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => "No posts found",
    //                 'data' => [],
    //             ], 200);
    //         }

    //         // Get the IDs of the posts that the user has liked
    //         $likedPostIds = $user ? $user->likes->pluck('post_id')->toArray() : [];

    //         // Iterate through the posts and add the 'likedByMe' attribute
    //         $postsData->each(function ($post) use ($likedPostIds) {
    //             $post->likedByMe = in_array($post->id, $likedPostIds);
    //         });

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Posts retrieved successfully',
    //             'data' => $postsData,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
    //     }
    // }

    public function getFeedPosts(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();

            $postsData = Post::with('user')
                ->whereIn('user_id', function ($query) use ($user) {
                    $query->select('user_id')
                        ->from('followers')
                        ->where('follower_id', $user->id);
                })
                ->get()
                ->map(function ($post) use ($user) {
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

    public function createPost(Request $request)
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

            $post = new Post();
            $post->user_id = $user->id;
            $post->caption = $request->input('caption');
            $post->type = $request->input('type');
            $post->allowComments = $request->input('turnOffComments') == "false" ? "0" : "1";
            
            $taggedUsers = [];

            if ($request->has('taggedUsers')) {
                $taggedUsers = json_decode($request->input('taggedUsers'), true);
                $post->tagged_users = json_encode($taggedUsers);
            }
            
            if ($request->hasFile('media')) {
                $getMedia = $request->file('media');
                $mediaName = time() . '.' . $getMedia->getClientOriginalExtension();
                $getMedia->move(public_path('uploads'), $mediaName);
                $post->media_url = $mediaName;
            }
            
            $post->save();
            
            $postId = $post->id;

            foreach ($taggedUsers as $taggedUserId) {
                $notification = new Notifications([
                    'user_id' => $taggedUserId,
                    'notification_type' => 'Post',
                    'post_id' => $postId,
                    'from_user_id' => $user->id,
                    'content' => 'tagged you in a post.',
                ]);
                $notification->save();

                try {
                    $taggedUser = User::find($taggedUserId);
                    $this->sendPushNotification(
                        "Tagged you", 
                        $user->name. " tagged you in a post", 
                        $taggedUser->device_id
                    );
                } catch (Exception $e) {
                }
            }

        
            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $post,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function likePost(Request $request)
    {
        try {
            if (empty($request->postId)) {
                return response()->json(['success' => false, 'message' => 'postId is required'], 400);
            }

            $user = auth()->user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            $post = Post::find($request->postId);

            if (!$post) {
                return response()->json(['message' => 'Post not found'], 404);
            }

            $likedByMe = $post->likes()->where('user_id', $user->id)->exists();

            if ($likedByMe) {
                //// If the user has already liked the post, delete the like
                $post->likes()->where('user_id', $user->id)->delete();
                $isLikedByMe = false;
            
                $postOwner = $post->user;
                $notification = Notifications::where('user_id', $postOwner->id)
                    ->where('content', $user->name . ' liked your post.')
                    ->first();
                if ($notification) {
                    $notification->delete();
                }
            } else {
                $like = new Like(['user_id' => $user->id]);
                $post->likes()->save($like);
                $isLikedByMe = true;
                
                if($post->user_id != $user->id) {
                    //// Create a notification for the post owner if the post owner is not user them self
                    $notification = new Notifications([
                        'user_id' => $post->user->id,
                        'notification_type' => 'Post',
                        'post_id' => $post->id,
                        'from_user_id' => $user->id,
                        'content' => 'liked your post.',
                    ]);
                    $notification->save();

                    try{
                        $this->sendPushNotification(
                            "New like on post", 
                            $user->name. " liked your post", 
                            $post->user->device_id
                        );
                    } catch(Exception $e) {
    
                    }
                }
            }

            $post->likes_count = $post->likes()->count();
            $post->likedByMe = $isLikedByMe;
            $post->save();
            
            $post->load('user');

            return response()->json([
                'success' => true,
                'message' => $likedByMe ? 'Post unliked successfully' : 'Post liked successfully',
                'data' => $post,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }



    public function commentPost(Request $request)
    {
        try {
            if (empty($request->postId)) {
                return response()->json(['success' => false, 'message' => 'postId is required'], 400);
            }

            if (empty($request->content)) {
                return response()->json(['success' => false, 'message' => 'content is required'], 400);
            }

            $user = auth()->user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            $post = Post::find($request->postId);

            if (!$post) {
                return response()->json(['message' => 'Post not found'], 404);
            }

            $validatedData = $request->validate([
                'content' => 'required|string|max:255',
            ]);

            $comment = new Comment([
                'user_id' => $user->id,
                'content' => $validatedData['content'],
            ]);

            $post->comments()->save($comment);
            $post->comments_count = $post->comments()->count();
            $post->likedByMe = $user ? $post->likes()->where('user_id', $user->id)->exists() : false;
            $post->save();

            if($post->user_id != $user->id) {
                //// Create a notification for the post owner if the post owner is not user them self
                $notification = new Notifications([
                    'user_id' => $post->user->id,
                    'notification_type' => 'Post',
                    'post_id' => $post->id,
                    'from_user_id' => $user->id,
                    'content' => 'commented on your post.',
                ]);
                $notification->save();
                try{
                    $this->sendPushNotification(
                        "New comment on post", 
                        $user->name. " commented on your post", 
                        $post->user->device_id
                    );
                } catch(Exception $e) {}
            }

            $post->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully',
                'data' => $post,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }



    public function viewPostComments($postId): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();
            $comments = Comment::where('post_id', $postId)->with('user')->get();

            if ($comments->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => "No comments found for this post",
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Comments retrieved successfully',
                'data' => $comments,
            ], 200);
        } catch (\Exception $e) {
            // dd($e->getMessage());
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

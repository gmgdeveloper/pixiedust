<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blocked;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Services;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\Post;

class ServiceController extends Controller
{
    public function get_All_User_Post()
    {
        $allUserPosts = Services::with('user')->get();

        $totalServices = $allUserPosts->count();

        $userServiceCount = 0; // Initialize a counter variable

        foreach ($allUserPosts as $post) {
            $post->image = asset('public/uploads/' . $post->user->image);
            $post->user_name = $post->user->name; // Add user_name to the post object

            // Increment the counter for each user service processed
            $userServiceCount++;
        }

        return response()->json([
            'success' => true,
            'message' => 'All user posts retrieved successfully',
            'data' => $allUserPosts,
            'totalService' => $totalServices,
            'userServiceCount' => $userServiceCount, // Include the user service count in the response
        ]);
    }





    public function get_post_id($user_id)
    {
        // if (empty($request->id)) {
        //     return response()->json(['success' => false, 'message' => 'id is required'], 400);
        // }

        $user = Services::where('user_id', $user_id)->get();
        if (count($user) > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'get user by id',
                'users' => $user,
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'user services not found',
            ]);
        }
    }



    public function edit_user_post(Request $request, $id)
    {
        $edit = Services::findOrFail($id);
        $edit->description = $request->input('description');
        $edit->price = $request->input('price');
        $edit->show_price = $request->input('show_price');
        $edit->userName = $request->input('image');

        $edit->update();
        return response()->json([
            'status' => 200,
            'message' => 'Data Update Successfully',
            'data' => $edit
        ]);
    }



    public function delete_post($id)
    {
        $delete = Services::findOrFail($id);
        $delete->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Data Deleted Succesfully',
            'data' => $delete
        ]);
    }


    // Notification
    public function getNotifications()
    {
        $notifications = Notifications::all();

        $result = [];

        foreach ($notifications as $notification) {
            $fromUser = User::find($notification->from_user_id);
            $toUser = User::find($notification->user_id);

            $words = explode(' ', $notification->content);
            $firstWord = $words[0];

            if ($fromUser && $toUser) {
                $result[] = [
                    'content' => $firstWord,
                    'notification_type' => $notification->notification_type,
                    'from_user_name' => $fromUser->name,
                    'to_user_name' => $toUser->name,
                    'created_at' => $notification->created_at->toDateTimeString(),

                ];
            }
        }

        return response()->json([
            'status' => 200,
            'notifications' => $result,
        ]);
    }


    ////////////Posts///////////
    public function getAllPosts()
    {
        $posts = Post::with('user', 'comments', 'likes')->get();

        $totalPosts = $posts->count();

        $posts = $posts->map(function ($post) {
            $user = $post->user;
            $post->user_name = $user->name;
            $post->image =  asset('public/uploads/' . $user->image);
            $post->media_url = asset('public/uploads/' . $post->media_url);

            $comments = $post->comments->map(function ($comment) {
                $commentUser = User::find($comment->user_id);
                $comment->userName = $commentUser ? $commentUser->name : null;
                return $comment;
            });

            $likes = $post->likes->map(function ($like) {
                $likeUser = User::find($like->user_id);
                $like->userName = $likeUser ? $likeUser->name : null;
                $like->userImage = $likeUser ? asset('public/uploads/' . $likeUser->image) : null;
                return $like;
            });

            $post->comments = $comments;
            $post->likes = $likes;

            return $post;
        });

        $response = [
            'status' => 200,
            'totalPosts' => $totalPosts,
            'posts' => $posts,
        ];

        return response()->json($response);
    }



    public function getSinglePost($id)
    {
        $post = Post::findorFail($id);

        return response()->json([
            'status' => 200,
            'msg' => 'post fetched successfully',
            'data' => $post,
        ]);
    }

    public function UpdatePost(Request $request, $id)
    {
        // dd($request)
        $update = Post::findorFail($id);
        $update->caption = $request->input('caption');

        if ($request->hasFile('media_url')) {
            $file = $request->file('media_url');
            $imageName = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $imageName);
            $update->media_url = $imageName;
        }

        $update->save();
        return response()->json([
            'status' => 200,
            'msg' => 'Data Updated Successfully',
            'data' => $update,
        ]);
    }



    public function DeletePostData($id)

    {
        $deletePost = Post::findorFail($id);

        $deletePost->delete();

        return response()->json([
            'status' => 200,
            'msg' => 'data deleted successfully',
            'data' => $deletePost
        ]);
    }


    public function getSingleRecord($id)
    {
        $comment = Comment::findOrFail($id);

        return response()->json([
            'status' => 200,
            'msg' => 'data fetch successfully',
            'data' => $comment,
        ]);
    }

    public function editComments(Request $request, $id)
    {


        $comment = Comment::findOrFail($id);

        $comment->content = $request->input('content');
        $comment->update();

        return response()->json([
            'status' => 200,
            'msg' => 'data updated successfully',
            'data' => $comment,
        ]);
    }


    public function deleteComment($postId, $commentId)
    {
        $deleteComment = Comment::findOrFail($commentId);
        $deleteComment->delete();

        return response()->json([
            'status' => 200,
            'msg' => 'comment deleted',
            'data' => $deleteComment,
        ]);
    }

    //Blocked user 

    public function getBlockedUsers(Request $request)
    {
        try {
            $blocker_id = $request->blocker_id;
            $blockedUsers = Blocked::where('blocker_id', $blocker_id)->get();
            $blockedUserData = [];
            foreach ($blockedUsers as $blockedUser) {
                $userToFetch = User::find($blockedUser->user_id);

                if ($userToFetch) {
                    $userData = [
                        'name' => $userToFetch->name,
                        'id'=>$userToFetch->id,
                        'created_at' => $userToFetch->created_at,
                    ];

                    $blockedUserData[] = $userData;
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Blocked Users Successfully Retrieved',
                'blocked_users' => $blockedUserData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function unblockUser(Request $request)
    {
        try {
            $user_id = $request->user_id;
            $blocker_id = $request->blocker_id;
            $blockedUser = Blocked::where('user_id', $user_id)
                ->where('blocker_id', $blocker_id)
                ->first();
            if ($blockedUser) {
                $blockedUser->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'User successfully unblocked',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not blocked by the specified user',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Services;
use App\Models\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function create_user(Request $request)
    {


        $request->validate([
           
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $iamgePath = 'public/uploads';
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move($iamgePath, $imageName);
        } else {
            $image = 'default.jpg';
        }

        $create = new User();
        $create->name = $request->input('name');
        $create->email = $request->input('email');
        $create->password = $request->input('password');
        $create->bio = $request->input('bio');
        $create->userName = $request->input('userName');
        $create->dateOfBirth = $request->input('dateOfBirth');
        $create->status = $request->input('status');

        $create->save();
        return response()->json([
            'status' => 200,
            'message' => "Data Inserted Succesfully",
            'data' => $create,
        ]);
    }
    public function get_all_users(Request $request)
{
    $name = $request->input('name');
    $age = $request->input('dateOfBirth');
    $address = $request->input('dob');
    $registration = $request->input('created_at');

    $query = User::query();
    if ($name) {
        $query->where('name', 'like', '%' . $name . '%');
    }
    if ($age) {
        $query->where('dateOfBirth', $age);
    }
    if ($address) {
        $query->where('dob', $address);
    }
    if ($registration) {
        $query->where('created_at', $registration);
    }

    // Get All users
    $allUsers = $query->latest()->get();

    foreach ($allUsers as $user) {
        $user->image = asset('/public/uploads/' . $user->image);
    }

    // Get Weekly Users
    $startOfWeek = now()->startOfWeek();
    $weeklyUsers = $query->where('created_at', '>=', $startOfWeek)->get();

    // Get Monthly Users
    $startOfMonth = now()->startOfMonth();
    $monthlyUsers = $query->where('created_at', '>=', $startOfMonth)->get();

    // Get Today's Users
    $todayUsers = $query->whereDate('created_at', now())->get();

   

    return response()->json([
        'status' => 200,
        'message' => 'Data get Successfully',
        'users' =>  $query,
        'allUsers' => $allUsers,
        'weeklyUsers' => $weeklyUsers,
        'monthlyUsers' => $monthlyUsers,
        'todayUsers' => $todayUsers,
    ]);
}

    

    public function get_user($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'status' => 200,
            'message' => 'get user by id',
            'users' => $user,
        ]);
    }

   

    public function edit_user(Request $request,  $id)
    {
    

        $edit = User::findOrfail($id);
        $edit->name = $request->input('name');
        $edit->email = $request->input('email');
        $edit->password = $request->input('password');
        $edit->bio = $request->input('bio');
        $edit->userName = $request->input('userName');
        $edit->dateOfBirth = $request->input('dateOfBirth');
        $edit->status = $request->input('status');

        $edit->update();
        
        return response()->json([
            'status' => 200,
            'message' => 'Data Update Succesfully',
            'data' => $edit
        ]);
    }


    public function delete_user($id)
    {
        $delete = User::findOrFail($id);
        $delete->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Data Deleted Succesfully',
            'data' => $delete
        ]);
    }


    public function showAllRecordUser(Request $request, $id)
    {

        $user = User::where('id',$id)->first();
        $userPosts = Post::where('user_id', $id)->get();
        $userLikes = Like::where('user_id', $id)->count();
        $service = Services::where('user_id' ,$id)->get();
        $receivedComments = [];
        $receivedLikes = [];
    
        foreach ($userPosts as $post) {
            $likes = $post->likes()->with('user')->get()->toArray();
            $receivedLikes = array_merge($receivedLikes, $likes);
    
            $comments = $post->comments()->with('user')->get()->toArray();
            $receivedComments = array_merge($receivedComments, $comments);

            $post->image =  asset('public/uploads/' . $user->image);
            $post->media_url = asset('public/uploads/' . $post->media_url);
        }
    
        return response()->json([
            'status' => 200,
            'msg' => 'Data fetched',
            'user_posts' => $userPosts,
            'user_likes' => $userLikes,
            'user_comments' => $receivedComments,
            'user_received_likes' => $receivedLikes,
            'user' => $user,
            'services' =>$service,
        ]);
    }
        
}
    


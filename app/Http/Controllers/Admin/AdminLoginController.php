<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

use App\Http\Controllers\Controller;
use App\Models\AdminLogin;
use App\Models\ResetPassword;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class AdminLoginController extends Controller
{

    public function admin_create(Request $request)
    {
        $request->validate([

            'user_name' => 'required',
            'user_email' => 'required',
            // 'user_password'=> 'required',
        ]);

        $createAdmin = new AdminLogin();

        $createAdmin->user_name = $request->input('user_name');
        $createAdmin->user_email = $request->input('user_email');
        $createAdmin->user_password = Hash::make($request->input('user_password'));

        $createAdmin->save();
        return response()->json([
            'status' => 200,
            'message' => "Admin Data inserted Successfull",
            'data' =>  $createAdmin,
        ]);
    }

    // Create an admin
    public function createAdmin(Request $request)
    {
        // Validation and data saving logic here
        $admin = new AdminLogin();
        $admin->user_name = $request->input('user_name');
        $admin->user_email = $request->input('user_email');
       


        $admin->save();

        return response()->json([
            'status' => 200,
            'msg' => 'Admin created',
            'data' => $admin,
        ]);
    }


    public function getAllAdmins()
    {
        $admins = AdminLogin::all();

        return response()->json([
            'status' => 200,
            'admins' => $admins,
        ]);
    }

    // Edit admin by ID
    public function getAdminUpdate(Request $request, $id)
    {
        $admin = AdminLogin::findOrFail($id);
        $admin->user_name = $request->input('user_name');
        $admin->user_email = $request->input('user_email');
        $admin->user_password = Hash::make($request->input('user_password'));

        $admin->update();

        return response()->json([
            'status' => 200,
            'msg' => 'Admin updated',
            'data' => $admin,
        ]);
    }



    public function login_admin(Request $request)
    {
        $admin = AdminLogin::where('user_email', $request->user_email)->first();
    
        if (!$admin || !Hash::check($request->user_password, $admin->user_password)) {
            return response()->json([
                'status' => 401,
                'message' => 'Login Failed. Invalid email or password.',
            ]);
        }
    
        $token = $admin->createToken($request->user_email)->plainTextToken;
    
        return response()->json([
            'token' => $token,
            'message' => 'Login successfully',
            'status' => 200,
        ]);
    }
    

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $request->user()->tokens()->delete();
        }
        return response()->json([
            'status' => 200,
            'message' => 'Successfully logged out'
        ]);
    }




    public function forgetPassword(Request $request)
    {
        try {
            $user = AdminLogin::where('user_email', $request->input('user_email'))->first();

            if ($user) {


                $token = Str::random(40);
                $resetPasswordUrl = config('app.react_app.base_url') . '/reset-password?token=' . $token;

                $data['url'] = $resetPasswordUrl;
                $data['user_email'] = $request->user_email;
                $data['title'] = 'Password Reset';
                $data['body'] = 'Please click on the link below to reset your password.';

                Mail::send('forget', ['data' => $data], function ($message) use ($data) {
                    $message->to($data['user_email'])->subject($data['title']);
                });


                $datetime = now();

                ResetPassword::updateOrCreate(
                    ['user_email' => $request->user_email],
                    [
                        'user_email' => $request->user_email,
                        'token' => $token,
                        'created_at' => $datetime
                    ]
                );

                return response()->json(['success' => true, 'msg' => 'Please check your email for a password reset link.']);
            } else {
                return response()->json(['success' => false, 'msg' => 'User not found']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function resetPasswordLoad($token)
    {
        $resetData = ResetPassword::where('token', $token)->first();

        if ($resetData) {
            $user = AdminLogin::where('user_email', $resetData->user_email)->first();

            if ($user) {
                // You can return JSON data or a view here as needed
                return response()->json(['user' => $user, 'resetData' => $resetData], 200);
            } else {
                return response()->json(['error' => 'Data is not matched, sorry'], 404);
            }
        } else {
            return response()->json(['error' => 'Invalid token'], 404);
        }
    }

    public function resetPassword(Request $request)
    {
        $tokencheck = ResetPassword::where('token', $request->token)->first();

        if (!empty($tokencheck)) {
            $user = AdminLogin::where('user_email', $tokencheck->user_email)->first();
            if ($user) {
                $user->user_password = bcrypt($request->user_password);
                $user->save();
                $tokencheck->delete();

                return response()->json(['success' => true, 'message' => 'Your password has been successfully updated. You can now log in with your new password.'], 200);
            }
        }

        return response()->json(['success' => false, 'message' => 'Data is not matched, sorry'], 404);
    }
}

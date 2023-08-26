<?php

namespace App\Http\Controllers;

use App\Mail\ResetPassword;
use App\Mail\VerificationMail;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserAuthController extends Controller
{
    public function forgot_password(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
        ]);
        $user = User::where('email', $validatedData['email'])->first();
        if ($user) {
            $token = Str::random();
            $user->remember_token = $token;
            if ($user->save()) {
                try {
                    Mail::to($user->email)->send(new ResetPassword($user->name, $token));
                } catch (\Exception $e) {
                    return response([
                        'errors' => 'Mail sending failed: ' . $e->getMessage()
                    ], 500);
                }
            } else
                return response([
                    'errors' => 'Something went wrong.'
                ], 400);
        }

        return response([
            'message' => 'Check your mail for code.'
        ], 200);
    }

    public function reset_password(string $email, string $token, Request $request)
    {
        if (!$token || !$email)
            return redirect('http://localhost:3000');
        $user = User::where('email', $email)->first();
        if (!$user)
            return redirect('http://localhost:3000');
        if ($user->remember_token === $token) {
            $validatedData = $request->validate([
                'password' => 'required|string|confirmed',
            ]);
            $user->password = bcrypt($validatedData['password']);
            $user->remember_token = null;
            $user->save();
            if ($user->save())
                return response([
                    'message' => 'Password updated.'
                ], 200);
            else
                return response([
                    'errors' => 'Updated failed.'
                ], 500);
        }
        return response([
            'errors' => ['Something went wrong.']
        ], 403);
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed',
            'name' => 'required|string',
            'bio' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048'
        ]);

        $remember_token = Str::random();

        $user = User::create([
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'remember_token' => $remember_token
        ]);

        if ($user) {
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $encryptedImageName = rand(10, 1000000) . '-' . hash_file('md5', $image->path()) . '.' . $image->getClientOriginalExtension();
                $image->storeAs('images/users', $encryptedImageName, 'public');
            }
            $profile = Profile::create([
                'users_id' => $user->id,
                'name' => $validatedData['name'],
                'image' => $request->hasFile('image') ? $encryptedImageName : 'default.png',
            ]);

            if ($profile) {
                $verificationUrl = url("/verify/$user->id/$remember_token");
                try {
                    Mail::to($user->email)->send(new VerificationMail($profile->name, $verificationUrl));
                } catch (\Exception $e) {
                    return response([
                        'errors' => 'Mail sending failed: ' . $e->getMessage()
                    ], 500);
                }

                return response(true, 201);
            }
        }
        return response([
            'errors' => 'Creation failed'
        ], 500);
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Check email
        $user = User::where('email', $validatedData['email'])->first();
        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return response([
                'errors' => ['Email or password is wrong']
            ], 401);
        }

        $token = $user->createToken('myAuth')->plainTextToken;
        $response = [
            'token' => $token
        ];

        return response($response, 200);
    }

    public function getSelf()
    {
        $user = Auth::user();
        if (!$user)
            return response([
                'errors' => ['User not found.']
            ], 401);

        return response([
            'user' => $user
        ], 200);
    }

    public function logout(Request $request)
    {
        $tokenDeleted  = $request->user()->currentAccessToken()->delete();

        if ($tokenDeleted) {
            // Logout was successful
            $response = [
                'message' => 'Logged out'
            ];
            return response($response, 204);
        } else {
            // Logout failed
            $response = [
                'errors' => 'Logout failed'
            ];
            return response($response, 500);
        }
    }


    public function deleteProfilePic()
    {
        $user = User::find(Auth::id());
        $user->profile->image = 'default.png';

        if ($user->profile->save())
            return response([
                'user' => $user
            ], 200);
        else
            return response([
                'errors' => 'Deletion failed'
            ], 500);
    }

    public function updateProfilePic(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048'
        ]);
        $user = User::find(Auth::id());

        //return explode('/storage/images/users/', $user->profile->image)[1];

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $encryptedImageName = rand(10, 1000000) . '-' . hash_file('md5', $image->path()) . '.' . $image->getClientOriginalExtension();
            if (
                !isset(explode('/storage/images/users/', $user->profile->image)[1]) ||
                explode('-', $encryptedImageName)[1] !== explode('-', $user->profile->image)[1]
            ) {
                $user->profile->image = $encryptedImageName;
                $image->storeAs('images/users', $encryptedImageName, 'public');
            }
        }

        if ($user->profile->save())
            return response([
                'user' => $user
            ], 200);
        else
            return response([
                'errors' => 'Updated failed'
            ], 500);
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'sometimes|required|string|unique:users,username,' . Auth::id(),
            'email' => 'sometimes|required|email|unique:users,email,' . Auth::id(),
            'password' => 'sometimes|string|confirmed',
            'name' => 'sometimes|required|string',
            'bio' => 'nullable|string',
        ]);

        $user = User::find(Auth::id());
        $profile = $user->profile;

        if (isset($validatedData['username']))
            $user->username = $validatedData['username'];
        if (isset($fields['email']))
            $user->email = $validatedData['email'];
        if (isset($fields['password']))
            $user->password = bcrypt($validatedData['password']);
        if (isset($validatedData['name']))
            $profile->name = $validatedData['name'];
        $profile->bio = isset($validatedData['bio']) ? $validatedData['bio'] : null;

        if ($user->save() && $profile->save()) {
            $request->user()->currentAccessToken()->delete();
            $token = $user->createToken('myAuth')->plainTextToken;
            $response = [
                'token' => $token
            ];
            return response($response, 200);
        } else {
            return response([
                'errors' => 'Updated failed'
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        if (User::destroy(Auth::id())) {
            $request->user()->currentAccessToken()->delete();
            return response([
                'message' => 'Deleted successfully'
            ], 204);
        } else {
            return response([
                'errors' => 'Deletion failed'
            ], 500);
        }
    }
}

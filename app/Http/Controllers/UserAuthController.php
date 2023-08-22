<?php

namespace App\Http\Controllers;

use App\Mail\ResetPassword;
use App\Mail\VerificationMail;
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
        $user = User::where('email', '=', $validatedData['email'])->first();
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
            } else {
                return response([
                    'errors' => 'Something went wrong.'
                ], 400);
            }
        }

        return response([
            'message' => 'Check your mail for code.'
        ], 200);
    }

    public function reset_password(Request $request, string $email, string $token)
    {
        $validatedData = $request->validate([
            'password' => 'required|string|confirmed',
        ]);
        if (!$token || !$email)
            return redirect('http://localhost:3000');
        $user = User::where('email', '=', $email)->first();
        if (!$user)
            return redirect('http://localhost:3000');
        if ($user->remember_token === $token) {
            $user->password = bcrypt($validatedData['password']);
            $user->remember_token = null;
            $user->save();
            if ($user->save()) {
                return response('', 204);
            } else {
                return response([
                    'errors' => 'Updated failed.'
                ], 500);
            }
        }
        return response([
            'errors' => ['Something went wrong.']
        ], 403);
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        $remember_token = Str::random();

        $user = User::create([
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'remember_token' => $remember_token,
            'verified' => 0
        ]);

        if ($user) {
            $token = $user->createToken('myapptoken')->plainTextToken;
            $response = [
                'token' => $token
            ];

            $verificationUrl = url("/verify/$user->id/$remember_token");
            try {
                Mail::to($user->email)->send(new VerificationMail($verificationUrl));
            } catch (\Exception $e) {
                return response([
                    'errors' => 'Mail sending failed: ' . $e->getMessage()
                ], 500);
            }

            return response($response, 201);
        } else {
            return response([
                'errors' => 'Creation failed'
            ], 500);
        }
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

        $token = $user->createToken('myapptoken')->plainTextToken;
        $response = [
            'token' => $token
        ];

        return response($response, 200);
    }

    public function getSelf()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['errors' => 'User not found.'], 404);
        }

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

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'sometimes|required|email|unique:users,email,' . Auth::id(),
            'password' => 'sometimes|string|confirmed',
        ]);

        $user = User::find(Auth::id());

        if (isset($fields['email']))
            $user->email = $validatedData['email'];
        if (isset($fields['password']))
            $user->password = bcrypt($validatedData['password']);

        return $user->email;
        return $user;

        if ($user->save()) {
            $request->user()->currentAccessToken()->delete();
            $token = $user->createToken('myapptoken')->plainTextToken;
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

<?php

namespace App\Http\Controllers;

use App\Mail\IsVerified;
use App\Mail\VerificationMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailController extends Controller
{

    public function verifyUser($id, $token)
    {
        if (!$token)
            return redirect('http://localhost:3000?verified=false');
        $user = User::find($id);
        if (!$user)
            return redirect('http://localhost:3000?verified=false');

        // Perform email verification logic
        if ($user->email_verified_at === null) {
            if ($user->remember_token === $token) {
                $user->email_verified_at = now();
                $user->remember_token = null;
                $user->save();
                Mail::to($user->email)->send(new IsVerified());
                return redirect('http://localhost:3000?verified=true');
            }
        }
        return redirect('http://localhost:3000?verified=false');
    }

    public function resendVerification($id, $token)
    {
        if (!$token)
            return redirect('http://localhost:3000');
        $user = User::find($id);
        if (!$user)
            return redirect('http://localhost:3000');

        // Perform email verification logic
        if ($user->email_verified_at === null) {
            if ($user->remember_token === $token) {
                $remember_token = Str::random();

                $verificationUrl = url("/verify/$user->id/$remember_token");
                Mail::to($user->email)->send(new VerificationMail($user->profile->name, $verificationUrl));

                $user->remember_token = $remember_token;
                $user->save();
                return redirect('http://localhost:3000');
            }
        }
    }
}

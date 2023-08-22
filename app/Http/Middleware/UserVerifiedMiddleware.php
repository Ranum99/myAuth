<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserVerifiedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::find(Auth::id());
        // Check if the user is logged in and their user type is allowed
        if ($user->email_verified_at === null) {
            $response = [
                'message' => 'You must verify your email first. If you need us to resend email,',
                'link' => "http://localhost:8000/verify/resend/$user->id/$user->remember_token"
            ];
            return response($response, 403);
        } else {
            return $next($request);
        }
    }
}

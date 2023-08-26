<?php

namespace App\Http\Controllers;

use App\Models\Profile;

class ProfileController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(string $username)
    {
        $profile = Profile::whereHas('user', function ($query) use ($username) {
            $query->where('username', $username);
        })->withCount('reviews')
            ->first();
        if (!$profile)
            return response([
                'errors' => 'Profile not found.'
            ], 404);

        return response([
            'profile' => $profile
        ], 200);
    }
}

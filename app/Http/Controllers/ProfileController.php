<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $alreadyProfile = Profile::where('users_id', Auth::id())->first();
        if ($alreadyProfile)
            return response(['errors' => 'You have already made your profile. Try edit it instead.'], 404);

        $validatedData = $request->validate([
            'name' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'date_of_birth' => 'required|date|adult',
            'height' => 'nullable|integer',
            'work' => 'nullable|string',
            'school' => 'nullable|string',
            'bio' => 'nullable|string',
            'gender' => 'required|in:male,female',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $image = $request->file('image');
        $encryptedImageName = rand(10, 1000000) . '-' . hash_file('md5', $image->path()) . '.' . $image->getClientOriginalExtension();
        $imagePath = $image->storeAs('images/users', $encryptedImageName, 'public');
        $imageUrl = "http://127.0.0.1:8000/storage/$imagePath";

        $profile = new Profile();
        $profile->users_id = Auth::id();
        $profile->name = $validatedData['name'];
        $profile->image = $imageUrl;
        $profile->date_of_birth = $validatedData['date_of_birth'];
        $profile->gender = $validatedData['gender'];
        $profile->latitude = $validatedData['latitude'];
        $profile->longitude = $validatedData['longitude'];

        if (isset($validatedData['height']))
            $profile->height = $validatedData['height'];
        if (isset($validatedData['work']))
            $profile->work = $validatedData['work'];
        if (isset($validatedData['school']))
            $profile->school = $validatedData['school'];
        if (isset($validatedData['bio']))
            $profile->bio = $validatedData['bio'];

        if ($profile->save()) {
            return response([
                'profile' => $profile
            ], 201);
        } else {
            return response([
                'errors' => 'Creation failed'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function getSelf()
    {
        $profile = Profile::where('users_id', Auth::id())->first();
        if (!$profile)
            return response(['errors' => 'Profile not found.'], 404);

        return response([
            'profile' => $profile
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $profile = Profile::find($id);
        if (!$profile)
            return response(['errors' => 'Profile not found.'], 404);

        return response([
            'profile' => $profile
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'date_of_birth' => 'required|date|adult',
            'height' => 'nullable|integer',
            'work' => 'nullable|string',
            'school' => 'nullable|string',
            'bio' => 'nullable|string',
            'gender' => 'required|in:male,female',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);
        $userProfile = Profile::where('users_id', Auth::id())->first();
        if (!$userProfile)
            return response(['errors' => 'Profile not found.'], 404);

        if (isset($validatedData['name']))
            $userProfile->name = $validatedData['name'];
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $encryptedImageName = rand(10, 1000000) . '-' . hash_file('md5', $image->path()) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('images/users', $encryptedImageName, 'public');
            $userProfile->image = "http://127.0.0.1:8000/storage/$imagePath";
        }

        if (isset($validatedData['date_of_birth']))
            $userProfile->date_of_birth = $validatedData['date_of_birth'];
        if (isset($validatedData['height']))
            $userProfile->height = $validatedData['height'];
        if (isset($validatedData['work']))
            $userProfile->work = $validatedData['work'];
        if (isset($validatedData['school']))
            $userProfile->school = $validatedData['school'];
        if (isset($validatedData['bio']))
            $userProfile->bio = $validatedData['bio'];
        if (isset($validatedData['gender']))
            $userProfile->gender = $validatedData['gender'];

        if ($userProfile->save()) {
            return response([
                'message' => 'Profile updated'
            ], 200);
        } else {
            return response([
                'errors' => 'Updated failed'
            ], 500);
        }
    }
}

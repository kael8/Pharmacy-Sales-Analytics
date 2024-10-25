<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ManageStaffController extends Controller
{
    public function addStaff(Request $request, $id = null)
    {
        $user = null;
        $profile_image = null;

        if ($id !== null) {
            $user = User::find($id);
            $profile_image = Image::where('user_id', $id)->first();
        }

        return view('manage-staff.addStaff', compact('user', 'profile_image'));
    }

    public function createStaff(Request $request)
    {
        $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|string|email|max:255|unique:users,email', // Ensure email is unique in the users table
            'password' => 'required|string|min:8|confirmed', // Confirms with a field named `password_confirmation`
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Optional image upload validation
        ]);

        DB::beginTransaction();

        try {
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                // Upload image to Cloudinary
                $uploadedFileUrl = Cloudinary::upload($request->file('profile_image')->getRealPath(), [
                    'folder' => 'staff'
                ])->getSecurePath();
                $profileImagePath = $uploadedFileUrl;
            }

            $user = User::create([
                'fname' => $request->fname,
                'lname' => $request->lname,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'profile_image' => $profileImagePath,
                'role' => 'Staff'
            ]);

            Image::create([
                'name' => $profileImagePath,
                'user_id' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Staff account created successfully',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create staff account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function viewStaff(Request $request)
    {
        $staffs = User::where('role', 'Staff')->get();
        return view('manage-staff.viewStaff', compact('staffs'));
    }

    public function updateStaff(Request $request, $id)
    {
        // Find the user by ID or fail if not found
        $user = User::findOrFail($id);
        $image = Image::where('user_id', $id)->first();

        // Validate the incoming request data
        $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id, // Unique email validation except for the current user
            'password' => 'nullable|string|min:8|confirmed', // Password is optional on update
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Optional image upload validation
        ]);

        // Handle profile image upload if a new one is provided
        if ($request->hasFile('profile_image')) {
            // Upload image to Cloudinary
            $uploadedFileUrl = Cloudinary::upload($request->file('profile_image')->getRealPath(), [
                'folder' => 'staff'
            ])->getSecurePath();

            // Check if the user already has an associated image
            if ($image) {
                // Extract the public ID from the URL to delete the old image
                $publicId = pathinfo(parse_url($image->name, PHP_URL_PATH), PATHINFO_FILENAME);
                Cloudinary::destroy('staff/' . $publicId);

                // Update the existing image record with the new URL
                $image->update(['name' => $uploadedFileUrl]);
            } else {
                // Create a new image record
                $user->image()->create(['name' => $uploadedFileUrl]);
            }
        }

        // Update user data
        $user->fname = $request->input('fname');
        $user->lname = $request->input('lname');
        $user->phone = $request->input('phone');
        $user->email = $request->input('email');

        // Update password only if a new one is provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        // Save the updated user data to the database
        $user->save();

        // Return a JSON response with a success message and the updated user data
        return response()->json([
            'message' => 'Staff account updated successfully',
            'user' => $user,
        ]);
    }
    public function recordSale(Request $request)
    {
        return view('manage-staff.recordSale');
    }

}

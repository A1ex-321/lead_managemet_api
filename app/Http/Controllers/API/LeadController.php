<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Hash;

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
class LeadController extends Controller
{
    public function lead_create(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                // 'name' => 'required|string|max:255',
                'phone' => 'required|unique:lead,phone',
                // 'password' => 'required|string|min:8',
            ]);
            // $formData = $request->all();
            // lead::create($formData);
            // Create a new user
            $user = Lead::create([
                'comment' => $request->input('comment'),
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'platform' => $request->input('platform'),
                'address' => $request->input('address'),
                'websiteDetails' => $request->input('websiteDetails'),
                'projectDetails' => $request->input('projectDetails'),
                'interestedServices' => $request->input('interestedServices'),
                'servicesTaken' => $request->input('servicesTaken'),
                'group' => $request->input('group'),
                'tags' => $request->input('tags'),
                'category' => $request->input('category')

            ]);
            return response()->json(['message' => 'lead created successfully','lead'=>$user], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed',$e->getMessage()], 500);
        }
    }
}

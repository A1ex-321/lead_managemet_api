<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Hash;

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

 


class LeadController extends Controller
{
    public function lead_create(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                // 'name' => 'required|string|max:255',
                'phone' => 'required|max:10|min:10|unique:lead,phone',
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
            return response()->json(['message' => 'lead created successfully',], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed', $e->getMessage()], 500);
        }
    }
    public function all_lead()
    {
        try {
            $users = Lead::paginate(5);
            return response()->json(['leads' => $users]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed', $e->getMessage()], 500);
        }
    }
    public function single_lead($id)
    {
        try {
        $cartItem = Lead::find($id);
            return response()->json(['leads' => $cartItem]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed', $e->getMessage()], 500);
        }
    }
    public function age(Request $request)
    {
        try {
            $users = Lead::all();
            foreach ($users as $item) {
                $daysDifference = Carbon::now()->diffInDays($item->created_at)+1;
                $userDetails[] = [
                    'id' =>$item->id,
                    'name' => $item->name,
                    'age' => $daysDifference,
                    'phone'=>$item->phone,
                    'category'=>$item->category
                ];
              }
            $perPage = 5; 
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $pagedData = array_slice($userDetails, ($currentPage - 1) * $perPage, $perPage);
            $usersPaginated = new LengthAwarePaginator($pagedData, count($userDetails), $perPage);
            $usersPaginated->setPath(request()->url());
            // Log::info('date aaaaaAPI Request: ' . json_encode($userDetails));
            // Log::info('date currentAPI Request: ' . json_encode($date));
            return response()->json(['leads' => $usersPaginated]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'failed', $e->getMessage()], 500);
        }
    }
    public function lead_update(Request $request,$id)
    {
        try {
            // Validate the request data
            $cartItem = Lead::findOrFail($id);
            $request->validate([
                // 'name' => 'required|string|max:255',
                'phone' => 'required|max:10|min:10|unique:lead,phone,'. $cartItem->id,
                // 'password' => 'required|string|min:8',
            ]);
            
            // update a exist user
            $cartItem->comment = $request->input('comment');
            $cartItem->name = $request->input('name');
            $cartItem->phone = $request->input('phone');
            $cartItem->email = $request->input('email');
            $cartItem->platform = $request->input('platform');
            $cartItem->address = $request->input('address');
            $cartItem->websiteDetails = $request->input('websiteDetails');
            $cartItem->projectDetails = $request->input('projectDetails');
            $cartItem->interestedServices = $request->input('interestedServices');
            $cartItem->servicesTaken = $request->input('servicesTaken');
            $cartItem->group = $request->input('group');
            $cartItem->tags = $request->input('tags');
            $cartItem->category = $request->input('category');
            $cartItem->save();
            return response()->json(['message' => 'lead update successfully',], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed', $e->getMessage()], 500);
        }
    }
    public function lead_delete($id)
    {
        $cartItem = lead::find($id);

        if (!$cartItem) {
            return response()->json(['error' => 'Item not found'], 404);
        }
        $cartItem->delete();
        // $updatedCart = $this->getUpdatedCartData();
        return response()->json(['msg' => 'Item deleted successfully'], 404);
    }
}

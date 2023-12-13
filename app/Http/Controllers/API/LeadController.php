<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Hash;

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Comments;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;


class LeadController extends Controller
{
    public function lead_create(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|max:10|min:10|unique:lead,phone',
            ]);

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
            return response()->json(['error' => ' failed', $e->getMessage()], 500);
        }
    }
    public function all_lead()
    {
        try {
            $this->shedule_date();
            $users = Lead::orderBy('created_at', 'desc')->get();
            $userscount = Lead::where('is_shedule', '0')->get()->count();
            $users = Lead::where('is_shedule', '0')->orderBy('created_at', 'desc')->get();

            return response()->json(['leads' => $users, 'userscount' => $userscount]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'failed', $e->getMessage()], 500);
        }
    }
    public function single_lead($id)
    {
        try {
            $lead = Lead::where("id", $id)->with(['comments' => function ($query) {
                $query->select('*');
            }])
                ->select('*')
                ->first();

            return response()->json(['leads' => $lead]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed', $e->getMessage()], 500);
        }
    }
    public function age(Request $request)
    {
        try {
            $users = Lead::all();
            foreach ($users as $item) {
                $daysDifference = Carbon::now()->diffInDays($item->created_at) + 1;
                $userDetails[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'age' => $daysDifference,
                    'phone' => $item->phone,
                    'category' => $item->category
                ];
            }
            usort($userDetails, function ($a, $b) {
                return $a['age'] - $b['age'];
            });
            $perPage = count($userDetails);
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $pagedData = array_slice($userDetails, ($currentPage - 1) * $perPage, $perPage);
            $usersPaginated = new LengthAwarePaginator($pagedData, count($userDetails), $perPage);
            $usersPaginated->setPath(request()->url());
            return response()->json(['leads' => $usersPaginated]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'failed', $e->getMessage()], 500);
        }
    }
    public function lead_update(Request $request, $id)
    {
        try {
            // Validate the request data
            $cartItem = Lead::findOrFail($id);
            $request->validate([
                'phone' => 'required|max:10|min:10|unique:lead,phone,' . $cartItem->id,
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
            return response()->json(['error' => 'update failed', $e->getMessage()], 500);
        }
    }
    public function lead_delete($id)
    {
        $cartItem = lead::find($id);

        if (!$cartItem) {
            return response()->json(['error' => 'lead not found'], 404);
        }
        $cartItem->delete();
        return response()->json(['msg' => 'lead deleted successfully'], 404);
    }
    public function search(Request $request)
    {
        try {
            $query = lead::query();

            // Filter by username
            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            }

            // Filter by phone number
            if ($request->has('phone')) {
                $query->where('phone', 'like', '%' . $request->input('phone') . '%');
            }

            $users = $query->get();
            return response()->json(['users' => $users]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed', $e->getMessage()], 500);
        }
    }

    public function message_create(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'lead_id' => 'required',
            ]);
            $user = comments::create([
                'comment' => $request->input('comment'),
                'lead_id' => $request->input('lead_id'),
                'postedOn' => $request->input('postedOn'),
            ]);
            // Log::info('date currentAPI Request: ' . json_encode($user));
            $lead = lead::where('id', $user->lead_id)->first();
            $comments = comments::where('lead_id', $user->lead_id)->orderBy('created_at', 'asc')->get();
            foreach ($comments as $item) {
                $comment[] = [
                    'id' => $lead->id,
                    'userName' => $lead->name,
                    'postedOn' => $item->postedOn,
                    'comment' => $item->comment,
                    'comment_id' => $item->id,
                    'userPic' => 'https://img.freepik.com/free-psd/3d-illustration-human-avatar-profile_23-2150671132.jpg?w=740&t=st=1702363051~exp=1702363651~hmac=c72204cd50f9532760a676be0f9407cd87bb69c00645202763974ea861f1e88d'
                ];
            }
            return response()->json(['message' => 'message created successfully', 'comments' => $comment], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => ' failed', $e->getMessage()], 500);
        }
    }
    public function schedule_date_send(Request $request, $id)
    {
        try {
            $request->validate([
                'is_shedule' => 'required',
                'date_shedule' => 'required',

            ]);

            $lead = lead::where('id', $id)->first();
            $lead->is_shedule = $request->input('is_shedule');
            $lead->date_shedule = $request->input('date_shedule');

            $lead->save();
            return response()->json(['message' => 'update successfully', 'comments' => $lead], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => ' failed', $e->getMessage()], 500);
        }
    }

    public function shedule_date()
    {
        try {
            $update_shedule = Lead::where('is_shedule', '1')->get();
            foreach ($update_shedule as $item) {
                $shedule = Lead::where('id', $item->id)->where('is_shedule', '1')->get();
                foreach ($shedule as $item) {
                    $parsedDate = Carbon::createFromFormat('d/M/Y g:iA', $item->date_shedule);
                    $daysDifference = Carbon::now()->timezone('Asia/Kolkata');

                    $dateOnly = $daysDifference->toDateString();
                    $hoursOnly = $daysDifference->format('h:i A');
                    $hoursOnlyAM = $daysDifference->format('A');
                    $minutesOnly = $daysDifference->minute;

                    $dateOnlydb = $parsedDate->toDateString();
                    $hoursOnlydb = $parsedDate->format('h:i A');
                    $hoursOnlydbAM = $parsedDate->format('A');
                    $minutesOnlydb = $parsedDate->minute;
                    if (($dateOnly == $dateOnlydb) && ($hoursOnly == $hoursOnlydb)) {
                        if ($minutesOnly > $minutesOnlydb) {
                            $new_update = Lead::where('id', $item->id)->where('is_shedule', '1')->first();
                            if ($new_update) {
                                $new_update->is_shedule = 0;
                                $new_update->save();
                            }
                            return response()->json(['message' => 'minu successfully', 'comments' => ''], 201);
                        }
                    }
                    // To hours AM
                    if (($hoursOnlydbAM == 'AM') && ($hoursOnlyAM == 'AM')) {
                        if ($dateOnly == $dateOnlydb) {
                            if (($hoursOnly > $hoursOnlydb) && ($minutesOnly > $minutesOnlydb))  {
                                $new_update = Lead::where('id', $item->id)->where('is_shedule', '1')->first();
                                if ($new_update) {
                                    $new_update->is_shedule = 0;
                                    $new_update->save();
                                }                              
                            }
                        }
                    }
                    // To hours PM
                    if (($hoursOnlydbAM == 'PM') && ($hoursOnlyAM == 'PM')) {
                        if ($dateOnly == $dateOnlydb) {
                            if (($hoursOnly > $hoursOnlydb) && ($minutesOnly >  $minutesOnlydb)) {
                                $new_update = Lead::where('id', $item->id)->where('is_shedule', '1')->first();
                                if ($new_update) {
                                    $new_update->is_shedule = 0;
                                    $new_update->save();
                                }
                            }
                        }
                    }
                    //To day
                    if ($dateOnly > $dateOnlydb) {
                        $new_update = Lead::where('id', $item->id)->where('is_shedule', '1')->first();
                        if ($new_update) {
                            $new_update->is_shedule = 0;
                            $new_update->save();
                        }
                    }
                }
            }
            return response()->json(['leads' => '']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'shedule_date failed', $e->getMessage()], 500);
        }
    }
    public function message_get(Request $request, $id)
    {
        try {

            $lead = lead::where('id', $id)->first();
            $comments = comments::where('lead_id', $lead->id)->orderBy('created_at', 'desc')->get();

            foreach ($comments as $item) {
                $comment[] = [
                    'id' => $lead->id,
                    'userPic' => 'https://img.freepik.com/free-psd/3d-illustration-human-avatar-profile_23-2150671132.jpg?w=740&t=st=1702363051~exp=1702363651~hmac=c72204cd50f9532760a676be0f9407cd87bb69c00645202763974ea861f1e88d',
                    'userName' => $lead->name,
                    'comment' => $item->comment,
                    'postedOn' => $item->postedOn,
                    'comment_id' => $item->id,

                ];
            }

            return response()->json(['comment' => $comment]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'failed', $e->getMessage()], 500);
        }
    }
}

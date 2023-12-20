<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\stdClass;

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
    public function all_lead(Request $request)
    {
        try {
            $this->shedule_date();
            Log::info('Request Data: ' . json_encode($request->all()));
            if ($request->has('category') && !$request->filled('date') && empty($request->input('tags'))) {
                try {
                    $request->validate([]);
                } catch (ValidationException $e) {
                    return response()->json(['error' => $e->validator->errors()->first()], 422);
                }

                $query = lead::query();
                $searchTerm = $request->input('category');
                $query->where('is_shedule', 0);
                $query->whereIn('category', $searchTerm);
                $users = $query->get();
                $leadCount = $users->count();
                return response()->json(['leads' => $users, 'lead_count' => $leadCount]);
            } else if ($request->has('tags') && empty($request->input('category')) && empty($request->input('date'))) {
                $tags = $request->input('tags');
                // Log::info('date currentAPI Request: ' . json_encode($tags));
                $query = lead::query();
                $query->where('is_shedule', 0);
                foreach ($query->get() as $item) {
                    $tagsArrays = json_decode($item->tags, true);
                    $tagsid = $item->id;
                    $tagsData[$tagsid] = $tagsArrays;
                }
                $id = [];
                foreach ($tagsData as $tagsid => $tagsArrays) {
                    Log::info('date currentAPI Request: ' . json_encode($tagsArrays));

                    if (count(array_intersect($tags, $tagsArrays)) > 0) {
                        $id[] = $tagsid;
                    }
                }
                $leadCount = count($id);
                $leads = lead::whereIn('id', $id)->get();
                return response()->json(['leads' => $leads, 'lead_count' => $leadCount]);
            } else if ($request->has('date') &&  empty($request->input('category') && empty($request->input('tags')))) {

                try {
                    $request->validate([]);
                } catch (ValidationException $e) {
                    return response()->json(['error' => $e->validator->errors()->first()], 422);
                }

                $query = lead::query();
                $date = $request->input('date');
                // ($query->created_at)
                $formattedDate = Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
                Log::info('date currentAPI Request: ' . json_encode($formattedDate));
                $query->where('is_shedule', 0);

                $query->whereDate('updated_at', '=', $formattedDate);
                $users = $query->get();
                $leadCount = $users->count();
                return response()->json(['leads' => $users, 'lead_count' => $leadCount]);
            } else if ($request->has('date') && $request->has('category') && empty($request->input('tags'))) {
                try {
                    $request->validate([]);
                } catch (ValidationException $e) {
                    return response()->json(['error' => $e->validator->errors()->first()], 422);
                }

                $query = lead::query();
                $date = $request->input('date');
                $formattedDate = Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
                $category = $request->input('category');
                $query->whereDate('updated_at', '=', $formattedDate);
                $query->where('is_shedule', 0);
                $query->whereIn('category', $category);
                $users = $query->get();
                $leadCount = $users->count();
                return response()->json(['leads' => $users, 'lead_count' => $leadCount]);
            } else {
                //  $users = Lead::orderBy('created_at', 'desc')->get();
                // $userscount = Lead::where('is_shedule', '0')->get()->count();
                $users = Lead::where('is_shedule', '0')->orderBy('created_at', 'desc')->whereNotIn('category', ['Unwanted', 'For Job', 'Not Sale'])->get();
                $leadCount = $users->count();
                return response()->json(['leads' => $users, 'lead_count' => $leadCount]);
            }
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
                ->select('*')->where('is_shedule', '0')
                ->first();
            if ($lead == null) {
                $lead = (object) [];
            }
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
            $leads = Lead::findOrFail($id);
            $request->validate([
                'phone' => 'required|max:10|min:10|unique:lead,phone,' . $leads->id,
            ]);

            // update a exist user
            $leads->comment = $request->input('comment');
            $leads->name = $request->input('name');
            $leads->phone = $request->input('phone');
            $leads->email = $request->input('email');
            $leads->platform = $request->input('platform');
            $leads->address = $request->input('address');
            $leads->websiteDetails = $request->input('websiteDetails');
            $leads->projectDetails = $request->input('projectDetails');
            $leads->interestedServices = $request->input('interestedServices');
            $leads->servicesTaken = $request->input('servicesTaken');
            $leads->group = $request->input('group');
            $leads->tags = $request->input('tags');
            $leads->category = $request->input('category');
            $leads->save();
            return response()->json(['message' => 'lead update successfully',], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'update failed', $e->getMessage()], 500);
        }
    }
    public function lead_delete($id)
    {
        $lead = lead::find($id);

        if (!$lead) {
            return response()->json(['message' => 'lead not found'], 404);
        }
        $lead->delete();
        return response()->json(['message' => 'lead deleted successfully'], 404);
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
                'date_shedule' => 'required|date_format:d/M/Y g:iA',
            ]);

            $date = $request->input('date_shedule');

            $timeZone = new \DateTimeZone('Asia/Kolkata');

            $dateTime = \DateTime::createFromFormat('d/M/Y g:iA', $date, $timeZone);

            $currentDateTime = new \DateTime('now', $timeZone);

            if ($dateTime && $dateTime < $currentDateTime) {
                return response()->json(['error' => 'Please enter a future date and time.'], 422);
            }

            $lead = Lead::where('id', $id)->first();
            $lead->is_shedule = $request->input('is_shedule');
            $lead->date_shedule  = $date;
            $lead->save();

            return response()->json(['message' => 'Lead scheduled', 'data' => $lead], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Update failed', 'message' => $e->getMessage()], 500);
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
                    $hoursOnly = $daysDifference->format('h');
                    $hoursOnlyAM = $daysDifference->format('A');
                    $minutesOnly = $daysDifference->minute;

                    $dateOnlydb = $parsedDate->toDateString();
                    $hoursOnlydb = $parsedDate->format('h');
                    $hoursOnlydbAM = $parsedDate->format('A');
                    $minutesOnlydb = $parsedDate->minute;
                    // To minute AM
                    if (($hoursOnlydbAM == 'AM') && ($hoursOnlyAM == 'AM')) {
                        if (($dateOnly == $dateOnlydb) && ($hoursOnly == $hoursOnlydb)) {
                            if ($minutesOnly >= $minutesOnlydb) {
                                $new_update = Lead::where('id', $item->id)->where('is_shedule', '1')->first();
                                if ($new_update) {
                                    $new_update->is_shedule = 0;
                                    $new_update->save();
                                }
                            }
                        }
                    }
                    // To minute PM
                    if (($hoursOnlydbAM == 'PM') && ($hoursOnlyAM == 'PM')) {
                        if (($dateOnly == $dateOnlydb) && ($hoursOnly == $hoursOnlydb)) {
                            if ($minutesOnly >= $minutesOnlydb) {
                                $new_update = Lead::where('id', $item->id)->where('is_shedule', '1')->first();
                                if ($new_update) {
                                    $new_update->is_shedule = 0;
                                    $new_update->save();
                                }
                            }
                        }
                    }
                    // To hours AM
                    if (($hoursOnlydbAM == 'AM') && ($hoursOnlyAM == 'AM')) {
                        if ($dateOnly == $dateOnlydb) {
                            if (($hoursOnly >= $hoursOnlydb) && ($minutesOnly >= $minutesOnlydb)) {
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
                            if (($hoursOnly >= $hoursOnlydb) && ($minutesOnly >=  $minutesOnlydb)) {
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
            return response()->json(['leads' => $update_shedule]);
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
    public function scheduled_all_lead(Request $request)
    {
        try {
            $this->shedule_date();
            if ($request->has('category') && !$request->filled('date')) {
                try {
                    $request->validate([]);
                } catch (ValidationException $e) {
                    return response()->json(['error' => $e->validator->errors()->first()], 422);
                }

                $query = lead::query();
                $searchTerm = $request->input('category');
                $query->where('is_shedule', 1);
                $query->whereIn('category', $searchTerm);
                $users = $query->get();
                $leadCount = $users->count();
                return response()->json(['sheduleduser' => $users, 'sheduleddatecount' => $leadCount]);
            } else if ($request->has('date') &&  empty($request->input('category'))) {

                try {
                    $request->validate([]);
                } catch (ValidationException $e) {
                    return response()->json(['error' => $e->validator->errors()->first()], 422);
                }

                $query = lead::query();
                $date = $request->input('date');
                // ($query->created_at)
                $formattedDate = Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
                Log::info('date currentAPI Request: ' . json_encode($formattedDate));
                $query->where('is_shedule', 1);

                $query->whereDate('updated_at', '=', $formattedDate);
                $users = $query->get();
                $leadCount = $users->count();
                return response()->json(['sheduleduser' => $users, 'sheduleddatecount' => $leadCount]);
            } else if ($request->has('date') && $request->has('category')) {
                try {
                    $request->validate([]);
                } catch (ValidationException $e) {
                    return response()->json(['error' => $e->validator->errors()->first()], 422);
                }

                $query = lead::query();
                $date = $request->input('date');
                $formattedDate = Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
                $category = $request->input('category');
                $query->whereDate('updated_at', '=', $formattedDate);
                $query->where('is_shedule', 1);
                $query->whereIn('category', $category);
                $users = $query->get();
                $leadCount = $users->count();
                return response()->json(['sheduleduser' => $users, 'sheduleddatecount' => $leadCount]);
            } else {
                // $userscount = Lead::where('is_shedule', '1')->get()->count();
                $users = Lead::where('is_shedule', '1')->orderBy('created_at', 'desc')->whereNotIn('category', ['Unwanted', 'For Job', 'Not Sale'])->get();
                $userscount = $users->count();

                return response()->json(['sheduleduser' => $users, 'sheduleddatecount' => $userscount]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'failed', $e->getMessage()], 500);
        }
    }
    // public function scheduled_all_lead1()
    // {
    //     try {
    //         $userscount = Lead::where('is_shedule', '1')->get()->count();
    //         $users = Lead::where('is_shedule', '1')->orderBy('created_at', 'desc')->get();
    //         // $scheduleItemsArray = [];

    //         // $usersWithSchedule = $users->each(function ($item) {
    //         //     $shedule = Lead::where('id', $item->id)->where('is_shedule', '1')->get();
    //         // // Log::info('date currentAPI Request: ' . json_encode($shedule));

    //         //     // $scheduleItemsArray = [];
    //         //     foreach ($shedule as $scheduleItem) {
    //         //         $dynamicObject = new \stdClass();

    //         //         $parsedDate = Carbon::createFromFormat('d/M/Y g:iA', $scheduleItem->date_shedule);

    //         //         $daysDifference = Carbon::now()->timezone('Asia/Kolkata');

    //         //         $dateOnly = $daysDifference->toDateString();
    //         //         $hoursOnly = $daysDifference->format('h');
    //         //         $hoursOnlyAM = $daysDifference->format('A');
    //         //         $minutesOnly = $daysDifference->minute;
    //         //         $dateOnlydb = $parsedDate->toDateString();
    //         //         $hoursOnlydb = $parsedDate->format('h');
    //         //         $hoursOnlydbAM = $parsedDate->format('A');
    //         //         $minutesOnlydb = $parsedDate->minute;
    //         //         // To minutePM
    //         //         if (($hoursOnlydbAM == 'PM') && ($hoursOnlyAM == 'PM')) {
    //         //             if (($dateOnly == $dateOnlydb) && ($hoursOnly == $hoursOnlydb)) {
    //         //                 if ($minutesOnly <= $minutesOnlydb) {
    //         //                     $totalMinutesDifference = $minutesOnlydb - $minutesOnly;
    //         //                     $dynamicObject->total = $totalMinutesDifference .' '.  'Minute';
    //         //                 }
    //         //             }
    //         //         }
    //         //         //to minute AM
    //         //         if (($hoursOnlydbAM == 'AM') && ($hoursOnlyAM == 'AM')) {
    //         //             if (($dateOnly == $dateOnlydb) && ($hoursOnly == $hoursOnlydb)) {
    //         //                 if ($minutesOnly <= $minutesOnlydb) {
    //         //                     $totalMinutesDifference = $minutesOnlydb - $minutesOnly;
    //         //                     $dynamicObject->total = $totalMinutesDifference.'minute';
    //         //                 }
    //         //             }
    //         //         }
    //         //         // To hours AM
    //         //         if (($hoursOnlydbAM == 'AM') && ($hoursOnlyAM == 'AM')) {
    //         //             if ($dateOnly == $dateOnlydb) {
    //         //                 if (($hoursOnly < $hoursOnlydb) && ($minutesOnly <= $minutesOnlydb)) {
    //         //                     $totalhours = $hoursOnlydb - $hoursOnly;
    //         //                     $totalminutes = $minutesOnlydb - $minutesOnly;
    //         //                     $dynamicObject->total = $totalhours . ' ' .'Hour' . ':' . $totalminutes . 'Minute';
    //         //                 }
    //         //             }
    //         //         }
    //         //         // To hours PM
    //         //         if (($hoursOnlydbAM == 'PM') && ($hoursOnlyAM == 'PM')) {
    //         //             if ($dateOnly == $dateOnlydb) {
    //         //                 if (($hoursOnly < $hoursOnlydb) && ($minutesOnly <= $minutesOnlydb)) {
    //         //                     $totalhours = $hoursOnlydb - $hoursOnly;
    //         //                     $totalminutes = $minutesOnlydb - $minutesOnly;
    //         //                     $dynamicObject->total = $totalhours . 'Hour' . ':' . $totalminutes . 'Minute';
    //         //                 }
    //         //             }
    //         //         }
    //         //         if (($dateOnly < $dateOnlydb)) {

    //         //             $totald = $parsedDate->diffInDays($dateOnly);
    //         //             $dynamicObject->total = $totald . 'Day';
    //         //         }
    //         //     }
    //         //     $item->total = $dynamicObject;
    //         //     return $item;
    //         // });

    //         return response()->json(['sheduleduser' => $users, 'sheduleddatecount' => $userscount]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'failed', $e->getMessage()], 500);
    //     }
    // }

    public function lead_category(Request $request, $id)
    {
        try {
            $request->validate([
                'category' => 'required',
            ]);
            $category = Lead::where('id', $id)->first();
            $category->category = $request->input('category');
            $category->updated_at = now();
            $category->save();
            return response()->json(['message' => 'Lead category added', 'categoy' => $category], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Update failed', 'message' => $e->getMessage()], 500);
        }
    }
    public function sheduled_single_lead($id)
    {
        try {
            $lead = Lead::where("id", $id)->with(['comments' => function ($query) {
                $query->select('*');
            }])
                ->select('*')->where('is_shedule', '1')
                ->first();
            if ($lead == null) {
                $lead = (object) [];
            }
            return response()->json(['leads' => $lead]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed', $e->getMessage()], 500);
        }
    }
    public function tags_create(Request $request, $id)
    {
        try {
            $request->validate([
                'tags' => 'required|array',
            ]);

            $lead = Lead::findOrFail($id);
            $tags = $request->input('tags');
            // $tagsString = implode(',', $request->input('tags'));            
            // $lead->tags = $tagsString;
            $lead->tags_update = now();
            $lead->tags = json_encode($tags);
            $lead->save();
            return response()->json(['message' => 'tag created successfully',], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => ' failed', $e->getMessage()], 500);
        }
    }
}

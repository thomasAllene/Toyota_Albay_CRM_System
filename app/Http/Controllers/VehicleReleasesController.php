<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\Transactions;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Vehicle;
use App\Models\Inventory;
use App\Models\Application;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Auth;

class VehicleReleasesController extends Controller
{
    public function index() {
        if(Auth::check()){

            return view('vehicle_releases.vehicle_releases');
        }else{
            return view('index');
        }

    }

    public function releasedUnitsList(Request $request){
        DB::statement("SET SQL_MODE=''");

        if(Auth::user()->usertype->name === 'SuperAdmin'
        || Auth::user()->usertype->name === 'Sales Admin Staff'
        || Auth::user()->usertype->name === 'General Manager'
        || Auth::user()->usertype->name === 'Financing Staff'
        ){
            $query = Vehicle::with('inventory')
                            ->whereNull('deleted_at')
                            // ->whereHas('inventory', function($subQuery) {
                            //     $subQuery->where('status', 'like', 'Released')
                            //              ->where('CS_number_status', 'like', 'Released');
                            // })
                            ->groupBy('unit');

        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Vehicle::with('inventory')
                            ->whereNull('deleted_at')
                            ->whereHas('inventory', function($subQuery) {
                                $subQuery->where('status', 'like', 'Released')
                                         ->where('CS_number_status', 'like', 'Released');
                            })
                            ->whereHas('inventory', function($subQuery) {
                                $subQuery->whereHas('transaction', function($subQuery) {
                                    $subQuery->where('team_id', Auth::user()->team_id);
                                });
                            })
                            ->groupBy('unit');
        }else{
            $query = Vehicle::with('inventory')
                            ->whereNull('deleted_at')
                            ->whereHas('inventory', function($subQuery) {
                                $subQuery->where('status', 'like', 'Released')
                                         ->where('CS_number_status', 'like', 'Released');
                            })
                            ->whereHas('inventory', function($subQuery) {
                                $subQuery->whereHas('transaction', function($subQuery) {
                                    $subQuery->whereHas('application', function($subQuery) {
                                        $subQuery->where('created_by', Auth::user()->id);
                                    });
                                });
                            })
                            ->groupBy('unit');
        }

        $list = $query->get();

        return DataTables::of($list)
        ->addColumn('id', function($data) {
            return encrypt($data->id);
        })
        ->addColumn('unit', function($data) {
            return $data->unit;
        })
        ->addColumn('quantity', function($data) use($request) {

            if(Auth::user()->usertype->name === 'SuperAdmin' || Auth::user()->usertype->name === 'General Manager'){
                $count = Inventory::with('vehicle', 'team', 'user')
                ->whereHas('vehicle', function($subQuery) use($data) {
                    $subQuery->where('unit', $data->unit);
                })
                ->where('status', 'Released')
                ->where('CS_number_status', 'Released');

            }elseif(Auth::user()->usertype->name === 'Group Manager'){
                $count = Inventory::with('vehicle', 'team', 'user', 'transaction')
                ->whereHas('vehicle', function($subQuery) use($data) {
                    $subQuery->where('unit', $data->unit);
                })
                ->where('status', 'Released')
                ->where('CS_number_status', 'Released')
                ->whereHas('transaction', function($subQuery) {
                    $subQuery->where('team_id', Auth::user()->team_id);
                });
            }else{
                $count = Inventory::with('vehicle', 'team', 'user', 'transaction')
                ->whereHas('vehicle', function($subQuery) use($data) {
                    $subQuery->where('unit', $data->unit);
                })
                ->where('status', 'Released')
                ->where('CS_number_status', 'Released')
                ->whereHas('transaction', function($subQuery) {
                    $subQuery->whereHas('application', function($subQuery) {
                        $subQuery->where('created_by', Auth::user()->id);
                    });
                });
            }


            if ($request->has('date_range') && !empty($request->date_range)) {
                [$startDate, $endDate] = explode(' to ', $request->date_range);
                $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
                $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

                $count->whereBetween('updated_at', [$startDate, $endDate]);
            } else {
                $count->whereMonth('updated_at', now()->month)  // Filter by current month
                      ->whereYear('updated_at', now()->year);   // Filter by current year
            };
            $unitCount = $count->count();

            return $unitCount;
        })
        ->make(true);
    }

    public function releasedPerTeam(Request $request){
        DB::statement("SET SQL_MODE=''");

        if(Auth::user()->usertype->name === 'SuperAdmin'
        || Auth::user()->usertype->name === 'Sales Admin Staff'
        || Auth::user()->usertype->name === 'General Manager'
        || Auth::user()->usertype->name === 'Financing Staff'
        ){
            $query = Team::whereNull('deleted_at');
        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Team::whereNull('deleted_at')
                        ->where('id', Auth::user()->team_id);
        }else{
            $query = Team::whereNull('deleted_at')
                        ->where('id', Auth::user()->team_id);
        }


        $list = $query->get();

        return DataTables::of($list)
        ->addColumn('id', function($data) {
            return encrypt($data->id);
        })
        ->addColumn('team', function($data) {
            return $data->name;
        })

        ->addColumn('quantity', function($data) use($request) {
            if(Auth::user()->usertype->name === 'Agent' ){

                $released_status = Status::where('status', 'like', 'Released')->first();
                $posted_status = Status::where('status', 'like', 'Posted')->first();
                $count = Transactions::with(['inquiry', 'inventory', 'application'])
                ->whereNull('deleted_at')
                ->where('team_id', $data->id)
                ->whereNotNull('reservation_id')
                ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id])
                ->whereHas('application', function($subQuery) {
                    $subQuery->where('created_by', Auth::user()->id);
                });

            }else{
                $released_status = Status::where('status', 'like', 'Released')->first();
                $posted_status = Status::where('status', 'like', 'Posted')->first();
                $count = Transactions::with(['inquiry', 'inventory', 'application'])
                ->whereNull('deleted_at')
                ->where('team_id', $data->id)
                ->whereNotNull('reservation_id')
                ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id]);

            }

            if ($request->has('date_range') && !empty($request->date_range)) {
                [$startDate, $endDate] = explode(' to ', $request->date_range);
                $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
                $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

                $count->whereBetween('updated_at', [$startDate, $endDate]);
            } else {
                $count->whereMonth('updated_at', now()->month)  // Filter by current month
                      ->whereYear('updated_at', now()->year);   // Filter by current year
            };

           $quantity =  $count->count();

            return $quantity;
        })

        ->addColumn('total_profit', function($data) use($request) {
            $released_status = Status::where('status', 'like', 'Released')->first();
            $posted_status = Status::where('status', 'like', 'Posted')->first();

            if(Auth::user()->usertype->name === 'Agent' ){
                $profit = Transactions::with(['inquiry', 'inventory', 'application'])
                ->whereNull('deleted_at')
                ->where('team_id', $data->id)
                ->whereNotNull('reservation_id')
                ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id])
                ->whereHas('application', function($subQuery) {
                    $subQuery->where('created_by', Auth::user()->id);
                });

            }else{
                $profit = Transactions::with(['inquiry', 'inventory', 'application'])
                ->whereNull('deleted_at')
                ->where('team_id', $data->id)
                ->whereNotNull('reservation_id')
                ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id]);

            }

           if ($request->has('date_range') && !empty($request->date_range)) {
                [$startDate, $endDate] = explode(' to ', $request->date_range);
                $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
                $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

                $profit->whereBetween('updated_at', [$startDate, $endDate]);
            } else {
                $profit->whereMonth('updated_at', now()->month)  // Filter by current month
                    ->whereYear('updated_at', now()->year);   // Filter by current year
            };

           $totalProfit = $profit ->sum('profit');
           return number_format($totalProfit, 2);
        })

        ->make(true);
    }

    public function getReleasedCount(Request $request){
        $released_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();
        $pending_for_release_status = Status::where('status', 'like', 'Pending For Release')->first();

        if(Auth::user()->usertype->name === 'SuperAdmin'
        || Auth::user()->usertype->name === 'Sales Admin Staff'
        || Auth::user()->usertype->name === 'General Manager'
        || Auth::user()->usertype->name === 'Financing Staff'
        ){
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('deleted_at')
            ->whereNotNull('reservation_id')
            ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id]);
        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('deleted_at')
            ->whereNotNull('reservation_id')
            ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id])
            ->where('team_id', Auth::user()->team_id);
        }else{
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('deleted_at')
            ->whereNotNull('reservation_id')
            ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id])
            ->whereHas('application', function($subQuery) {
                $subQuery->where('created_by', Auth::user()->id);
            });
        }



            if ($request->has('date_range') && !empty($request->date_range)) {
                [$startDate, $endDate] = explode(' to ', $request->date_range);
                $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
                $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

                $query->whereBetween('updated_at', [$startDate, $endDate]);
            } else {
                $query->whereMonth('updated_at', now()->month)  // Filter by current month
                      ->whereYear('updated_at', now()->year);   // Filter by current year
            }

        $releasedCount = $query->count();

        if(Auth::user()->usertype->name === 'SuperAdmin'
        || Auth::user()->usertype->name === 'Sales Admin Staff'
        || Auth::user()->usertype->name === 'General Manager'
        ){
            $pendingForReleaseQuery = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('deleted_at')
            ->whereNotNull('reservation_id')
            ->where('reservation_transaction_status', $pending_for_release_status->id);
        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $pendingForReleaseQuery = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('deleted_at')
            ->whereNotNull('reservation_id')
            ->where('reservation_transaction_status', $pending_for_release_status->id)
            ->where('team_id', Auth::user()->team_id);
        }else{
            $pendingForReleaseQuery = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('deleted_at')
            ->whereNotNull('reservation_id')
            ->where('reservation_transaction_status', $pending_for_release_status->id)
            ->whereHas('application', function($subQuery) {
                $subQuery->where('created_by', Auth::user()->id);
            });
        }

            if ($request->has('date_range') && !empty($request->date_range)) {
                [$startDate, $endDate] = explode(' to ', $request->date_range);
                $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
                $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

                $pendingForReleaseQuery->whereBetween('updated_at', [$startDate, $endDate]);
            } else {
                $pendingForReleaseQuery->whereMonth('updated_at', now()->month)  // Filter by current month
                      ->whereYear('updated_at', now()->year);   // Filter by current year
            }


        $pendingForReleaseCount = $pendingForReleaseQuery->count();

        return response()->json(['releasedCount' => $releasedCount, 'pendingForReleaseCount' => $pendingForReleaseCount]);
    }

    public function list_pending_for_release(Request $request){

        $pending_for_release_status = Status::where('status', 'like', 'Pending For Release')->first();
        if(Auth::user()->usertype->name === 'SuperAdmin'
        || Auth::user()->usertype->name === 'Sales Admin Staff'
        || Auth::user()->usertype->name === 'General Manager'
        || Auth::user()->usertype->name === 'Financing Staff'
        ){
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->where('reservation_transaction_status', $pending_for_release_status->id)
                        ->whereNotNull('reservation_id')
                        ->orderBy('updated_at', 'desc');
        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->where('reservation_transaction_status', $pending_for_release_status->id)
                        ->whereNotNull('reservation_id')
                        ->whereHas('application', function($subQuery) {
                            $subQuery->whereHas('user', function($subQuery) {
                                $subQuery->where('team_id', Auth::user()->team_id);
                            });
                        })
                        ->orderBy('updated_at', 'desc');
        }else{
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->where('reservation_transaction_status', $pending_for_release_status->id)
                        ->whereNotNull('reservation_id')
                        ->whereHas('application', function($subQuery) {
                            $subQuery->where('created_by', Auth::user()->id);
                        })
                        ->orderBy('updated_at', 'desc');

        }

        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('updated_at', [$startDate, $endDate]);
        }

        $list = $query->get();

        // dd($list->toArray());

        return DataTables::of($list)
        ->editColumn('id', function($data) {
            return encrypt($data->id);
        })

        ->editColumn('unit', function($data) {
            return $data->application->vehicle->unit;
        })

        ->addColumn('customer_name', function($data) {
            if($data->inquiry->inquiryType->inquiry_type === 'Individual'){
                return $data->inquiry->customer->customer_first_name . ' ' . $data->inquiry->customer->customer_last_name;
            }else if($data->inquiry->inquiryType->inquiry_type === 'Fleet'){
                return $data->inquiry->customer->company_name;
            }else if($data->inquiry->inquiryType->inquiry_type === 'Company'){
                return $data->inquiry->customer->company_name;
            }else if($data->inquiry->inquiryType->inquiry_type === 'Government'){
                return $data->inquiry->customer->department_name;
            }
        })

        ->editColumn('year_model', function($data) {
            return $data->inventory->year_model ?? '';
        })

        ->addColumn('variant', function($data) {
            return $data->application->vehicle->variant;
        })

        ->addColumn('color', function($data) {
            return $data->application->vehicle->color;
        })

        ->addColumn('category', function($data) {
            return $data->application->vehicle->category ?? '' ;
        })

        ->addColumn('insurance', function($data) {
            return $data->insurance ?? 'Select Insurance';
        })

        ->addColumn('other_profit', function($data) {
            return '0.00';
        })


        ->addColumn('cs_number', function($data) {
            return $data->inventory->CS_number ?? '';
        })

        // ->addColumn('trans_type', function($data) {
        //     return $data->inquiry->inquiryType->inquiry_type;
        // })
        ->addColumn('transaction', function($data) {
            return $data->inquiry->transaction;
        })
        ->addColumn('trans_bank', function($data) {
            return $data->application->bank->bank_name ?? '';
        })

        ->addColumn('team', function($data) {
            $team = Team::where('id',  $data->application->updatedBy->team_id)->first();
            return $team->name ?? '';
        })

        ->addColumn('agent', function($data) {
            return $data->inventory->user->first_name. ' ' . $data->inventory->user->last_name;
        })

        ->addColumn('date_released', function($data) {
            return $data->released_date ? \Carbon\Carbon::parse($data->released_date)->format('d/m/Y') : '';
            // return $data->released_date ? $data->released_date->format('M d, Y h:i A') : '-';
        })

        ->addColumn('status', function($data) {
            $status = Status::where('id', $data->status)->first()->status;
            return $status;
        })

        ->addColumn('profit', function($data) {
            return number_format($data->profit ?? 0, 2);
        })
        ->addColumn('folder_number', function($data) {
            return $data->folder_number ?? '';
        })

        ->addColumn('source', function($data) {
            return $data->inquiry->customer->source ?? '';
        })

        ->addColumn('address', function($data) {
            return $data->inquiry->customer->address ?? '';
        })

        ->addColumn('gender', function($data) {
            return $data->inquiry->customer->gender ?? '';
        })


        ->make(true);
    }

    public function list_release(Request $request){

        $release_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();
        if(Auth::user()->usertype->name === 'SuperAdmin'
        || Auth::user()->usertype->name === 'Sales Admin Staff'
        || Auth::user()->usertype->name === 'General Manager'
        || Auth::user()->usertype->name === 'Financing Staff'
        ){
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->whereIn('reservation_transaction_status', [$release_status->id, $posted_status->id])
                        ->whereNotNull('reservation_id')
                        ->orderBy('updated_at', 'desc');

        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->whereIn('reservation_transaction_status', [$release_status->id, $posted_status->id])
                        ->whereNotNull('reservation_id')
                        ->whereHas('application', function($subQuery) {
                            $subQuery->whereHas('user', function($subQuery) {
                                $subQuery->where('team_id', Auth::user()->team_id);
                            });
                        })
                        ->orderBy('updated_at', 'desc');

        }else{
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->whereIn('reservation_transaction_status', [$release_status->id, $posted_status->id])
                        ->whereNotNull('reservation_id')
                        ->whereHas('application', function($subQuery) {
                            $subQuery->where('created_by', Auth::user()->id);
                        })
                        ->orderBy('updated_at', 'desc');

        }

        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('updated_at', [$startDate, $endDate]);
        }

        $list = $query->get();

        // dd($list->toArray());

        return DataTables::of($list)
        ->editColumn('id', function($data) {
            return encrypt($data->id);
        })

        ->editColumn('unit', function($data) {
            return $data->application->vehicle->unit;
        })

        ->addColumn('customer_name', function($data) {
            if($data->inquiry->inquiryType->inquiry_type === 'Individual'){
                return $data->inquiry->customer->customer_first_name . ' ' . $data->inquiry->customer->customer_last_name;
            }else if($data->inquiry->inquiryType->inquiry_type === 'Fleet'){
                return $data->inquiry->customer->company_name;
            }else if($data->inquiry->inquiryType->inquiry_type === 'Company'){
                return $data->inquiry->customer->company_name;
            }else if($data->inquiry->inquiryType->inquiry_type === 'Government'){
                return $data->inquiry->customer->department_name;
            }
        })

        ->editColumn('year_model', function($data) {
            return $data->inventory->year_model ?? '';
        })

        ->addColumn('variant', function($data) {
            return $data->application->vehicle->variant;
        })

        ->addColumn('color', function($data) {
            return $data->application->vehicle->color;
        })

        ->addColumn('cs_number', function($data) {
            return $data->inventory->CS_number ?? '';
        })

        ->addColumn('category', function($data) {
            return $data->application->vehicle->category ?? '' ;
        })

        ->addColumn('insurance', function($data) {
            return $data->insurance ?? 'Select Insurance';
        })

        ->addColumn('other_profit', function($data) {
            return '0.00';
        })


        // ->addColumn('trans_type', function($data) {
        //     return $data->inquiry->inquiryType->inquiry_type;
        // })
        ->addColumn('transaction', function($data) {
            return $data->inquiry->transaction;
        })
        ->addColumn('trans_bank', function($data) {
            return $data->application->bank->bank_name ?? '';
        })

        ->addColumn('team', function($data) {
            $team = Team::where('id',  $data->application->updatedBy->team_id)->first();
            return $team->name ?? '';
        })

        ->addColumn('agent', function($data) {
            return $data->inventory->user->first_name. ' ' . $data->inventory->user->last_name;
        })

        ->addColumn('date_reserved', function($data) {
            return $data->reservation_date ? \Carbon\Carbon::parse($data->reservation_date)->format('d/m/Y') : '';
        })

        ->addColumn('date_released', function($data) {
            return $data->released_date ? \Carbon\Carbon::parse($data->released_date)->format('M d, Y') : '';
            // return $data->released_date ? $data->released_date->format('M d, Y h:i A') : '-';
        })  

        ->addColumn('status', function($data) {
            $status = Status::where('id', $data->status)->first()->status;
            return $status;
        })
        ->addColumn('profit', function($data) {
            return number_format($data->profit ?? 0, 2);
        })

        ->addColumn('folder_number', function($data) {
            return $data->folder_number ?? '';
        })

        ->addColumn('source', function($data) {
            return $data->inquiry->customer->source ?? '';
        })

        ->addColumn('address', function($data) {
            return $data->inquiry->customer->address ?? '';
        })

        ->addColumn('gender', function($data) {
            return $data->inquiry->customer->gender ?? '';
        })

        ->make(true);
    }

    public function processing(Request $request){
        try {


            $posted_status = Status::where('status', 'like', 'Posted')->first()->id;
            $pending_for_release_status = Status::where('status', 'like', 'Pending For Release')->first()->id;

            $transaction = Transactions::findOrFail(decrypt($request->id));

            if (is_null($transaction->insurance)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insurance is required.'
                ], 400);
            }


            if($transaction->reservation_transaction_status == $pending_for_release_status){

                $inventory = Inventory::findOrFail($transaction->inventory_id);
                $inventory->CS_number_status = 'Released';
                $inventory->status = 'Released';
                $inventory->save();

                $transaction->status = $posted_status;
                $transaction->reservation_transaction_status = $posted_status;
                $transaction->folder_number = $request->folder_number;
                $transaction->released_date = now();
                $transaction->updated_at = now();
                $transaction->save();

            }
            return response()->json([
                'success' => true,
                'message' => 'Vehicle release request successfully processed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating reservation process: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel_release(Request $request){
        try {

            $pending_for_release_status = Status::where('status', 'like', 'Pending For Release')->first()->id;
            $reserved_status = Status::where('status', 'like', 'Reserved')->first()->id;

            $transaction = Transactions::findOrFail(decrypt($request->id));

            if($transaction->reservation_transaction_status == $pending_for_release_status){

                $transaction->status = $reserved_status;
                $transaction->reservation_transaction_status = $reserved_status;
                $transaction->released_date = null;
                $transaction->updated_at = now();
                $transaction->save();

            }
            return response()->json([
                'success' => true,
                'message' => 'Vehicle release request has been canceled'
            ]);


        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error updating: ' . $e->getMessage()
            ], 500);

        }

    }

    public function updateProfit(Request $request){
       try {
            $request->validate([
                'profit' => 'required|numeric|min:0'
            ]);

            $transaction = Transactions::findOrFail(decrypt($request->id));
            $transaction->profit = $request->profit;
            $transaction->timestamps = false;
            $transaction->save();

            return response()->json([
                'success' => true,
                'message' => 'Profit updated successfully'
            ]);

       } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating profit: ' . $e->getMessage()
            ], 500);
       }
    }

    public function updateLTORemarks(Request $request){
        try {
            // dd($request->all());
            $transaction = Transactions::findOrFail(decrypt($request->id));
            $transaction->lto_remarks = $request->remarks;
            $transaction->timestamps = false;
            $transaction->save();

            return response()->json([
                'success' => true,
                'message' => 'LTO remarks updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating LTO remarks: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateReleasedRemarks(Request $request){
        try {
            // dd($request->all());
            $transaction = Transactions::findOrFail(decrypt($request->id));
            $transaction->released_remarks = $request->remarks;
            $transaction->timestamps = false;
            $transaction->save();            

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'date_released' => $updatedRecord->released_date, // Include the updated date
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating released remarks: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStatus(Request $request){
        $status = Status::whereIn('status', ['Posted', 'Released'])->get();
        return response()->json($status);
    }

    public function updateStatus(Request $request) {
        try {
            $status = Status::where('status', 'like', 'Released')->first()->id;

            $transaction = Transactions::findOrFail(decrypt($request->id));
            $transaction->status = $request->status;
            $transaction->reservation_transaction_status = $request->status;

            // Use the selected date instead of `now()`
            $transaction->released_date = $request->released_date ? Carbon::parse($request->released_date) : null;
            $transaction->updated_at = now();
            $transaction->save();

            if ($request->status == $status) {
                $inventory = Inventory::findOrFail($transaction->inventory_id);
                $inventory->CS_number_status = 'Released';
                $inventory->status = 'Released';
                $inventory->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating transaction status: ' . $e->getMessage()
            ], 500);
        }
    }


    public function GrandTotalProfit(Request $request){
        $released_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();

        if(Auth::user()->usertype->name === 'SuperAdmin' || Auth::user()->usertype->name === 'General Manager' || Auth::user()->usertype->name === 'Financing Staff' || Auth::user()->usertype->name === 'Sales Admin Staff'){
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
                ->whereNull('deleted_at')
                ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id]);
        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
                ->whereNull('deleted_at')
                ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id])
                ->where('team_id', Auth::user()->team_id);
        }else{
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
                ->whereNull('deleted_at')
                ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id])
                ->whereHas('application', function($subQuery) {
                    $subQuery->where('created_by', Auth::user()->id);
                });
        }

        // Apply date range filter if provided, otherwise use current month/year
        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('updated_at', [$startDate, $endDate]);
        } else {
            $query->whereMonth('updated_at', now()->month)  // Filter by current month
                  ->whereYear('updated_at', now()->year);   // Filter by current year
        }

        $profit = $query->sum('profit');

        return number_format($profit, 2);
    }

    public function addInsurance(Request $request){
        try {
            $request->validate([
                'insurance' => 'required',
            ]);

            $transaction = Transactions::findOrFail(decrypt($request->id));
            $transaction->insurance = $request->insurance;
            $transaction->timestamps = false;
            $transaction->save();

            return response()->json([
                'success' => true,
                'message' => 'Transaction insurance updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating transaction insurance: ' . $e->getMessage()
            ], 500);
        }

    }

    public function getVehicleReleaseCount(){
        $pending_for_release_status = Status::where('status', 'like', 'Pending For Release')->first();
        $release_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();
        if(Auth::user()->usertype->name === 'SuperAdmin'
        || Auth::user()->usertype->name === 'Sales Admin Staff'
        || Auth::user()->usertype->name === 'General Manager'
        || Auth::user()->usertype->name === 'Financing Staff'
        ){
            $pending_query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->where('notif_status', 'open')
                        ->where('reservation_transaction_status', $pending_for_release_status->id)
                        ->whereNotNull('reservation_id')
                        ->orderBy('updated_at', 'desc');
            $released_query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->whereIn('reservation_transaction_status', [$release_status->id, $posted_status->id])
                        ->whereNotNull('reservation_id')
                        ->orderBy('updated_at', 'desc');
        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $pending_query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->where('notif_status', 'open')
                        ->where('reservation_transaction_status', $pending_for_release_status->id)
                        ->whereNotNull('reservation_id')
                        ->whereHas('application', function($subQuery) {
                            $subQuery->whereHas('user', function($subQuery) {
                                $subQuery->where('team_id', Auth::user()->team_id);
                            });
                        })
                        ->orderBy('updated_at', 'desc');
            $released_query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->whereIn('reservation_transaction_status', [$release_status->id, $posted_status->id])
                        ->whereNotNull('reservation_id')
                        ->whereHas('application', function($subQuery) {
                            $subQuery->whereHas('user', function($subQuery) {
                                $subQuery->where('team_id', Auth::user()->team_id);
                            });
                        })
                        ->orderBy('updated_at', 'desc');
        }else{
            $pending_query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->where('notif_status', 'open')
                        ->where('reservation_transaction_status', $pending_for_release_status->id)
                        ->whereNotNull('reservation_id')
                        ->whereHas('application', function($subQuery) {
                            $subQuery->where('created_by', Auth::user()->id);
                        })
                        ->orderBy('updated_at', 'desc');
            $released_query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->where('notif_status', 'open')
                        ->whereIn('reservation_transaction_status', [$release_status->id, $posted_status->id])
                        ->whereNotNull('reservation_id')
                        ->whereHas('application', function($subQuery) {
                            $subQuery->where('created_by', Auth::user()->id);
                        })
                        ->orderBy('updated_at', 'desc');
        }

        $pending_count = $pending_query->count();
        $released_count = $released_query->count();

        return response()->json([
            'success' => true,
            'pending_count' => $pending_count,
            'released_count' => $released_count
        ]);
    }

    public function updateNotifStatus(Request $request){
        if(!Auth::user()->usertype->name === 'SuperAdmin'){
            $pending_for_release_status = Status::where('status', 'like', 'Pending For Release')->first();
            $release_status = Status::where('status', 'like', 'Released')->first();
            $posted_status = Status::where('status', 'like', 'Posted')->first();

            if($request->buttonTitle === 'For Release Units'){
                $query = Transactions::with(['inquiry', 'inventory', 'application'])
                            ->whereNull('deleted_at')
                            ->where('notif_status', 'open')
                            ->where('reservation_transaction_status', $pending_for_release_status->id)
                            ->whereNotNull('reservation_id')
                            ->orderBy('updated_at', 'desc');

                $query->update([
                    'notif_status' => 'closed'
                ]);
            }elseif($request->buttonTitle === 'Released Units'){
                $query = Transactions::with(['inquiry', 'inventory', 'application'])
                            ->whereNull('deleted_at')
                            ->where('notif_status', 'open')
                            ->whereIn('reservation_transaction_status', [$release_status->id, $posted_status->id])
                            ->whereNotNull('reservation_id')
                            ->orderBy('updated_at', 'desc');

                $query->update([
                    'notif_status' => 'closed'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification status updated successfully'
            ]);

        }else{
            return response()->json([
                'success' => true,
                'message' => 'Not authorized for this action'
            ]);
        }

    }


}

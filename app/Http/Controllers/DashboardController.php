<?php

namespace App\Http\Controllers;

use App\Models\Banks;
use App\Models\Status;
use App\Models\Transactions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if(Auth::check()){

            return view('dashboard.dashboard');
        }else{
            return view('index');
        }

    }

    public function barChartMonthlyRelease(Request $request)
    {
        $released_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();
        $pending_for_release_status = Status::where('status', 'like', 'Pending For Release')->first();

        $query = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('deleted_at')
            ->whereNotNull('reservation_id')
            ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id]);

            if ($request->has('group') && !empty($request->group)) {
                $query->where('team_id', $request->group);
            }
            
            if ($request->has('agent') && !empty($request->agent)) {

                $query->whereHas('application', function($subQuery)  use ($request) {
                    $subQuery->where('created_by', $request->agent);
                });
            }

        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('updated_at', [$startDate, $endDate]);
        }

        $monthlyReleasedCount = $query->selectRaw('MONTH(updated_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month');

        $monthlyData = array_fill(1, 12, 0); // Initialize array with 12 months
        foreach ($monthlyReleasedCount as $month => $count) {
            $monthlyData[$month] = $count;
        }

        return response()->json(['monthlyData' => array_values($monthlyData)]);
    }

    public function getReleasedCountPerTransType(Request $request){
        $released_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();
        $queryFinancing = Transactions::with(['inquiry', 'inventory', 'application'])
        ->whereNull('deleted_at')
        ->whereNotNull('reservation_id')
        ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id]);
        $queryFinancing->whereHas('application', function($subQuery) {
                    $subQuery->where('transaction', 'like', 'financing');
                });
                if ($request->has('group') && !empty($request->group)) {
                    $queryFinancing->where('team_id', $request->group);
                }
                if ($request->has('date_range') && !empty($request->date_range)) {
                    [$startDate, $endDate] = explode(' to ', $request->date_range);
                    $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
                    $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

                }else {
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                }
                $queryFinancing->whereBetween('updated_at', [$startDate, $endDate]);

                if ($request->has('agent') && !empty($request->agent)) {

                    $queryFinancing->whereHas('application', function($subQuery)  use ($request) {
                        $subQuery->where('created_by', $request->agent);
                    });
                }

        $financingCount = $queryFinancing->count();

        $queryCash = Transactions::with(['inquiry', 'inventory', 'application'])
        ->whereNull('deleted_at')
        ->whereNotNull('reservation_id')
        ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id]);
        $queryCash->whereHas('application', function($subQuery) {
                    $subQuery->where('transaction', 'like', 'cash');
                });
                if ($request->has('group') && !empty($request->group)) {
                    $queryCash->where('team_id', $request->group);
                }
                if ($request->has('date_range') && !empty($request->date_range)) {
                    [$startDate, $endDate] = explode(' to ', $request->date_range);
                    $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
                    $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

                }else {
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                }
                $queryCash->whereBetween('updated_at', [$startDate, $endDate]);

                if ($request->has('agent') && !empty($request->agent)) {

                    $queryCash->whereHas('application', function($subQuery)  use ($request) {
                        $subQuery->where('created_by', $request->agent);
                    });
                }

        $cashCount = $queryCash->count();

        $queryPO = Transactions::with(['inquiry', 'inventory', 'application'])
        ->whereNull('deleted_at')
        ->whereNotNull('reservation_id')
        ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id]);
        $queryPO->whereHas('application', function($subQuery) {
                    $subQuery->where('transaction', 'like', 'po');
                });
                if ($request->has('group') && !empty($request->group)) {
                    $queryPO->where('team_id', $request->group);
                }
                if ($request->has('date_range') && !empty($request->date_range)) {
                    [$startDate, $endDate] = explode(' to ', $request->date_range);
                    $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
                    $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

                }else {
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                }
                $queryPO->whereBetween('updated_at', [$startDate, $endDate]);

                if ($request->has('agent') && !empty($request->agent)) {

                    $queryPO->whereHas('application', function($subQuery)  use ($request) {
                        $subQuery->where('created_by', $request->agent);
                    });
                }

        $poCount = $queryPO->count();

        return response()->json(['Financing' => $financingCount, 'Cash' => $cashCount, 'PO' => $poCount]);


    }

    public function getReleasedDataByBank(Request $request) {
        $released_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();

        $query = Transactions::whereNull('deleted_at')
            ->whereNotNull('reservation_id')
            ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id])
            ->with('application.bank') // Assuming 'bank' is the relationship in 'application'
            ->whereHas('application', function($subQuery) {
                $subQuery->whereNotNull('bank_id');
            });

            if ($request->has('group') && !empty($request->group)) {
                $query->where('team_id', $request->group);
            }else {
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
            }

            if ($request->has('agent') && !empty($request->agent)) {

                $query->whereHas('application', function($subQuery)  use ($request) {
                    $subQuery->where('created_by', $request->agent);
                });
            }

        // Apply date range filter if provided
        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

        }else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }
        $query->whereBetween('updated_at', [$startDate, $endDate]);




        // Group by bank_id and count released transactions per bank
        $data = $query->get()
            ->groupBy('application.bank_id') // Group by bank_id
            ->map(function ($transactions, $bankId) {
                return [
                    'bank_name' => $transactions->first()->application->bank,
                    'count' => $transactions->count()
                ];
            })
            ->values();

        return response()->json($data);
    }

    public function getReleasedDataByGender(Request $request){
        $released_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();
        $queryFemale = Transactions::with(['inquiry', 'inventory', 'application'])
        ->whereNull('deleted_at')
        ->whereNotNull('reservation_id')
        ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id]);
        $queryFemale->whereHas('inquiry', function($subQuery) {
                    $subQuery->whereHas('customer', function($subQuery) {
                        $subQuery->where('gender', 'like',  'Female');
                    });
                });

                if ($request->has('group') && !empty($request->group)) {
                    $queryFemale->where('team_id', $request->group);
                }

                if ($request->has('date_range') && !empty($request->date_range)) {
                    [$startDate, $endDate] = explode(' to ', $request->date_range);
                    $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
                    $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

                }else {
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                }
                $queryFemale->whereBetween('updated_at', [$startDate, $endDate]);

                if ($request->has('agent') && !empty($request->agent)) {

                    $queryFemale->whereHas('application', function($subQuery)  use ($request) {
                        $subQuery->where('created_by', $request->agent);
                    });
                }


        $FemaleCount = $queryFemale->count();


        $queryMale = Transactions::with(['inquiry', 'inventory', 'application'])
        ->whereNull('deleted_at')
        ->whereNotNull('reservation_id')
        ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id]);

        $queryMale->whereHas('inquiry', function($subQuery) {
            $subQuery->whereHas('customer', function($subQuery) {
                $subQuery->where('gender', 'like',  'Male');
            });
        });

        if ($request->has('group') && !empty($request->group)) {
            $queryMale->where('team_id', $request->group);
        }

        if ($request->has('agent') && !empty($request->agent)) {

            $queryMale->whereHas('application', function($subQuery)  use ($request) {
                $subQuery->where('created_by', $request->agent);
            });
        }

        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();
        }else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }

        $queryMale->whereBetween('updated_at', [$startDate, $endDate]);

        $MaleCount = $queryMale->count();

        return response()->json(['Female' => $FemaleCount, 'Male' => $MaleCount]);

    }

    public function getReleaseDataBySource(Request $request){
        $released_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();

        $source = ['Social-Media', 'Referral', 'Mall Duty', 'Show Room', 'Saturation', 'Repeat-Customer'];

        $query = Transactions::with(['inquiry', 'inventory', 'application'])
        ->whereNull('deleted_at')
        ->whereNotNull('reservation_id')
        ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id]);
        $query->whereHas('inquiry', function($subQuery) use ($source) {
            $subQuery->whereHas('customer', function($subQuery) {
                $subQuery->whereIn('source', ['Social-Media', 'Referral', 'Mall Duty', 'Show Room', 'Saturation', 'Repeat-Customer']);
            });
        });

        if ($request->has('group') && !empty($request->group)) {
            $query->where('team_id', $request->group);
        }

        if ($request->has('agent') && !empty($request->agent)) {

            $query->whereHas('application', function($subQuery)  use ($request) {
                $subQuery->where('created_by', $request->agent);
            });
        }

        // Apply date range filter if provided
        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

        }else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }

        $query->whereBetween('updated_at', [$startDate, $endDate]);

            // Group by source and count released transactions per source
        $data = $query->get()
        ->groupBy('inquiry.customer.source')
        ->map(function ($transactions, $source) {
            return [
                'source' => $source,
                'count' => $transactions->count()
            ];
        })
        ->values();

        return response()->json($data);
    }

    public function getReleasedCount(Request $request){
        $released_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();

            $query = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('deleted_at')
            ->whereNotNull('reservation_id')
            ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id]);

            if ($request->has('group') && !empty($request->group)) {
                $query->where('team_id', $request->group);
            }

            if ($request->has('agent') && !empty($request->agent)) {

                $query->whereHas('application', function($subQuery)  use ($request) {
                    $subQuery->where('created_by', $request->agent);
                });
            }

            if ($request->has('date_range') && !empty($request->date_range)) {
                [$startDate, $endDate] = explode(' to ', $request->date_range);
                $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
                $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

                $query->whereBetween('updated_at', [$startDate, $endDate]);
            }else {
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
            }

            $query->whereBetween('updated_at', [$startDate, $endDate]);

        $releasedCount = $query->count();

        return response()->json(['releasedCount' => $releasedCount]);
    }
    
}

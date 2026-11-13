<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Status;
use App\Models\Transactions;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SFMDashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.sales_funnel_management_dashboard');
    }

    public function fetchInquiryCount(Request $request)
    {
        $status = Status::where('status', 'like', 'Processed')->first()->id;

        $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
        ->whereNull('deleted_at')
        ->where('is_dispute', '0')
        ->where('status_id', '<>', $status);

        if ($request->has('group') && !empty($request->group)) {

            $query->whereHas('user', function ($q) use ($request) {
                $q->where('team_id', $request->group);
            });
        }

        if ($request->has('agent') && !empty($request->agent)) {

            $query->where('created_by', $request->agent);
        }

        if ($request->has('start_date') && !empty($request->start_date) && $request->has('end_date') && !empty($request->end_date)) {
            // [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $request->start_date)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $request->end_date)->endOfDay();

            $query->whereBetween('updated_at', [$startDate, $endDate]);
        }

        $monthlyInquiryCount = $query->selectRaw('MONTH(updated_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month');

        $monthlyData = array_fill(1, 12, 0); // Initialize array with 12 months
        foreach ($monthlyInquiryCount as $month => $count) {
            $monthlyData[$month] = $count;

        }

        return response()->json(['monthlyData' => array_values($monthlyData)]);
    }

    public function fetchReservationCount(Request $request)
    {
        $reserved_status = Status::where('status', 'like', 'Reserved')->first();
        $released_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();

        $query = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('deleted_at')
            ->whereNotNull('inventory_id')
            ->whereNotNull('reservation_id')
            ->whereIn('reservation_transaction_status', [$reserved_status->id,  $released_status->id, $posted_status->id]);

            if ($request->has('group') && !empty($request->group)) {
                $query->where('team_id', $request->group);
            }

            if ($request->has('agent') && !empty($request->agent)) {

                $query->whereHas('application', function($subQuery)  use ($request) {
                    $subQuery->where('created_by', $request->agent);
                });
            }

            if ($request->has('start_date') && !empty($request->start_date) && $request->has('end_date') && !empty($request->end_date)) {
                // [$startDate, $endDate] = explode(' to ', $request->date_range);
                $startDate = Carbon::createFromFormat('m/d/Y', $request->start_date)->startOfDay();
                $endDate = Carbon::createFromFormat('m/d/Y', $request->end_date)->endOfDay();

                $query->whereBetween('updated_at', [$startDate, $endDate]);
            }

        $monthlyReservationCount = $query->selectRaw('MONTH(updated_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month');

        $monthlyData = array_fill(1, 12, 0); // Initialize array with 12 months
        foreach ($monthlyReservationCount as $month => $count) {
            $monthlyData[$month] = $count;

        }

        return response()->json(['monthlyData' => array_values($monthlyData)]);
    }

    public function fetchVehicleQuantity(Request $request){
        DB::statement("SET SQL_MODE=''");

        $status = Status::where('status', 'like', 'Processed')->first()->id;

        $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType']);


        if ($request->has('group') && !empty($request->group)) {

            $query->whereHas('user', function ($q) use ($request) {
                $q->where('team_id', $request->group);
            });
        }

        if ($request->has('agent') && !empty($request->agent)) {

            $query->where('inquiry.created_by', $request->agent);
        }

        if ($request->has('start_date') && !empty($request->start_date) && $request->has('end_date') && !empty($request->end_date)) {
            $startDate = Carbon::createFromFormat('m/d/Y', $request->start_date)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $request->end_date)->endOfDay();

            $query->whereBetween('inquiry.updated_at', [$startDate, $endDate]);
        }

        $query->join('vehicle', 'inquiry.vehicle_id', '=', 'vehicle.id')
        ->whereNull('inquiry.deleted_at')
        ->where('inquiry.is_dispute', '0')
        ->where('inquiry.status_id', '<>', $status)
        ->select('vehicle.unit', DB::raw('SUM(inquiry.quantity) as total_quantity'))
        ->groupBy('vehicle.unit');

        $result = $query->get();

        return response()->json(['inquiryCount' => $result]);
    }

    public function getInquiryCount(Request $request){
        DB::statement("SET SQL_MODE=''");

        $status = Status::where('status', 'like', 'Processed')->first()->id;

        $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType']);

        if ($request->has('group') && !empty($request->group)) {

            $query->whereHas('user', function ($q) use ($request) {
                $q->where('team_id', $request->group);
            });
        }

        if ($request->has('agent') && !empty($request->agent)) {

            $query->where('inquiry.created_by', $request->agent);
        }

        if ($request->has('start_date') && !empty($request->start_date) && $request->has('end_date') && !empty($request->end_date)) {
            $startDate = Carbon::createFromFormat('m/d/Y', $request->start_date)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $request->end_date)->endOfDay();

            $query->whereBetween('inquiry.updated_at', [$startDate, $endDate]);
        }

        // if ($request->has('date_range') && !empty($request->date_range)) {
        //     [$startDate, $endDate] = explode(' to ', $request->date_range);
        //     $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
        //     $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

        //     $query->whereBetween('inquiry.updated_at', [$startDate, $endDate]);
        // }

        $Quantity = $query->join('vehicle', 'inquiry.vehicle_id', '=', 'vehicle.id')
        ->whereNull('inquiry.deleted_at')
        ->where('inquiry.is_dispute', '0')
        ->where('inquiry.status_id', '<>', $status)
        ->select('vehicle.unit', DB::raw('SUM(inquiry.quantity) as total_quantity'));

        $inquiryCount = $query->whereNull('inquiry.deleted_at')
        ->where('inquiry.is_dispute', '0')
        ->where('inquiry.status_id', '<>', $status)
        ->count();

        $quantityPerUnit = $query->pluck('total_quantity');
        $implodedQuantities = implode(', ', json_decode($quantityPerUnit));



        return response()->json([
            'inquiryCount' => $inquiryCount,
            'quantityPerUnit' => $implodedQuantities
        ]);

    }
}

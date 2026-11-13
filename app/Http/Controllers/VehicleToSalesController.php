<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Status;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VehicleToSalesController extends Controller
{
    public function index()
    {
        return view('dashboard.vehicle_to_sales_dashboard');
    }

    public function totalDeliveriesToday(Request $request){

        $query = Inventory::whereNull('deleted_at');

            if ($request->has('date_range') && !empty($request->date_range)) {
                [$startDate, $endDate] = explode(' to ', $request->date_range);
                $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

            }else {
                $startDate = Carbon::today();
                $endDate = Carbon::today();
            }
            $query->whereBetween('delivery_date', [$startDate, $endDate]);

        $deliveryCount = $query->count();

        return response()->json(['deliveryCount' => $deliveryCount]);

    }

    public function getReleasedToday(Request $request){
        $released_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();

            $query = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('deleted_at')
            ->whereNotNull('reservation_id')
            ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id]);

            if ($request->has('date_range') && !empty($request->date_range)) {
                [$startDate, $endDate] = explode(' to ', $request->date_range);
                $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

            }else {
                $startDate = Carbon::today();
                $endDate = Carbon::today();
            }
            $query->whereBetween('updated_at', [$startDate, $endDate]);



        $releasedCount = $query->count();

        return response()->json(['releasedCount' => $releasedCount]);
    }

    public function getDailyDeliveries(Request $request)
    {

        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

        }else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }
       
    
        $deliveries = Inventory::whereNull('deleted_at')
            ->whereBetween('delivery_date', [$startDate, $endDate])
            ->selectRaw('DATE(delivery_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
    
        // Format data for chart
        $formattedData = [];
        $daysInMonth = $startDate->daysInMonth;
    
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $day = $startDate->copy()->day($i)->toDateString();
            $formattedData[$day] = 0; // Default count is 0
        }
    
        foreach ($deliveries as $delivery) {
            $formattedData[$delivery->date] = $delivery->count;
        }
    
        return response()->json(array_values($formattedData));
    }
    
    public function getDailyReservation(Request $request)
    {

        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

        }else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }

        $released_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();

        $reservations = Transactions::with(['inquiry', 'inventory', 'application'])
        ->whereNull('deleted_at')
        ->whereNotNull('reservation_id')
        ->whereIn('reservation_transaction_status', [$released_status->id, $posted_status->id])
        ->whereBetween('updated_at', [$startDate, $endDate])
        ->selectRaw('DATE(updated_at) as date, COUNT(*) as count')
        ->groupBy('date')
        ->orderBy('date', 'ASC')
        ->get();
    
    
        // Format data for chart
        $formattedData = [];
        $daysInMonth = $startDate->daysInMonth;
    
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $day = $startDate->copy()->day($i)->toDateString();
            $formattedData[$day] = 0; // Default count is 0
        }
    
        foreach ($reservations as $reservation) {
            $formattedData[$reservation->date] = $reservation->count;
        }
    
        return response()->json(array_values($formattedData));
    }

}

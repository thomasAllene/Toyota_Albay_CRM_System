<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RankingDashboardController extends Controller
{
    public function index(Request $request)
    {
       

        return view('dashboard.ranking_dashboard');
    }

    public function topAgent(Request $request){
        DB::statement("SET SQL_MODE=''");

        $released_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();
        $pending_for_release_status = Status::where('status', 'like', 'Pending For Release')->first();

        $query = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('transactions.deleted_at')
            ->whereNotNull('transactions.reservation_id')
            ->whereIn('transactions.reservation_transaction_status', [$released_status->id, $posted_status->id]);

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
 
        }else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }
        $query->whereBetween('transactions.updated_at', [$startDate, $endDate]);

        $topAgents = $query->join('application', 'transactions.application_id', '=', 'application.id')
        ->select('application.created_by', DB::raw('count(*) as total'))
        ->groupBy('application.created_by')
        ->orderBy('total', 'desc')
        ->get();

        $rankedAgents = $topAgents->groupBy('total')->take(3)->flatten();

        if ($rankedAgents->isNotEmpty()) {
            $agents = $rankedAgents->map(function ($topAgent) {
                $agent = User::find($topAgent->created_by);
                return ['agent' => $agent, 'total' => $topAgent->total];
            });

            return response()->json(['agents' => $agents]);
        }

        return response()->json(['message' => 'No transactions found']);

    }

    public function topAgentList(Request $request){
        DB::statement("SET SQL_MODE=''");

        $released_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();
        $pending_for_release_status = Status::where('status', 'like', 'Pending For Release')->first();

        $query = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('transactions.deleted_at')
            ->whereNotNull('transactions.reservation_id')
            ->whereIn('transactions.reservation_transaction_status', [$released_status->id, $posted_status->id]);

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

        }else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }
        $query->whereBetween('transactions.updated_at', [$startDate, $endDate]);

        $topAgents = $query->join('application', 'transactions.application_id', '=', 'application.id')
        ->select('application.created_by', DB::raw('count(*) as total'))
        ->groupBy('application.created_by')
        ->orderBy('total', 'desc')
        ->get();

        if ($topAgents->isNotEmpty()) {
            $agents = $topAgents->map(function ($topAgent, $index) {
                $agent = User::find($topAgent->created_by);
                return [
                    'rank' => $index + 1,
                    'agent' => $agent,
                    'total' => $topAgent->total
                ];
            });

            return DataTables::of($agents)
            ->addColumn('rank', function($data) {
                return $data['rank'];
            })
            ->addColumn('agent', function($data) {
                return $data['agent']->first_name . ' ' . $data['agent']->last_name;
            })
            ->addColumn('total', function($data) {
                return $data['total'];
            })
            ->make(true);
            
        }

        return response()->json(['message' => 'No transactions found']);

    }

    public function topAgentBarChart(Request $request){
        DB::statement("SET SQL_MODE=''");

        $released_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();

        $query = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('transactions.deleted_at')
            ->whereNotNull('transactions.reservation_id')
            ->whereIn('transactions.reservation_transaction_status', [$released_status->id, $posted_status->id]);

        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();
    
        }else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }
        $query->whereBetween('transactions.updated_at', [$startDate, $endDate]);

        if ($request->has('group') && !empty($request->group)) {
            $query->where('team_id', $request->group);
        }

        if ($request->has('agent') && !empty($request->agent)) {
            $query->whereHas('application', function($subQuery)  use ($request) {
                $subQuery->where('created_by', $request->agent);
            });
        }

        // dd($query->get());

        $topAgents = $query->join('application', 'transactions.application_id', '=', 'application.id')
        ->select('application.created_by', DB::raw('count(*) as total'));

        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();
    
        }else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }
        $topAgents->whereBetween('transactions.updated_at', [$startDate, $endDate]);
        

        if ($request->has('group') && !empty($request->group)) {
            $topAgents->where('transactions.team_id', $request->group);
        }

        if ($request->has('agent') && !empty($request->agent)) {
            $topAgents->where('application.created_by', $request->agent);

        }
        $topAgents = $topAgents
        ->groupBy('application.created_by')
        ->orderBy('total', 'desc')
        ->get();

        if ($topAgents->isNotEmpty()) {
            $agents = $topAgents->map(function ($topAgent) {
                // dd($topAgent->created_by);
                $agent = User::find($topAgent->created_by);
                return ['agent' => $agent, 'total' => $topAgent->total];
            });

            // dd($agents);

            return response()->json(['agents' => $agents]);
        }

        return response()->json(['message' => 'No transactions found']);

    }

}

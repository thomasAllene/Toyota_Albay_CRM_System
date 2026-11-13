<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TeamController extends Controller
{
    public function index()
    {
        if(Auth::check()){
            $user = Auth::user();
            return view('team.team', compact('user'));
        }

        return view('index');
    }

    public function createTeam(Request $request)
    {

        try {
            $team = Team::create([
                'name' => $request->name,
                'status' => 'Active',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);
            return response()->json(['success' => true, 'message' => 'Team created successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateTeam(Request $request, $id)
    {

        try {
            $team = Team::findorFail(decrypt($id));
            $team->update([
                'name' => $request->name,
            'status' => $request->status,
            'updated_by' => Auth::user()->id,
                'updated_at' => Carbon::now(),
            ]);
            return response()->json(['success' => true, 'message' => 'Team updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function listTeam(){
        DB::statement("SET SQL_MODE=''");

        $query = Team::orderBy('updated_at', 'desc')->get();

        return DataTables::of($query)
        ->addColumn('id', function($data) {
            return encrypt($data->id);
        })
        ->addColumn('updated_by', function($data) {
            return $data->updated_by ? $data->updatedBy->first_name . ' ' . $data->updatedBy->last_name : '';
        })
        ->addColumn('updated_at', function($data) {
            return $data->updated_at->format('M d, Y h:i A');
        })
        ->addColumn('action', function($data) {
            return '';
        })

        ->make(true);


    }


}

<?php

namespace App\Http\Controllers;

use App\Models\Banks;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class BankController extends Controller
{
    public function index(){

        if(Auth::check()){
            return view('banks.banks');
        }else{
            return view ('index');
        }
    }

    public function store(Request $request){
        try{

            $request->validate([
                'bank_name' => 'required|string|unique:banks,bank_name',
            ]);


            $banks = new Banks();
            $banks->bank_name = $request->bank_name;
            $banks->created_by = Auth::user()->id;
            $banks->updated_by = Auth::user()->id;
            $banks->created_at = now();
            $banks->updated_at = now();
            $banks->save();

            return response()->json(['success' => true, 'message' => 'Bank added successfully']);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error adding bank: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id){
        try{
            $bank = Banks::findorFail(decrypt($id));
            $bank_id = encrypt($bank->id);
            return response()->json(['bank' => $bank, 'bank_id' => $bank_id]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error editing bank: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id){
        try{
            $bank = Banks::findorFail(decrypt($id));

            $bank->bank_name = $request->bank_name;
            $bank->updated_by = Auth::user()->id;
            $bank->updated_at = now();
            $bank->update();

            return response()->json(['success' => true, 'message' => 'Bank updated successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating bank: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id){
        try{
            $bank = Banks::findorFail(decrypt($id));
            $bank->delete();

            return response()->json(['success' => true, 'message' => 'Bank deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting bank: ' . $e->getMessage()
            ], 500);
        }
    }

    public function list(Request $request ){

        $query = Banks::with(['createdBy', 'updatedBy'])->whereNull('deleted_at')->orderBy('updated_at', 'desc')
        ;

        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('updated_at', [$startDate, $endDate]);
        }
        $list = $query->get();

        return DataTables::of($list)
            ->editColumn('id', function($row){
                return encrypt($row->id);
            })
            ->editColumn('created_by', function($row){
                return $row->createdBy->first_name . ' ' . $row->createdBy->last_name;
            })
            ->editColumn('updated_by', function($row){
                return $row->updatedBy->first_name . ' ' . $row->updatedBy->last_name;
            })
            ->editColumn('created_at', function($row){
                return $row->updated_at->format('M d, Y h:i A');
            })
            ->editColumn('updated_at', function($row){
                return $row->updated_at->format('M d, Y h:i A');
            })
            ->make(true);

    }
}

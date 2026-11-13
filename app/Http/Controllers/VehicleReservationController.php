<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Inventory;
use App\Models\Status;
use App\Models\Team;
use App\Models\Transactions;
use App\Models\Vehicle;
use App\Models\Banks;
use App\Models\InquryType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Inquiry;
use Illuminate\Foundation\Auth\User;
use Yajra\DataTables\Facades\DataTables;

class VehicleReservationController extends Controller
{
    public function index() {
        if(Auth::check()){
            return view('vehicle_reservation.vehicle_reservation');
        }else{
            return view('index');
        }
    }

    public function availableUnitsList(){
        DB::statement("SET SQL_MODE=''");

        $query = Vehicle::with('inventory')
            ->whereNull('deleted_at')
            ->whereHas('inventory', function($subQuery) {
                $subQuery->where('status', 'available');
                $subQuery->where('CS_number_status', 'available');
            })
        ->orderBy('created_at', 'desc')
        ->groupBy('unit');

        $list = $query->get();

        return DataTables::of($list)
        ->addColumn('id', function($data) {
            return encrypt($data->id);
        })
        ->addColumn('unit', function($data) {
            return $data->unit;
        })
        ->addColumn('quantity', function($data) {

            $count = Inventory::with('vehicle')
            ->whereHas('vehicle', function($subQuery) use($data) {
                $subQuery->where('unit', $data->unit);
            })
            ->where('status', 'available')
            ->where('CS_number_status', 'available')
            ->count();

            return $count;
        })
        ->make(true);
    }

    public function getReservedCount(){
        $reserved_status = Status::where('status', 'like', 'Reserved')->first();

        if(Auth::user()->usertype->name === 'SuperAdmin' || Auth::user()->usertype->name === 'General Manager' || Auth::user()->usertype->name === 'Sales Admin Staff'){
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('deleted_at')
            ->whereNotNull('inventory_id')
            ->whereNotNull('reservation_id')
            ->where('reservation_transaction_status', $reserved_status->id);
        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('deleted_at')
            ->whereNotNull('inventory_id')
            ->whereNotNull('reservation_id')
            ->where('reservation_transaction_status', $reserved_status->id)
            ->where('team_id', Auth::user()->team_id);
        }else{
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNull('deleted_at')
            ->whereNotNull('inventory_id')
            ->whereNotNull('reservation_id')
            ->where('reservation_transaction_status', $reserved_status->id)
            ->whereHas('application', function($subQuery) {
                $subQuery->where('created_by', Auth::user()->id);
            });
        }
        $count = $query->count();

        return response()->json(['count' => $count]);
    }

    public function list_pending(Request $request){

        // dd($request->start_date);
        DB::statement("SET SQL_MODE=''");
        $pending_status = Status::where('status', 'like', 'pending')->first();
        if(Auth::user()->usertype->name === 'SuperAdmin' || Auth::user()->usertype->name === 'General Manager' || Auth::user()->usertype->name === 'Sales Admin Staff'){
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
                            ->where('reservation_transaction_status', $pending_status->id)
                            ->whereNull('deleted_at')
                        ->whereNotNull('reservation_id')
                        ->orderBy('updated_at', 'desc');
        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
                            ->where('reservation_transaction_status', $pending_status->id)
                            ->whereNull('deleted_at')
                        ->whereNotNull('reservation_id')
                        ->whereHas('application', function($subQuery) {
                            $subQuery->whereHas('user', function($subQuery) {
                                $subQuery->where('team_id', Auth::user()->team_id);
                            });
                        })
                        ->orderBy('updated_at', 'desc');

        }
        else{
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
                            ->where('reservation_transaction_status', $pending_status->id)
                            ->whereNull('deleted_at')
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

        $list = $query->groupBy('application_id')->get();

        // dd($list->toArray());

        return DataTables::of($list)
        ->editColumn('id', function($data) {
            return encrypt($data->id);
        })

        ->editColumn('unit', function($data) {
            return $data->application->vehicle->unit;
        })

        ->addColumn('client_name', function($data) {
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
            return '';
        })

        ->addColumn('variant', function($data) {
            return $data->application->vehicle->variant;
        })

        ->addColumn('color', function($data) {
            return $data->application->vehicle->color;
        })

        ->addColumn('cs_number', function($data) {
            return '';
        })

        ->addColumn('trans_type', function($data) {
            return $data->inquiry->inquiryType->inquiry_type;
        })
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
            return $data->application->updatedBy->first_name . ' ' . $data->application->updatedBy->last_name;
        })

        ->addColumn('date_assigned', function($data) {
            // return $data->updated_at->format('d/m/Y H:i:s');
            return $data->updated_at ? $data->updated_at->format('M d, Y h:i A') : '-';

        })

        ->make(true);
    }

    public function list_reserved(Request $request){

        // dd($request->start_date);
        $reserved_status = Status::where('status', 'like', 'Reserved')->first();
        if(Auth::user()->usertype->name === 'SuperAdmin' || Auth::user()->usertype->name === 'General Manager' || Auth::user()->usertype->name === 'Sales Admin Staff'){
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->where('reservation_transaction_status', $reserved_status->id)
                        ->whereNotNull('reservation_id')
                        ->orderBy('updated_at', 'desc');

        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->where('reservation_transaction_status', $reserved_status->id)
                        ->whereNotNull('reservation_id')
                        ->where('team_id', Auth::user()->team_id)
                        ->orderBy('updated_at', 'desc');

        }else{
            $query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->where('reservation_transaction_status', $reserved_status->id)
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

            $query->whereBetween('created_at', [$startDate, $endDate]);
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

        ->addColumn('client_name', function($data) {
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
            return $data->inventory->CS_number ?? 'Select cs number';
        })

        ->addColumn('trans_type', function($data) {
            return $data->inquiry->inquiryType->inquiry_type;
        })
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
            return $data->application->updatedBy->first_name . ' ' . $data->application->updatedBy->last_name;
        })

        ->addColumn('date_assigned', function($data) {
            // return $data->updated_at->format('d/m/Y H:i:s');
            return $data->updated_at ? $data->updated_at->format('M d, Y h:i A') : '-';
        })

        ->addColumn('vehicle_id', function($data) {
            return $data->application->vehicle_id ?? '';
        })

        ->make(true);
    }


    public function reservationPerTeam(){
        DB::statement("SET SQL_MODE=''");

        if(Auth::user()->usertype->name === 'SuperAdmin' || Auth::user()->usertype->name === 'General Manager' || Auth::user()->usertype->name === 'Sales Admin Staff'){
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

        ->addColumn('quantity', function($data) {
            $reserved_status = Status::where('status', 'like', 'Reserved')->first();
            $count = Transactions::with(['inquiry', 'inventory', 'application'])
            ->whereNotNull('inventory_id')
            ->whereNull('deleted_at')
            ->where('team_id', $data->id)
            ->whereNotNull('reservation_id')
            ->where('reservation_transaction_status', $reserved_status->id)
            ->count();

            return $count;
        })

        ->make(true);
    }

    public function processing_pending(Request $request){
        try {
            $pending_status = Status::where('status', 'like', 'pending')->first()->id;
            $reserved_status = Status::where('status', 'like', 'Reserved')->first()->id;

            $transaction_pendings = Transactions::where('application_id', $request->id)
            ->where('reservation_transaction_status', $pending_status)
            ->whereNull('deleted_at')
            ->get();

            foreach ($transaction_pendings as $transaction) {
                $transaction->status = $reserved_status;
                $transaction->reservation_transaction_status = $reserved_status;
                $transaction->reservation_date = now();
                $transaction->save();

            }

            $application = Application::findOrFail($transaction->application_id);
            $application->updated_by = Auth::user()->id;
            $application->updated_at = now();
            $application->save();


            return response()->json([
                'success' => true,
                'message' => 'Vehicle reservation request successfully processed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating reservation process: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processing_reserved(Request $request){
        try {

            $reserved_status = Status::where('status', 'like', 'Reserved')->first()->id;
            $pending_for_release_status = Status::where('status', 'like', 'Pending For Release')->first()->id;

            $transaction_pendings = Transactions::where('id', decrypt($request->id))
            ->where('reservation_transaction_status', $reserved_status)
            ->whereNull('deleted_at')
            ->first();

            if (empty($transaction_pendings->inventory_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'CS Number is required for this transaction.'
                ], 500);
            }

            $transaction_pendings->status = $pending_for_release_status;
            $transaction_pendings->reservation_transaction_status = $pending_for_release_status;
            $transaction_pendings->reservation_date = now();
            $transaction_pendings->save();

            $application = Application::findOrFail($transaction_pendings->application_id);
            $application->updated_by = Auth::user()->id;
            $application->updated_at = now();
            $application->save();


            return response()->json([
                'success' => true,
                'message' => 'Vehicle reservation successfully processed to pending for release'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating reservation process: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel_pending(Request $request){
        try {

            $pending_status = Status::where('status', 'like', 'pending')->first()->id;
            $cancel_status = Status::where('status', 'like', 'cancel')->first()->id;

            $transaction_pendings = Transactions::where('application_id', $request->id)
            ->where('reservation_transaction_status', $pending_status)
            ->whereNull('deleted_at')
            ->get();

            foreach ($transaction_pendings as $transaction) {
                $transaction->status = $cancel_status;
                $transaction->reservation_id = null;
                $transaction->reservation_transaction_status = $cancel_status;
                $transaction->reservation_date = null;
                $transaction->save();
            }

            $application = Application::findOrFail($transaction->application_id);
            $application->status_id =  $cancel_status;
            $application->updated_by = Auth::user()->id;
            $application->updated_at = now();
            $application->save();

            return response()->json([
                'success' => true,
                'message' => 'Vehicle reservation request successfully canceled'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error canceling reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCSNumberByVehicleId(Request $request, $vehicle_id) {

        if($request->color === 'Any Color'){
            $vehicle = Vehicle::where('unit', $request->unit)
            ->where('variant', $request->variant)
            ->get()->toArray();

           $vehicle_ids = array_column($vehicle, 'id');
           $inventories = Inventory::with('vehicle')
           ->whereIn('vehicle_id', $vehicle_ids)
           ->where('status', 'Available')
           ->where('CS_number_status', 'Available')
           ->whereIn('incoming_status', ['Invoice', 'On Stock', 'Pull Out', 'In Transit'])->get()->toArray();


        }else{
            $inventories = Inventory::with('transaction')
            ->where('vehicle_id', $vehicle_id)
            ->where('status', 'Available')
            ->where('CS_number_status', 'Available')
            ->whereIn('incoming_status', ['Invoice', 'On Stock', 'Pull Out', 'In Transit'])->get()->toArray();

        }


        return response()->json($inventories);
    }

    public function addCSNumber(Request $request){

        try {
            $transaction = Transactions::FindOrFail(decrypt($request->transaction_id));
            $application = Application::findOrFail($transaction->application_id);
            $application_team = User::findOrFail($application->created_by);

            if ($transaction->inventory_id) {
                $inventory = Inventory::find($transaction->inventory_id);
                if ($inventory) {

                    $inventory->tag = null;
                    $inventory->team_id = null;
                    $inventory->CS_number_status = 'Available';
                    $inventory->status = 'Available';
                    $inventory->updated_by = Auth::id();
                    $inventory->updated_at = now();
                    $inventory->save();
                }
            }

            $inventory = Inventory::where('CS_number', $request->cs_number)
                ->where('status', 'Available')
                ->where('CS_number_status', 'Available')
                ->whereIn('incoming_status', ['Invoice', 'On Stock', 'Pull Out', 'In Transit'])
                ->first();

            if ($inventory) {
                $transaction->team_id = $application_team->team_id;
                $transaction->inventory_id = $inventory->id;
                $transaction->save();


                $inventory->tag = $application->created_by;
                $inventory->team_id = $application_team->team_id;
                $inventory->CS_number_status = 'Reserved';
                $inventory->status = 'Reserved';
                $inventory->updated_by = Auth::id();
                $inventory->updated_at = now();
                $inventory->save();

                return response()->json([
                    'success' => true,
                    'message' => 'CS number successfully added'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'CS number not found'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating CS number: ' . $e->getMessage()
            ], 500);
        }
    }

    public function editUnit(Request $request, $id){
        try {
            $decryptedId = $id;
            $data = Application::with(['user', 'customer', 'vehicle', 'status', 'bank', 'transactions'])
                ->where('id', $decryptedId)
                ->first();

            $firstTransaction = $data->transactions->first();
            if ($firstTransaction) {
                $inquiry = Inquiry::where('id', $firstTransaction->inquiry_id)->first();
                $inquirytype = InquryType::where('id', $inquiry->inquiry_type_id)->first()->inquiry_type;
            }

            $statuses = Status::all();
            $banks = Banks::all();

            return response()->json([
                'firstTransaction' => $firstTransaction,
                'inquiry' => $inquiry,
                'inquirytype' => $inquirytype,
                'application' => $data,
                'statuses' => $statuses,
                'banks' => $banks
            ]);

        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating CS number: ' . $e->getMessage()
            ], 500);
        }


    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                
                'car_unit' => 'required|string',
                'car_variant' => 'required|string',
                'car_color' => 'required|string',
                
            ]);

            // Find the inquiry and related customer and vehicle
            $application = Application::findOrFail($id);
            $transaction_id = Transactions::where('application_id', $application->id )->first();
            $inquiry_id = Inquiry::where('id', $transaction_id->inquiry_id)->first();
            $inquiry = Inquiry::findOrFail($inquiry_id->id);

            $vehicle = Vehicle::firstOrCreate(
                [
                    'unit' => $validated['car_unit'],
                    'variant' => $validated['car_variant'],
                    'color' => $validated['car_color'],
                ],
                [
                    'unit' => $validated['car_unit'],
                    'variant' => $validated['car_variant'],
                    'color' => $validated['car_color'],
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]
            );

            Transactions::where('application_id', $application->id )
                ->whereNull('deleted_at')
                ->update([
                    'inventory_id' => null,
            ]);

            //
            $inquiry->vehicle_id = $vehicle->id;
            $inquiry->updated_by = Auth::id();
            $inquiry->updated_at = now();
            $inquiry->save();

            // Update inquiry data
            $application->vehicle_id = $vehicle->id;
            $application->updated_by = Auth::id();
            $application->updated_at = now();
            $application->save();

            return response()->json([
                'success' => true,
                'message' => 'Reservation Unit updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating reservation unit: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getVehicleReservationCount(){

        DB::statement("SET SQL_MODE=''");
        $pending_status = Status::where('status', 'like', 'pending')->first();
        $reserved_status = Status::where('status', 'like', 'Reserved')->first();
        if(Auth::user()->usertype->name === 'SuperAdmin' || Auth::user()->usertype->name === 'General Manager' || Auth::user()->usertype->name === 'Sales Admin Staff'){
            $pending_query = Transactions::with(['inquiry', 'inventory', 'application'])
                            ->where('reservation_transaction_status', $pending_status->id)
                            ->where('notif_status', 'open')
                            ->whereNull('deleted_at')
                        ->whereNotNull('reservation_id')
                        ->orderBy('updated_at', 'desc');
            $reserved_query = Transactions::with(['inquiry', 'inventory', 'application'])
                    ->whereNull('deleted_at')
                    ->where('notif_status', 'open')
                    ->where('reservation_transaction_status', $reserved_status->id)
                    ->whereNotNull('reservation_id')
                    ->orderBy('updated_at', 'desc');

        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $pending_query = Transactions::with(['inquiry', 'inventory', 'application'])
                            ->where('reservation_transaction_status', $pending_status->id)
                            ->where('notif_status', 'open')
                            ->whereNull('deleted_at')
                        ->whereNotNull('reservation_id')
                        ->whereHas('application', function($subQuery) {
                            $subQuery->whereHas('user', function($subQuery) {
                                $subQuery->where('team_id', Auth::user()->team_id);
                            });
                        })
                        ->orderBy('updated_at', 'desc');
            $reserved_query = Transactions::with(['inquiry', 'inventory', 'application'])
                    ->whereNull('deleted_at')
                    ->where('notif_status', 'open')
                    ->where('reservation_transaction_status', $reserved_status->id)
                    ->whereNotNull('reservation_id')
                    ->where('team_id', Auth::user()->team_id)
                    ->orderBy('updated_at', 'desc');

        }
        else{
            $pending_query = Transactions::with(['inquiry', 'inventory', 'application'])
                            ->where('reservation_transaction_status', $pending_status->id)
                            ->where('notif_status', 'open')
                            ->whereNull('deleted_at')
                        ->whereNotNull('reservation_id')
                        ->whereHas('application', function($subQuery) {
                            $subQuery->where('created_by', Auth::user()->id);
                        })
                        ->orderBy('updated_at', 'desc');
            $reserved_query = Transactions::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->where('notif_status', 'open')
                        ->where('reservation_transaction_status', $reserved_status->id)
                        ->whereNotNull('reservation_id')
                        ->whereHas('application', function($subQuery) {
                            $subQuery->where('created_by', Auth::user()->id);
                        })
                        ->orderBy('updated_at', 'desc');
            

        }

        $pending_count = $pending_query->count();
        $reserved_count = $reserved_query->count();
        return response()->json([
            'success' => true,
            'pending_count' => $pending_count,
            'reserved_count' => $reserved_count
        ]);
    }

    public function updateNotifStatus(Request $request){
        if(!Auth::user()->usertype->name === 'SuperAdmin'){
            $pending_status = Status::where('status', 'like', 'pending')->first();
            $reserved_status = Status::where('status', 'like', 'Reserved')->first();
    
            if($request->buttonTitle === 'Pending'){
            $pending_query = Transactions::with(['inquiry', 'inventory', 'application'])
                                ->where('reservation_transaction_status', $pending_status->id)
                                ->where('notif_status', 'open')
                                ->whereNull('deleted_at')
                            ->whereNotNull('reservation_id')
                            ->orderBy('updated_at', 'desc');
    
                $pending_query->update([
                    'notif_status' => 'closed'
                ]);
            }elseif($request->buttonTitle === 'Reservation'){
                $reserved_query = Transactions::with(['inquiry', 'inventory', 'application'])
                            ->whereNull('deleted_at')
                            ->where('notif_status', 'open')
                            ->where('reservation_transaction_status', $reserved_status->id)
                            ->whereNotNull('reservation_id')
                            ->orderBy('updated_at', 'desc');
    
                $reserved_query->update([
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inventory;
use App\Models\Transactions;
use App\Models\User;
use App\Models\Usertype;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\Vehicle;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use Illuminate\Support\Facades\Log;

class VehicleInventoryController extends Controller
{
    public function index() {

        if(Auth::check()){
            return view('vehicle_inventory.vehicle_inventory');
        }else{
            return view('index');
        }
    }

    public function inventoryIncomingList(Request $request){

         // dd($request->start_date);
         $query = Inventory::with(['vehicle', 'transaction'])
                        ->whereIn('incoming_status', ['Invoice', 'Pull Out', 'In Transit'])
                         ->whereNull('deleted_at')
                         ->where('status', '<>', 'Released')
                        //  ->orderBy('updated_at', 'desc');
                        ->orderBy('actual_invoice_date', 'desc');


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
             return $data->vehicle->unit;
         })

         ->editColumn('color', function($data) {
             return $data->vehicle->color;
         })

         ->editColumn('category', function($data) {
             return $data->vehicle->category ?? '';
         })

         ->editColumn('cs_number', function($data) {
             return $data->CS_number;
         })

         ->editColumn('model', function($data) {
             return $data->vehicle->variant;
         })

         ->editColumn('ear_mark', function($data) {
             return '';
         })
         ->editColumn('actual_invoice_date', function($data) {
            return $data->actual_invoice_date ? \Carbon\Carbon::parse($data->actual_invoice_date)->format('M d, Y h:i A') : '';
         })
         ->editColumn('delivery_date', function($data) {
            return $data->delivery_date ? \Carbon\Carbon::parse($data->delivery_date)->format('M d, Y h:i A') : '';

         })

         ->addColumn('updated_at', function($user){
            return $user->updated_at->format('M d, Y h:i A');
        })



         ->addColumn('tags', function($data) {

            $tag = $data->tag;

            if($tag){
                $user = User::find($tag);
                return $user->first_name . ' ' . $user->last_name;
            }else{
                return '';
            }
        })

         ->addColumn('invoice_number', function($data) {
            return $data->invoice_number;
        })



         ->make(true);

    }

    public function inventoryList(Request $request){

         // dd($request->start_date);
         $query = Inventory::with(['vehicle', 'transaction'])
                        ->whereIn('incoming_status', ['On Stock', 'For Swapping', 'Reserved', 'Freeze', 'Ear Mark'])
                         ->whereNull('deleted_at')
                         ->where('status', '<>', 'Released')
                         ->orderBy('actual_invoice_date', 'desc');


         if ($request->has('date_range') && !empty($request->date_range)) {
             [$startDate, $endDate] = explode(' to ', $request->date_range);
             $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
             $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

             $query->whereBetween('updated_at', [$startDate, $endDate]);
         }

         $list = $query->get();

         return DataTables::of($list)
         ->editColumn('id', function($data) {
             return encrypt($data->id);
         })

         ->editColumn('unit', function($data) {
             return $data->vehicle->unit;
         })

         ->editColumn('color', function($data) {
             return $data->vehicle->color;
         })

        ->editColumn('category', function($data) {
            return $data->vehicle->category ?? '';
        })

         ->editColumn('cs_number', function($data) {
             return $data->CS_number;
         })

         ->editColumn('model', function($data) {
             return $data->vehicle->variant;
         })

         ->editColumn('ear_mark', function($data) {
             return '';
         })

         ->addColumn('updated_at', function($user){
            return $user->updated_at->format('M d, Y h:i A');
        })

         ->addColumn('tags', function($data) {

            $tag = $data->tag;

            if($tag){
                $user = User::find($tag);
                return $user->first_name . ' ' . $user->last_name;
            }else{
                return '';
            }

        })

         ->addColumn('invoice_number', function($data) {
            return $data->invoice_number;
        })

        ->editColumn('actual_invoice_date', function($data) {
            return $data->actual_invoice_date ? \Carbon\Carbon::parse($data->actual_invoice_date)->format('M d, Y h:i A') : '';
         })
         ->editColumn('delivery_date', function($data) {
            return $data->delivery_date ? \Carbon\Carbon::parse($data->delivery_date)->format('M d, Y h:i A') : '';

         })

         ->make(true);

    }

    public function incomingUnitsList(Request $request){

        $query = Status::whereIn('status', ['Invoice', 'Pull Out', 'In Transit'])
                        ->get();

        return DataTables::of($query)
        ->editColumn('id', function($data) {
            return encrypt($data->id);
        })
        ->editColumn('for', function($data) {
            return $data->status;
        })
        ->editColumn('quantity', function($data) {

            $inventoryCount = Inventory::where('incoming_status', $data->status)
                                 ->whereNull('deleted_at')
                                 ->where('status', '<>', 'Released')
                                 ->count();
            return $inventoryCount;
        })

        ->make(true);

    }

    public function tagsPerTeam(){
        DB::statement("SET SQL_MODE=''");

        $query = Team::whereNull('deleted_at');


        $list = $query->get();

        return DataTables::of($list)
        ->addColumn('id', function($data) {
            return encrypt($data->id);
        })
        ->addColumn('team', function($data) {
            return $data->name;
        })

        ->addColumn('quantity', function($data) {
            $tagUser =  Inventory::whereNull('deleted_at')
                                 ->where('team_id', $data->id)
                                 ->where('status', 'Ear Mark')
                                 ->count();
            return $tagUser;
        })

        ->make(true);
    }

    public function getTotalInventory(){

        $query = Inventory::with(['vehicle']);
        $totalInventory = $query->where('status', '<>', 'Released')->count();

        return response()->json(['totalInventory' => $totalInventory]);
    }

    public function store(Request $request)
    {
        // Validate input data
        $request->validate([
            'unit' => 'required|string|max:255',
            'variant' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'category' => 'required|string|max:255',
        ]);

        // Check for duplicate entry
        $exists = Vehicle::where('unit', $request->unit)
            ->where('variant', $request->variant)
            ->where('color', $request->color)
            ->where('category', $request->category)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This vehicle already exists, You may proceed to the Inventory form.'
            ], 422);
        }

        // Insert new vehicle data
        Vehicle::create([
            'unit' => $request->unit,
            'variant' => $request->variant,
            'color' => $request->color,
            'category' => $request->category,
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_by' => Auth::id(),
            'updated_at' => now(), // Initially null
        ]);

        // Redirect with success message
        return response()->json([
            'success' => true,
            'message' => 'Vehicle added successfully!'
        ]);
    }

    public function inventoryStore(Request $request) {
        // Validate the incoming request data
        $request->validate([
            'car_unit' => 'required|string|max:255',
            'car_variant' => 'required|string|max:255',
            'car_color' => 'required|string|max:255',
            'year_model' => 'required|string|max:255',
            'cs_number' => 'required|string|max:255|unique:inventory,CS_number',
            'actual_invoice_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'invoice_number' => 'required|string|max:255',
            // Add other fields as necessary
        ]);

        $vehicle = Vehicle::where('unit', $request->car_unit)
                           ->where('variant', $request->car_variant)
                           ->where('color', $request->car_color)
                           ->first()->id;

        if($request->input('delivery_date')){
            $deliveryDate = Carbon::parse($request->input('delivery_date'));
            $currentDate = Carbon::now();
            $age = (int) $currentDate->diffInDays($deliveryDate);
        }


        // Create a new inventory record
        Inventory::create([
            'vehicle_id' => $vehicle,
            'year_model' => $request->year_model,
            'CS_number' => $request->cs_number,
            'actual_invoice_date' => $request->actual_invoice_date,
            'delivery_date' => $request->delivery_date ? $request->delivery_date : null,
            'invoice_number' => $request->invoice_number,
            'age' => $request->input('delivery_date') ? $age : null,
            // Add other fields as necessary
            'created_by' => Auth::id(), // Assuming you want to track who created the inventory
            'updated_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
            'remarks' => $request->remarks,
        ]);

        return response()->json(['success' => true, 'message' => 'New unit added to inventory!']);
    }

    public function editInventory(Request $request){
       $inventory = Inventory::findOrFail(decrypt($request->id));
       $vehicle = Vehicle::findOrFail($request->vehicleId);
       return response()->json(['inventory' => $inventory, 'vehicle' => $vehicle]);
    }

    public function updateInventory(Request $request){

        try{

            $vehicle = Vehicle::where('unit', $request->car_unit)
                               ->where('variant', $request->car_variant)
                               ->where('color', $request->car_color)
                               ->first()->id;


            $inventory = Inventory::findOrFail(decrypt($request->id));


            $deliveryDate = Carbon::parse($request->input('delivery_date'));
            $currentDate = Carbon::now();
            $age = (int) $currentDate->diffInDays($deliveryDate);

            $inventory->update([
            'vehicle_id' => $vehicle,
            'year_model' => $request->year_model,
            'CS_number' => $request->cs_number,
            'actual_invoice_date' => $request->actual_invoice_date,
            'delivery_date' => $request->delivery_date ? $request->delivery_date : null ,
            'invoice_number' => $request->invoice_number,
            'remarks' => $request->remarks,
            'age' => $request->delivery_date? $age : null,
            'updated_by' => Auth::id(),
            'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Inventory updated successfully!']);
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAgent(Request $request){
        $usertype = Usertype::where('name', 'Agent')->first()->id;

        if(Auth::user()->usertype->name === 'Group Manager'){
            $agent = User::where('usertype_id', $usertype)
                        ->whereNull('deleted_at')
                        ->where('status', 'Active')
                        ->where('team_id', Auth::user()->team_id)
                        ;

        }else{
            $agent = User::where('usertype_id', $usertype)
            ->whereNull('deleted_at')
            ->where('status', 'Active')
            ->get();
        }



        return response()->json($agent);
    }


    public function getIncomingStatus(Request $request){

        $inventory = Inventory::findOrFail(decrypt($request->id));
        if(in_array($inventory->incoming_status, ['On Stock', 'For Swapping', 'Reserved', 'Freeze'])){
            $data = Status::whereIn('status', ['On Stock','For Swapping', 'Reserved', 'Freeze'])->orderBy('id', 'asc')
            ->get();
        }else{
            $data = Status::whereIn('status', ['On Stock', 'In Transit', 'Invoice', 'Pull Out'])->orderBy('id', 'asc')
            ->get();
        }

         return response()->json($data);
    }

    public function updateInventoryStatus(Request $request){
        try{

            $inventory = Inventory::findOrFail(decrypt($request->id));
            $inventory->incoming_status = $request->status;
            $inventory->updated_by = Auth::id();
            $inventory->updated_at = now();
            $inventory->save();
            return response()->json(['success' => true, 'message' => 'Inventory status updated successfully!']);

        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateTags(Request $request){
        try{

            $user = User::where('id', $request->earmark)->first();

            $inventory = Inventory::findOrFail(decrypt($request->id));
            $inventory->tag = $request->earmark;
            $inventory->team_id = $user->team_id;
            $inventory->status = 'Ear Mark';
            $inventory->updated_by = Auth::id();
            $inventory->updated_at = now();
            $inventory->save();

        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}

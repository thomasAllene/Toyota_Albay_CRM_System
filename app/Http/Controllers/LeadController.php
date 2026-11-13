<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Customer;
use App\Models\Inquiry;
use App\Models\Province;
use App\Models\Status;
use App\Models\Team;
use App\Models\Transactions;
use App\Models\Vehicle;
use App\Models\InquryType;
use App\Models\User;
use App\Models\Usertype;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class LeadController extends Controller
{
    public function index()
    {
        if(Auth::check()){
            return view('leads.leads');
        }else{
            return view('index');
        }
    }

    public function individualList(Request $request){

        // dd($request->start_date);
        $status = Status::where('status', 'like', 'Processed')->first()->id;

        if(Auth::user()->usertype->name === 'SuperAdmin'){
        $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '0')
                        ->whereHas('inquiryType', function($subQuery) {
                            $subQuery->where('inquiry_type', 'Individual');
                        })
                        ->where('status_id', '<>', $status)
                        ->orderBy('created_at', 'desc');

        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '0')
                        ->whereHas('inquiryType', function($subQuery) {
                            $subQuery->where('inquiry_type', 'Individual');
                        })
                        ->whereHas('user', function($subQuery) {
                            $subQuery->where('team_id', Auth::user()->team_id);
                        })
                        ->where('status_id', '<>', $status)
                        ->orderBy('created_at', 'desc');
        }
        else{
            $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '0')
                        ->where('created_by', Auth::user()->id)
                        ->whereHas('inquiryType', function($subQuery) {
                            $subQuery->where('inquiry_type', 'Individual');
                        })
                        ->where('status_id', '<>', $status)
                        ->orderBy('created_at', 'desc');
        }


        if ($request->has('date_range') && !empty($request->date_range)) {
            $dateRange = preg_replace('/\s*to\s*/i', ' to ', $request->date_range); // Normalize 'to' and 'TO'
            [$startDate, $endDate] = explode(' to ', $dateRange);

            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if($request->has('group') && !empty($request->group)){
            $query->whereHas('user', function($subQuery) use ($request) {
                $subQuery->where('team_id', $request->group);
            });
        }

        if($request->has('agent') && !empty($request->agent)){
            $query->where('created_by', $request->agent);
        }

        if($request->has('source') && !empty($request->source)){
            $query->whereHas('customer', function($subQuery) use ($request) {
                $subQuery->where('source', $request->source);
            });
        }


        $list = $query->get();

        return DataTables::of($list)
        ->addColumn('id', function($data) {
            return encrypt($data->id);
        })

        ->addColumn('team', function($data) {
            $team = Team::where('id',  $data->user->team_id)->first();
            return $team->name ?? '';
        })

        ->addColumn('agent', function($data) {
            return $data->user->first_name . ' ' . $data->user->last_name;
        })

        ->addColumn('client_name', function($data) {
            return $data->customer->customer_first_name . ' ' . $data->customer->customer_last_name;
        })

        ->addColumn('contact_number', function($data) {
            return $data->customer->contact_number;
        })

        ->addColumn('address', function($data) {
            return $data->customer->address;
        })

        ->addColumn('source', function($data) {
            return $data->customer->source;
        })

        ->addColumn('unit', function($data) {
            return $data->vehicle->unit;
        })

        ->addColumn('variant', function($data) {
            return $data->vehicle->variant;
        })

        ->addColumn('color', function($data) {
            return $data->vehicle->color;
        })

        ->addColumn('status', function($data) {
            return $data->status->status;
        })

        // ->editColumn('created_at', function($data) {
        //     return $data->created_at->format('d/m/Y H:i:s');
        // })

        ->editColumn('created_at', function($data) {
            return $data->created_at ? $data->created_at->format('M d, Y h:i A') : '-';
        })


        ->make(true);
    }

    public function fleetList(Request $request){

        // dd($request->start_date);
        $status = Status::where('status', 'like', 'Processed')->first()->id;

        if(Auth::user()->usertype->name === 'SuperAdmin'){
            $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '0')
                        ->whereHas('inquiryType', function($subQuery) {
                            $subQuery->where('inquiry_type', 'Fleet');
                        })
                        ->where('status_id', '<>', $status)
                        ->orderBy('created_at', 'desc');
        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '0')
                        ->whereHas('inquiryType', function($subQuery) {
                            $subQuery->where('inquiry_type', 'Fleet');
                        })
                        ->whereHas('user', function($subQuery) {
                            $subQuery->where('team_id', Auth::user()->team_id);
                        })
                        ->where('status_id', '<>', $status)
                        ->orderBy('created_at', 'desc');
        }
        else{
            $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '0')
                        ->where('created_by', Auth::user()->id)
                        ->whereHas('inquiryType', function($subQuery) {
                            $subQuery->where('inquiry_type', 'Fleet');
                        })
                        ->where('status_id', '<>', $status)
                        ->orderBy('created_at', 'desc');
        }

        if ($request->has('date_range') && !empty($request->date_range)) {
             $dateRange = preg_replace('/\s*to\s*/i', ' to ', $request->date_range); // Normalize 'to' and 'TO'
            [$startDate, $endDate] = explode(' to ', $dateRange);

            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if($request->has('group') && !empty($request->group)){
            $query->whereHas('user', function($subQuery) use ($request) {
                $subQuery->where('team_id', $request->group);
            });
        }

        if($request->has('agent') && !empty($request->agent)){
            $query->where('created_by', $request->agent);
        }

        if($request->has('source') && !empty($request->source)){
            $query->whereHas('customer', function($subQuery) use ($request) {
                $subQuery->where('source', $request->source);
            });
        }

        $list = $query->get();

        return DataTables::of($list)
        ->addColumn('id', function($data) {
            return encrypt($data->id);
        })

        ->addColumn('team', function($data) {
            $team = Team::where('id',  $data->user->team_id)->first();
            return $team->name ?? '';
        })

        ->addColumn('agent', function($data) {
            return $data->user->first_name . ' ' . $data->user->last_name;
        })

        ->addColumn('client_name', function($data) {
            return $data->customer->company_name;
        })

        ->addColumn('contact_number', function($data) {
            return $data->customer->contact_number;
        })

        ->addColumn('address', function($data) {
            return $data->customer->address;
        })

        ->addColumn('source', function($data) {
            return $data->customer->source;
        })

        ->addColumn('unit', function($data) {
            return $data->vehicle->unit;
        })

        ->addColumn('variant', function($data) {
            return $data->vehicle->variant;
        })

        ->addColumn('color', function($data) {
            return $data->vehicle->color;
        })

        ->addColumn('status', function($data) {
            return $data->status->status;
        })

        // ->editColumn('created_at', function($data) {
        //     return $data->created_at->format('d/m/Y H:i:s');
        // })

        ->editColumn('created_at', function($data) {
            return $data->created_at ? $data->created_at->format('M d, Y h:i A') : '-';
        })


        ->make(true);
    }

    public function companyList(Request $request){

        // dd($request->start_date);
        $status = Status::where('status', 'like', 'Processed')->first()->id;
        if(Auth::user()->usertype->name === 'SuperAdmin'){
            $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '0')
                        ->whereHas('inquiryType', function($subQuery) {
                            $subQuery->where('inquiry_type', 'Company');
                        })
                        ->where('status_id', '<>', $status)
                        ->orderBy('created_at', 'desc');
        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '0')
                        ->whereHas('inquiryType', function($subQuery) {
                            $subQuery->where('inquiry_type', 'Company');
                        })
                        ->whereHas('user', function($subQuery) {
                            $subQuery->where('team_id', Auth::user()->team_id);
                        })
                        ->where('status_id', '<>', $status)
                        ->orderBy('created_at', 'desc');
        }
        else{
            $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '0')
                        ->where('created_by', Auth::user()->id)
                        ->whereHas('inquiryType', function($subQuery) {
                            $subQuery->where('inquiry_type', 'Company');
                        })
                        ->where('status_id', '<>', $status)
                        ->orderBy('created_at', 'desc');
        }



        if ($request->has('date_range') && !empty($request->date_range)) {
             $dateRange = preg_replace('/\s*to\s*/i', ' to ', $request->date_range); // Normalize 'to' and 'TO'
            [$startDate, $endDate] = explode(' to ', $dateRange);

            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if($request->has('group') && !empty($request->group)){
            $query->whereHas('user', function($subQuery) use ($request) {
                $subQuery->where('team_id', $request->group);
            });
        }

        if($request->has('agent') && !empty($request->agent)){
            $query->where('created_by', $request->agent);
        }

        if($request->has('source') && !empty($request->source)){
            $query->whereHas('customer', function($subQuery) use ($request) {
                $subQuery->where('source', $request->source);
            });
        }

        $list = $query->get();

        return DataTables::of($list)
        ->addColumn('id', function($data) {
            return encrypt($data->id);
        })

        ->addColumn('team', function($data) {
            $team = Team::where('id',  $data->user->team_id)->first();
            return $team->name ?? '';
        })

        ->addColumn('agent', function($data) {
            return $data->user->first_name . ' ' . $data->user->last_name;
        })

        ->addColumn('client_name', function($data) {
            return $data->customer->company_name;
        })

        ->addColumn('contact_number', function($data) {
            return $data->customer->contact_number;
        })

        ->addColumn('address', function($data) {
            return $data->customer->address;
        })

        ->addColumn('source', function($data) {
            return $data->customer->source;
        })

        ->addColumn('unit', function($data) {
            return $data->vehicle->unit;
        })

        ->addColumn('variant', function($data) {
            return $data->vehicle->variant;
        })

        ->addColumn('color', function($data) {
            return $data->vehicle->color;
        })

        ->addColumn('status', function($data) {
            return $data->status->status;
        })

        // ->editColumn('created_at', function($data) {
        //     return $data->created_at->format('d/m/Y H:i:s');
        // })

        ->editColumn('created_at', function($data) {
            return $data->created_at ? $data->created_at->format('M d, Y h:i A') : '-';
        })


        ->make(true);
    }

    public function governmentList(Request $request){

        // dd($request->start_date);
        $status = Status::where('status', 'like', 'Processed')->first()->id;
        if(Auth::user()->usertype->name === 'SuperAdmin'){
            $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '0')
                        ->whereHas('inquiryType', function($subQuery) {
                            $subQuery->where('inquiry_type', 'Government');
                        })
                        ->where('status_id', '<>', $status)
                        ->orderBy('created_at', 'desc');
        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '0')
                        ->whereHas('inquiryType', function($subQuery) {
                            $subQuery->where('inquiry_type', 'Government');
                        })
                        ->whereHas('user', function($subQuery) {
                            $subQuery->where('team_id', Auth::user()->team_id);
                        })
                        ->where('status_id', '<>', $status)
                        ->orderBy('created_at', 'desc');
        }
        else{
            $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '0')
                        ->where('created_by', Auth::user()->id)
                        ->whereHas('inquiryType', function($subQuery) {
                            $subQuery->where('inquiry_type', 'Government');
                        })
                        ->where('status_id', '<>', $status)
                        ->orderBy('created_at', 'desc');
        }

        if ($request->has('date_range') && !empty($request->date_range)) {
             $dateRange = preg_replace('/\s*to\s*/i', ' to ', $request->date_range); // Normalize 'to' and 'TO'
            [$startDate, $endDate] = explode(' to ', $dateRange);

            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if($request->has('group') && !empty($request->group)){
            $query->whereHas('user', function($subQuery) use ($request) {
                $subQuery->where('team_id', $request->group);
            });
        }

        if($request->has('agent') && !empty($request->agent)){
            $query->where('created_by', $request->agent);
        }

        if($request->has('source') && !empty($request->source)){
            $query->whereHas('customer', function($subQuery) use ($request) {
                $subQuery->where('source', $request->source);
            });
        }

        $list = $query->get();

        return DataTables::of($list)
        ->addColumn('id', function($data) {
            return encrypt($data->id);
        })

        ->addColumn('team', function($data) {
            $team = Team::where('id',  $data->user->team_id)->first();
            return $team->name ?? '';
        })

        ->addColumn('agent', function($data) {
            return $data->user->first_name . ' ' . $data->user->last_name;
        })

        ->addColumn('client_name', function($data) {
            return $data->customer->department_name;
        })

        ->addColumn('contact_number', function($data) {
            return $data->customer->contact_number;
        })

        ->addColumn('address', function($data) {
            return $data->customer->address;
        })

        ->addColumn('source', function($data) {
            return $data->customer->source;
        })

        ->addColumn('unit', function($data) {
            return $data->vehicle->unit;
        })

        ->addColumn('variant', function($data) {
            return $data->vehicle->variant;
        })

        ->addColumn('color', function($data) {
            return $data->vehicle->color;
        })

        ->addColumn('status', function($data) {
            return $data->status->status;
        })

        // ->editColumn('created_at', function($data) {
        //     return $data->created_at->format('d/m/Y H:i:s');
        // })

        ->editColumn('created_at', function($data) {
            return $data->created_at ? $data->created_at->format('M d, Y h:i A') : '-';
        })


        ->make(true);
    }


    public function destroy(){
        try {

            $customer = Customer::where('inquiry_id', decrypt(request()->id))->first();
            $customer->updated_by = Auth::user()->id;
            $customer->save();
            $customer->delete();

            $inquiry = Inquiry::findOrFail(decrypt(request()->id));
            $inquiry->updated_by = Auth::user()->id;
            $inquiry->save();
            $inquiry->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lead successfully deleted'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting lead: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProvince(){
        $data = Province::all();

        return response()->json($data);
    }

    public function getUnit(){
        $data = Vehicle::select('unit')
        ->whereNull('deleted_at')
        ->groupBy('unit')
        ->get();


        return response()->json($data);
    }

    public function getVariantsAndColors(Request $request)
    {
        $unit = $request->input('unit');
        $vehicles = Vehicle::where('unit', $unit)
        ->get();

        $variants = $vehicles->pluck('variant')->unique()->values()->toArray();
        $colors = $vehicles->pluck('color')->unique()->values()->toArray();

        return response()->json([
            'variants' => $variants,
            'colors' => $colors,
        ]);
    }

    public function getVariants(Request $request)
    {
        $unit = $request->input('unit');
        $vehicles = Vehicle::where('unit', $unit)
        ->get();

        $variants = $vehicles->pluck('variant')->unique()->values()->toArray();


        return response()->json([
            'variants' => $variants,
        ]);
    }

    public function getColor(Request $request)
    {
        $unit = $request->input('unit');
        $variant = $request->input('variant');
        $vehicles = Vehicle::where('variant', $variant)
        ->get();

        $colors = $vehicles->pluck('color')->unique()->values()->toArray();

        return response()->json([
            'colors' => $colors,
        ]);
    }

    public function edit($id)
    {
        // Fetch the inquiry data by ID
       $decryptedId = decrypt($id);
       $data = Inquiry::with([ 'user', 'customer', 'vehicle', 'inquiryType', 'status'])->where('id', $decryptedId)->first();
        return response()->json($data);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'nullable',
                'last_name' => 'nullable',
                'government' => 'nullable',
                'company' => 'nullable',
                'fleet' => 'nullable',
                'gender' => 'nullable',
                'birthdate' => 'nullable|date',
                'age' => 'nullable',
                'mobile_number' => 'required|string',
                'car_unit' => 'required|string',
                'car_variant' => 'required|string',
                'car_color' => 'required|string',
                'transaction' => 'required|string',
                'source' => 'required|string',
                'additional_info' => 'nullable|string',
                'address' => 'required',
                'inquiry_type_id' => 'required',
                'category' => 'required',
                'quantity' => 'nullable',

            ]);

            $ExistingCustomer = Customer::whereNull('deleted_at')
                                        ->where('customer_first_name', $validated['first_name'])
                                        ->where('customer_last_name', $validated['last_name'])
                                        ->first();

            if($ExistingCustomer){
                $processed_status = Status::where('status', 'like', 'Processed')->first()->id;


                $existingInquiry = Inquiry::whereNull('deleted_at')
                                          ->where('is_dispute', '0')
                                          ->where('customer_id', $ExistingCustomer->id)
                                          ->where('created_by', '<>', Auth::user()->id)
                                          ->where('status_id', '<>', $processed_status)
                                          ->first();

                if( $existingInquiry){
                    $usertype = Usertype::where('name', 'like', 'Agent')->first()->id;

                    $ActiveAgent = User::where('id', $existingInquiry->created_by)
                    ->where('usertype_id',  $usertype)
                    ->where('status', 'Active')
                    ->first();

                    if($ActiveAgent){

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

                        $pending_status = Status::where('status', 'like', 'pending')->first()->id;

                        $inquiry = new Inquiry();
                        $inquiry->inquiry_type_id =  $validated['inquiry_type_id'];
                        $inquiry->customer_id = $ExistingCustomer->id;
                        $inquiry->vehicle_id = $vehicle->id;
                        $inquiry->quantity = $validated['quantity'];
                        $inquiry->transaction = $validated['transaction'];
                        $inquiry->category = $validated['category'];
                        $inquiry->remarks = $validated['additional_info'];
                        $inquiry->date = now()->format('d/m/y'); // Numeric date format: dd/mm/yy
                        $inquiry->status_id = $pending_status;
                        $inquiry->status_updated_by = Auth::id();
                        $inquiry->status_updated_at = now();
                        $inquiry->created_at = now();
                        $inquiry->is_dispute = 1;
                        $inquiry->created_by = Auth::id();
                        $inquiry->updated_by = Auth::id();
                        $inquiry->save();

                        return response()->json([
                            'success' => true,
                            'message' => 'The primary agent for this inquiry has been assigned, and it is now marked as disputed'
                        ]);

                    }else{

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

                        $pending_status = Status::where('status', 'like', 'pending')->first()->id;

                        $inquiry = new Inquiry();
                        $inquiry->inquiry_type_id =  $validated['inquiry_type_id'];
                        $inquiry->customer_id = $ExistingCustomer->id;
                        $inquiry->vehicle_id = $vehicle->id;
                        $inquiry->quantity = $validated['quantity'];
                        $inquiry->transaction = $validated['transaction'];
                        $inquiry->category = $validated['category'];
                        $inquiry->remarks = $validated['additional_info'];
                        $inquiry->date = now()->format('d/m/y'); // Numeric date format: dd/mm/yy
                        $inquiry->status_id = $pending_status;
                        $inquiry->status_updated_by = Auth::id();
                        $inquiry->status_updated_at = now();
                        $inquiry->created_at = now();
                        $inquiry->is_dispute = 0;
                        $inquiry->created_by = Auth::id();
                        $inquiry->updated_by = Auth::id();
                        $inquiry->save();

                        return response()->json([
                            'success' => true,
                            'message' => 'Inquiry created successfully'
                        ]);

                    }

                }else{

                    $customer = new Customer();
                    $customer->inquiry_type_id =  $validated['inquiry_type_id'];
                    $customer->customer_first_name = $validated['first_name'];
                    $customer->customer_last_name = $validated['last_name'];
                    $customer->department_name = $validated['government'];
                    $customer->company_name = $validated['company'] ?  $validated['company'] : $validated['fleet'];
                    $customer->contact_number = $validated['mobile_number'];
                    $customer->gender = $validated['gender'];
                    $customer->address = $validated['address'];
                    $customer->birthdate = $validated['birthdate'];
                    $customer->age = $validated['age'];
                    $customer->source = $validated['source'];
                    $customer->created_by = Auth::id();
                    $customer->updated_by = Auth::id();
                    $customer->save();

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

                    $approved_status = Status::where('status', 'like', 'approved')->first()->id;
                    $pending_status = Status::where('status', 'like', 'pending')->first()->id;

                    $inquiry = new Inquiry();
                    $inquiry->inquiry_type_id =  $validated['inquiry_type_id'];
                    $inquiry->customer_id = $customer->id;
                    $inquiry->vehicle_id = $vehicle->id;
                    $inquiry->quantity = $validated['quantity'];
                    $inquiry->transaction = $validated['transaction'];
                    $inquiry->category = $validated['category'];
                    $inquiry->remarks = $validated['additional_info'];
                    $inquiry->date = now()->format('F d'); // Month name day
                    $inquiry->status_id = $pending_status;
                    $inquiry->status_updated_by = Auth::id();
                    $inquiry->status_updated_at = now();
                    $inquiry->created_at = now();
                    $inquiry->is_dispute = 0;
                    $inquiry->created_by = Auth::id();
                    $inquiry->updated_by = Auth::id();
                    $inquiry->save();

                    $customer = Customer::findOrFail($customer->id);
                    $customer->inquiry_id = $inquiry->id;
                    $customer->save();



                    return response()->json([
                        'success' => true,
                        'message' => 'Inquiry created successfully'
                    ]);
                }

            }else{

                $customer = new Customer();
                $customer->inquiry_type_id =  $validated['inquiry_type_id'];
                $customer->customer_first_name = $validated['first_name'];
                $customer->customer_last_name = $validated['last_name'];
                $customer->department_name = $validated['government'];
                $customer->company_name = $validated['company'] ?  $validated['company'] : $validated['fleet'];
                $customer->contact_number = $validated['mobile_number'];
                $customer->gender = $validated['gender'];
                $customer->address = $validated['address'];
                $customer->birthdate = $validated['birthdate'];
                $customer->age = $validated['age'];
                $customer->source = $validated['source'];
                $customer->created_by = Auth::id();
                $customer->updated_by = Auth::id();
                $customer->save();

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

                $approved_status = Status::where('status', 'like', 'approved')->first()->id;
                $pending_status = Status::where('status', 'like', 'pending')->first()->id;

                $inquiry = new Inquiry();
                $inquiry->inquiry_type_id =  $validated['inquiry_type_id'];
                $inquiry->customer_id = $customer->id;
                $inquiry->vehicle_id = $vehicle->id;
                $inquiry->quantity = $validated['quantity'];
                $inquiry->transaction = $validated['transaction'];
                $inquiry->category = $validated['category'];
                $inquiry->remarks = $validated['additional_info'];
                $inquiry->date = now()->format('d/m/y'); // Numeric date format: dd/mm/yy
                $inquiry->status_id = $pending_status;
                $inquiry->status_updated_by = Auth::id();
                $inquiry->status_updated_at = now();
                $inquiry->created_at = now();
                $inquiry->is_dispute = 0;
                $inquiry->created_by = Auth::id();
                $inquiry->updated_by = Auth::id();
                $inquiry->save();

                $customer = Customer::findOrFail($customer->id);
                $customer->inquiry_id = $inquiry->id;
                $customer->save();



                return response()->json([
                    'success' => true,
                    'message' => 'Inquiry created successfully'
                ]);

            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating inquiry: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'nullable',
                'last_name' => 'nullable',
                'company' => 'nullable',
                'fleet' => 'nullable',
                'gender' => 'nullable',
                'birthdate' => 'nullable|date',
                'age' => 'nullable',
                'mobile_number' => 'required|string',
                'car_unit' => 'required|string',
                'car_variant' => 'required|string',
                'car_color' => 'required|string',
                'transaction' => 'required|string',
                'source' => 'required|string',
                'address' => 'required',
                'category' => 'required',
                'quantity' => 'nullable',
                'government' => 'nullable',
            ]);

            // Find the inquiry and related customer and vehicle
            $inquiry = Inquiry::findOrFail($id);
            $customer = Customer::findOrFail($inquiry->customer_id);

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

            // Update customer data
            $customer->customer_first_name = $validated['first_name'];
            $customer->customer_last_name = $validated['last_name'];
            $customer->department_name = $validated['government'];
            $customer->company_name = $validated['company'] ?  $validated['company'] : $validated['fleet'];
            $customer->contact_number = $validated['mobile_number'];
            $customer->gender = $validated['gender'];
            $customer->address = $validated['address'];
            $customer->birthdate = $validated['birthdate'];
            $customer->age = $validated['age'];
            $customer->source = $validated['source'];
            $customer->updated_by = Auth::id();
            $customer->updated_at = now();
            $customer->save();

            $approved_status = Status::where('status', 'like', 'approved')->first()->id;
            $pending_status = Status::where('status', 'like', 'pending')->first()->id;

            // Update inquiry data
            $inquiry->vehicle_id = $vehicle->id;
            $inquiry->transaction = $validated['transaction'];
            $inquiry->category = $validated['category'];
            $inquiry->quantity = $validated['quantity'];
            $inquiry->updated_by = Auth::id();
            $inquiry->updated_at = now();
            $inquiry->status_id = $pending_status;
            $inquiry->save();


            return response()->json([
                'success' => true,
                'message' => 'Inquiry updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating inquiry: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processing(Request $request){
        try {

            $status = Status::where('status', 'like', 'Processed')->first()->id;
            $inquiry = Inquiry::findOrFail(decrypt($request->id));
            $inquiry->status_id =  $status;
            $inquiry->status_updated_by = Auth::user()->id;
            $inquiry->status_updated_at = now();
            $inquiry->save();


            if ($inquiry->status_id === $status ) {
                // Add the inquiry_id to the transactions table
                $pending_status = Status::where('status', 'like', 'pending')->first();

                $application = new Application();
                $application->customer_id = $inquiry->customer_id;
                $application->vehicle_id = $inquiry->vehicle_id;
                $application->transaction = $inquiry->transaction;
                $application->status_id = $pending_status->id;
                $application->created_by = Auth::id();
                $application->updated_by = Auth::id();
                $application->save();
                // Update the transaction table's application_id with the latest inserted application's id
                if ($inquiry->quantity > 1) {
                    for ($i = 0; $i < $inquiry->quantity; $i++) {

                        $transaction = new Transactions();
                        $transaction->inquiry_id = $inquiry->id;
                        $transaction->status = $pending_status->id;
                        $transaction->application_id = $application->id;
                        $transaction->status =  $application->status_id;
                        $transaction->application_transaction_date = now();
                        $transaction->transaction_updated_date = now();
                        $transaction->save();
                    }
                }else{

                    $transaction = new Transactions();
                    $transaction->inquiry_id = $inquiry->id;
                    $transaction->status = $pending_status->id;
                    $transaction->application_id = $application->id;
                    $transaction->status =  $application->status_id;
                    $transaction->application_transaction_date = now();
                    $transaction->transaction_updated_date = now();
                    $transaction->save();
                }

            }

            return response()->json([
                'success' => true,
                'message' => 'Lead status updated to processed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating lead status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getInquiryType(){
        $data = InquryType::all();
        return response()->json($data);
    }

    public function updateRemarks(Request $request){
        try {
            $inquiry = Inquiry::findOrFail($request->id);
            $inquiry->remarks = $request->remarks;
            $inquiry->save();

            return response()->json([
                'success' => true,
                'message' => 'Remarks updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating remarks: ' . $e->getMessage()
            ], 500);
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
            ->where('status', 'Active');
        }

        if($request->has('team_id') && !empty($request->team_id)){
            $agent->where('team_id', $request->team_id);

        };
        $agent = $agent->get();
        return response()->json($agent);
    }

    public function CountInquiry(){
        $inquiry_type_individual = InquryType::where('inquiry_type', 'Individual')->first()->id;
        $inquiry_type_fleet = InquryType::where('inquiry_type', 'Fleet')->first()->id;
        $inquiry_type_government = InquryType::where('inquiry_type', 'Government')->first()->id;
        $inquiry_type_company = InquryType::where('inquiry_type', 'Company')->first()->id;

        $processed_status = Status::where('status', 'Processed')->first()->id;

        if(Auth::user()->usertype->name === 'SuperAdmin'){

            $inquiryIndividual = Inquiry::where('inquiry_type_id', $inquiry_type_individual)
                ->whereNull('deleted_at')
                ->where('is_dispute', '0')
                ->where('notif_status', 'open')
                ->whereNotIn('status_id', [$processed_status])
                ->count();
            $inquiryFleet = Inquiry::where('inquiry_type_id', $inquiry_type_fleet)
                ->whereNull('deleted_at')
                ->where('is_dispute', '0')
                ->where('notif_status', 'open')
                ->whereNotIn('status_id', [$processed_status])
                ->count();
            $inquiryGovernment = Inquiry::where('inquiry_type_id', $inquiry_type_government)
                ->whereNull('deleted_at')
                ->where('is_dispute', '0')
                ->where('notif_status', 'open')
                ->whereNotIn('status_id', [$processed_status])
                ->count();
            $inquiryCompany = Inquiry::where('inquiry_type_id', $inquiry_type_company)
                ->whereNull('deleted_at')
                ->where('is_dispute', '0')
                ->where('notif_status', 'open')
                ->whereNotIn('status_id', [$processed_status])
            ->count();


            }elseif(Auth::user()->usertype->name === 'Group Manager'){
                $inquiryIndividual = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                    ->where('inquiry_type_id', $inquiry_type_individual)
                    ->whereNull('deleted_at')
                    ->where('is_dispute', '0')
                    ->where('notif_status', 'open')
                    ->whereNotIn('status_id', [$processed_status])
                    ->whereHas('user', function($subQuery) {
                        $subQuery->where('team_id', Auth::user()->team_id);
                    })
                    ->count();
                $inquiryFleet = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                    ->where('inquiry_type_id', $inquiry_type_fleet)
                    ->whereNull('deleted_at')
                    ->where('is_dispute', '0')
                    ->where('notif_status', 'open')
                    ->whereNotIn('status_id', [$processed_status])
                    ->whereHas('user', function($subQuery) {
                        $subQuery->where('team_id', Auth::user()->team_id);
                    })
                    ->count();
                $inquiryGovernment = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                    ->where('inquiry_type_id', $inquiry_type_government)
                    ->whereNull('deleted_at')
                    ->where('is_dispute', '0')
                    ->where('notif_status', 'open')
                    ->whereNotIn('status_id', [$processed_status])
                    ->whereHas('user', function($subQuery) {
                        $subQuery->where('team_id', Auth::user()->team_id);
                    })
                    ->count();
                $inquiryCompany = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                    ->where('inquiry_type_id', $inquiry_type_company)
                    ->whereNull('deleted_at')
                    ->where('is_dispute', '0')
                    ->where('notif_status', 'open')
                    ->whereNotIn('status_id', [$processed_status])
                    ->whereHas('user', function($subQuery) {
                        $subQuery->where('team_id', Auth::user()->team_id);
                    })
                    ->count();
                }
            else{

                $inquiryIndividual = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                    ->where('inquiry_type_id', $inquiry_type_individual)
                    ->whereNull('deleted_at')
                    ->where('is_dispute', '0')
                    ->where('notif_status', 'open')
                    ->whereNotIn('status_id', [$processed_status])
                    ->where('created_by', Auth::user()->id)
                    ->count();
                $inquiryFleet = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                    ->where('inquiry_type_id', $inquiry_type_fleet)
                    ->whereNull('deleted_at')
                    ->where('is_dispute', '0')
                    ->where('notif_status', 'open')
                    ->whereNotIn('status_id', [$processed_status])
                    ->where('created_by', Auth::user()->id)
                    ->count();
                $inquiryGovernment = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                    ->where('inquiry_type_id', $inquiry_type_government)
                    ->whereNull('deleted_at')
                    ->where('is_dispute', '0')
                    ->where('notif_status', 'open')
                    ->whereNotIn('status_id', [$processed_status])
                    ->where('created_by', Auth::user()->id)
                    ->count();
                $inquiryCompany = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType'])
                    ->where('inquiry_type_id', $inquiry_type_company)
                    ->whereNull('deleted_at')
                    ->where('is_dispute', '0')
                    ->where('notif_status', 'open')
                    ->whereNotIn('status_id', [$processed_status])
                    ->where('created_by', Auth::user()->id)
                    ->count();
            }

        return response()->json([
            'inquiryIndividual' => $inquiryIndividual,
            'inquiryFleet' => $inquiryFleet,
            'inquiryGovernment' => $inquiryGovernment,
            'inquiryCompany' => $inquiryCompany
        ]);
    }

    public function updateNotifStatus(Request $request){
        $processed_status = Status::where('status', 'Processed')->first()->id;
        $inquiry_type = InquryType::where('inquiry_type', $request->inquiry_type)->first()->id;

        $inquiry =  Inquiry::where('inquiry_type_id', $inquiry_type)
        ->whereNull('deleted_at')
        ->where('is_dispute', '0')
        ->where('notif_status', 'open')
        ->where('created_by', Auth::user()->id)
        ->whereNotIn('status_id', [$processed_status])
        ->get();

        foreach ($inquiry as $item) {
            $item->notif_status = 'closed';
            $item->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification status updated successfully'
        ]);
    }

}

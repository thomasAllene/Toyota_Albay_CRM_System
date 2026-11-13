<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Customer;
use App\Models\Status;
use App\Models\Team;
use App\Models\Transactions;
use App\Models\Vehicle;
use App\Models\Banks;
use App\Models\Inquiry;
use App\Models\Inventory;
use App\Models\InquryType;
use App\Models\BankTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ApplicationController extends Controller
{
    public function index()
    {
        if(Auth::check()){
            return view('application.application');
        }else{
            return view('index');
        }

    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'birthdate' => 'nullable|date',
                'age' => 'required|integer',
                'mobile_number' => 'required|string',
                'car_unit' => 'required|string',
                'car_variant' => 'required|string',
                'car_color' => 'required|string',
                'transaction' => 'required|string',
                'source' => 'required|string',
                'additional_info' => 'nullable|string',
                'gender' => 'required|string',
                'address' => 'required',
                'bank_id' => 'required',
                'birthdate' => 'nullable|date',
            ]);

            $customer = new Customer();
            $customer->customer_first_name = $validated['first_name'];
            $customer->customer_last_name = $validated['last_name'];
            $customer->contact_number = $validated['mobile_number'];
            $customer->gender = $validated['gender'];
            $customer->address = $validated['address'];
            $customer->birthdate = $validated['birthdate'];
            $customer->age = $validated['age'];
            $customer->source = $validated['source'];
            $customer->created_by = Auth::id();
            $customer->updated_by = Auth::id();
            $customer->save();

            $vehicle = Vehicle::where('unit', $validated['car_unit'])
            ->where('variant', $validated['car_variant'])
            ->where('color',$validated['car_color'])
            ->first();

            $transaction = new Transactions();
            $transaction->status = Status::where('status', 'like', 'pending')->first()->id;
            $transaction->save(); // Save the transaction



            if (in_array($validated['transaction'], ['cash', 'po'])) {

                $approved_status = Status::where('status', 'like', 'approved')->first();
                $application = new Application();
                $application->customer_id = $customer->id;
                $application->vehicle_id = $vehicle->id;
                $application->transaction_id = $transaction->id;
                $application->status_id = $approved_status->id;
                $application->bank_id = $validated['bank_id'];
                $application->transaction = $validated['transaction'];
                $application->created_by = Auth::id();
                $application->updated_by = Auth::id();
                $application->save();

                // Update the transaction table's application_id with the latest inserted application's id
                $transaction->application_id = $application->id;
                $transaction->status = Status::where('status', 'like', 'approved')->first()->id;
                $transaction->application_transaction_date = now();
                $transaction->transaction_updated_date = now();
                $transaction->save();

            }else{

                $pending_status = Status::where('status', 'like', 'pending')->first();
                $application = new Application();
                $application->customer_id = $customer->id;
                $application->vehicle_id = $vehicle->id;
                $application->transaction_id = $transaction->id;
                $application->status_id = $pending_status->id;
                $application->bank_id = $validated['bank_id'];
                $application->transaction = $validated['transaction'];
                $application->created_by = Auth::id();
                $application->updated_by = Auth::id();
                $application->save();

                $transaction->application_id = $application->id;
                $transaction->status = $pending_status->id;
                $transaction->application_transaction_date = now();
                $transaction->transaction_updated_date = now();
                $transaction->save();

            }


            return response()->json([
                'success' => true,
                'message' => 'Application created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating application: ' . $e->getMessage()
            ], 500);
        }
    }

    public function list_approved(Request $request){

        // dd($request->start_date);
        $status = Status::where('status', 'like', 'approved')->first();
        if(Auth::user()->usertype->name === 'SuperAdmin'
        || Auth::user()->usertype->name === 'General Manager'
        || Auth::user()->usertype->name === 'Sales Admin Staff'
        || Auth::user()->usertype->name === 'Financing Staff'
        ){
            $query = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                        ->whereNull('deleted_at')
                        ->where('status_id', $status->id)
                        ->orderBy('updated_at', 'desc');
        }
        elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                        ->whereNull('deleted_at')
                        ->where('status_id', $status->id)
                        ->whereHas('user', function($subQuery) {
                            $subQuery->where('team_id', Auth::user()->team_id);
                        })
                        ->orderBy('updated_at', 'desc');

        }else{
            $query = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                        ->whereNull('deleted_at')
                        ->where('status_id', $status->id)
                        ->where('created_by', Auth::user()->id)
                        ->orderBy('updated_at', 'desc');
        }

        // if ($request->has('date_range') && !empty($request->date_range)) {
        //     [$startDate, $endDate] = explode(' to ', $request->date_range);
        //     $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
        //     $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

        //     $query->whereBetween('updated_at', [$startDate, $endDate]);
        // }

        if ($request->has('date_range') && !empty($request->date_range)) {
            $dateRange = preg_replace('/\s*to\s*/i', ' to ', $request->date_range); // Normalize 'to' and 'TO'
            [$startDate, $endDate] = explode(' to ', $dateRange);

            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
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

        ->addColumn('type', function($data) {
            $firstTransaction = $data->transactions->first();
            if ($firstTransaction) {

                $inquiry = Inquiry::where('id', $firstTransaction->inquiry_id)->first();
                $inquirytype = InquryType::where('id', $inquiry->inquiry_type_id)->first()->inquiry_type;
                return $inquirytype;
            }
            return null;
        })

        ->addColumn('client_name', function($data) {
            $firstTransaction = $data->transactions->first();
            if ($firstTransaction) {

                $inquiry = Inquiry::where('id', $firstTransaction->inquiry_id)->first();
                $inquirytype = InquryType::where('id', $inquiry->inquiry_type_id)->first()->inquiry_type;

                if($inquirytype === 'Individual'){
                    return $data->customer->customer_first_name . ' ' . $data->customer->customer_last_name;
                }else if($inquirytype === 'Fleet'){
                    return $data->customer->company_name;
                }else if($inquirytype === 'Company'){
                    return $data->customer->company_name;
                }else if($inquirytype === 'Government'){
                    return $data->customer->department_name;
                }
            }

        })

        ->addColumn('contact_number', function($data) {
            return $data->customer->contact_number;
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

        ->addColumn('transaction', function($data) {
            return $data->transaction;
        })

        ->addColumn('reservation_status', function($data) {
            $transaction = $data->transactions->first();
            return $transaction ? $transaction->reservation_status : 'N/A';
        })

        // ->editColumn('date', function($data) {
        //     return $data->updated_at->format('d/m/Y H:i:s');

        // })

        ->editColumn('date', function($data) {
            return $data->updated_at ? $data->updated_at->format('M d, Y h:i A') : '-';
        })

        ->make(true);
    }

    public function list_cancel(Request $request){

        // dd($request->start_date);
        $statusIds = Status::whereIn('status', ['Denied', 'Cancel'])->pluck('id')->toArray();

        if(Auth::user()->usertype->name === 'SuperAdmin'
        || Auth::user()->usertype->name === 'General Manager'
        || Auth::user()->usertype->name === 'Sales Admin Staff'
        || Auth::user()->usertype->name === 'Financing Staff'
        ){
            $query = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                        ->whereNull('deleted_at')
                        ->where('status_id', $statusIds)
                        ->orderBy('updated_at', 'desc');

        }
        elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                        ->whereNull('deleted_at')
                        ->where('status_id', $statusIds)
                        ->whereHas('user', function($subQuery) {
                            $subQuery->where('team_id', Auth::user()->team_id);
                        })
                        ->orderBy('updated_at', 'desc');

        }else{
            $query = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                        ->whereNull('deleted_at')
                        ->where('status_id', $statusIds)
                        ->where('created_by', Auth::user()->id)
                        ->orderBy('updated_at', 'desc');
        }


        // if ($request->has('date_range') && !empty($request->date_range)) {
        //     [$startDate, $endDate] = explode(' to ', $request->date_range);
        //     $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
        //     $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

        //     $query->whereBetween('updated_at', [$startDate, $endDate]);
        // }

        if ($request->has('date_range') && !empty($request->date_range)) {
            $dateRange = preg_replace('/\s*to\s*/i', ' to ', $request->date_range); // Normalize 'to' and 'TO'
            [$startDate, $endDate] = explode(' to ', $dateRange);

            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
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

        ->addColumn('type', function($data) {
            $firstTransaction = $data->transactions->first();
            if ($firstTransaction) {

                $inquiry = Inquiry::where('id', $firstTransaction->inquiry_id)->first();
                $inquirytype = InquryType::where('id', $inquiry->inquiry_type_id)->first()->inquiry_type;
                return $inquirytype;
            }
            return null;
        })

        ->addColumn('client_name', function($data) {
            $firstTransaction = $data->transactions->first();
            if ($firstTransaction) {

                $inquiry = Inquiry::where('id', $firstTransaction->inquiry_id)->first();
                $inquirytype = InquryType::where('id', $inquiry->inquiry_type_id)->first()->inquiry_type;

                if($inquirytype === 'Individual'){
                    return $data->customer->customer_first_name . ' ' . $data->customer->customer_last_name;
                }else if($inquirytype === 'Fleet'){
                    return $data->customer->company_name;
                }else if($inquirytype === 'Company'){
                    return $data->customer->company_name;
                }else if($inquirytype === 'Government'){
                    return $data->customer->department_name;
                }
            }

        })

        ->addColumn('contact_number', function($data) {
            return $data->customer->contact_number;
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

        ->addColumn('transaction', function($data) {
            return $data->transaction;
        })

        ->addColumn('reservation_status', function($data) {
            $transaction = $data->transactions->first();
            return $transaction ? $transaction->reservation_status : 'N/A';
        })

        // ->editColumn('date', function($data) {
        //     return $data->updated_at->format('d/m/Y H:i:s');
        // })

        ->editColumn('date', function($data) {
            return $data->updated_at ? $data->updated_at->format('M d, Y h:i A') : '-';
        })

        ->make(true);
    }

    public function list_cash(Request $request){

        // dd($request->start_date);
        $statusIds = Status::whereIn('status', ['Denied', 'Cancel', 'Processed', 'Approved', 'Reserved'])->pluck('id')->toArray();

        if(Auth::user()->usertype->name === 'SuperAdmin'
        || Auth::user()->usertype->name === 'General Manager'
        || Auth::user()->usertype->name === 'Sales Admin Staff'
        || Auth::user()->usertype->name === 'Financing Staff'
        ){
            $query = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                ->whereNull('deleted_at')
                ->whereNotIn('status_id', $statusIds)
                ->whereIn('transaction', ['cash', 'po'])
                ->orderBy('updated_at', 'desc');

        }
        elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                ->whereNull('deleted_at')
                ->whereNotIn('status_id', $statusIds)
                ->whereIn('transaction', ['cash', 'po'])
                ->whereHas('user', function($subQuery) {
                    $subQuery->where('team_id', Auth::user()->team_id);
                })
                ->orderBy('updated_at', 'desc');


        }else{
            $query = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                ->whereNull('deleted_at')
                ->whereNotIn('status_id', $statusIds)
                ->whereIn('transaction', ['cash', 'po'])
                ->where('created_by', Auth::user()->id)
                ->orderBy('updated_at', 'desc');
        }

        // if ($request->has('date_range') && !empty($request->date_range)) {
        //     [$startDate, $endDate] = explode(' to ', $request->date_range);
        //     $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
        //     $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

        //     $query->whereBetween('updated_at', [$startDate, $endDate]);
        // }

        if ($request->has('date_range') && !empty($request->date_range)) {
            $dateRange = preg_replace('/\s*to\s*/i', ' to ', $request->date_range); // Normalize 'to' and 'TO'
            [$startDate, $endDate] = explode(' to ', $dateRange);

            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
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

        ->addColumn('type', function($data) {
            $firstTransaction = $data->transactions->first();
            if ($firstTransaction) {

                $inquiry = Inquiry::where('id', $firstTransaction->inquiry_id)->first();
                $inquirytype = InquryType::where('id', $inquiry->inquiry_type_id)->first()->inquiry_type;
                return $inquirytype;
            }
            return null;
        })

        ->addColumn('client_name', function($data) {
            $firstTransaction = $data->transactions->first();
            if ($firstTransaction) {

                $inquiry = Inquiry::where('id', $firstTransaction->inquiry_id)->first();
                $inquirytype = InquryType::where('id', $inquiry->inquiry_type_id)->first()->inquiry_type;

                if($inquirytype === 'Individual'){
                    return $data->customer->customer_first_name . ' ' . $data->customer->customer_last_name;
                }else if($inquirytype === 'Fleet'){
                    return $data->customer->company_name;
                }else if($inquirytype === 'Company'){
                    return $data->customer->company_name;
                }else if($inquirytype === 'Government'){
                    return $data->customer->department_name;
                }
            }

        })

        ->addColumn('contact_number', function($data) {
            return $data->customer->contact_number;
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

        ->addColumn('transaction', function($data) {
            return $data->transaction;
        })

        ->addColumn('reservation_status', function($data) {
            $transaction = $data->transactions->first();
            return $transaction ? $transaction->reservation_status : 'N/A';
        })

        // ->editColumn('date', function($data) {
        //     return $data->updated_at->format('d/m/Y H:i:s');
        // })

        ->editColumn('date', function($data) {
            return $data->updated_at ? $data->updated_at->format('M d, Y h:i A') : '-';
        })

        ->make(true);
    }

    public function list_pending(Request $request){

        // dd($request->start_date);
        $pending_status = Status::where('status', 'like', 'pending')->first();
        if(Auth::user()->usertype->name === 'SuperAdmin'
        || Auth::user()->usertype->name === 'General Manager'
        || Auth::user()->usertype->name === 'Sales Admin Staff'
        || Auth::user()->usertype->name === 'Financing Staff'
        ){
            $query = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                        ->whereNull('deleted_at')
                        ->whereNotIn('transaction', ['cash', 'po'])
                        ->where('status_id', $pending_status->id)
                        ->orderBy('updated_at', 'desc');

        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                        ->whereNull('deleted_at')
                        ->whereNotIn('transaction', ['cash', 'po'])
                        ->where('status_id', $pending_status->id)
                        ->whereHas('user', function($subQuery) {
                            $subQuery->where('team_id', Auth::user()->team_id);
                        })
                        ->orderBy('updated_at', 'desc');

        }else{
            $query = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                        ->whereNull('deleted_at')
                        ->whereNotIn('transaction', ['cash', 'po'])
                        ->where('status_id', $pending_status->id)
                        ->where('created_by', Auth::user()->id)
                        ->orderBy('updated_at', 'desc');

        }

        // if ($request->has('date_range') && !empty($request->date_range)) {
        //     [$startDate, $endDate] = explode(' to ', $request->date_range);
        //     $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
        //     $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

        //     $query->whereBetween('updated_at', [$startDate, $endDate]);
        // }

        if ($request->has('date_range') && !empty($request->date_range)) {
            $dateRange = preg_replace('/\s*to\s*/i', ' to ', $request->date_range); // Normalize 'to' and 'TO'
            [$startDate, $endDate] = explode(' to ', $dateRange);

            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
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

        ->addColumn('type', function($data) {
            $firstTransaction = $data->transactions->first();
            if ($firstTransaction) {

                $inquiry = Inquiry::where('id', $firstTransaction->inquiry_id)->first();
                $inquirytype = InquryType::where('id', $inquiry->inquiry_type_id)->first()->inquiry_type;
                return $inquirytype;
            }
            return null;
        })

        ->addColumn('client_name', function($data) {
            $firstTransaction = $data->transactions->first();
            if ($firstTransaction) {

                $inquiry = Inquiry::where('id', $firstTransaction->inquiry_id)->first();
                $inquirytype = InquryType::where('id', $inquiry->inquiry_type_id)->first()->inquiry_type;

                if($inquirytype === 'Individual'){
                    return $data->customer->customer_first_name . ' ' . $data->customer->customer_last_name;
                }else if($inquirytype === 'Fleet'){
                    return $data->customer->company_name;
                }else if($inquirytype === 'Company'){
                    return $data->customer->company_name;
                }else if($inquirytype === 'Government'){
                    return $data->customer->department_name;
                }
            }

        })

        ->addColumn('contact_number', function($data) {
            return $data->customer->contact_number;
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

        ->addColumn('transaction', function($data) {
            return $data->transaction;
        })

        ->addColumn('reservation_status', function($data) {
            $transaction = $data->transactions->first();
            return $transaction ? $transaction->reservation_status : 'N/A';
        })

        // ->editColumn('date', function($data) {
        //     return $data->updated_at->format('d/m/Y H:i:s');
        // })

        ->editColumn('date', function($data) {
            return $data->updated_at ? $data->updated_at->format('M d, Y h:i A') : '-';
        })

        ->make(true);
    }

    public function edit($id)
    {
        $decryptedId = decrypt($id);
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
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
               'first_name' => 'nullable',
                'last_name' => 'nullable',
                'government' => 'nullable',
                'company' => 'nullable',
                'fleet' => 'nullable',
                'gender' => 'nullable',
                'age' => 'nullable',
                'mobile_number' => 'required|string',
                'car_unit' => 'required|string',
                'car_variant' => 'required|string',
                'car_color' => 'required|string',
                'transaction' => 'required|string',
                'source' => 'required|string',
                'additional_info' => 'nullable|string',
                'address' => 'required',
                'category' => 'required',
                'quantity' => 'nullable',
                'payment_status' => 'nullable',
                'birthdate' => 'nullable|date',
            ]);

            // Find the inquiry and related customer and vehicle
            $application = Application::findOrFail($id);

            $transaction_id = Transactions::where('application_id', $application->id )->first();
            $inquiry_id = Inquiry::where('id', $transaction_id->inquiry_id)->first();

            $customer = Customer::findOrFail($application->customer_id);
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
                    'reservation_status' => $validated['payment_status'],
            ]);



            $inquiry->category = $validated['category'];
            $inquiry->quantity = $validated['quantity'];
            $inquiry->updated_by = Auth::id();
            $inquiry->updated_at = now();
            $inquiry->save();

            // Update customer data
            $customer->customer_first_name = $validated['first_name'];
            $customer->customer_last_name = $validated['last_name'];
            $customer->department_name = $validated['government'];
            $customer->company_name = $validated['company'] ?  $validated['company'] : $validated['fleet'];
            $customer->contact_number = $validated['mobile_number'];
            $customer->gender = $validated['gender'];
            $customer->address = $validated['address'];
            $customer->age = $validated['age'];
            $customer->birthdate = $validated['birthdate'];
            $customer->source = $validated['source'];
            $customer->updated_by = Auth::id();
            $customer->updated_at = now();
            $customer->save();

            // Update inquiry data
            $application->vehicle_id = $vehicle->id;
            $application->transaction = $validated['transaction'];
            $application->remarks = $validated['additional_info'];
            $application->updated_by = Auth::id();
            $application->updated_at = now();
            $application->save();

            return response()->json([
                'success' => true,
                'message' => 'Application updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating application: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBanks(){
        $data = Banks::all();
        return response()->json($data);
    }

    public function getStatus(){
        $data = Status::all();
        return response()->json($data);
    }

    public function processing(Request $request){
        try {

            $approved_status = Status::where('status', 'like', 'approved')->first()->id;
            $pending_status = Status::where('status', 'like', 'pending')->first()->id;
            $cancel_status = Status::where('status', 'like', 'cancel')->first()->id;
            $processing_status = Status::where('status', 'like', 'Processed')->first()->id;

            $application = Application::findOrFail(decrypt($request->id));
            $application_team = User::findOrFail($application->created_by);

            if( $application->status_id == $pending_status  ){

                if($request->transaction === 'financing'){
                    $existingBankTransactions = BankTransaction::where('application_id', decrypt($request->id))
                        ->whereNull('deleted_at')
                        ->get();

                    if($existingBankTransactions->count() === 0){
                        return response()->json([
                            'success' => false,
                                'message' => 'Bank is required for processing.'
                            ], 500);
                    }
                }
                if($request->transaction === 'po'){
                    $bankId = $application->bank_id;
                    if (!$bankId) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Bank is required for PO transactions.'
                        ], 500);
                    }
                }

                $application->status_id =  $approved_status;
                $application->updated_by = Auth::user()->id;
                $application->updated_at = now();
                $application->save();

            }else if( $application->status_id == $approved_status){

                if($request->transaction === 'financing'){

                    $existingBankTransactions = BankTransaction::where('application_id', decrypt($request->id))
                        ->whereNull('deleted_at')
                        ->whereNotNull('approval_date')
                        ->whereNotNull('approval_status')
                        ->get();

                    if($existingBankTransactions->count() === 0){
                        return response()->json([
                            'success' => false,
                                'message' => 'Bank approval is required for processing.'
                            ], 500);
                    }


                    $bankId = $application->bank_id;

                    if (!$bankId) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Bank is required for financing transactions.'
                        ], 500);
                    }

                    $terms = $application->terms;
                    $percentage = $application->percentage;

                    if(!$terms || !$percentage){
                        return response()->json([
                            'success' => false,
                            'message' => 'Terms and percentage are required for financing transactions.'
                        ], 500);
                    }

                }

                if($request->transaction === 'po'){
                    $bankId = $application->bank_id;
                    if (!$bankId) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Bank is required for PO transactions.'
                        ], 500);
                    }
                }
                $transactions = Transactions::where('application_id', $application->id)
                ->whereNull('inventory_id')
                ->whereNull('deleted_at')
                ->get();

                foreach ($transactions as $transaction) {

                    $transaction = Transactions::findOrFail($transaction->id);
                    $transaction->status = $approved_status;
                    $transaction->reservation_id = Transactions::max('reservation_id') + 1;
                    $transaction->reservation_transaction_status = $pending_status;
                    $transaction->team_id = $application_team->team_id;
                    $transaction->save();

                }

                $application->status_id = $processing_status;
                $application->transaction = $application->transaction;
                $application->updated_by = Auth::id();
                $application->updated_at = now();
                $application->save();

            } else if($application->status_id == $cancel_status){

                $application->status_id =  $pending_status;
                $application->updated_by = Auth::user()->id;
                $application->updated_at = now();
                $application->save();

            }

            return response()->json([
                'success' => true,
                'message' => 'Application updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating application status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Request $request){
        try {

            $cancel_status = Status::where('status', 'like', 'cancel')->first()->id;

            $application = Application::findOrFail(decrypt($request->id));
            $application->status_id =  $cancel_status;
            $application->updated_by = Auth::user()->id;
            $application->updated_at = now();
            $application->save();

            return response()->json([
                'success' => true,
                'message' => 'Application has been canceled successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating application status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateBanks(Request $request){
        try {
        $validated = $request->validate([
            'bank_id' => 'required|exists:banks,id'
        ]);

        $application = Application::findOrFail(decrypt($request->application_id));

        $banksArray = $request->bank_id;

        // Fetch all existing bank transactions for the application
        $existingBankTransactions = BankTransaction::where('application_id', decrypt($request->application_id))
            ->pluck('bank_id');

        // Check for banks that were removed from the array and soft delete them
        foreach ($existingBankTransactions as $existingBank) {
            if (!in_array($existingBank, $banksArray)) {
                BankTransaction::where('application_id', decrypt($request->application_id))
                    ->where('bank_id', $existingBank)
                    ->first()
                    ->delete();
            }
        }

        // Process each bank in the request
        foreach ($banksArray as $bank) {
            // Check if the bank already exists for the application
            if (!$existingBankTransactions->contains($bank)) {
                $bankTrans = new BankTransaction;
                $bankTrans->application_id = decrypt($request->application_id);
                $bankTrans->bank_id = $bank;
                $bankTrans->created_by = Auth::id();
                $bankTrans->updated_by = Auth::id();
                $bankTrans->save();
            }
        }



        return response()->json([
            'success' => true,
            'message' => 'Banks updated successfully'
        ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating banks: ' . $e->getMessage()
            ], 500);
        }

    }

    public function getBanksForApplication($id)
    {
        try {
            $decryptedId = decrypt($id);

            // Get all bank transactions for this application
            $bankTransactions = BankTransaction::with('bank')
                ->where('application_id', $decryptedId)
                ->get()
                ->map(function($transaction) {
                    return [
                        'bank_id' => $transaction->bank_id,
                        'bank_name' => $transaction->bank->bank_name,
                        'approval_date' => $transaction->approval_date,
                        'approval_status' => $transaction->approval_status,
                        'is_preferred' => $transaction->is_preferred
                    ];
                });

            return response()->json([
                'success' => true,
                'banks' => $bankTransactions,
                'id' => $decryptedId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching banks: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateBankApproval(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'bank_ids' => 'required|array',
                'bank_ids.*' => 'required|exists:banks,id',
                'approval_dates' => 'required|array',
                'approval_dates.*' => 'nullable|date',
                'approval_statuses' => 'required|array',
                'approval_statuses.*' => 'required|in:approve,disapprove,pending',
                'preferred_bank' => 'required|exists:banks,id'
            ]);

            // Update approval dates and preferred status for each bank
            foreach ($validated['bank_ids'] as $index => $bankId) {
                BankTransaction::where('application_id', decrypt($id))
                    ->where('bank_id', $bankId)
                    ->update([
                        'approval_date' => $validated['approval_dates'][$index],
                        'approval_status' => $validated['approval_statuses'][$index],
                        'is_preferred' => $bankId == $validated['preferred_bank'],
                        'updated_by' => Auth::id()
                    ]);
            }

            // Update preferred bank if it has an approved or pending approval status
            $preferredBankIndex = array_search($validated['preferred_bank'], $validated['bank_ids']);
            if ($preferredBankIndex !== false && in_array($validated['approval_statuses'][$preferredBankIndex], ['approve', 'pending'])) {
                $application = Application::findOrFail(decrypt($id));
                $application->bank_id = $validated['preferred_bank'];
                $application->updated_by = Auth::id();
                $application->updated_at = now();
                $application->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Bank approval dates updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating bank approval: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getApplicationBanks($id)
    {
        try {
            $decryptedId = decrypt($id);

            // Get all bank transactions for this application
            $bankTransactions = BankTransaction::with('bank')
                ->where('application_id', $decryptedId)
                ->get()
                ->map(function($transaction) {
                    return [
                        'bank_id' => $transaction->bank_id,
                        'bank_name' => $transaction->bank->bank_name,
                        'approval_date' => $transaction->approval_date,
                        'is_preferred' => $transaction->is_preferred,
                        'approval_status' => $transaction->approval_status
                    ];
                });

            return response()->json([
                'success' => true,
                'banks' => $bankTransactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching banks: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateTerms(Request $request){
        try {

            $validated = $request->validate([
                'terms' => 'required',
                'percentage' => 'required'
            ]);

            $application = Application::findOrFail(decrypt($request->id));
            $application->terms = $validated['terms'];
            $application->percentage = $validated['percentage'];
            $application->updated_by = Auth::id();
            $application->updated_at = now();
            $application->save();

            return response()->json([
                'success' => true,
                'message' => 'Terms updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating terms: ' . $e->getMessage()
            ], 500);
        }

    }

    public function updateApplicationBank(Request $request){
        try {
            $application = Application::findOrFail(decrypt($request->application_id));
            $application->bank_id = $request->bank_id;
            $application->updated_by = Auth::id();
            $application->updated_at = now();
            $application->save();


            return response()->json([
                'success' => true,
                'message' => 'Application bank updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating application bank: ' . $e->getMessage()
            ], 500);
        }
    }

    public function countApplication(){

        $status = Status::where('status', 'like', 'approved')->first();
        $pending_status = Status::where('status', 'like', 'pending')->first();
        $statusIds = Status::whereIn('status', ['Denied', 'Cancel', 'Processed', 'Approved', 'Reserved'])->pluck('id')->toArray();
        $cancel_statuses = Status::whereIn('status', ['Cancel'])->pluck('id')->toArray();

        if(Auth::user()->usertype->name === 'SuperAdmin'
        || Auth::user()->usertype->name === 'General Manager'
        || Auth::user()->usertype->name === 'Sales Admin Staff'
        || Auth::user()->usertype->name === 'Financing Staff'
        ){
            $approved_application = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                        ->where('notif_status', 'open')
                        ->whereNull('deleted_at')
                        ->where('status_id', $status->id)
                        ->orderBy('updated_at', 'desc');

            $pending_application = Application::whereNull('deleted_at')
                                    ->where('notif_status', 'open')
                                    ->whereNotIn('transaction', ['cash', 'po'])
                                    ->where('status_id', $pending_status->id)
                                    ->orderBy('updated_at', 'desc');
                                    ;
            $poOrCash_application = Application::whereNull('deleted_at')
                                    ->where('notif_status', 'open')
                                    ->whereIn('transaction', ['cash', 'po'])
                                    ->whereNotIn('status_id', $statusIds)
                                    ->orderBy('updated_at', 'desc');
            $cancel_application = Application::whereNull('deleted_at')
                                    ->where('notif_status', 'open')
                                    ->whereIn('status_id', $cancel_statuses)
                                    ->orderBy('updated_at', 'desc');
        }
        elseif(Auth::user()->usertype->name === 'Group Manager'){
            $approved_application = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                        ->whereNull('deleted_at')
                        ->where('notif_status', 'open')
                        ->where('status_id', $status->id)
                        ->whereHas('user', function($subQuery) {
                            $subQuery->where('team_id', Auth::user()->team_id);
                        })
                        ->orderBy('updated_at', 'desc');

            $pending_application = Application::whereNull('deleted_at')
                                    ->where('notif_status', 'open')
                                    ->whereNotIn('transaction', ['cash', 'po'])
                                    ->where('status_id', $status->id)
                                    ->whereHas('user', function($subQuery) {
                                        $subQuery->where('team_id', Auth::user()->team_id);
                                    })
                                    ->orderBy('updated_at', 'desc');
            $poOrCash_application = Application::whereNull('deleted_at')
                                    ->where('notif_status', 'open')
                                    ->whereIn('transaction', ['cash', 'po'])
                                    ->whereNotIn('status_id', $statusIds)
                                    ->whereHas('user', function($subQuery) {
                                        $subQuery->where('team_id', Auth::user()->team_id);
                                    })
                                    ->orderBy('updated_at', 'desc');
            $cancel_application = Application::whereNull('deleted_at')
                                    ->where('notif_status', 'open')
                                    ->whereIn('status_id', $cancel_statuses)
                                    ->whereHas('user', function($subQuery) {
                                        $subQuery->where('team_id', Auth::user()->team_id);
                                    })
                                    ->orderBy('updated_at', 'desc');

        }else{
            $approved_application = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                        ->whereNull('deleted_at')
                        ->where('notif_status', 'open')
                        ->where('status_id', $status->id)
                        ->where('created_by', Auth::user()->id)
                        ->orderBy('updated_at', 'desc');
            $pending_application = Application::whereNull('deleted_at')
                                    ->where('notif_status', 'open')
                                    ->whereNotIn('transaction', ['cash', 'po'])
                                    ->where('status_id', $pending_status->id)
                                    ->where('created_by', Auth::user()->id)
                                    ->orderBy('updated_at', 'desc');
            $poOrCash_application = Application::whereNull('deleted_at')
                                    ->where('notif_status', 'open')
                                    ->whereIn('transaction', ['cash', 'po'])
                                    ->whereNotIn('status_id', $statusIds)
                                    ->where('created_by', Auth::user()->id)
                                    ->orderBy('updated_at', 'desc');
            $cancel_application = Application::whereNull('deleted_at')
                                    ->where('notif_status', 'open')
                                    ->whereIn('status_id', $cancel_statuses)
                                    ->where('created_by', Auth::user()->id)
                                    ->orderBy('updated_at', 'desc');
        }

        return response()->json([
            'success' => true,
            'approved_application' => $approved_application->count(),
            'pending_application' => $pending_application->count(),
            'poOrCash_application' => $poOrCash_application->count(),
            'cancel_application' => $cancel_application->count()
        ]);
    }

    public function updateApplicationNotifStatus(Request $request){

        if(!Auth::user()->usertype->name === 'SuperAdmin'){
            $status = Status::where('status', 'like', 'approved')->first();
            $pending_status = Status::where('status', 'like', 'pending')->first();
            $statusIds = Status::whereIn('status', ['Denied', 'Cancel', 'Processed', 'Approved', 'Reserved'])->pluck('id')->toArray();
            $cancel_statuses = Status::whereIn('status', ['Cancel'])->pluck('id')->toArray();
    
            if($request->tab_title === 'Cash/PO Applications'){
                $poOrCash_application = Application::whereNull('deleted_at')
                ->where('notif_status', 'open')
                ->whereIn('transaction', ['cash', 'po'])
                ->whereNotIn('status_id', $statusIds)
                ->orderBy('updated_at', 'desc');
    
                $poOrCash_application->update([
                    'notif_status' => 'closed'
                ]);
    
            }
            elseif($request->tab_title === 'Pending Applications'){
    
                $pending_application = Application::whereNull('deleted_at')
                ->where('notif_status', 'open')
                ->whereNotIn('transaction', ['cash', 'po'])
                ->where('status_id', $pending_status->id)
                ->orderBy('updated_at', 'desc');
    
                $pending_application->update([
                    'notif_status' => 'closed'
                ]);
    
            }elseif($request->tab_title === 'Approved Applications'){
    
                $approved_application = Application::with(['user', 'customer', 'vehicle','status', 'bank', 'transactions'])
                        ->whereNull('deleted_at')
                        ->where('notif_status', 'open')
                        ->where('status_id', $status->id)
                        ->orderBy('updated_at', 'desc');
    
                $approved_application->update([
                    'notif_status' => 'closed'
                ]);
    
            }elseif($request->tab_title === 'Denied/Canceled Applications'){
    
                $cancel_application = Application::whereNull('deleted_at')
                ->where('notif_status', 'open')
                ->whereIn('status_id', $cancel_statuses)
                ->orderBy('updated_at', 'desc');
    
                $cancel_application->update([
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

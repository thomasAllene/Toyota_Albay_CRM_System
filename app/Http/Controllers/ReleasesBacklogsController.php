<?php

namespace App\Http\Controllers;

use App\Models\ApplicationBacklogs;
use App\Models\Banks;
use App\Models\Customer;
use App\Models\InquiryBacklogs;
use App\Models\InquryType;
use App\Models\Inventory;
use App\Models\Status;
use App\Models\Team;
use App\Models\TransactionBacklogs;
use App\Models\Transactions;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Facades\DB;

class ReleasesBacklogsController extends Controller
{
    public function index(){
        return view('upload_backlogs.releases_backlogs');
    }

    public function list_release_backlogs(Request $request){

        $release_status = Status::where('status', 'like', 'Released')->first();
        $posted_status = Status::where('status', 'like', 'Posted')->first();
        $pending_for_release_status = Status::where('status', 'like', 'Pending For Release')->first();

        if(Auth::user()->usertype->name === 'SuperAdmin'
        || Auth::user()->usertype->name === 'Sales Admin Staff'
        || Auth::user()->usertype->name === 'General Manager'
        || Auth::user()->usertype->name === 'Financing Staff'
        ){
            $query = TransactionBacklogs::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->whereIn('reservation_transaction_status', [$release_status->id, $posted_status->id, $pending_for_release_status->id])
                        ->whereNotNull('reservation_id')
                        ->orderBy('updated_at', 'desc');

        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = TransactionBacklogs::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->whereIn('reservation_transaction_status', [$release_status->id, $posted_status->id, $pending_for_release_status->id])
                        ->whereNotNull('reservation_id')
                        ->whereHas('application', function($subQuery) {
                            $subQuery->whereHas('user', function($subQuery) {
                                $subQuery->where('team_id', Auth::user()->team_id);
                            });
                        })
                        ->orderBy('updated_at', 'desc');

        }else{
            $query = TransactionBacklogs::with(['inquiry', 'inventory', 'application'])
                        ->whereNull('deleted_at')
                        ->whereIn('reservation_transaction_status', [$release_status->id, $posted_status->id, $pending_for_release_status->id])
                        ->whereNotNull('reservation_id')
                        ->whereHas('application', function($subQuery) {
                            $subQuery->where('created_by', Auth::user()->id);
                        })
                        ->orderBy('updated_at', 'desc');

        }

        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('updated_at', [$startDate, $endDate]);
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

        ->addColumn('customer_name', function($data) {
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
            return $data->inventory->CS_number ?? '';
        })

        ->addColumn('category', function($data) {
            return $data->application->vehicle->category ?? '' ;
        })

        ->addColumn('insurance', function($data) {
            return $data->insurance ?? 'Select Insurance';
        })

        ->addColumn('other_profit', function($data) {
            return '0.00';
        })


        // ->addColumn('trans_type', function($data) {
        //     return $data->inquiry->inquiryType->inquiry_type;
        // })
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
            return $data->inventory->user->first_name. ' ' . $data->inventory->user->last_name;
        })

        ->addColumn('date_reserved', function($data) {
            return $data->reservation_date ? \Carbon\Carbon::parse($data->reservation_date)->format('d/m/Y') : '';
        })

        ->addColumn('date_released', function($data) {
            // return $data->updated_at->format('d/m/Y H:i:s');
            return $data->updated_at ? $data->updated_at->format('M d, Y h:i A') : '-';
        })

        ->addColumn('status', function($data) {
            $status = Status::where('id', $data->status)->first()->status;
            return $status;
        })
        ->addColumn('profit', function($data) {
            return number_format($data->profit ?? 0, 2);
        })

        ->addColumn('folder_number', function($data) {
            return $data->folder_number ?? '';
        })

        ->addColumn('source', function($data) {
            return $data->inquiry->customer->source ?? '';
        })

        ->addColumn('address', function($data) {
            return $data->inquiry->customer->address ?? '';
        })

        ->addColumn('gender', function($data) {
            return $data->inquiry->customer->gender ?? '';
        })

        ->make(true);
    }

    public function upload_backlogs(Request $request){
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls',
        ]);
            DB::beginTransaction();
            
            // Get the uploaded file
            $file = $request->file('file');
            
            // Get the original file name
            $originalName = $file->getClientOriginalName();

            // Move the file to a temporary location with its original name
            $tempPath = sys_get_temp_dir() . '/' . $originalName;
            $file->move(sys_get_temp_dir(), $originalName);

            // Create reader with the original file
            $reader = SimpleExcelReader::create($tempPath);
            $rows = $reader->getRows();

            // Process the data
            $data = [];

            foreach ($rows as $row) {
               
                //add the customer or get the cutomer id if it exists
                $customer = Customer::whereRaw('LOWER(customer_first_name) = LOWER(?)', [$row['Customer First Name']])
                                    ->whereRaw('LOWER(customer_last_name) = LOWER(?)', [$row['Customer Last Name']])
                                    ->first();

                if(!$customer){
                    $customer = new Customer();
                    $customer->customer_first_name = $row['Customer First Name'];
                    $customer->customer_last_name = $row['Customer Last Name'];
                    $customer->department_name = $row['Department Name'];
                    $customer->company_name = $row['Company Name'];
                    $customer->gender = $row['Gender'];
                    $customer->birthdate = $row['BirthDate'];
                    $customer->address = $row['Address'];
                    $customer->source = $row['Source'];
                    $customer->contact_number = $row['Contact'];
                    $customer->created_by = Auth::user()->id;
                    $customer->updated_by = Auth::user()->id;
                    $customer->save();
                }

                $vehicle = Vehicle::whereRaw('LOWER(unit) = LOWER(?)', [$row['Unit']])
                                    ->whereRaw('LOWER(variant) = LOWER(?)', [$row['Variant']])
                                    ->whereRaw('LOWER(color) = LOWER(?)', [$row['Color']])
                                    ->first();
                if(!$vehicle){
                    $vehicle = new Vehicle();
                    $vehicle->unit = $row['Unit'];
                    $vehicle->variant = $row['Variant'];
                    $vehicle->color = $row['Color'];
                    $vehicle->save();
                }

                //add in inquiry table with status processed
                $inquiry_type = InquryType::where('inquiry_type', $row['Type'])->first();

                $inquiry_backlogs = new InquiryBacklogs();
                $inquiry_backlogs->inquiry_type_id = $inquiry_type->id;
                $inquiry_backlogs->customer_id = $customer->id;
                $inquiry_backlogs->vehicle_id = $vehicle->id;
                $inquiry_backlogs->transaction = $row['Transaction'];
                $inquiry_backlogs->category = $row['Category'];
                $inquiry_backlogs->quantity = $row['Quantity'];
                $inquiry_backlogs->remarks = $row['Remarks'];
                $inquiry_backlogs->date = $row['Date Assigned'];
                $inquiry_backlogs->status_id = Status::where('status', 'like', 'Processed')->first()->id;
                $inquiry_backlogs->status_updated_by = Auth::user()->id;
                $inquiry_backlogs->status_updated_at = now();
                $inquiry_backlogs->created_by = Auth::user()->id;
                $inquiry_backlogs->updated_by = Auth::user()->id;
                $inquiry_backlogs->save();


                //update customer table with inquiry_backlogs_id
                $customer = Customer::where('id', $inquiry_backlogs->customer_id)->first();
                $customer->inquiry_id = $inquiry_backlogs->id;
                $customer->save();


                //add in application table with status processed

                $bank = Banks::where('bank_name', $row['Bank'])->first();
                if(!$bank){
                    $bank = new Banks();
                    $bank->bank_name = $row['Bank'];
                    $bank->save();
                }

                $application = new ApplicationBacklogs();
                $application->customer_id = $customer->id;
                $application->vehicle_id = $vehicle->id;
                $application->status_id = Status::where('status', 'like', 'Processed')->first()->id;
                $application->bank_id = $bank->id;
                $application->terms = $row['Terms'];
                $application->percentage = $row['Percentage'];
                $application->transaction = $row['Transaction'];
                $application->remarks = $row['Remarks'];
                $application->created_by = Auth::user()->id;
                $application->updated_by = Auth::user()->id;
                $application->save();

                //add in transaction_backlogs table
                $status = Status::where('status', 'like', $row['Status'])->first();
                $inventory = Inventory::where('CS_number', $row['CS Number'])->first();
                $team = Team::where('name', $row['Team'])->first();

                $transaction_backlogs = new TransactionBacklogs();
                $transaction_backlogs->inquiry_id = $inquiry_backlogs->id;
                $transaction_backlogs->application_id = $application->id;
                $transaction_backlogs->reservation_id = Transactions::max('reservation_id') + 1;
                $transaction_backlogs->reservation_transaction_status = $status->id;
                $transaction_backlogs->inventory_id = $inventory->id;
                $transaction_backlogs->team_id = $team->id;  
                $transaction_backlogs->profit = $row['Profit'];
                $transaction_backlogs->application_transaction_date = $row['Application Date'];
                $transaction_backlogs->transaction_updated_date = $application->updated_at->format('Y-m-d');
                $transaction_backlogs->reservation_date = $application->updated_at->format('Y-m-d');
                $transaction_backlogs->released_date = $row['Date Released'];
                $transaction_backlogs->remarks = $row['Remarks'];
                $transaction_backlogs->status = $status->id;
                $transaction_backlogs->lto_remarks = $row['LTO_Remarks'];
                $transaction_backlogs->insurance = $row['Insurance'];
                $transaction_backlogs->created_by = Auth::user()->id;
                $transaction_backlogs->updated_by = Auth::user()->id;
                $transaction_backlogs->save();
            }
    }
}

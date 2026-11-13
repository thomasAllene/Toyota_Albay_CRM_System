<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Inquiry;
use App\Models\Status;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class DisputeController extends Controller
{
    public function index(){
        if(Auth::check()){
            return view('dispute.dispute');
        }else{
            return view('index');
        }
    }

    public function getDisputes(Request $request){
        $status = Status::where('status', 'like', 'Processed')->first()->id;

        if(Auth::user()->usertype->name === 'SuperAdmin'){
        $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType', 'updateBy'])
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '1')

                        ->where('status_id', '<>', $status)
                        ->orderBy('updated_at', 'desc');

        }elseif(Auth::user()->usertype->name === 'Group Manager'){
            $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType', 'updateBy'])
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '1')
                        ->whereHas('user', function($subQuery) {
                            $subQuery->where('team_id', Auth::user()->team_id);
                        })
                        ->where('status_id', '<>', $status)
                        ->orderBy('updated_at', 'desc');

        }
        else{
            $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType', 'updateBy'])
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '1')
                        ->where(function($subQuery) {
                            $subQuery->where('created_by', Auth::user()->id)
                                     ->orWhereHas('customer', function($customerQuery) {
                                         $customerQuery->whereHas('inquiry', function($inquiryQuery) {
                                             $inquiryQuery->where('is_dispute', '0')
                                                          ->where('created_by', Auth::user()->id);
                                         });
                                     });
                        })
                        // ->whereHas('inquiryType', function($subQuery) {
                        //     $subQuery->where('inquiry_type', 'Individual');
                        // })
                        ->where('status_id', '<>', $status)
                        ->orderBy('updated_at', 'desc');

        }


        if ($request->has('date_range') && !empty($request->date_range)) {
            [$startDate, $endDate] = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $endDate)->endOfDay();

            $query->whereBetween('updated_at', [$startDate, $endDate]);
        }

        $list = $query->get();

        return DataTables::of($list)
        ->addColumn('id', function($data) {
            return encrypt($data->id);
        })

        ->addColumn('agent', function($data) {
            $inquiry = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType', 'updateBy'])
                        ->where('customer_id', $data->customer_id)
                        ->whereNull('deleted_at')
                        ->where('is_dispute', '0')
                        ->first();
            return $inquiry->user->first_name. ' ' .$inquiry->user->last_name;
        })

        ->addColumn('disputed_agent', function($data) {
            return $data->user->first_name . ' ' . $data->user->last_name;
        })

        ->addColumn('client_name', function($data) {
            return $data->customer->customer_first_name . ' ' . $data->customer->customer_last_name;
        })

        ->editColumn('created_at', function($data) {
            return $data->created_at->format('M d, Y h:i A');
            // return $data->created_at ? $data->created_at->format('M d, Y h:i A') : '-';
        })

        ->editColumn('updated_at', function($data) {
            return $data->updated_at->format('M d, Y h:i A');
        })

        ->editColumn('created_by', function($data){
            return $data->user->first_name . ' ' . $data->user->last_name;
        })

        ->editColumn('updated_by', function($data){
            return $data->updateBy->first_name . ' ' . $data->updateBy->last_name;
        })

        ->addColumn('status', function($data) {
            return $data->status->status;
        })


        ->make(true);
    }

    public function cancel(){
        try {
            $inquiry = Inquiry::findOrFail(decrypt(request()->id));
            $inquiry->updated_by = Auth::user()->id;
            $inquiry->save();
            $inquiry->delete();

            return response()->json([
                'success' => true,
                'message' => 'The dispute has been disapproved.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approved(){
        try {
            $inquiry = Inquiry::findOrFail(decrypt(request()->id));
            $inquiry->is_dispute = 0;
            $inquiry->updated_by = $inquiry->created_by;
            $inquiry->save();

            $status = Status::where('status', 'like', 'Processed')->first()->id;


            $firstInquiry = Inquiry::whereNull('deleted_at')
                            ->where('customer_id', $inquiry->customer_id)
                            ->where('is_dispute', '0')
                            ->where('created_at', '<>', Auth::user()->id)
                            ->where('status_id', '<>',  $status)
                            ->first();

            if ($firstInquiry) {

                $customer = Customer::whereNull('deleted_at')->where('inquiry_id', $firstInquiry->id)->first();
                $customer->inquiry_id = $inquiry->id;
                $customer->save();

                $firstInquiry->save();
                $firstInquiry->delete();
                
            }

            return response()->json([
                'success' => true,
                'message' => 'The dispute has been approved.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDisputeCount(){

        $status = Status::where('status', 'like', 'Processed')->first()->id;

            if(Auth::user()->usertype->name === 'SuperAdmin'){
                $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType', 'updateBy'])
                            ->where('notif_status', 'open')
                            ->whereNull('deleted_at')
                            ->where('is_dispute', '1')
                            ->where('status_id', '<>', $status)
                            ->orderBy('updated_at', 'desc');
    
            }elseif(Auth::user()->usertype->name === 'Group Manager'){
                $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType', 'updateBy'])
                            ->where('notif_status', 'open')
                            ->whereNull('deleted_at')
                            ->where('is_dispute', '1')
                            ->whereHas('user', function($subQuery) {
                                $subQuery->where('team_id', Auth::user()->team_id);
                            })
                            ->where('status_id', '<>', $status)
                            ->orderBy('updated_at', 'desc');
    
            }
            else{
                $query = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType', 'updateBy'])
                            ->where('notif_status', 'open')
                            ->whereNull('deleted_at')
                            ->where('is_dispute', '1')
                            ->where(function($subQuery) {
                                $subQuery->where('created_by', Auth::user()->id)
                                         ->orWhereHas('customer', function($customerQuery) {
                                             $customerQuery->whereHas('inquiry', function($inquiryQuery) {
                                                 $inquiryQuery->where('is_dispute', '0')
                                                              ->where('created_by', Auth::user()->id);
                                             });
                                         });
                            })
                            ->where('status_id', '<>', $status)
                            ->orderBy('updated_at', 'desc');
    
            }

            $count = $query->count();

            return response()->json([
                'count' => $count
            ]);
    }

    public function updateDisputeStatus(){
        $inquiry = Inquiry::with([ 'user', 'customer', 'vehicle', 'status', 'inquiryType', 'updateBy'])
        ->where('notif_status', 'open')
        ->whereNull('deleted_at')
        ->where('is_dispute', '1')
        ->where(function($subQuery) {
            $subQuery->where('created_by', Auth::user()->id)
                     ->orWhereHas('customer', function($customerQuery) {
                         $customerQuery->whereHas('inquiry', function($inquiryQuery) {
                             $inquiryQuery->where('is_dispute', '0')
                                          ->where('created_by', Auth::user()->id);
                         });
                     });
        })
        ->get();

        foreach($inquiry as $inq){
            $inq->notif_status = 'closed';
            $inq->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'The dispute status has been updated.'
        ]);
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Usertype;
use App\Models\InventoryBacklog;
use App\Models\Team;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use Yajra\DataTables\Facades\DataTables;

class InventoryBacklogsController extends Controller
{
    public function index()
    {
        if(Auth::check()){
            return view('upload_backlogs.inventory_backlogs');
        }else{
            return view('index');
        }
    }

    public function uploadBacklogs(Request $request){
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
                // Skip header row if it exists
                if (isset($row['unit']) && $row['unit'] === 'Unit') {
                    continue;
                }

                // Create inventory backlog record

                $vehicle = Vehicle::whereRaw('LOWER(unit) = LOWER(?)', [$row['UNIT']])
                                    ->whereRaw('LOWER(variant) = LOWER(?)', [$row['VAIRANT']])
                                    ->whereRaw('LOWER(color) = LOWER(?)', [$row['COLOR']])
                                    ->first();

                $tag = User::whereRaw('LOWER(CONCAT(first_name, " ", last_name)) = LOWER(?)', [$row['tag']])
                            ->first();
                $team = Team::whereRaw('LOWER(name) = LOWER(?)', [$row['team_id']])
                            ->first();


                if(!$vehicle){
                    $vehicle = new Vehicle();
                    $vehicle->unit = $row['UNIT'];
                    $vehicle->variant = $row['VAIRANT'];
                    $vehicle->color = $row['COLOR'];
                    $vehicle->category = $row['CATEGORY'];
                    $vehicle->created_by = Auth::user()->id;
                    $vehicle->updated_by = Auth::user()->id;
                    $vehicle->save();
                }

                if($vehicle){
                    $vehicle = Vehicle::find($vehicle->id);
                    $vehicle->category = $row['CATEGORY'];
                    $vehicle->save();
                }

                // Parse dates properly, handling empty values
                $actualInvoiceDate = !empty($row['actual_invoice_date']) ? Carbon::parse($row['actual_invoice_date']) : null;
                $deliveryDate = !empty($row['delivery_date']) ? Carbon::parse($row['delivery_date']) : null;

                $inventoryBacklog = InventoryBacklog::create([
                    'vehicle_id' => $vehicle->id,
                    'year_model' => $row['year_model'],
                    'CS_number' => $row['CS NO.'],
                    'actual_invoice_date' => $actualInvoiceDate,
                    'delivery_date' => $deliveryDate,
                    'invoice_number' => $row['invoice_number'] ?? null,
                    'status' => $row['status'] ?? 'Available',
                    'incoming_status' => $row['incoming_status'] ?? 'On Stock',
                    'remarks' => $row['remarks'] ?? null,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                    'tag' => $tag->id ?? null,
                    'team_id' => $team->id ?? null,
                ]);

                // Add to response data
                $data[] = $inventoryBacklog;
                // dd($inventoryBacklog);
            }

            // Close the reader to release the file
            $reader = null;

            // Clean up the temporary file
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => $data
            ]);

    }

    public function inventoryBacklogsList(Request $request){

        // dd($request->start_date);
        $query = InventoryBacklog::with(['vehicle', 'transaction'])
                        ->whereNull('deleted_at')
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

       ->editColumn('variant', function($data) {
           return $data->vehicle->variant;
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

   public function transferToInventory(Request $request){
        $inventoryBacklogs = InventoryBacklog::whereNull('deleted_at')->get();
        $transferredCount = 0;
        $skippedCount = 0;

        foreach($inventoryBacklogs as $inventoryBacklog){
            // Check if inventory with same CS_number already exists
            $exists = Inventory::where('CS_number', $inventoryBacklog->CS_number)->exists();

            if (!$exists) {
                $inventory = Inventory::create([
                    'vehicle_id' => $inventoryBacklog->vehicle_id,
                    'year_model' => $inventoryBacklog->year_model,
                    'CS_number' => $inventoryBacklog->CS_number,
                    'actual_invoice_date' => $inventoryBacklog->actual_invoice_date,
                    'delivery_date' => $inventoryBacklog->delivery_date,
                    'invoice_number' => $inventoryBacklog->invoice_number,
                    'status' => $inventoryBacklog->status,
                    'incoming_status' => $inventoryBacklog->incoming_status,
                    'remarks' => $inventoryBacklog->remarks,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                    'tag' => $inventoryBacklog->tag,
                    'team_id' => $inventoryBacklog->team_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'backlogs_status' => 1,

                ]);

                $inventoryBacklog->delete();
                $transferredCount++;
            } else {
                $inventoryBacklog->delete();
                $skippedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Inventory transfer completed. Transferred: {$transferredCount}, Skipped: {$skippedCount}",
        ]);
   }

   public function deleteInventoryBacklog(Request $request){
        $inventoryBacklog = InventoryBacklog::find(decrypt($request->id));
        $inventoryBacklog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inventory backlog deleted successfully',
    ]);
   }

}

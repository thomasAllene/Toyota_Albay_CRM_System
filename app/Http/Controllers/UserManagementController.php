<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\Usertype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\TemporaryPasswordMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class UserManagementController extends Controller
{

    public function index(){
        if (Auth::check()){
            return view('user.user_management');
        }else{
            return view('index');
        }
    }

    public function list(){

       $query = User::with(['usertype', 'team'])->orderBy('updated_at', 'desc')
       ->get();

       return DataTables::of($query)
            ->editColumn('id', function($user){
                return encrypt($user->id);
            })
            ->addColumn('usertype', function($user){
                return $user->usertype->name;
            })
            ->addColumn('team', function($user){
                return $user->team->name ?? '';
            })
            // ->addColumn('updated_at', function($user){
            //     // return $user->updated_at;
            //     return $user->updated_at->format('M d, Y h:i A');
            // })
            ->addColumn('updated_at', function($user){
                return [
                    'display' => $user->updated_at->format('M d, Y h:i A'),
                    'timestamp' => $user->updated_at->timestamp
                ];
            })
            ->make(true);

    }

    public function store(Request $request){

        try{

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'usertype_id' => 'required|exists:usertypes,id',
                'team_id' => 'nullable|exists:team,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->usertype_id = $request->usertype_id;
            $user->team_id = $request->team_id;
            $user->status = 'Active';
            $user->created_by = Auth::user()->id;
            $user->created_at = now();
            $user->updated_by = Auth::user()->id;
            $user->updated_at = now();
            $user->save();

            return response()->json(['success' => true, 'message' => 'User created successfully'], 200);

        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function update(Request $request){
        try{

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required',
                'usertype_id' => 'required|exists:usertypes,id',
                'status' => 'required',
                'password' => 'nullable|string|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = User::findOrFail(decrypt($request->id));
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->usertype_id = $request->usertype_id;
            $user->team_id = $request->team_id ?? null;
            if($request->password){
                $user->password = Hash::make($request->password);
            }
            $user->status = $request->status;
            $user->updated_by = Auth::user()->id;
            $user->updated_at = now();
            $user->save();

            return response()->json(['success' => true, 'message' => 'User updated successfully'], 200);

        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    public function destroy(){

    }

    public function edit($id){
        try{
            $id = decrypt($id);
            $user = User::with(['usertype', 'team'])->findOrFail($id);

            $user->id = encrypt($user->id);
            $user->password = '';
            return response()->json($user);
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function getUserTypes(){

        $userTypes = Usertype::all();
        return response()->json($userTypes);

    }

    public function getTeams(){

        if(in_array(Auth::user()->usertype->name, ['Agent', 'Group Manager'])){
            $teams = Team::where('id', Auth::user()->team_id)->get();
        }else{
            $teams = Team::all();
        }
        return response()->json($teams);

    }

    public function sendTemporaryPassword($id){
        try {
            $user = User::findOrFail(decrypt($id));
            $letters = Str::upper(Str::random(4));
            $numbers = rand(100, 999);
            $password = 'TYT' . $letters . $numbers;
            $user->password = Hash::make($password);
            $user->save();

            // Send email
            // Mail::to($user->email)->send(new TemporaryPasswordMail($user, $password));

            return response()->json(['success' => 'Temporary password sent successfully', 'password' => $password], 200);
        } catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function changePasswordAll(){
        try {
            $defaultPassword = env('DEFAULT_PASSWORD'); // Fetch from .env, fallback to VSMS2025

            if (!$defaultPassword) {
                return response()->json(['error' => 'Default password not set'], 500);
            }
            $usertype = Usertype::where('name', 'Agent')->first()->id;
            $users = User::where('usertype_id', $usertype)->get();
            foreach ($users as $user) {
                $user->password = Hash::make($defaultPassword);
                $user->save();
            }

            return response()->json(['success' => 'Temporary password updated successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Password Reset Error: ' . $e->getMessage()); // Log the error securely
            return response()->json(['error' => 'Something went wrong. Please try again.'], 500);
        }
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Usertype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return view('permission.permission');
        } else {
            return view('index');
        }
    }

    public function list()
    {
        $permissions = Permission::all();
        return DataTables::of($permissions)
            ->addColumn('id', function ($permission) {
                return $permission->id;
            })
            ->make(true);
    }

    public function store(Request $request)
    {
       $permission = Permission::create([
        'permission_name' => $request->name,
        'permission_description' => $request->description,
        'created_at' => now(),
        'updated_at' => now(),
       ]);

       return response()->json(['success' => true, 'message' => 'Permission created successfully']);
    }

    public function updatePermissions(Request $request)
    {
        try{
            // Find the role by ID
            $role = Usertype::findOrFail($request->usertype_id);
            // dd(array_map('decrypt', $request->permissions));

            // Sync the permissions with the role
            $role->permissions()->sync($request->permissions, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Redirect back with a success message
            return response()->json(['success' => true, 'message' => 'Permissions updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating permissions: ' . $e->getMessage()], 500);
        }
    }

    public function getUserTypes()
    {
        $userTypes = Usertype::all();
        return response()->json($userTypes);
    }

    public function getUserTypePermissions($usertypeId)
    {
        $usertype = Usertype::findOrFail($usertypeId);
        $permissionIds = $usertype->permissions->pluck('id')->map(function($id) {
            return $id;
        });
        return response()->json($permissionIds);
    }
}

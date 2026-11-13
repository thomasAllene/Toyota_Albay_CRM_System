<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Usertype;
use App\Models\Permission;

class PermissionUsertypeSeeder extends Seeder
{
    public function run(): void
    {
        // AGENT/MP Permissions
        $agentPermissions = [
            'view_dashboard',
            'view_leads', 'create_lead', 'edit_lead', 'update_lead', 'update_remarks', 'process_leads', 'delete_leads',
            'view_application', 'list_pending_applications', 'list_approved_applications', 'list_cancelled_applications', 'list_cash_applications',
            'view_vehicle_reservation', 'list_available_units',
            'view_vehicle_releases',
            'view_vehicle_inventory', 'list_inventory',
            'view_disputes', 'list_disputes',
        ];

        // GROUP MANAGER Permissions
        $groupManagerPermissions = [
            'view_dashboard',
            'view_leads',
            'view_application', 'list_pending_applications', 'list_approved_applications', 'list_cancelled_applications', 'list_cash_applications',
            'view_vehicle_reservation', 'list_available_units', 'get_reserved_count', 'reservation_per_team',
            'view_vehicle_releases', 'released_units_list', 'released_per_team', 'get_released_count', 'update_profit',
            'view_vehicle_inventory', 'list_inventory', 'update_released_remarks'
        ];

        // FINANCING STAFF Permissions
        $financingStaffPermissions = [
            'view_dashboard',
            'view_application', 'list_pending_applications', 'list_approved_applications', 'edit_application',
            'update_application', 'get_banks', 'process_application', 'store_banks', 'update_bank_approval', 'update_terms',
            'view_vehicle_releases', 'released_units_list',
            'view_vehicle_inventory', 'list_inventory', 'list_available_units', 'get_total_inventory', 'edit_unit'
        ];

        // ADMIN STAFF Permissions
        $adminStaffPermissions = [
            'view_dashboard',
            'view_application', 'list_cancelled_applications', 'list_cash_applications', 'process_application',
            'list_cash_applications', 'list_cancelled_applications', 'cancel_application',
            'store_banks', 'get_banks', 'edit_application', 'update_application',
            'view_vehicle_reservation', 'list_available_units', 'get_reserved_count', 'process_pending_reservation',
            'process_reserved_reservation', 'get_cs_number', 'add_cs_number', 'cancel_pending_reservation',
            'view_vehicle_releases', 'released_units_list', 'process_vehicle_release', 'cancel_vehicle_release',
            'update_ltoremarks', 'get_status', 'update_status',
            'view_vehicle_inventory', 'list_inventory', 'get_total_inventory', 'store_vehicle', 'store_inventory', 'update_profit',
            'update_incoming_status', 'update_tags_inventory', 'update_inventory', 'edit_inventory', 'update_released_remarks', 'add_folder_number'

        ];

        // VEHICLE ADMIN Permissions
        $vehicleAdminPermissions = [
            'view_dashboard',
            'list_available_units',
            'view_vehicle_inventory', 'list_inventory', 'get_total_inventory', 'store_vehicle', 'store_inventory',
            'update_tags_inventory', 'update_incoming_status', 'update_status_inventory', 'update_inventory', 'edit_inventory',
            'update_released_remarks'

        ];

        // GENERAL MANAGER Permissions
        $generalManagerPermissions = [
            'view_dashboard',
            'view_vehicle_releases', 'released_units_list', 'get_released_count', 'grand_total_profit',
            'view_vehicle_inventory', 'list_inventory', 'get_total_inventory', 'list_available_units',
        ];

        $permissionsByUserType = [
            'Agent' => $agentPermissions,
            'Group Manager' => $groupManagerPermissions,
            'Financing Staff' => $financingStaffPermissions,
            'Sales Admin Staff' => $adminStaffPermissions,
            'Vehicle Admin' => $vehicleAdminPermissions,
            'General Manager' => $generalManagerPermissions,
        ];

        foreach ($permissionsByUserType as $usertypeName => $permissions) {
            $usertype = Usertype::where('name', $usertypeName)->first();
            if ($usertype) {
                foreach ($permissions as $permissionName) {
                    $permission = Permission::where('permission_name', $permissionName)->first();
                    if ($permission) {
                        DB::table('permission_usertype')->insert([
                            'permission_id' => $permission->id,
                            'usertype_id' => $usertype->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}

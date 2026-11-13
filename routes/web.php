<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SFMDashboardController;
use App\Http\Controllers\VehicleToSalesController;
use App\Http\Controllers\RankingDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\VehicleReservationController;
use App\Http\Controllers\VehicleReleasesController;
use App\Http\Controllers\VehicleInventoryController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\InventoryBacklogsController;
use App\Http\Controllers\ReleasesBacklogsController;

//LOGIN
Route::get('/', [LoginController::class, 'index']);
Route::post('/login', [LoginController::class, 'login'])->name("login.user");
Route::post('/logout', [LoginController::class, 'logout'])->name("logout");

//DASHBOARD
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:view_dashboard');
Route::get('/api/bar-chart-monthly-release', [DashboardController::class, 'barChartMonthlyRelease'])->name('api.bar-chart-monthly-release');
Route::get('/api/pie-per-transaction-type', [DashboardController::class, 'getReleasedCountPerTransType'])->name('api.pie-per-transaction-type');
Route::get('/api/released-data-by-bank', [DashboardController::class, 'getReleasedDataByBank'])->name('api.released-data-by-bank');
Route::get('/api/released-data-by-gender', [DashboardController::class, 'getReleasedDataByGender'])->name('api.released-data-by-gender');
Route::get('/api/released-data-by-source', [DashboardController::class, 'getReleaseDataBySource'])->name('api.released-data-by-source');
Route::get('/api/released-data', [DashboardController::class, 'getReleasedCount'])->name('api.released-data');

//SALES FUNNNEL MANAGEMENT DASHBOARD
Route::get('/sales-funnel-management-dashboard', [SFMDashboardController::class, 'index'])->name('dashboard.sales-funnel-management-dashboard');
Route::get('/fetchInquiryCount', [SFMDashboardController::class, 'fetchInquiryCount'])->name('dashboard.fetchInquiryCount');
Route::get('/fetchReservationCount', [SFMDashboardController::class, 'fetchReservationCount'])->name('dashboard.fetchReservationCount');
Route::get('/fetchVehicleQuantity', [SFMDashboardController::class, 'fetchVehicleQuantity'])->name('dashboard.fetchVehicleQuantity');
Route::get('/getInquiryCount', [SFMDashboardController::class, 'getInquiryCount'])->name('dashboard.getInquiryCount');

//VEHICLE TO SALES DASHBOARD
Route::get('/vehicle-to-sales-dashboard', [VehicleToSalesController::class, 'index'])->name('dashboard.vehicle-to-sales-dashboard');
Route::get('/vehicle-to-sales-dashboard/getReleasedToday', [VehicleToSalesController::class, 'getReleasedToday'])->name('dashboard.vehicle-to-sales-dashboard.getReleasedToday');
Route::get('/vehicle-to-sales-dashboard/totalDeliveriesToday', [VehicleToSalesController::class, 'totalDeliveriesToday'])->name('dashboard.vehicle-to-sales-dashboard.totalDeliveriesToday');
Route::get('/vehicle-to-sales-dashboard/getDailyDeliveries', [VehicleToSalesController::class, 'getDailyDeliveries'])->name('dashboard.vehicle-to-sales-dashboard.getDailyDeliveries');
Route::get('/vehicle-to-sales-dashboard/getDailyReservation', [VehicleToSalesController::class, 'getDailyReservation'])->name('dashboard.vehicle-to-sales-dashboard.getDailyReservation');

//RANKING DASHBOARD
Route::get('/ranking-dashboard', [RankingDashboardController::class, 'index'])->name('dashboard.ranking-dashboard');
Route::get('/ranking-dashboard/topAgent', [RankingDashboardController::class, 'topAgent'])->name('dashboard.ranking-dashboard.topAgent');
Route::get('/ranking-dashboard/topAgentList', [RankingDashboardController::class, 'topAgentList'])->name('dashboard.ranking-dashboard.topAgentList');
Route::get('/ranking-dashboard/topAgentBarChart', [RankingDashboardController::class, 'topAgentBarChart'])->name('dashboard.ranking-dashboard.topAgentBarChart');

Route::get('/dashboard/sales-funnel', [DashboardController::class, 'salesFunnel'])->name('dashboard.sales-funnel');
Route::get('/dashboard/profitability', [DashboardController::class, 'profitability'])->name('dashboard.profitability');
Route::get('/dashboard/vehicle-to-sales', [DashboardController::class, 'vehicleToSales'])->name('dashboard.vehicle-to-sales');
Route::get('/dashboard/ranking', [DashboardController::class, 'ranking'])->name('dashboard.ranking');



//LEADS
Route::get('/leads', [LeadController::class, 'index'])->name('leads')->middleware('permission:view_leads');
Route::post('/leads', [LeadController::class, 'store'])->name('leads.store')->middleware('permission:create_lead');
Route::get('/leads/individual/list', [LeadController::class, 'individualList'])->name('leads.individual.list')->middleware('permission:view_leads');
Route::get('/leads/fleet/list', [LeadController::class, 'fleetList'])->name('leads.fleet.list')->middleware('permission:view_leads');
Route::get('/leads/company/list', [LeadController::class, 'companyList'])->name('leads.company.list')->middleware('permission:view_leads');
Route::get('/leads/government/list', [LeadController::class, 'governmentList'])->name('leads.government.list')->middleware('permission:view_leads');
Route::post('/leads/processing', [LeadController::class, 'processing'])->name('leads.processing')->middleware('permission:process_leads');
Route::delete('/leads/destroy', [LeadController::class, 'destroy'])->name('leads.destroy')->middleware('permission:delete_leads');
Route::get('/getProvince', [LeadController::class, 'getProvince'])->name('leads.getProvince');
Route::get('/getUnit', [LeadController::class, 'getUnit'])->name('leads.getUnit');
Route::get('/getInquiryType', [LeadController::class, 'getInquiryType'])->name('leads.getInquiryType');
Route::get('/leads/get-variants-and-colors', [LeadController::class, 'getVariantsAndColors']);
Route::get('/leads/get-variants', [LeadController::class, 'getVariants'])->name('leads.getVariants');
Route::get('/leads/get-colors', [LeadController::class, 'getColor'])->name('leads.getColor');
Route::get('leads/edit/{id}', [LeadController::class, 'edit'])->name('leads.edit')->middleware('permission:edit_lead');
Route::post('/leads/update/{id}', [LeadController::class, 'update'])->name('leads.update')->middleware('permission:update_lead');
Route::post('/leads/updateRemarks/', [LeadController::class, 'updateRemarks'])->name('leads.updateRemarks')->middleware('permission:update_remarks');
Route::get('/leads/getAgent/', [LeadController::class, 'getAgent'])->name('leads.getAgent');
Route::get('/leads/count-inquiry', [LeadController::class, 'CountInquiry'])->name('leads.countInquiry');
Route::post('/leads/updateNotifStatus', [LeadController::class, 'updateNotifStatus'])->name('leads.updateNotifStatus');


// APPLICATION
Route::get('/application', [ApplicationController::class, 'index'])->name('application')->middleware('permission:view_application');
Route::get('/list/pending', [ApplicationController::class, 'list_pending'])->name('application.pending')->middleware('permission:list_pending_applications');
Route::get('/list/approved', [ApplicationController::class, 'list_approved'])->name('application.approved')->middleware('permission:list_approved_applications');
Route::get('/list/cancel', [ApplicationController::class, 'list_cancel'])->name('application.cancel')->middleware('permission:list_cancelled_applications');
Route::get('/list/cash', [ApplicationController::class, 'list_cash'])->name('application.cash')->middleware('permission:list_cash_applications');
// Route::post('/application/store', [ApplicationController::class, 'store'])->name('application.store')->middleware('permission:create_application');
Route::get('application/edit/{id}', [ApplicationController::class, 'edit'])->name('application.edit')->middleware('permission:edit_application');
Route::post('/application/update/{id}', [ApplicationController::class, 'update'])->name('application.update')->middleware('permission:update_application');
Route::get('/getBanks', [ApplicationController::class, 'getBanks'])->name('application.getBanks')->middleware('permission:get_banks');
Route::get('/getStatus', [ApplicationController::class, 'getStatus'])->name('application.getStatus');
Route::post('/application/processing', [ApplicationController::class, 'processing'])->name('application.processing')->middleware('permission:process_application');
Route::post('/application/cancel', [ApplicationController::class, 'cancel'])->name('application.status.cancel')->middleware('permission:cancel_application');
Route::post('/application/store/banks', [ApplicationController::class, 'updateBanks'])->name('application.store.banks')->middleware('permission:store_banks');
Route::get('/application/banks/{id}', [ApplicationController::class, 'getApplicationBanks'])->name('application.banks.get')->middleware('permission:get_banks');
Route::post('/application/banks/approval/{id}', [ApplicationController::class, 'updateBankApproval'])->name('application.banks.approval')->middleware('permission:update_bank_approval');
Route::post('/application/terms', [ApplicationController::class, 'updateTerms'])->name('application.terms')->middleware('permission:update_terms');
Route::post('/application/banks/update', [ApplicationController::class, 'updateApplicationBank'])->name('application.banks.update');
Route::get('/application/count', [ApplicationController::class, 'countApplication'])->name('application.count');
Route::post('/application/notif/status', [ApplicationController::class, 'updateApplicationNotifStatus'])->name('application.notif.status')->middleware('permission:application_update_notification');



// VEHICLE RESERVATION
Route::get('vehicle-reservation', [VehicleReservationController::class, 'index'])->name('vehicle.reservation')->middleware('permission:view_vehicle_reservation');
Route::get('vehicle-reservation/units/list', [VehicleReservationController::class, 'availableUnitsList'])->name('vehicle.reservation.units.list')->middleware('permission:list_available_units');
Route::get('vehicle-reservation/pending/list', [VehicleReservationController::class, 'list_pending'])->name('vehicle.reservation.pending.list')->middleware('permission:view_vehicle_reservation');
Route::get('vehicle-reservation/list', [VehicleReservationController::class, 'list_reserved'])->name('vehicle.reservation.list')->middleware('permission:view_vehicle_reservation');
Route::get('vehicle-reservation/getReservedCount', [VehicleReservationController::class, 'getReservedCount'])->name('vehicle.reservation.getReservedCount')->middleware('permission:view_vehicle_reservation');
Route::get('vehicle-reservation/reservationPerTeam', [VehicleReservationController::class, 'reservationPerTeam'])->name('vehicle.reservation.reservationPerTeam')->middleware('permission:view_vehicle_reservation');
Route::post('vehicle-reservation/processing_pending', [VehicleReservationController::class, 'processing_pending'])->name('vehicle.reservation.processing_pending')->middleware('permission:process_pending_reservation');
Route::post('vehicle-reservation/processing_reserved', [VehicleReservationController::class, 'processing_reserved'])->name('vehicle.reservation.processing_reserved')->middleware('permission:process_reserved_reservation');
Route::get('get-cs-number/{id}', [VehicleReservationController::class, 'getCSNumberByVehicleId'])->name('get-cs-number')->middleware('permission:get_cs_number');
Route::post('vehicle/reservation/addCSNumber', [VehicleReservationController::class, 'addCSNumber'])->name('vehicle.reservation.addCSNumber')->middleware('permission:add_cs_number');
Route::post('vehicle/reservation/cancel/pending', [VehicleReservationController::class, 'cancel_pending'])->name('vehicle.reservation.cancel.pending')->middleware('permission:cancel_pending_reservation');
Route::get('vehicle-reservation/edit/{id}', [VehicleReservationController::class, 'editUnit'])->name('vehicle.reservation.edit')->middleware('permission:edit_unit');
Route::post('vehicle-reservation/update/{id}', [VehicleReservationController::class, 'update'])->name('vehicle.reservation.update')->middleware('permission:edit_unit');
Route::get('vehicle-reservation/getVehicleReservationCount', [VehicleReservationController::class, 'getVehicleReservationCount'])->name('vehicle.reservation.getVehicleReservationCount')->middleware('permission:view_vehicle_reservation');
Route::post('vehicle-reservation/updateNotifStatus', [VehicleReservationController::class, 'updateNotifStatus'])->name('vehicle.reservation.updateNotifStatus')->middleware('permission:reservation_update_notification');


// VEHICLE RELEASES
Route::get('vehicle-releases', [VehicleReleasesController::class, 'index'])->name('vehicle.releases')->middleware('permission:view_vehicle_releases');
Route::get('vehicle-releases/pending/list', [VehicleReleasesController::class, 'list_pending_for_release'])->name('vehicle.releases.pending.list')->middleware('permission:view_vehicle_releases');
Route::get('vehicle-releases/released/list', [VehicleReleasesController::class, 'list_release'])->name('vehicle.releases.list')->middleware('permission:view_vehicle_releases');
Route::get('vehicle-releases/releasedUnitsList', [VehicleReleasesController::class, 'releasedUnitsList'])->name('vehicle.releases.units.list')->middleware('permission:view_vehicle_releases');
Route::get('vehicle-releases/releasedPerTeam', [VehicleReleasesController::class, 'releasedPerTeam'])->name('vehicle.releases.releasedPerTeam')->middleware('permission:view_vehicle_releases');
Route::get('vehicle-releases/getReleasedCount', [VehicleReleasesController::class, 'getReleasedCount'])->name('vehicle.releases.getReleasedCount')->middleware('permission:view_vehicle_releases');
Route::post('vehicle-releases/processing', [VehicleReleasesController::class, 'processing'])->name('vehicle.releases.processing')->middleware('permission:process_vehicle_release');
Route::post('vehicle-releases/cancel', [VehicleReleasesController::class, 'cancel_release'])->name('vehicle.releases.cancel')->middleware('permission:cancel_vehicle_release');
Route::post('vehicle-releases/updateProfit', [VehicleReleasesController::class, 'updateProfit'])->name('vehicle.releases.updateProfit')->middleware('permission:update_profit');
Route::post('vehicle-releases/updateLTORemarks', [VehicleReleasesController::class, 'updateLTORemarks'])->name('vehicle.releases.updateLTORemarks')->middleware('permission:update_ltoremarks');
Route::get('vehicle-releases/getStatus', [VehicleReleasesController::class, 'getStatus'])->name('vehicle.releases.getStatus')->middleware('permission:get_status');
Route::post('vehicle-releases/updateStatus', [VehicleReleasesController::class, 'updateStatus'])->name('vehicle.releases.updateStatus')->middleware('permission:update_status');
Route::get('vehicle-releases/GrandTotalProfit', [VehicleReleasesController::class, 'GrandTotalProfit'])->name('vehicle.releases.GrandTotalProfit')->middleware('permission:view_vehicle_releases');
Route::post('vehicle-releases/updateReleasedRemarks', [VehicleReleasesController::class, 'updateReleasedRemarks'])->name('vehicle.releases.updateReleasedRemarks')->middleware('permission:update_released_remarks');
Route::post('vehicle-releases/addInsurance', [VehicleReleasesController::class, 'addInsurance'])->name('vehicle.releases.addInsurance')->middleware('permission:add_insurance');
Route::get('vehicle-releases/getVehicleReleaseCount', [VehicleReleasesController::class, 'getVehicleReleaseCount'])->name('vehicle.releases.getVehicleReleaseCount')->middleware('permission:view_vehicle_releases');
Route::post('vehicle-releases/updateNotifStatus', [VehicleReleasesController::class, 'updateNotifStatus'])->name('vehicle.releases.updateNotifStatus')->middleware('permission:release_update_notification');

// VEHICLE INVENTORY
Route::get('vehicle-inventory', [VehicleInventoryController::class, 'index'])->name('vehicle.inventory')->middleware('permission:view_vehicle_inventory');
Route::get('vehicle-inventory/list/incoming', [VehicleInventoryController::class, 'inventoryIncomingList'])->name('vehicle.inventory.incoming.list')->middleware('permission:view_vehicle_inventory');
Route::get('vehicle-inventory/list', [VehicleInventoryController::class, 'inventoryList'])->name('vehicle.inventory.list')->middleware('permission:view_vehicle_inventory');
Route::get('vehicle-inventory/getTotalInventory', [VehicleInventoryController::class, 'getTotalInventory'])->name('vehicle.inventory.getTotalInventory')->middleware('permission:view_vehicle_inventory');
Route::post('/vehicle/store', [VehicleInventoryController::class, 'store'])->name('vehicle.store')->middleware('permission:store_vehicle');
Route::post('/inventory/store', [VehicleInventoryController::class, 'inventoryStore'])->name('inventory.store')->middleware('permission:store_inventory');
Route::get('vehicle-inventory/edit', [VehicleInventoryController::class, 'editInventory'])->name('vehicle.inventory.edit')->middleware('permission:edit_inventory');
Route::post('/inventory/update', [VehicleInventoryController::class, 'updateInventory'])->name('inventory.update')->middleware('permission:update_inventory');
// Route::get('inventory/status', [VehicleInventoryController::class, 'getInventoryStatus'])->name('inventory.status')->middleware('permission:view_vehicle_inventory');
Route::get('inventory/status/incomng', [VehicleInventoryController::class, 'getIncomingStatus'])->name('inventory.incoming.status')->middleware('permission:update_incoming_status');
Route::post('inventory/updateStatus', [VehicleInventoryController::class, 'updateInventoryStatus'])->name('inventory.updateStatus')->middleware('permission:update_status_inventory');
Route::get('inventory/getAgent', [VehicleInventoryController::class, 'getAgent'])->name('inventory.getAgent')->middleware('permission:view_vehicle_inventory');
Route::post('inventory/updateTags', [VehicleInventoryController::class, 'updateTags'])->name('inventory.updateTags')->middleware('permission:update_tags_inventory');
Route::get('inventory/incomingUnitsList', [VehicleInventoryController::class, 'incomingUnitsList'])->name('inventory.incomingUnitsList')->middleware('permission:view_vehicle_inventory');
Route::get('inventory/tagsPerTeam', [VehicleInventoryController::class, 'tagsPerTeam'])->name('inventory.tagsPerTeam')->middleware('permission:view_vehicle_inventory');

// PERMISSIONS
Route::get('permissions', [PermissionController::class, 'index'])->name('permissions')->middleware('permission:view_permissions');
Route::get('permissions/list', [PermissionController::class, 'list'])->name('permissions.list')->middleware('permission:view_permissions');
Route::post('permissions/store', [PermissionController::class, 'store'])->name('permissions.store')->middleware('permission:manage_permissions');
Route::post('permissions/update', [PermissionController::class, 'updatePermissions'])->name('permissions.update')->middleware('permission:manage_permissions');
Route::get('permissions/user-types', [PermissionController::class, 'getUserTypes'])->name('permissions.user-types')->middleware('permission:view_permissions');
Route::get('/permissions/usertype/{usertypeId}', [PermissionController::class, 'getUserTypePermissions'])
    ->name('permissions.usertype.permissions')->middleware('permission:view_permissions');


// BANKS
Route::get('banks', [BankController::class, 'index'])->name('banks')->middleware('permission:view_banks');
Route::post('banks/store', [BankController::class, 'store'])->name('banks.store')->middleware('permission:create_bank');
Route::get('banks/edit/{id}', [BankController::class, 'edit'])->name('banks.edit')->middleware('permission:edit_bank');
Route::match(['post', 'put'], 'banks/update/{id}', [BankController::class, 'update'])->name('banks.update')->middleware('permission:edit_bank');
Route::delete('banks/destroy/{id}', [BankController::class, 'destroy'])->name('banks.destroy')->middleware('permission:delete_bank');
Route::get('banks/list', [BankController::class, 'list'])->name('banks.list')->middleware('permission:view_banks');


// USER MANAGEMENT
Route::get('user-management', [UserManagementController::class, 'index'])->name('user.management')->middleware('permission:view_users');
Route::get('user-management/list', [UserManagementController::class, 'list'])->name('user.management.list')->middleware('permission:view_users');
Route::get('user-management/usertypes/list', [UserManagementController::class, 'getUserTypes'])->name('usertypes.list')->middleware('permission:view_users');
Route::get('user-management/teams/list', [UserManagementController::class, 'getTeams'])->name('teams.list');
Route::post('user-management/store', [UserManagementController::class, 'store'])->name('user.management.store')->middleware('permission:create_user');
Route::post('user-management/update', [UserManagementController::class, 'update'])->name('user.management.update')->middleware('permission:edit_user');
Route::delete('user-management/{id}/destroy', [UserManagementController::class, 'destroy'])->name('user.management.destroy')->middleware('permission:delete_user');
Route::get('user-management/{id}/edit', [UserManagementController::class, 'edit'])->name('user.management.edit')->middleware('permission:edit_user');
Route::get('user-management/{id}/send-temporary-password', [UserManagementController::class, 'sendTemporaryPassword'])->name('user.management.sendTemporaryPassword')->middleware('permission:manage_passwords');
Route::post('user-management/change-all-password', [UserManagementController::class, 'changePasswordAll'])->name('user.managemen.changePasswordAll')->middleware('permission:manage_passwords');

// USER MANAGEMENT
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

//TEAM MANAGEMENT
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::post('/team/create', [TeamController::class, 'createTeam'])->name('team.create');
    Route::post('/team/update/{id}', [TeamController::class, 'updateTeam'])->name('team.update');
    Route::get('/team/list', [TeamController::class, 'listTeam'])->name('team.list');

//DISPUTE
    Route::get('/dispute', [DisputeController::class, 'index'])->name('dispute.index');
    Route::get('/dispute/getDisputes', [DisputeController::class, 'getDisputes'])->name('dispute.getDisputes');
    Route::post('/dispute/cancel', [DisputeController::class, 'cancel'])->name('dispute.cancel');
    Route::post('/dispute/approved', [DisputeController::class, 'approved'])->name('dispute.approved');
    Route::get('/dispute/getDisputeCount', [DisputeController::class, 'getDisputeCount'])->name('dispute.getDisputeCount');
    Route::post('/dispute/updateDisputeStatus', [DisputeController::class, 'updateDisputeStatus'])->name('dispute.updateDisputeStatus');


//UPLOAD BACKLOGS
    //INVENTORY BACKLOGS
        Route::get('/inventory_backlogs', [InventoryBacklogsController::class, 'index'])->name('inventorybacklogs.index');
        Route::post('/inventory_backlogs/upload', [InventoryBacklogsController::class, 'uploadBacklogs'])->name('inventory.backlogs.upload');
        Route::get('/inventory_backlogs/list', [InventoryBacklogsController::class, 'inventoryBacklogsList'])->name('inventory.backlogs.list');
        Route::post('/inventory_backlogs/transfer', [InventoryBacklogsController::class, 'transferToInventory'])->name('inventory.backlogs.transfer');
        Route::post('/inventory_backlogs/delete', [InventoryBacklogsController::class, 'deleteInventoryBacklog'])->name('inventory.backlogs.delete');


    //RELEASES BACKLOGS
        Route::get('/releases_backlogs', [ReleasesBacklogsController::class, 'index'])->name('releasesbacklogs.index');
        Route::get('/releases_backlogs/list', [ReleasesBacklogsController::class, 'list_release_backlogs'])->name('releases.backlogs.list');
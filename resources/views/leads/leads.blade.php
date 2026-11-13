@extends('components.app')

@section('content')

<style>
    #inquiryTable td{
        white-space: nowrap;
    }

    /* Ensure parent structure doesn't mess up the layout */
    .table-responsive-wrapper {
        position: relative;
    }

    .fixed-header-scroll {
        position: sticky;
        top: 0;
        z-index: 999; /* Make sure it stays on top */
        background-color: #fff;
        border-bottom: 2px solid #ddd;
    }

    .table-responsive {
        max-height: 510px; /* Adjust this based on your layout */
        overflow-y: auto;
        overflow-x: auto;
    }

    /* Style the scrollbar */
    .table-responsive::-webkit-scrollbar {
        width: 12px; /* Scrollbar width */
        height: 12px; /* Horizontal scrollbar height */
    }

    /* Track (the empty space behind the scrollbar) */
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1; /* Light gray track */
    }

    /* Handle (the draggable part of the scrollbar) */
    .table-responsive::-webkit-scrollbar-thumb {
        background: #6f767e; /* Primary color, change to your color */
        border-radius: 10px; /* Rounded edges */
    }

    /* Hover effect on the handle */
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #233446; /* Darker shade on hover */
    }

    /* Style for horizontal scrollbar */
    .table-responsive::-webkit-scrollbar-horizontal {
        height: 10px; /* Horizontal scrollbar height */
    }
</style>

{{-- Page Title --}}
<div class="card bg-dark shadow-none mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <i class='bx bxs-layer-plus text-white' style="font-size: 24px;">&nbsp;</i>
            <h4 class="text-white mb-0">Leads</h4>
        </div>
    </div>
</div>

<!-- Data Privacy -->
<div class="modal fade" id="dataPrivacy" tabindex="-1" aria-labelledby="dataPrivacyLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
            <div class="d-flex align-items-center gap-1">
                <i class='bx bxs-error-alt fs-4' style="color: #ff0000;"></i>
                <h4 class="modal-title" id="exampleModalLabel" style="color: #ff0000;">Data Privacy</h4>
            </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
            </button>
        </div>
        <div class="modal-body">
          <p class="" style="color: #ff0000;">Reminder that all the information that is processed in this system is covered by the data privacy act of 2012.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-dark" id="addNewInquiryButton" data-bs-dismiss="modal">Proceed</button>
        </div>
      </div>
    </div>
</div>

{{-- View Remarks Modal --}}
<div class="modal fade" id="viewRemarksModal" tabindex="-1" aria-labelledby="largeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header d-flex align-items-center gap-2">
        <i class='bx bxs-message-rounded-detail'></i>
        <h5 class="modal-title" id="largeModalLabel">Remarks</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="remarksContent">
            <input type="hidden" name="remarks_id" id="remarks_id">
            <textarea class="form-control mb-2 d-none" id="remarks" name="remarks" rows="5" placeholder="">
            {{-- display remarks here --}}
            </textarea>
            <p class="fs-5 text-dark" id="remarksParagraph">
            </p>
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-label-success" id="editRemarksButton">Edit</button>
            <button class="btn btn-dark d-none save-remark" id="saveEditRemarksButton">Save</button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Edit Inquiry Modal --}}
<div class="modal fade" id="editInquiryFormModal" tabindex="-1" aria-labelledby="largeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="largeModalLabel">Inquiry Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editInquiryFormData">
            <div class="mb-4">
                {{-- Inquiry Type Field --}}
                <div class="row mb-2">
                    <div class="col-md">
                        <label for="edit_inquiry_type" class="form-label required">Select Inquiry Type</label>
                        <input type="text" class="form-control" id="edit_inquiry_type" name="edit_inquiry_type" placeholder="" / disabled>
                        <small class="text-danger" id="validateInquiryType">Please Select Inquiry Type</small>
                    </div>
                    <input type="hidden" class="form-control" id="edit_inquiry_type_id" name="inquiry_type_id" placeholder="" />
                </div>
                {{-- Fleet Field --}}
                <div class="row mb-2 d-none" id="editFleetColumnField">
                    <div class="col-md">
                        <label for="edit_fleet" class="form-label required">Fleet</label>
                        <input type="text" class="form-control" id="edit_fleet" name="fleet" placeholder="" />
                        <small class="text-danger" id="validateFleet">Enter Fleet Name</small>
                    </div>
                </div>
                {{-- Company Field --}}
                <div class="row mb-2 d-none" id="editCompanyColumnField">
                    <div class="col-md">
                        <label for="edit_company" class="form-label required">Company</label>
                        <input type="text" class="form-control" id="edit_company" name="company" placeholder="" />
                        <small class="text-danger" id="validateCompany">Enter Company Name</small>
                    </div>
                </div>
                {{-- Government Field --}}
                <div class="row mb-2 d-none" id="editGovernmentColumnField">
                    <div class="col-md">
                        <label for="edit_government" class="form-label required">Government</label>
                        <input type="text" class="form-control" id="edit_government" name="government" placeholder="" />
                        <small class="text-danger" id="validateGovernment">Enter Government Agency</small>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md">
                        <input type="hidden" class="form-control" id="edit_id" name="id" />
                        <label for="edit_first_name" class="form-label required">First Name</label>
                        <input type="text" class="form-control" id="edit_first_name" name="first_name" placeholder="" />
                        <small class="text-danger" id="validateFirstname">Enter Customer First Name</small>
                    </div>
                    <div class="col-md">
                        <label for="edit_last_name" class="form-label required">Last Name</label>
                        <input type="text" class="form-control" id="edit_last_name" name="last_name" placeholder="" />
                        <small class="text-danger" id="validateLastname">Enter Customer Last Name</small>
                    </div>
                </div>

                {{-- Gender and Age Field --}}
                <div class="row mb-2">
                    <div class="col-md" id="editGenderColumnField">
                        <label for="edit_gender" class="form-label required">Gender</label>
                        <select class="form-control" id="edit_gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="Female">Female</option>
                            <option value="Male">Male</option>
                        </select>
                        <small class="text-danger" id="validateGender">Please Select Gender</small>
                    </div>
                    <div class="col-md" id="birthdateColumnField">
                        <label for="age" class="form-label required">Birthdate</label>
                        <input type="date" class="form-control" id="edit_birthdate" name="birthdate" placeholder="" />
                        <small class="text-danger" id="validateEditBirthdate">Enter Customer Birthdate</small>
                    </div>
                    <div class="col-md" id="editAgeColumnField">
                        <label for="edit_age" class="form-label required">Age</label>
                        <input type="number" class="form-control" id="edit_age" name="age" placeholder="" />
                        <small class="text-danger" id="validateLastname">Enter Customer Age</small>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md">
                        <label for="mobile_number" class="form-label required">Mobile Number</label>
                        <input type="text" class="form-control" id="edit_mobile_number" name="mobile_number" placeholder="09" />
                        <small class="text-danger" id="validateMobileNumber">Enter Valid Mobile Number</small>
                    </div>
                    <div class="col-md">
                        <label for="edit_address" class="form-label required">Address</label>
                        <input type="text" class="form-control" id="edit_address" name="address" placeholder="" />
                        <small class="text-danger" id="validateAddress">Enter <Address></Address></small>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <div class="row mb-2">
                    <div class="col-md">
                        <label for="car_unit" class="form-label required">Unit</label>
                        <select class="form-control" id="edit_car_unit" name="car_unit">
                            <option value="">Select Unit</option>
                        </select>
                        <small class="text-danger" id="validateUnit">Please Select Unit</small>
                    </div>
                    <div class="col-md">
                        <label for="car_variant" class="form-label required">Variants</label>
                        <select class="form-control" id="edit_car_variant" name="car_variant">
                            <option value="">Select Variants</option>
                        </select>
                        <small class="text-danger" id="validateVariant required">Please Select Variant</small>
                    </div>
                    <div class="col-md">
                        <label for="car_color" class="form-label required">Color</label>
                        <select class="form-control" id="edit_car_color" name="car_color">
                            <option value="">Select Color</option>
                            <option value="any_color">Any Color</option>
                        </select>
                        <small class="text-danger" id="validateColor">Please Select Color</small>
                    </div>
                    <div class="col-md d-none" id="editQuantityColumnField">
                        <label for="edit_quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="edit_quantity" name="quantity" placeholder=""/>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md">
                    <label for="transaction" class="form-label required">Transactions</label>
                    <select class="form-control" id="edit_transaction" name="transaction">
                        <option value="">Select Transactions</option>
                        <option value="cash">Cash</option>
                        <option value="po">PO</option>
                        <option value="financing">Financing</option>
                    </select>
                    <small class="text-danger" id="validateTransaction">Please Select Transaction</small>
                </div>
                <div class="col-md">
                    <label for="source" class="form-label required">Source</label>
                    <select class="form-control" id="edit_source" name="source" >
                        <option value="">Select Source</option>
                        <option value="Repeat-Customer">Repeat Customer</option>
                        <option value="Social-Media">Social-Media</option>
                        <option value="Referral">"Referral"</option>
                        <option value="Mall Duty">Mall Duty</option>
                        <option value="Show Room">Show Room</option>
                        <option value="Saturation">Saturation</option>
                    </select>
                    <small class="text-danger" id="validateSource">Please Select Source</small>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md">
                    <label for="edit_category" class="form-label required">Category</label>
                    <select class="form-control" id="edit_category" name="category">
                        <option value="">Select Category</option>
                        <option class="" value="Hot" style="color: #ff0000; font-weight: bold;">Hot</option>
                        <option class="text-warning" value="Warm" style="font-weight: bold;">Warm</option>
                        <option class="text-info" value="Cold" style="font-weight: bold;">Cold</option>
                    </select>
                    <small class="text-danger" id="validateCategory">Please Select Category</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md d-flex justify-content-end gap-2">
                    @if(auth()->user()->can('update_lead'))
                    <button type="button" class="btn btn-success" id="editInquiryModalButton">Edit Details</button>
                    <button type="button" class="btn btn-label-danger d-none" id="cancelInquiryModalButton">Cancel</button>
                    <button type="submit" class="btn btn-dark d-none" id="saveEditInquiryModalButton">Save Changes</button>
                    @endif
                </div>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Inquiry Form --}}
<div class="row mb-4">
    <div class="col-md">
        <div class="card border border-primary shadow-none" id="inquiryFormCard" style="display: none;">
            <div class="card-header">
                <h5 class="text-primary card-title">Inquiry Form</h5>
            </div>
            <div class="card-body">
                <form id="leadFormData">
                    <div class="mb-4">
                        {{-- Inquiry Type Field --}}
                        <div class="row mb-4">
                            <div class="col-md">
                                <label for="inquiry_type" class="form-label required">Select Inquiry Type</label>
                                <select class="form-control" id="inquiry_type" name="inquiry_type">
                                    <option value="">Select Inquiry Type</option>
                                </select>
                                <small class="text-danger" id="validateInquiryType">Please Select Inquiry Type</small>
                            </div>
                            <input type="hidden" class="form-control" id="inquiry_type_id" name="inquiry_type_id" placeholder="" />
                        </div>
                        {{-- Fleet Field --}}
                        <div class="row mb-2 d-none" id="fleetColumnField">
                            <div class="col-md">
                                <label for="fleet" class="form-label required">Fleet</label>
                                <input type="text" class="form-control" id="fleet" name="fleet" placeholder="" />
                                <small class="text-danger" id="validateFleet">Enter Fleet Name</small>
                            </div>
                        </div>
                        {{-- Company Field --}}
                        <div class="row mb-2 d-none" id="companyColumnField">
                            <div class="col-md">
                                <label for="company" class="form-label required">Company</label>
                                <input type="text" class="form-control" id="company" name="company" placeholder="" />
                                <small class="text-danger" id="validateCompany">Enter Company Name</small>
                            </div>
                        </div>
                        {{-- Government Field --}}
                        <div class="row mb-2 d-none" id="governmentColumnField">
                            <div class="col-md">
                                <label for="government" class="form-label required">Government</label>
                                <input type="text" class="form-control" id="government" name="government" placeholder="" />
                                <small class="text-danger" id="validateGovernment">Enter Government Agency</small>
                            </div>
                        </div>
                        {{-- Customer First and Last Name Field --}}
                        <div class="row mb-2">
                            <div class="col-md">
                                <label for="first_name" class="form-label required">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Contact Person First Name" />
                                <small class="text-danger" id="validateFirstname">Enter Customer First Name</small>
                            </div>
                            <div class="col-md">
                                <label for="last_name" class="form-label required">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Contact Person Last Name" />
                                <small class="text-danger" id="validateLastname">Enter Customer Last Name</small>
                            </div>
                        </div>
                        {{-- Gender and Age Field --}}
                        <div class="row mb-2">
                            <div class="col-md" id="genderColumnField">
                                <label for="gender" class="form-label required">Gender</label>
                                <select class="form-control" id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Female">Female</option>
                                    <option value="Male">Male</option>
                                </select>
                                <small class="text-danger" id="validateGender">Please Select Gender</small>
                            </div>
                            <div class="col-md" id="birthdateColumnField">
                                <label for="age" class="form-label">Birthdate</label>
                                <input type="date" class="form-control" id="birthdate" name="birthdate" placeholder="" />
                                <small class="text-danger" id="validateBirthdate">Enter Customer Birthdate</small>
                            </div>
                            <div class="col-md" id="ageColumnField">
                                <label for="age" class="form-label">Age</label>
                                <input type="number" class="form-control" id="age" name="age" placeholder=""/>
                                <small class="text-danger" id="validateLastname">Enter Customer Age</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md">
                                <label for="mobile_number" class="form-label required">Mobile Number</label>
                                <input type="text" class="form-control" id="mobile_number" name="mobile_number" placeholder="09" />
                                <small class="text-danger" id="validateMobileNumber">Enter Valid Mobile Number</small>
                            </div>
                            <div class="col-md">
                                <label for="address" class="form-label required">Address</label>
                                <input type="text" class="form-control" id="address" name="address" placeholder="House/Number and Street, Barangay/Subdivision, City/Municipality, Province" />
                                <small class="text-danger" id="validateAddress">Enter <Address></Address></small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="row mb-2">
                            <div class="col-md">
                                <label for="car_unit" class="form-label required">Unit</label>
                                <select class="form-control" id="car_unit" name="car_unit">
                                    <option value="">Select Unit</option>
                                </select>
                                <small class="text-danger" id="validateUnit">Please Select Unit</small>
                            </div>
                            <div class="col-md">
                                <label for="car_variant" class="form-label required">Variants</label>
                                <select class="form-control" id="car_variant" name="car_variant">
                                    <option value="">Select Variants</option>
                                </select>
                                <small class="text-danger" id="validateVariant required">Please Select Variant</small>
                            </div>
                            <div class="col-md">
                                <label for="car_color" class="form-label required">Color</label>
                                <select class="form-control" id="car_color" name="car_color">
                                    <option value="">Select Color</option>
                                </select>
                                <small class="text-danger" id="validateColor">Please Select Color</small>
                            </div>
                            <div class="col-md d-none" id="quantityColumnField">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" placeholder="" value="1" />
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md">
                            <label for="transaction" class="form-label required">Transactions</label>
                            <select class="form-control" id="transaction" name="transaction">
                                <option value="">Select Transactions</option>
                                <option value="cash">Cash</option>
                                <option value="po">PO</option>
                                <option value="financing">Financing</option>
                            </select>
                            <small class="text-danger" id="validateTransaction">Please Select Transaction</small>
                        </div>
                        <div class="col-md">
                            <label for="source" class="form-label required">Source</label>
                            <select class="form-control" id="source" name="source" >
                                <option value="">Select Source</option>
                                <option value="Repeat-Customer">Repeat Customer</option>
                                <option value="Social-Media">Social-Media</option>
                                <option value="Referral">Referral</option>
                                <option value="Mall Duty">Mall Duty</option>
                                <option value="Show Room">Show Room</option>
                                <option value="Saturation">Saturation</option>
                            </select>
                            <small class="text-danger" id="validateSource">Please Select Source</small>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md">
                            <label for="category" class="form-label required">Category</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">Select Category</option>
                                <option class="" value="Hot" style="color: #ff0000; font-weight: bold;">Hot</option>
                                <option class="text-warning" value="Warm" style="font-weight: bold;">Warm</option>
                                <option class="text-info" value="Cold" style="font-weight: bold;">Cold</option>
                            </select>
                            <small class="text-danger" id="validateCategory">Please Select Category</small>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md">
                            <label for="additional_info" class="form-label">Remarks</label>
                            <textarea class="form-control" placeholder="Message" id="remarks" name="additional_info" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-label-danger" id="cancelInquiryFormButton">Cancel</button>
                            <button type="submit" class="btn btn-success">Add Inquiry</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Trigger Inquiry Form Button --}}
<div class="row mb-2">
    <div class="col-md d-flex justify-content-end">
        @if(auth()->user()->can('create_lead'))
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#dataPrivacy">Add New Inquiry</button>
        @endif
    </div>
</div>

{{-- Datatables --}}
<div class="row mb-2">
    <div class="col">
        <div class="card custom-card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md">
                        <div class="">
                            <label for="exampleFormControlSelect1" class="form-label">Filter Date</label>
                            <div class="input-group">
                                <input type="text" id="date-range-picker" class="form-control" placeholder="Select date range">
                            </div>
                        </div>
                    </div>
                    @if(auth()->user()->usertype->name === 'SuperAdmin' || auth()->user()->usertype->name === 'General Manager')
                    <div class="col-md">
                        <label for="exampleFormControlSelect1" class="form-label">Select Group</label>
                        <select class="form-select" id="filterGroup" aria-label="Default select example">
                        </select>
                    </div>
                    @endif
                    @if(auth()->user()->usertype->name !== 'Agent')
                    <div class="col-md">
                        <label for="exampleFormControlSelect1" class="form-label">Select Agent</label>
                        <select id="filterAgent" class="form-control" style="width:100%;">
                        </select>
                    </div>
                    @endif
                    <div class="col-md">
                        <label for="exampleFormControlSelect1" class="form-label">Select Source</label>
                        <select class="form-select" id="filterSource" aria-label="Default select example">
                            <option value="">Filter Source</option>
                            <option value="Repeat-Customer">Repeat Customer</option>
                            <option value="Social-Media">Social-Media</option>
                            <option value="Referral">Referral</option>
                            <option value="Mall Duty">Mall Duty</option>
                            <option value="Show Room">Show Room</option>
                            <option value="Saturation">Saturation</option>
                        </select>
                    </div>

                </div>

                <div class="row mb-3">
                    <div class="col-md">
                        <div class="btn-group d-flex flex-wrap w-100" role="group" aria-label="Basic example">
                            <button type="button" class="btn btn-label-dark active" data-route="{{ route('leads.individual.list') }}">
                                Individual
                                <span id="leadsIndividualTabBadge" class="badge bg-danger rounded-circle ms-2" style="display: none;">1</span>
                            </button>
                            <button type="button" class="btn btn-label-dark" data-route="{{ route('leads.fleet.list') }}">
                                Fleet
                                <span id="leadsFleetTabBadge" class="badge bg-danger rounded-circle ms-2" style="display: none;">1</span>
                            </button>
                            <button type="button" class="btn btn-label-dark" data-route="{{ route('leads.company.list') }}">
                                Corporate
                                <span id="leadsCompanyTabBadge" class="badge bg-danger rounded-circle ms-2" style="display: none;">1</span>
                            </button>
                            <button type="button" class="btn btn-label-dark" data-route="{{ route('leads.government.list') }}">
                                Government
                                <span id="leadsGovernmentTabBadge" class="badge bg-danger rounded-circle ms-2" style="display: none;">1</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Default Table without Scroll Bar --}}
                {{-- <div class="table-responsive">
                    <table id="inquiryTable" class="table table-bordered table-hover" style="width:100%">
                        <tbody>
                        </tbody>
                    </table>
                </div> --}}

                {{-- Horizontal Scroll Bar with CSS --}}
                <div class="table-responsive-wrapper">
                    <div class="fixed-header-scroll">
                      <div class="table-responsive">
                        <table id="inquiryTable" class="table table-bordered table-hover" style="width:100%">
                          <tbody>
                          </tbody>
                        </table>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('components.specific_page_scripts')
<script>

    function getTeams(){
        $.ajax({
            url: '{{ route('teams.list') }}',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let teamSelect = $('#filterGroup');
                teamSelect.empty();
                teamSelect.append('<option value="">Filter Group...</option>');
                data.forEach(function(item) {
                    teamSelect.append(`<option value="${item.id}">${item.name}</option>`);
                });
            },
            error: function(error) {
                console.error('Error loading team:', error);
            }
        });
    }
    getTeams();

    function getAgent(){
        $.ajax({
            url: '{{ route('leads.getAgent') }}',
            type: 'GET',
            data : {
                team_id: $('#filterGroup').val()
            },
            dataType: 'json',
            success: function(data) {
                let agentSelect = $('#filterAgent');
                agentSelect.empty();
                agentSelect.append('<option value="">Filter Agent...</option>');
                data.forEach(function(item) {
                    agentSelect.append(`<option value="${item.id}">${item.first_name} ${item.last_name}</option>`);
                });
                // Initialize Select2
                agentSelect.select2({
                    placeholder: "Select an option",
                    allowClear: true
                });
            },
            error: function(error) {
                console.error('Error loading team:', error);
            }
        });
    }

    @if(auth()->user()->usertype->name === 'Group Manager' )
    getAgent();
    @endif

    //Date filter
    flatpickr("#date-range-picker", {
        mode: "range",
        dateFormat: "m/d/Y",
        onChange: function(selectedDates, dateStr, instance) {
            // Check if both start and end dates are selected
            if (selectedDates.length === 2) {
                // Check if the end date is earlier than or equal to the start date
                if (selectedDates[1] <= selectedDates[0]) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: 'Please select a valid date range.',
                    });
                } else {
                    // Reload the tables if a valid range is selected
                    inquiryTable.ajax.reload(null, false);
                }
            }
        },
        // Add clear button
        onReady: function(selectedDates, dateStr, instance) {
            // Create a "Clear" button
            const clearButton = document.createElement("button");
            clearButton.innerHTML = "Clear";
            clearButton.classList.add("clear-btn");

            // Create a "Close" button
            const closeButton = document.createElement("button");
            closeButton.innerHTML = "Close";
            closeButton.classList.add("close-btn");

            // Append the buttons to the flatpickr calendar
            instance.calendarContainer.appendChild(clearButton);
            instance.calendarContainer.appendChild(closeButton);

            // Add event listener to clear the date and reload the tables
            clearButton.addEventListener("click", function() {
                instance.clear(); // Clear the date range
                inquiryTable.ajax.reload(null, false); // Reload the tables
            });

            // Add event listener to close the calendar
            closeButton.addEventListener("click", function() {
                instance.close(); // Close the flatpickr calendar
            });
        }
    });

    $(document).ready(function () {
        // updateLeadsBadge();

        // setInterval(updateLeadsBadge, 1000);
        // Event listeners for filter dropdowns
        $('#filterGroup').on('change', function() {
            getAgent();
            inquiryTable.ajax.reload();
        });
    });

    $(document).ready(function () {
      // Event listeners for filter dropdowns
      $('#filterAgent').select2({
            placeholder: "Select an option",
            allowClear: true
        });

        $('#filterAgent, #filterSource').on('change', function() {
            inquiryTable.ajax.reload();
        });



    });


    // DataTable initialization
    const inquiryTable = $('#inquiryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("leads.individual.list") }}',
                data: function(d) {
                    // Include the date range in the AJAX request
                    d.date_range = $('#date-range-picker').val();
                    d.group = $('#filterGroup').val();
                    d.agent = $('#filterAgent').val();
                    d.source = $('#filterSource').val();
                },
            },
            lengthMenu: [ [10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "All"] ],
            pageLength: 10,
            paging: true,
            responsive: false,
            dom: '<"top"lf>rt<"bottom"ip>',
            language: {
                search: "",
                searchPlaceholder: "Search..."
            },
            ordering: false,
            columns: [
                @if(auth()->user()->usertype->name === 'SuperAdmin' || auth()->user()->usertype->name === 'General Manager')
                { data: 'team', name: 'team', title: 'Group' },
                @endif
                @if(auth()->user()->usertype->name === 'SuperAdmin' || auth()->user()->usertype->name === 'Group Manager')
                { data: 'agent', name: 'agent', title: 'Agent' },
                @endif
                { data: 'client_name', name: 'client_name', title: 'Client Name' },
                { data: 'contact_number', name: 'contact_number', title: 'Contact No.' },
                { data: 'unit', name: 'unit', title: 'Unit' },
                { data: 'variant', name: 'variant', title: 'Variant' },
                { data: 'color', name: 'color', title: 'Color' },
                { data: 'transaction', name: 'transaction', title: 'Transaction' },
                { data: 'source', name: 'source', title: 'Source' },
                { data: 'status', name: 'status', title: 'Status', render: function(data) { return data ? data.charAt(0).toUpperCase() + data.slice(1) : ''; } },
                @if(auth()->user()->can('update_remarks'))
                {
                    data: 'id',
                    name: 'id',
                    title: 'Remarks',
                    render: function(data) {
                        return `
                            <div class="d-flex">
                                <button type="button" class="btn btn-icon me-2 btn-label-secondary border remarks-btn" data-bs-toggle="modal" data-bs-target="#viewRemarksModal" data-id="${data}">
                                    <span class="tf-icons bx bxs-message-rounded-detail bx-22px"></span>
                                </button>
                            </div>
                                `;
                    }
                },
                @endif
                { data: 'created_at', name: 'created_at', title: 'Date' },
                @if(auth()->user()->can('edit_lead')||
                    auth()->user()->can('process_leads')  ||
                    auth()->user()->can('delete_leads')
                )
                {
                    data: 'id',
                    title: 'Action',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return `
                            <div class="d-flex">
                                @if(auth()->user()->can('edit_lead'))
                                <button type="button" class="btn btn-icon me-2 btn-success edit-btn" data-bs-toggle="modal" data-bs-target="#editInquiryFormModal" data-id="${data}">
                                    <span class="tf-icons bx bxs-show bx-22px"></span>
                                </button>
                                @endif
                                @if(auth()->user()->can('process_leads'))
                                <button type="button" class="btn btn-icon me-2 btn-primary processing-btn" data-id="${data}">
                                    <span class="tf-icons bx bxs-check-circle bx-22px"></span>
                                </button>
                                @endif
                                @if(auth()->user()->can('delete_leads'))
                                <button type="button" class="btn btn-icon me-2 btn-danger delete-btn" data-id="${data}">
                                    <span class="tf-icons bx bxs-trash bx-22px"></span>
                                </button>
                                @endif
                            </div>
                        `;
                    }
                },
                @endif
            ],
            columnDefs: [

                {
                    targets: '_all', // Apply to all columns
                    render: function (data, type, row) {
                        // Guard against null/undefined cell values: calling
                        // .toUpperCase() on null throws and freezes the table
                        // on "Processing..." (e.g. Fleet/Company/Government
                        // rows whose company_name/department_name is empty).
                        if (data === null || data === undefined) {
                            return '';
                        }
                        return type === 'display' ? String(data).toUpperCase() : data;
                    }
                }
            ],
    });

    // Change DataTable route based on button click
    $('.btn-group .btn').on('click', function (e) {
        e.preventDefault();
        updateLeadsBadge();


        // Get the button text/title
        const buttonTitle = $(this).clone()    // Clone the button
        .children()                        // Get all child elements
        .remove()                          // Remove all child elements (including badge)
        .end()                            // Go back to original element
        .text()                           // Get remaining text
        .trim();
        console.log(buttonTitle); // For debugging

        // Update the notification status
        $.ajax({
            url: '{{ route("leads.updateNotifStatus") }}',
            type: 'POST',
            data: { inquiry_type: buttonTitle },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(response) {
                console.log(response);
                updateLeadsBadge();
            },
            error: function(error) {
                console.error('Error updating notification status:', error);
            }

        });

        // Clear the date range picker
        $('#date-range-picker').val(''); // Clear the date range input

        // Reload the table without resetting the paging
        inquiryTable.ajax.reload(null, false);

        // Get the route from the clicked button
        var route = $(this).data('route');
        inquiryTable.ajax.url(route).load();

        // Remove 'active' class from all buttons
        $('.btn-group .btn').removeClass('active');

        // Add 'active' class to the clicked button
        $(this).addClass('active');
    });

    // Inquiry Form Validation
    $(document).ready(function () {

        $.ajax({
            url: '{{ route('leads.getInquiryType') }}',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let typeSelect = $('#inquiry_type, #edit_inquiry_type');
                typeSelect.empty();
                typeSelect.append('<option value="">Select Type...</option>');
                data.forEach(function(item) {
                    typeSelect.append(`<option value="${item.inquiry_type}" data-id="${item.id}">${item.inquiry_type}</option>`);
                });
            },
            error: function(error) {
                console.error('Error loading type:', error);
            }
        });

        $.ajax({
            url: '{{ route('leads.getUnit') }}',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let unitSelect = $('#car_unit, #edit_car_unit');
                unitSelect.empty();
                unitSelect.append('<option value="">Select Unit...</option>');
                data.forEach(function(item) {
                    unitSelect.append(`<option value="${item.unit}">${item.unit}</option>`);
                });
            },
            error: function(error) {
                console.error('Error loading unit:', error);
            }
        });


         // Load variants and colors based on selected unit
        $('#car_unit, #edit_car_unit').on('change', function() {
            const selectedUnit = $(this).val();
            if (selectedUnit) {
                $.ajax({
                    url: '{{ route("leads.getVariants") }}',
                    type: 'GET',
                    data: { unit: selectedUnit },
                    dataType: 'json',
                    success: function(data) {
                        let variantSelect = $('#car_variant, #edit_car_variant');
                        variantSelect.empty();
                        variantSelect.append('<option value="">Select Variants...</option>');
                        // Check if data.variants is an array or a single value
                        if (Array.isArray(data.variants)) {
                            data.variants.forEach(function(variant) {
                                variantSelect.append(`<option value="${variant}">${variant}</option>`);
                            });
                        } else {
                            variantSelect.append(`<option value="${data.variants}">${data.variants}</option>`);
                        }
                    },
                    error: function(error) {
                        console.error('Error loading variants and colors:', error);
                    }
                });
            } else {
                // Clear the selects if no unit is selected
                $('#car_variant').empty().append('<option value="">Select Variants...</option>');
            }
        });

        $('#car_variant').on('change', function() {
            const selectedVariant = $(this).val();
            if (selectedVariant) {
                $.ajax({
                    url: '{{ route("leads.getColor") }}',
                    type: 'GET',
                    data: { variant: selectedVariant },
                    dataType: 'json',
                    success: function(data) {

                        let colorSelect = $('#car_color');
                        colorSelect.empty();
                        colorSelect.append('<option value="">Select Color...</option>');
                        // Check if data.colors is an array or a single value
                        if (Array.isArray(data.colors)) {
                            data.colors.forEach(function(color) {
                                colorSelect.append(`<option value="${color}">${color}</option>`);
                            });
                        } else {
                            colorSelect.append(`<option value="${data.colors}">${data.colors}</option>`);
                        }

                        if (!Array.isArray(data.colors) || !data.colors.includes('Any Color')) {
                            colorSelect.append('<option value="Any Color">Any Color</option>');
                        }
                    },
                    error: function(error) {
                        console.error('Error loading variants and colors:', error);
                    }
                });
            } else {
                // Clear the selects if no unit is selected
                $('#car_color').empty().append('<option value="">Select Color...</option>');
            }
        });

        $('#edit_car_variant').on('change', function() {
            const selectedVariant = $(this).val();
            if (selectedVariant) {
                $.ajax({
                    url: '{{ route("leads.getColor") }}',
                    type: 'GET',
                    data: { variant: selectedVariant },
                    dataType: 'json',
                    success: function(data) {

                        let colorSelect = $('#edit_car_color');
                        colorSelect.empty();
                        colorSelect.append('<option value="">Select Color...</option>');
                        // Check if data.colors is an array or a single value
                        if (Array.isArray(data.colors)) {
                            data.colors.forEach(function(color) {
                                colorSelect.append(`<option value="${color}">${color}</option>`);
                            });
                        } else {
                            colorSelect.append(`<option value="${data.colors}">${data.colors}</option>`);
                        }

                        if (!Array.isArray(data.colors) || !data.colors.includes('Any Color')) {
                            colorSelect.append('<option value="Any Color">Any Color</option>');
                        }

                    },
                    error: function(error) {
                        console.error('Error loading variants and colors:', error);
                    }
                });
            } else {
                // Clear the selects if no unit is selected
                $('#car_color').empty().append('<option value="">Select Color...</option>');
            }
        });

        // Hide warning messages initially
        $("small").hide();

        // Helper function to capitalize first letter of each word
        function capitalizeWords(str) {
            return str.replace(/\b\w/g, function (txt) {
                return txt.toUpperCase();
            });
        }

        function handleInquiryTypeChange() {
            const inquiryType = $('#inquiry_type').val();

                // $('#first_name, #last_name').closest('.row').hide(); // Show first and last name by default
                // $('#companyColumnField, #governmentColumnField, #quantityColumnField').addClass('d-none');
                // $('#first_name, #last_name, #company, #government, #quantity').removeClass('is-invalid border-danger').siblings('small').hide();
                // $('#gender, #age').closest('.row').hide();

            if (inquiryType === 'Individual') {
                // No special validation changes for individual, just hide others
                $('#first_name, #last_name').closest('.row').show(); // Show first and last name by default
                $('#companyColumnField, #governmentColumnField, #quantityColumnField').addClass('d-none');
                $('#quantityColumnField').addClass('d-none');
                $('#companyColumnField').addClass('d-none');
                $('#gender, #age').closest('.row').show();

                $('#fleetColumnField').addClass('d-none');
            } else if (inquiryType === 'Fleet' || inquiryType === 'Company') {
                // Hide first and last name, show quantity
                $('#first_name, #last_name').closest('.row').show();
                $('#quantityColumnField').removeClass('d-none');
                $('#companyColumnField').toggleClass('d-none', inquiryType !== 'Company');
                $('#fleetColumnField').toggleClass('d-none', inquiryType !== 'Fleet');
                $('#gender, #age').closest('.row').hide(); // Hide gender and age for fleet and company
                $('#governmentColumnField').addClass('d-none');
            } else if (inquiryType === 'Government') {
                // Hide first name, last name, and company, show government field
                $('#fleetColumnField').addClass('d-none');
                $('#first_name, #last_name').closest('.row').show();
                $('#quantityColumnField').removeClass('d-none');
                $('#companyColumnField').addClass('d-none');
                $('#governmentColumnField').removeClass('d-none');
                $('#gender, #age').closest('.row').hide(); // Hide gender and age for fleet and company
            }
        }


        $('#inquiry_type').on('change', function () {
            let selectedId = $(this).find(':selected').data('id');
            $('#inquiry_type_id').val(selectedId || '');

            handleInquiryTypeChange();
        });


        function validateField(field, message) {
            const $field = $(field);
            const $errorMsg = $field.siblings('small');
            if (!$field.val()) {
                $field.addClass('is-invalid border-danger');
                $errorMsg.show();
                return false;
            }
            $field.removeClass('is-invalid border-danger');
            $errorMsg.hide();
            return true;
        }

        // Validate form on submit
        $("#leadFormData").on("submit", function (e) {
            e.preventDefault();
            let isValid = true;

            const inquiryType = $('#inquiry_type').val();

            // Always validate inquiry type
            isValid = validateField('#inquiry_type', 'Select Inquiry Type') && isValid;

            if (inquiryType === 'Individual' || inquiryType === '') {
                isValid = validateField('#first_name', 'Enter Customer First Name') && isValid;
                isValid = validateField('#last_name', 'Enter Customer Last Name') && isValid;
                // isValid = validateField('#birthdate', 'Enter Customer birthdate') && isValid;
                isValid = validateField('#age', 'Enter Customer Age') && isValid;
                isValid = validateField('#gender', 'Please Select Gender') && isValid;

            } else if (inquiryType === 'Fleet') {
                isValid = validateField('#fleet', 'Enter Fleet Name') && isValid;
                isValid = validateField('#first_name', 'Enter Customer First Name') && isValid;
                isValid = validateField('#last_name', 'Enter Customer Last Name') && isValid;

            } else if (inquiryType === 'Company') {
                isValid = validateField('#company', 'Enter Company Name') && isValid;
                isValid = validateField('#first_name', 'Enter Customer First Name') && isValid;
                isValid = validateField('#last_name', 'Enter Customer Last Name') && isValid;

            } else if (inquiryType === 'Government') {
                isValid = validateField('#government', 'Enter Government Agency') && isValid;
                isValid = validateField('#first_name', 'Enter Customer First Name') && isValid;
                isValid = validateField('#last_name', 'Enter Customer Last Name') && isValid;
            }

            // Validate shared required fields
            isValid = validateField('#mobile_number', 'Enter Valid Mobile Number') && isValid;
            isValid = validateField('#car_unit', 'Please Select Unit') && isValid;
            isValid = validateField('#car_variant', 'Please Select Variant') && isValid;
            isValid = validateField('#car_color', 'Please Select Color') && isValid;
            isValid = validateField('#transaction', 'Please Select Transaction') && isValid;
            isValid = validateField('#source', 'Please Select Source') && isValid;
            isValid = validateField('#address', 'Enter Address') && isValid;
            isValid = validateField('#category', 'Please Select a Category') && isValid;


            if (!isValid) {
                return; // Stop execution if validation fails
            }

            const formData = $(this).serialize();
            $.ajax({
                url: '{{ route("leads.store") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                //LOADER
                beforeSend: function() {
                    Swal.fire({
                        title: 'Loading...',
                        html: '<div class="spinner-grow text-primary" role="status" style="width: 3rem; height: 3rem;"></div>',
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                },
                success: function(response) {
                    //HIDE LOADER AFTER SUCCESS
                    Swal.close();

                    if (response.success) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                        });
                        // Reset form and hide it
                        $("#leadFormData")[0].reset();
                        $("#inquiryFormCard").hide();
                        $("#addNewInquiryButton").show();

                        // Clear all validation states
                        $(".text-danger").hide();
                        $("input, select").removeClass("is-invalid border-danger");
                        inquiryTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    //HIDE LOADER AFTER ERROR
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong!'
                    });
                }
            });

        });

         // Validate form on submit
        $("#editInquiryFormData").on("submit", function (e) {
            e.preventDefault();
            let isValid = true;

            const edit_inquiryType = $('#edit_inquiry_type').val();


            // Always validate inquiry type
            isValid = validateField('#edit_inquiry_type', 'Select Inquiry Type') && isValid;

            if (edit_inquiryType === 'Individual') {
                isValid = validateField('#edit_first_name', 'Enter Customer First Name') && isValid;
                isValid = validateField('#edit_last_name', 'Enter Customer Last Name') && isValid;
                isValid = validateField('#edit_age', 'Enter Customer Age') && isValid;
                isValid = validateField('#edit_gender', 'Please Select Gender') && isValid;

            } else if (edit_inquiryType === 'Fleet') {
                isValid = validateField('#edit_fleet', 'Enter Fleet Name') && isValid;
                isValid = validateField('#edit_last_name', 'Enter Customer Last Name') && isValid;

            } else if (edit_inquiryType === 'Company') {
                isValid = validateField('#edit_company', 'Enter Company Name') && isValid;
                isValid = validateField('#edit_last_name', 'Enter Customer Last Name') && isValid;

            } else if (edit_inquiryType === 'Government') {
                isValid = validateField('#edit_government', 'Enter Government Agency') && isValid;
                isValid = validateField('#edit_last_name', 'Enter Customer Last Name') && isValid;
            }

            // Validate required fields
            isValid = validateField('#edit_mobile_number', 'Enter Valid Mobile Number') && isValid;
            isValid = validateField('#edit_car_unit', 'Please Select Unit') && isValid;
            isValid = validateField('#edit_car_variant', 'Please Select Variant') && isValid;
            isValid = validateField('#edit_car_color', 'Please Select Color') && isValid;
            isValid = validateField('#edit_transaction', 'Please Select Transaction') && isValid;
            isValid = validateField('#edit_source', 'Please Select Source') && isValid;
            isValid = validateField('#edit_address', 'Enter Address') && isValid;

            // Special validation for mobile number
            const mobileNumber = $('#edit_mobile_number').val();
            if (mobileNumber && !mobileNumber.match(/^09\d{9}$/)) {
                $('#edit_mobile_number').addClass('is-invalid border-danger');
                $('#validateMobileNumber').text('Invalid Mobile Number').show();
                isValid = false;
            } else {
                $('#edit_mobile_number').removeClass('is-invalid border-danger');
                $('#validateMobileNumber').hide();
            }

            // Restore original values on invalid fields
            if (!isValid) {
                $('#edit_id').val(originalValues.id);

                $('#edit_car_unit').val(originalValues.carUnit);
                $('#edit_car_variant').val(originalValues.carVariant);
                $('#edit_car_color').val(originalValues.carColor);
                $('#edit_transaction').val(originalValues.transaction);
                $('#edit_source').val(originalValues.source);
                $('#edit_address').val(originalValues.address);
                $('#edit_quantity').val(originalValues.quantity);
                $('#edit_mobile_number').val(originalValues.mobileNumber);
                $('#edit_category').val(originalValues.category);
                $('#edit_birthdate').val(originalValues.birthdate).trigger('change');

                if (edit_inquiry_type === 'Individual') {
                    $('#edit_first_name').val(originalValues.firstName);
                    $('#edit_last_name').val(originalValues.lastName);
                    $('#edit_age').val(originalValues.age);
                    $('#edit_gender').val(originalValues.gender);

                } else if (edit_inquiry_type === 'Fleet' || edit_inquiry_type === 'Company') {

                    $('#edit_fleet').val(edit_inquiry_type === 'Company' ? '' : originalValues.fleet);
                    $('#edit_company').val(edit_inquiry_type === 'Fleet' ? '' : originalValues.company);

                } else if (edit_inquiry_type === 'Government') {
                    $('#edit_government').val(originalValues.government);
                }

                return; // Stop execution if validation fails
            }

            // Proceed with AJAX request if the form is valid
            const formData = $(this).serialize();
            const inquiryId = originalValues.id; // Assuming you set data-id on the form

            $.ajax({
                url: `/leads/update/${inquiryId}`, // Adjust URL as needed
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Loading...',
                        html: '<div class="spinner-grow text-primary" role="status" style="width: 3rem; height: 3rem;"></div>',
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                },
                success: function (response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                        });
                        // Reload the DataTable or update the UI as needed
                        inquiryTable.ajax.reload();
                        $('#editInquiryFormModal').modal('hide'); // Hide the modal
                    }
                },
                error: function (xhr) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong!'
                    });
                }
            });
        });


        $('#leadFormData input, #leadFormData select').on('input change', function() {
            validateField(this);
        });

        // Real-time Uppercase Transformation
        $("input[type='text'], textarea").on("input", function () {
            $(this).val($(this).val().toUpperCase());
        });

    });

    // Mobile Number Validation
    $(document).ready(function () {
        $("#mobile_number, #edit_mobile_number").on("input", function () {
            let value = $(this).val();

            // Enforce the number to start with "09" and allow only digits
            if (!/^09/.test(value)) {
                value = "09"; // If it doesn't start with "09", reset to "09"
            } else {
                value = value.replace(/[^0-9]/g, ""); // Remove any non-numeric characters
            }

            // Limit to exactly 11 digits
            if (value.length > 11) {
                value = value.slice(0, 11); // Truncate to 11 characters if exceeded
            }

            // Update the input field with the sanitized value
            $(this).val(value);
        });

        // Form submission event to check final validation
        $("#leadFormData, #editInquiryFormData").on("submit", function (event) {
            const mobileNumber = $("#mobile_number, #edit_mobile_number").val();
            if (mobileNumber.length !== 11) {
                event.preventDefault();
                $("#validateMobileNumber").show().text("Mobile number must be exactly 11 digits.");
            }
        });
    });

    let originalValues = {};0

    // Add this inside your <script> tag in the Blade file
    $(document).on('click', '.edit-btn', function() {
        // Clear all validation messages before opening the modal
        $(".text-danger").hide();
        $("input, select").removeClass("is-invalid border-danger");

        const inquiryId = $(this).data('id');
        $.ajax({
            url: `{{ url('leads/edit') }}/${inquiryId}`,
            type: 'GET',
            success: function(data) {
                // Populate the form fields with the inquiry data
                $('#edit_id').val(data.id);
                $('#edit_mobile_number').val(data.customer.contact_number);
                $('#edit_address').val(data.customer.address);
                $('#edit_car_unit').val(data.vehicle.unit).trigger('change');
                $('#edit_category').val(data.category).trigger('change');
                $('#edit_inquiry_type').val(data.inquiry_type.inquiry_type);
                $('#edit_quantity').val(data.quantity);
                $('#edit_birthdate').val(data.customer.birthdate).trigger('change');

                $('#edit_first_name').val(data.customer.customer_first_name);
                $('#edit_last_name').val(data.customer.customer_last_name);
                $('#edit_gender').val(data.customer.gender);
                $('#edit_age').val(data.customer.age);

                const edit_inquiry_type =  $('#edit_inquiry_type').val();

                if (edit_inquiry_type === 'Individual') {
                    // No special validation changes for individual, just hide others
                    $('#edit_first_name, #edit_first_name').closest('.row').show(); // Show first and last name by default
                    $('#editCompanyColumnField, #editGovernmentColumnField, #editQuantityColumnField').addClass('d-none');
                    $('#editQuantityColumnField').addClass('d-none');
                    $('#editCompanyColumnField').addClass('d-none');
                    $('#edit_gender, #edit_birthdate, #edit_age').closest('.row').show();
                    $('#editFleetColumnField').addClass('d-none');


                } else if (edit_inquiry_type === 'Fleet' || edit_inquiry_type === 'Company') {
                    // Hide first and last name, show quantity
                    $('#edit_first_name, #edit_first_name').closest('.row').show();
                    $('#editQuantityColumnField').removeClass('d-none');
                    $('#editCompanyColumnField').toggleClass('d-none', edit_inquiry_type !== 'Company');
                    $('#editFleetColumnField').toggleClass('d-none', edit_inquiry_type !== 'Fleet');
                    $('#edit_gender, #edit_birthdate, #edit_age').closest('.row').hide(); // Hide gender and age for fleet and company
                    $('#editGovernmentColumnField').addClass('d-none');

                    $('#edit_fleet').val(edit_inquiry_type === 'Company' ? '' : data.customer.company_name);
                    $('#edit_company').val(edit_inquiry_type === 'Fleet' ? '' : data.customer.company_name);

                } else if (edit_inquiry_type === 'Government') {
                    // Hide first name, last name, and company, show government field
                    $('#editFleetColumnField').addClass('d-none');
                    $('#edit_first_name, #edit_first_name').closest('.row').show();
                    $('#editQuantityColumnField').removeClass('d-none');
                    $('#editCompanyColumnField').addClass('d-none');
                    $('#editGovernmentColumnField').removeClass('d-none');
                    $('#edit_gender, #edit_birthdate, #edit_age').closest('.row').hide(); // Hide gender and age for fleet and company

                    $('#edit_government').val(data.customer.department_name);

                }


                // Get variants and colors based on the selected unit
                $.ajax({
                    url: '{{ route("leads.getVariants") }}',
                    type: 'GET',
                    data: { unit: data.vehicle.unit },
                    dataType: 'json',
                    success: function(variantsData) {
                        $('#edit_car_variant').val(data.vehicle.variant).trigger('change'); // Trigger change to update colors
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Could not fetch variants.'
                        });
                    }
                });

                $('#edit_car_variant').on('change', function() {
                    const selectedVariant = $(this).val(); // Get the selected variant

                     // Automatically select the color based on the variant
                        $.ajax({
                            url: '{{ route("leads.getColor") }}',
                            type: 'GET',
                            data: { variant: data.vehicle.variant },
                            dataType: 'json',
                            success: function(colorsData) {
                                // $('#edit_car_color').val(data.vehicle.color); // Automatically select the color

                                const colorValue = data.vehicle.color || 'any';
                                $('#edit_car_color').val(colorValue);
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Could not fetch colors.'
                                });
                            }
                        });

                })

                $('#edit_transaction').val(data.transaction);
                $('#edit_source').val(data.customer.source);
                $('#edit_remarks').val(data.remarks);

                // Store original values
                originalValues = {
                    id: data.id,
                    firstName: data.customer.customer_first_name,
                    lastName: data.customer.customer_last_name,
                    gender: data.customer.gender,
                    birthdate: data.customer.birthdate,
                    age: data.customer.age,
                    mobileNumber: data.customer.contact_number,
                    address: data.customer.address,
                    carUnit: data.vehicle.unit,
                    carVariant: data.vehicle.variant,
                    carColor: data.vehicle.color,
                    transaction: data.transaction,
                    source: data.customer.source,
                    remarks: data.remarks,
                    fleet: data.customer.company_name,
                    company: data.customer.company_name,
                    government: data.customer.department_name,
                    quantity: data.quantity,
                    category: data.category,
                    birthdate: data.customer.birthdate,
                };
                $('#editInquiryFormModal').modal('show');
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not fetch inquiry data.'
                });
            }
        });
    });

    // Add this inside your <script> tag in the Blade file
    $(document).on('click', '.remarks-btn', function() {
        // Clear all validation messages before opening the modal
        $(".text-danger").hide();
        $("input, select").removeClass("is-invalid border-danger");

        const inquiryId = $(this).data('id');
        $.ajax({
            url: `{{ url('leads/edit') }}/${inquiryId}`,
            type: 'GET',
            success: function(data) {

                $('#remarks').val(data.remarks);
                $('#remarks_id').val(data.id);
                $('#remarksParagraph').text(data.remarks);
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not fetch inquiry data.'
                });
            }
        });
    });

    // Displaying of Inquiry Form
    $(document).ready(function () {
            // Show #inquiryFormCard when #addNewInquiryButton is clicked
            $("#addNewInquiryButton").on("click", function () {
                // $("#addNewInquiryButton").hide();
                $("#inquiryFormCard").show(); // Display the form card
                // Clear validation messages
                $(".text-danger").hide();
                $("input, select").removeClass("is-invalid border-danger");
            });

            $("#cancelInquiryFormButton").on("click", function () {
                $("#addNewInquiryButton").show();
                $("#inquiryFormCard").hide(); // Hide the form card
                // Clear all fields in the form
                $("#inquiryFormCard input[type=text], textarea").val("");
                $("#inquiryFormCard select").val("");
                // Clear validation messages
                $(".text-danger").hide();
                $("input, select").removeClass("is-invalid border-danger");
            });
    });

    // Edit Modal Fields disabled state -> Editable State
    $(document).ready(function () {
        // Function to reset the modal to its initial uneditable state
        function resetModalToInitialState() {
            // Disable all input fields except the Edit button
            $("#editInquiryFormData :input").not("#editInquiryModalButton").prop("disabled", true);

            // Show the Edit button
            $("#editInquiryModalButton").removeClass("d-none");

            // Hide the Save and Cancel buttons
            $("#saveEditInquiryModalButton").addClass("d-none");
            $("#cancelInquiryModalButton").addClass("d-none");
        }

        // Initially, reset the modal to its initial state when the page is ready
        resetModalToInitialState();

        // When the Edit button is clicked
        $("#editInquiryModalButton").on("click", function () {
            // Enable all input fields except hidden fields
            $("#editInquiryFormData :input").not("[type='hidden']").prop("disabled", false);

            // Hide the Edit button
            $(this).addClass("d-none");

            // Show the Save Changes and Cancel buttons
            $("#saveEditInquiryModalButton").removeClass("d-none");
            $("#cancelInquiryModalButton").removeClass("d-none");
        });

        // When the Cancel button is clicked
        $("#cancelInquiryModalButton").on("click", function () {
            // Close the modal properly
            $("#editInquiryFormModal").modal("hide");

            // Reset the modal to its initial uneditable state when reopened
            resetModalToInitialState();
        });

        // Reset the modal when it's closed (using Bootstrap modal `hidden.bs.modal` event)
        $("#editInquiryFormModal").on("hidden.bs.modal", function () {
            resetModalToInitialState();
        });
    });

    // Save Remark
    $(document).on('click', '.save-remark', function() {
        const ID =  $('#remarks_id').val();
        const remarksData =  $('#remarks').val();
        $.ajax({
            url: '{{ route("leads.updateRemarks") }}',
            type: 'POST',
            data: {
                id: ID,
                remarks:remarksData,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                Swal.fire({
                    title: 'Loading...',
                    html: '<div class="spinner-grow text-primary" role="status" style="width: 3rem; height: 3rem;"></div>',
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
            },
            success: function(response) {
                Swal.close();
                if (response.success) {
                    Swal.fire(
                        'Updated!',
                        response.message,
                        'success'
                    );
                    $('#viewRemarksModal').modal('hide'); // Hide the modal

                     // Show the textarea and save button
                    $("#remarks").addClass("d-none");
                    $("#saveEditRemarksButton").addClass("d-none");

                    $("#remarksParagraph").removeClass("d-none");
                    $("#editRemarksButton").removeClass("d-none");
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire(
                    'Error!',
                    xhr.responseJSON?.message || 'Something went wrong!',
                    'error'
                );
                $("#remarks").addClass("d-none");
                $("#saveEditRemarksButton").addClass("d-none");

                $("#remarksParagraph").removeClass("d-none");
                $("#editRemarksButton").removeClass("d-none");

            }
        });

    });

    //Process Data
    $(document).on('click', '.processing-btn', function() {
        const leadId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to mark this lead as processing?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, process it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("leads.processing") }}',
                    type: 'POST',
                    data: {
                        id: leadId
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Loading...',
                            html: '<div class="spinner-grow text-primary" role="status" style="width: 3rem; height: 3rem;"></div>',
                            showConfirmButton: false,
                            allowOutsideClick: false
                        });
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            Swal.fire(
                                'Updated!',
                                response.message,
                                'success'
                            );
                            inquiryTable.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON?.message || 'Something went wrong!',
                            'error'
                        );
                    }
                });
            }
        });
    });

    //Delete data
    $(document).on('click', '.delete-btn', function() {
        const leadId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to delete this lead?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("leads.destroy") }}', // Ensure this route is defined in your routes
                    type: 'DELETE',
                    data: {
                        id: leadId,
                        _token: '{{ csrf_token() }}' // Include CSRF token
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Loading...',
                            html: '<div class="spinner-grow text-primary" role="status" style="width: 3rem; height: 3rem;"></div>',
                            showConfirmButton: false,
                            allowOutsideClick: false
                        });
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            );
                            inquiryTable.ajax.reload(); // Reload the DataTable
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON?.message || 'Something went wrong!',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Edit Remarks hide show
    $(document).ready(function () {
        $("#editRemarksButton").on("click", function () {
            // Hide the remarks paragraph and edit button
            $("#remarksParagraph").addClass("d-none");
            $("#editRemarksButton").addClass("d-none");

            // Show the textarea and save button
            $("#remarks").removeClass("d-none");
            $("#saveEditRemarksButton").removeClass("d-none");
        });
    });


    // Compute Birthdate store to age
    $(document).ready(function () {
        // Attach an event listener to the birthdate field
        $('#birthdate, #edit_birthdate').on('change', function () {
            // Get the entered birthdate value
            const birthdate = $(this).val();

            // Check if a valid date is provided
            if (birthdate) {
                // Calculate the age
                const birthDateObj = new Date(birthdate);
                const today = new Date();

                let age = today.getFullYear() - birthDateObj.getFullYear();
                const monthDiff = today.getMonth() - birthDateObj.getMonth();

                // Adjust age if the current date is before the birthdate this year
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDateObj.getDate())) {
                    age--;
                }

                // Set the age field value
                $('#age, #edit_age').val(age);
            } else {
                // Clear the age field if the birthdate is invalid
                $('#age, #edit_age').val('');
            }
        });
    });

    updateLeadsBadge();



</script>


@endsection

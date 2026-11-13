@extends('components.app')

@section('content')

<style>
    #applicationTable td{
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
        max-height: 580px; /* Adjust this based on your layout */
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
            <i class='bx bx-list-plus text-white' style="font-size: 24px;">&nbsp;</i>
            <h4 class="text-white mb-0">Application</h4>
        </div>
    </div>
</div>

<!-- Modal for Adding Bank for PO Transactions -->
<div class="modal fade" id="addSingleBankModal" tabindex="-1" role="dialog" aria-labelledby="addBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class='bx bxs-plus-circle'></i>
                    <h5 class="modal-title" id="largeModalLabel">Add Bank</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addSingleBankForm">
                    <!-- Bank Field Template -->
                    <div class="row mb-2 bank-field">
                        <div class="col-md">
                            <input type="hidden" name="application_id" id="bank_application_id">
                            <label for="add_single_bank" class="form-label required">Select a Bank</label>
                            <select class="form-control" name="bank_id" id="bank_id">
                            </select>
                            <small class="text-danger" id="validateBank">Please Select Bank</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-dark" form="addSingleBankForm">Add Bank</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding Terms -->
<div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="addBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class='bx bxs-offer'></i>
                    <h5 class="modal-title" id="largeModalLabel">Add Terms</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="termsForm">
                    <div class="row mb-2">
                        <div class="col-md">
                            <label for="terms" class="form-label required">Terms</label>
                            <select class="form-control" id="terms" name="terms" >
                                <option value="">Select Terms</option>
                                <option value="12 Months to Pay">12 Months to Pay</option>
                                <option value="24 Months to Pay">24 Months to Pay</option>
                                <option value="36 Months to Pay">36 Months to Pay</option>
                                <option value="48 Months to Pay">48 Months to Pay</option>
                                <option value="60 Months to Pay">60 Months to Pay</option>
                            </select>
                            <small class="text-danger" id="validateSource">Please Select Source</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md">
                            <div class="mb-3">
                                <label for="percentage" class="form-label required">Percentage-Based Down Payment</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="number" class="form-control" id="percentage" name="percentage" step="0.01" min="0">
                                    <b class="fs-4">%</b>
                                </div>
                                <small class="text-danger" id="validatePercentage">Please enter percentage</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-dark" form="termsForm">Add Terms</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding Banks -->
<div class="modal fade" id="selectBankModal" tabindex="-1" role="dialog" aria-labelledby="addBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class='bx bxs-bank' ></i>
                    <h5 class="modal-title" id="largeModalLabel">Select Banks</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="selectBankForm">
                    <input type="hidden" name="application_id" id="application_id">
                    <div id="bankFieldsContainer">
                        <div class="row mb-2">
                            <div class="col-md">
                                <button type="button" class="btn btn-label-dark" id="addBankFieldButton">
                                    {{-- <span class="tf-icons bx bxs-plus-circle bx-22px"></span> --}}
                                    Add More Field
                                </button>
                            </div>
                        </div>
                        <!-- Bank Field Template -->
                        <div class="row mb-2 bank-field">
                            <div class="col-md">
                                <select class="form-control" name="bank_id[]" id=bank_ids>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-dark" form="selectBankForm">Add these Banks</button>
            </div>
        </div>
    </div>
</div>

{{-- Bank Approval Modal --}}
<div class="modal fade" id="bankApprovalDateModal" tabindex="-1" role="dialog" aria-labelledby="bankApprovalDateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bankApprovalDateModalLabel">Bank Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="bankApprovalDateForm">
                    <div class="row d-flex align-items-center mb-2">
                    </div>

                    <label for="terms" class="form-label mt-2 required">Select Prefered Bank to Proceed in Application</label>
                    <select class="form-select" id="exampleFormControlSelect1" aria-label="Default select example" name="preferred_bank">
                        <option selected>Select Prefered Bank</option>
                    </select>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-dark" form="bankApprovalDateForm">Save</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Application Modal --}}
<div class="modal fade" id="editApplicationFormModal" tabindex="-1" aria-labelledby="largeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="largeModalLabel">Application Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editApplicationFormData">
            <div class="mb-4">
                {{-- Inquiry Type Field --}}
                <div class="row mb-2">
                    <div class="col-md">
                        <label for="edit_inquiry_type" class="form-label required">Select Inquiry Type</label>
                        <input type="text" class="form-control" id="edit_inquiry_type" name="edit_inquiry_type" placeholder="" / disabled>
                        <small class="text-danger" id="validateInquiryType">Please Select Inquiry Type</small>
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
                        <small class="text-danger" id="validateBirthdate">Enter Customer Birthdate</small>
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
                        <input type="number" class="form-control" id="edit_quantity" name="quantity" placeholder="" readonly/>
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
                        <option value="Referral">Referral</option>
                        <option value="Mall Duty">Mall Duty</option>
                        <option value="Show Room">Show Room</option>
                        <option value="Saturation">Saturation</option>
                    </select>
                    <small class="text-danger" id="validateSource">Please Select Source</small>
                </div>
            </div>
            <div class="row mb-2">
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
            <div class="row mb-4">
                <div class="col-md">
                    <label for="edit_payment_status" class="form-label required">Reservation Status</label>
                    <select class="form-control" id="edit_payment_status" name="payment_status">
                        <option value="none" selected>None</option>
                        <option value="paid">Paid</option>
                    </select>
                    <small class="text-danger" id="validatePaymentStatus">Please Select Payment Status</small>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md">
                    <label for="additional_info" class="form-label">Remarks</label>
                    <textarea class="form-control" placeholder="Message" id="edit_remarks" name="additional_info" rows="3"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md d-flex justify-content-end gap-2">
                    @if(auth()->user()->can('update_application'))
                    <button type="button" class="btn btn-success" id="editApplicationModalButton">Edit Details</button>
                    <button type="button" class="btn btn-label-danger d-none" id="cancelApplicationModalButton">Cancel</button>
                    <button type="submit" class="btn btn-primary d-none" id="saveEditApplicationModalButton">Save Changes</button>
                    @endif
                </div>
            </div>
          </form>
        </div>
      </div>
    </div>
</div>

{{-- Datatables Tabs --}}
<div class="row mb-2">
    <div class="col">
        <div class="card custom-card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="d-flex w-50 gap-2">
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" id="date-range-picker" class="form-control" placeholder="Select date range">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md">
                        <div class="btn-group w-100" role="group" aria-label="Basic example">
                            @if(auth()->user()->can('list_pending_applications'))
                            <button id="pending-tab" type="button" class="btn btn-label-dark" data-route="{{ route('application.pending') }}">
                                Pending Applications
                                <span id="applicationPendingTabBadge" class="badge bg-danger rounded-circle ms-2" style="display:;">1</span>
                            </button>
                            @endif
                            @if(auth()->user()->can('list_cash_applications'))
                            <button id="cash-tab" type="button" class="btn btn-label-dark" data-route="{{ route('application.cash') }}">
                                Cash/PO Applications
                                <span id="applicationCashPOTabBadge" class="badge bg-danger rounded-circle ms-2" style="display:;">1</span>
                            </button>
                            @endif
                            @if(auth()->user()->can('list_approved_applications'))
                            <button id="approved-tab" type="button" class="btn btn-label-dark" data-route="{{ route('application.approved') }}">
                                Approved Applications
                                <span id="applicationApprovedTabBadge" class="badge bg-danger rounded-circle ms-2" style="display:;">1</span>
                            </button>
                            @endif
                            @if(auth()->user()->can('list_cancelled_applications'))
                            <button id="canceled-tab" type="button" class="btn btn-label-dark" data-route="{{ route('application.cancel') }}">
                                Denied/Canceled Applications
                                <span id="applicationDeniedTabBadge" class="badge bg-danger rounded-circle ms-2" style="display:;">1</span>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- Default Table Without Scroll Bar --}}
                {{-- <div class="table-responsive">
                    <table id="applicationTable" class="table table-bordered table-hover" style="width:100%">
                        <tbody>
                        </tbody>
                    </table>
                </div> --}}

                {{-- Horizontal Scroll Bar with CSS --}}
                <div class="table-responsive-wrapper">
                    <div class="fixed-header-scroll">
                      <div class="table-responsive">
                        <table id="applicationTable" class="table table-bordered table-hover" style="width:100%">
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
                    applicationTable.ajax.reload(null, false);
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
                applicationTable.ajax.reload(null, false); // Reload the tables
            });

            // Add event listener to close the calendar
            closeButton.addEventListener("click", function() {
                instance.close(); // Close the flatpickr calendar
            });
        }
    });

    // DataTable initialization
    const applicationTable = $('#applicationTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            @if(auth()->user()->can('list_pending_applications'))
            url: '{{ route("application.pending") }}',
            @elseif(auth()->user()->can('list_cash_applications'))
            url: '{{ route('application.cash') }}',
            @elseif(auth()->user()->can('list_approved_applications'))
             url: '{{ route('application.approved') }}',
            @else
                url: '{{ route('application.cancel') }}',
            @endif

            data: function(d) {
                d.date_range = $('#date-range-picker').val();
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
            { data: 'id', name: 'id', title: 'ID' , visible: false},
            @if(auth()->user()->usertype->name === 'SuperAdmin' || auth()->user()->usertype->name === 'Financing Staff' || auth()->user()->usertype->name === 'Sales Admin Staff')
            { data: 'team', name: 'team', title: 'Group' },
            @endif
            { data: 'type', name: 'type', title: 'Type' },
            @if (auth()->user()->usertype->name === 'SuperAdmin' || auth()->user()->usertype->name === 'Group Manager')
            { data: 'agent', name: 'agent', title: 'Agent' },
            @endif
            { data: 'client_name', name: 'client_name', title: 'Customer' },
            { data: 'contact_number', name: 'contact_number', title: 'Contact No.' },
            { data: 'unit', name: 'unit', title: 'Unit' },
            { data: 'variant', name: 'variant', title: 'Variant' },
            { data: 'color', name: 'color', title: 'Color' },
            { data: 'transaction', name: 'transaction', title: 'Transaction' },
            { data: 'reservation_status', name: 'reservation_status', title: 'Reservation Status' },
            { data: 'source', name: 'source', title: 'Source' },
            { data: 'date', name: 'date', title: 'Date' },
            @if(auth()->user()->can('get_banks') || auth()->user()->can('store_banks') || auth()->user()->can('update_bank_approval') )
            {
                data: 'id',
                name: 'id',
                title: 'Bank',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    // Check if the transaction type is not 'cash'
                    if (row.transaction === 'financing') {
                        // Determine the active tab
                        let activeTab = $('.btn-group .active').attr('id'); // Get the ID of the active tab
                        let isPendingTab = activeTab === 'pending-tab';
                        let isApprovedTab = activeTab === 'approved-tab';
                        let isCashTab = activeTab === 'cash-tab';
                        let isCanceledTab = activeTab === 'canceled-tab';

                        // Hide or show buttons based on the active tab
                        let bankBtnStyle =  isCashTab || isCanceledTab ? 'display: none;' : ''; // Hide for Approved and Cash tabs
                        let approvalDateBtnStyle = isPendingTab || isCashTab || isCanceledTab ? 'display: none;' : ''; // Hide for Pending tab

                        return `
                            <div class="d-flex">
                                @if(auth()->user()->can('get_banks') && auth()->user()->can('store_banks') )
                                <button type="button" class="btn btn-icon me-2 btn-label-dark bank-btn" data-bs-toggle="modal" data-bs-target="#selectBankModal" data-id="${data}" style="${bankBtnStyle}">
                                    <span class="tf-icons bx bxs-bank bx-22px"></span>
                                </button>
                                @endif

                                @if(auth()->user()->can('update_bank_approval') )
                                    <button type="button" class="btn btn-icon me-2 btn-label-dark bank-approval-date-btn" data-bs-toggle="modal" data-bs-target="#bankApprovalDateModal" data-id="${data}" style="${approvalDateBtnStyle}">
                                        <span class="tf-icons bx bxs-calendar-plus bx-22px"></span>
                                    </button>
                                @endif
                            </div>
                        `;
                    }else if (row.transaction === 'po'){
                         // Determine the active tab
                        let activeTab = $('.btn-group .active').attr('id'); // Get the ID of the active tab
                        let isApprovedTab = activeTab === 'approved-tab';
                        let isCashTab = activeTab === 'cash-tab';

                        let bankBtnStyle =  isCashTab || isApprovedTab  ? '' : 'display: none;'; // Hide for Approved and Cash tabs

                        return `
                         @if(auth()->user()->can('get_banks') && auth()->user()->can('store_banks') )
                            <button type="button" class="btn btn-icon me-2 btn-label-dark single-bank-btn" data-bs-toggle="modal" data-bs-target="#addSingleBankModal" data-id="${data}" style="${bankBtnStyle}">
                                <span class="tf-icons bx bxs-bank bx-22px"></span>
                            </button>
                         @endif
                        `;
                    }else {
                        return ''; // Return empty string if transaction type is 'cash'
                    }
                }
            },
            @endif
            @if(auth()->user()->can('update_terms')  )

            {
                data: 'id',
                name: 'id',
                title: 'Terms',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    if(row.transaction === 'financing'){

                        let activeTab = $('.btn-group .active').attr('id'); // Get the ID of the active tab
                        let isApprovedTab = activeTab === 'approved-tab';
                        let termBtnStyle =  isApprovedTab ? '' : 'display: none;'; // Show for Approved tab

                        return `
                            <button type="button" class="btn btn-icon me-2 btn-warning term-btn" data-bs-toggle="modal" data-bs-target="#termsModal"  data-id="${data}" style="${termBtnStyle}">
                                    <span class="tf-icons bx bxs-offer bx-22px"></span>
                            </button>
                        `;
                    }else{
                        return '';
                    }
                }
            },
            @endif
            @if(auth()->user()->can('edit_application') || auth()->user()->can('update_application') || auth()->user()->can('cancel_application') || auth()->user()->can('process_application'))
            {
                data: 'id',
                name: 'id',
                title: 'Action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="d-flex">
                            @if(auth()->user()->can('edit_application'))
                            <button type="button" class="btn btn-icon me-2 btn-success edit-btn" data-bs-toggle="modal" data-bs-target="#editApplicationFormModal"  data-id="${data}">
                                <span class="tf-icons bx bxs-show bx-22px"></span>
                            </button>
                            @endif
                             @if(auth()->user()->can('process_application'))
                            <button type="button" class="btn btn-icon me-2 btn-primary processing-btn" data-id="${data}" data-transaction="${row.transaction}">
                                <span class="tf-icons bx bxs-check-circle bx-22px"></span>
                            </button>
                            @endif
                             @if(auth()->user()->can('cancel_application'))
                            <button type="button" class="btn btn-icon me-2 btn-danger cancel-btn" data-id="${data}">
                                <span class="tf-icons bx bxs-x-circle bx-22px"></span>
                            </button>
                            @endif
                        </div>
                    `;
                }
            }
            @endif
        ],
        columnDefs: [
            {
                targets: '_all', // Apply to all columns
                render: function (data, type, row) {
                    // Guard against null/undefined cell values: calling
                    // .toUpperCase() on null throws and freezes the table on
                    // "Processing..." (e.g. applications with no linked
                    // transaction return a null type / client_name).
                    if (data === null || data === undefined) {
                        return '';
                    }
                    return type === 'display' ? String(data).toUpperCase() : data;
                }
            }
        ],
    });

    // Automatically trigger the tab based on user access and remove active from the default active tab
    $(document).ready(function() {

        @if(auth()->user()->can('list_pending_applications'))
        $('.btn-group #pending-tab').addClass('active');
        @elseif(auth()->user()->can('list_cash_applications'))
        $('.btn-group #cash-tab').addClass('active');
        @elseif(auth()->user()->can('list_approved_applications'))
        $('.btn-group #approved-tab').addClass('active');
        @else
        $('.btn-group #canceled-tab').addClass('active');
        @endif

        $('.btn-group .btn').click(function() {
            $('.btn-group .btn').removeClass('active');
            $(this).addClass('active');
        });
    });

    // Change DataTable route based on button click
    $('.btn-group .btn').on('click', function(e) {
        e.preventDefault();
        updateApplicationBadge();

        const buttonTitle = $(this).clone()    // Clone the button
        .children()                        // Get all child elements
        .remove()                          // Remove all child elements (including badge)
        .end()                            // Go back to original element
        .text()                           // Get remaining text
        .trim();
        console.log(buttonTitle); // For debugging


        // Update the notification status
        $.ajax({
            url: '{{ route("application.notif.status") }}',
            type: 'POST',
            data: { tab_title: buttonTitle },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(response) {
                console.log(response);
                updateApplicationBadge();
            },
            error: function(error) {
                console.error('Error updating notification status:', error);
            }

        });


         // Clear the date range picker
        $('#date-range-picker').val(''); // Clear the date range input
        applicationTable.ajax.reload(null, false); // Reload the table without resetting the paging
        var route = $(this).data('route'); // Get the route from the clicked button
        applicationTable.ajax.url(route).load();
    });

    // Application Form Validation
    $(document).ready(function () {
        @if(auth()->user()->can('get_banks'))
        $.ajax({
            url: '{{ route('application.getBanks') }}',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let singleBankSelect = $('#bank_id');
                singleBankSelect.empty();
                singleBankSelect.append('<option value="">Select Bank...</option>');
                data.forEach(function(item) {
                    singleBankSelect.append(`<option value="${item.id}">${item.bank_name}</option>`);
                });
            },
            error: function(error) {
                console.error('Error loading unit:', error);
            }
        });
        @endif

        $.ajax({
            url: '{{ route('leads.getUnit') }}',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let unitSelect = $('#edit_car_unit');
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
        $('#edit_car_unit').on('change', function() {
            const selectedUnit = $(this).val();
            if (selectedUnit) {
                $.ajax({
                    url: '{{ route("leads.getVariants") }}',
                    type: 'GET',
                    data: { unit: selectedUnit },
                    dataType: 'json',
                    success: function(data) {
                        let variantSelect = $('#edit_car_variant');
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
        $("#editApplicationFormData").on("submit", function (e) {
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

            } else if (edit_inquiryType === 'Company') {
                isValid = validateField('#edit_company', 'Enter Company Name') && isValid;

            } else if (edit_inquiryType === 'Government') {
                isValid = validateField('#edit_government', 'Enter Government Agency') && isValid;
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

                if (edit_inquiryType === 'Individual') {
                    $('#edit_first_name').val(originalValues.firstName);
                    $('#edit_last_name').val(originalValues.lastName);
                    $('#edit_age').val(originalValues.age);
                    $('#edit_gender').val(originalValues.gender);

                } else if (edit_inquiryType === 'Fleet' || edit_inquiry_type === 'Company') {

                    $('#edit_fleet').val(edit_inquiryType === 'Company' ? '' : originalValues.fleet);
                    $('#edit_company').val(edit_inquiryType === 'Fleet' ? '' : originalValues.company);

                } else if (edit_inquiryType === 'Government') {
                    $('#edit_government').val(originalValues.government);
                }

                return; // Stop execution if validation fails
            }

            // Proceed with AJAX request if the form is valid
            const formData = $(this).serialize();
            const inquiryId = originalValues.id; // Assuming you set data-id on the form

            $.ajax({
                url: `/application/update/${inquiryId}`, // Adjust URL as needed
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                        });
                        // Reload the DataTable or update the UI as needed
                        applicationTable.ajax.reload();
                        $('#editApplicationFormModal').modal('hide'); // Hide the modal
                    }
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong!'
                    });
                }
            });
        });


        $('#editApplicationFormData input, #editApplicationFormData select').on('input change', function() {
            validateField(this);
        });

        // Real-time Capitalization
        $("input[type='text']").on("input", function () {
            $(this).val(capitalizeWords($(this).val()));
        });
    });

    let originalValues = {};0


    $(document).on('click', '.edit-btn', function() {
        const applicationId = $(this).data('id');
        $.ajax({
            url: `{{ url('application/edit') }}/${applicationId}`,
            type: 'GET',
            success: function(response) {
                const data = response.application;
                const statuses = response.statuses;
                const banks = response.banks;
                const inquiry = response.inquiry;
                const inquiry_type = response.inquirytype;
                const firstTransaction = response.firstTransaction;

                // Populate the form fields with the inquiry data
                $('#edit_id').val(data.id);
                $('#edit_first_name').val(data.customer.customer_first_name);
                $('#edit_last_name').val(data.customer.customer_last_name);
                $('#edit_gender').val(data.customer.gender);
                $('#edit_age').val(data.customer.age);
                $('#edit_mobile_number').val(data.customer.contact_number);
                $('#edit_address').val(data.customer.address);
                $('#edit_car_unit').val(data.vehicle.unit).trigger('change');
                $('#edit_inquiry_type').val(inquiry_type);
                $('#edit_category').val(inquiry.category).trigger('change');
                $('#edit_quantity').val(inquiry.quantity);
                $('#edit_payment_status').val(firstTransaction.reservation_status).trigger('change');
                $('#edit_first_name').val(data.customer.customer_first_name);
                $('#edit_last_name').val(data.customer.customer_last_name);
                $('#edit_gender').val(data.customer.gender);
                $('#edit_age').val(data.customer.age);
                $('#edit_birthdate').val(data.customer.birthdate).trigger('change');



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
                        $('#edit_gender, #edit_age').closest('.row').hide(); // Hide gender and age for fleet and company
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
                        $('#edit_gender, #edit_age').closest('.row').hide(); // Hide gender and age for fleet and company
                        $('#edit_government').val(data.customer.department_name);

                    }

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
                            $('#edit_car_color').val(data.vehicle.color); // Automatically select the color
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
                    quantity: inquiry.quantity,
                    category: inquiry.category,
                    birthdate: data.customer.birthdate,
                };

                $('#editApplicationFormModal').modal('show');

            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not fetch Application data.'
                });
            }
        });
    });

     //Process Data
    $(document).on('click', '.processing-btn', function() {
        const appID = $(this).data('id');
        const transaction = $(this).data('transaction');
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to update its status?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("application.processing") }}',
                    type: 'POST',
                    data: {
                        id: appID,
                        transaction: transaction,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Updated!',
                                response.message,
                                'success'
                            );
                            applicationTable.ajax.reload();
                        }
                    },
                    error: function(xhr) {
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

    $(document).on('click', '.cancel-btn', function() {
        const appID = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to cancel this application?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("application.status.cancel") }}',
                    type: 'POST',
                    data: {
                        id: appID
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Updated!',
                                response.message,
                                'success'
                            );
                            applicationTable.ajax.reload();
                        }
                    },
                    error: function(xhr) {
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

    // Edit Modal Fields disabled state -> Editable State
    $(document).ready(function () {
        // Function to reset the modal to its initial uneditable state
        function resetModalToInitialState() {
            // Disable all input fields except the Edit button
            $("#editApplicationFormData :input").not("#editApplicationModalButton").prop("disabled", true);

            // Show the Edit button
            $("#editApplicationModalButton").removeClass("d-none");

            // Hide the Save and Cancel buttons
            $("#saveEditApplicationModalButton").addClass("d-none");
            $("#cancelApplicationModalButton").addClass("d-none");
        }

        // Initially, reset the modal to its initial state when the page is ready
        resetModalToInitialState();

        // When the Edit button is clicked
        $("#editApplicationModalButton").on("click", function () {
            // Enable all input fields except hidden fields
            $("#editApplicationFormData :input").not("[type='hidden']").prop("disabled", false);

            // Hide the Edit button
            $(this).addClass("d-none");

            // Show the Save Changes and Cancel buttons
            $("#saveEditApplicationModalButton").removeClass("d-none");
            $("#cancelApplicationModalButton").removeClass("d-none");
        });

        // When the Cancel button is clicked
        $("#cancelApplicationModalButton").on("click", function () {
            // Close the modal properly
            $("#editApplicationFormModal").modal("hide");

            // Reset the modal to its initial uneditable state when reopened
            resetModalToInitialState();
        });

        // Reset the modal when it's closed (using Bootstrap modal `hidden.bs.modal` event)
        $("#editApplicationFormModal").on("hidden.bs.modal", function () {
            resetModalToInitialState();
        });
    });

    @if(auth()->user()->can('get_banks') && auth()->user()->can('store_banks') )
    function populateBankSelects(data, targetSelect = null) {
        if (targetSelect) {
            // Populate only the target select
            const selectedValue = targetSelect.val(); // Save the current selected value
            targetSelect.empty(); // Clear options
            targetSelect.append('<option value="">Select Banks...</option>'); // Add default option
            data.forEach(function (item) {
                targetSelect.append(`<option value="${item.id}">${item.bank_name}</option>`);
            });
            targetSelect.val(selectedValue); // Restore the selected value
        } else {
            // Populate all selects
            const bankSelects = $("select[name='bank_id[]']");
            bankSelects.each(function () {
                const bankSelect = $(this);
                const selectedValue = bankSelect.val(); // Save the current selected value
                bankSelect.empty();
                bankSelect.append('<option value="">Select Banks...</option>'); // Add default option
                data.forEach(function (item) {
                    bankSelect.append(`<option value="${item.id}">${item.bank_name}</option>`);
                });
                bankSelect.val(selectedValue); // Restore the selected value
            });
        }
    }

    // Fetch banks from the server
    function fetchBanks(targetSelect = null) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '{{ route('application.getBanks') }}',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    populateBankSelects(data, targetSelect);
                    resolve(data);
                },
                error: function (error) {
                    console.error('Error loading banks:', error);
                    reject(error);
                },
            });
        });
    }

    // Display selected banks on modal
    $(document).on('click', '.bank-btn', function () {
        const applicationId = $(this).data('id');
        $('#application_id').val(applicationId);

        // Clear existing bank fields
        $("#bankFieldsContainer .bank-field:not(:first)").remove();
        $("select[name='bank_id[]']").val('');

        // Fetch existing banks for this application
        $.ajax({
            url: `/application/banks/${applicationId}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const existingBanks = response.banks;

                    // If there are existing banks, create fields for each one
                    if (existingBanks.length > 0) {
                        // Remove the default empty field if it exists
                        $("#bankFieldsContainer .bank-field:first").remove();

                        existingBanks.forEach((bank, index) => {
                            const newBankField = `
                                <div class="row mb-2 bank-field">
                                    <div class="col-md d-flex align-items-center gap-2">
                                        <select class="form-control" name="bank_id[]">
                                            <option value="">Select Banks...</option>
                                        </select>
                                        ${index > 0 ? `
                                            <button type="button" class="btn btn-icon me-2 btn-label-danger removeBankFieldButton">
                                                <span class="tf-icons bx bxs-trash bx-22px"></span>
                                            </button>
                                        ` : ''}
                                    </div>
                                </div>
                            `;

                            $("#bankFieldsContainer").append(newBankField);
                        });

                        // Fetch and populate all bank options, then set selected values
                        fetchBanks().then(() => {
                            existingBanks.forEach((bank, index) => {
                                $("select[name='bank_id[]']").eq(index).val(bank.bank_id);
                            });
                        });
                    } else {
                        // If no existing banks, just fetch banks for the default single field
                        fetchBanks();
                    }
                }
            },
            error: function(xhr) {
                console.error('Error fetching existing banks:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Could not fetch existing banks.'
                });
            }
        });
    });

    $("#addBankFieldButton").on("click", function (e) {
        e.preventDefault();

        // Create a new bank field
        const newBankField = `
            <div class="row mb-2 bank-field">
                <div class="col-md d-flex align-items-center gap-2">
                    <select class="form-control" name="bank_id[]">
                        <option value="">Select Banks...</option>
                    </select>
                    <button type="button" class="btn btn-icon me-2 btn-label-danger removeBankFieldButton">
                        <span class="tf-icons bx bxs-trash bx-22px"></span>
                    </button>
                </div>
            </div>
        `;

        // Append the new field to the container
        const newField = $(newBankField);
        $("#bankFieldsContainer").append(newField);

        // Fetch and populate only the newly added select field
        const newSelect = newField.find("select[name='bank_id[]']");
        fetchBanks(newSelect);
    });
    @endif

    // Remove bank field
    $(document).on("click", ".removeBankFieldButton", function (e) {
        e.preventDefault();

        // Check if this field is not the very first field
        const bankFields = $(".bank-field");
        if (bankFields.length > 1) {
            // Remove the parent row of the clicked remove button
            $(this).closest(".bank-field").remove();
        } else {
            alert("The first field cannot be removed.");
        }
    });

    // Selecting Bank on submit
    $('#selectBankForm').on('submit', function(e) {
        e.preventDefault();

        // Collect selected bank IDs
        const bankIds = [];
        $("select[name='bank_id[]']").each(function() {
            const selectedValue = $(this).val();
            if (selectedValue) {
                bankIds.push(selectedValue);
            }
        });

        // Add bankIds to the form data
        const formData = $(this).serialize()
        // + '&bank_id=' + JSON.stringify(bankIds);

        // Submit the form data to the store method
        $.ajax({
            url: '{{ route('application.store.banks') }}',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire(
                        'Updated!',
                        response.message,
                        'success'
                    );
                    $('#selectBankModal').modal('hide'); // Hide the modal
                }
            },
            error: function(xhr) {
                Swal.fire(
                    'Error!',
                    xhr.responseJSON?.message || 'Something went wrong!',
                    'error'
                );
            }
        });
    });

    // When bank approval date button is clicked
    $(document).on('click', '.bank-approval-date-btn', function() {
        const applicationId = $(this).data('id');

        // Update the form action with the application ID
        $('#bankApprovalDateForm').attr('action', `/application/banks/approval/${applicationId}`);

        // Fetch banks associated with this application
        $.ajax({
            url: `/application/banks/${applicationId}`,
            type: 'GET',
            success: function(response) {
                // Clear existing bank fields
                $('#bankApprovalDateForm .bank-approval-row').remove();

                // Add fields for each bank
                let bankFields = '';
                response.banks.forEach(bank => {
                    bankFields += `
                        <div class="row d-flex align-items-center mb-2 bank-approval-row">
                            <div class="col-md-4">
                                <b>${bank.bank_name}</b>
                                <input type="hidden" name="bank_ids[]" value="${bank.bank_id}">
                            </div>
                            <div class="col-md-4">
                                <input type="date"
                                    class="form-control approval-date"
                                    name="approval_dates[]"
                                    value="${bank.approval_date || ''}"
                                    ${bank.approval_date}>
                            </div>
                            <div class="col-md-4">
                                <select class="form-control approval-status" name="approval_statuses[]">
                                    <option selected value= "">Select Approval Status</option>
                                    <option value="approve" ${bank.approval_status === 'approve' ? 'selected' : ''}>APPROVE</option>
                                    <option value="disapprove" ${bank.approval_status === 'disapprove' ? 'selected' : ''}>DISAPPROVE</option>
                                    <option value="pending" ${bank.approval_status === 'pending' ? 'selected' : ''}>PENDING</option>
                                </select>
                            </div>
                        </div>
                    `;
                });

                // Insert bank fields before the preferred bank select
                $(bankFields).insertBefore('#bankApprovalDateForm .mb-2');

                // Update preferred bank dropdown options
                let bankSelectOptions = '<option value="">Select Preferred Bank</option>';
                response.banks.forEach(bank => {
                    bankSelectOptions += `
                        <option value="${bank.bank_id}" ${bank.is_preferred ? 'selected' : ''}>
                            ${bank.bank_name}
                        </option>`;
                });
                $('#exampleFormControlSelect1').html(bankSelectOptions);

                $('#bankApprovalDateModal').modal('show');
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Could not fetch bank data.'
                });
            }
        });

        // Color Change indicator for Approve and Disapprove
        $(document).on('change', '.approval-status', function () {
            const $row = $(this).closest('.bank-approval-row'); // Find the closest row
            const $dateInput = $row.find('.approval-status');    // Find the associated date input field

            if ($(this).val() === 'approve') {
                $dateInput.removeClass('border-danger').addClass('border-success');
            } else if ($(this).val() === 'disapprove') {
                $dateInput.removeClass('border-success').addClass('border-danger');
            }
        });
    });

    // Handle bank approval date form submission
    $('#bankApprovalDateForm').on('submit', function(e) {
        e.preventDefault();

        // Validate fields
        let isValid = true;
        const bankIds = $('input[name="bank_ids[]"]');
        const approvalDates = $('input[name="approval_dates[]"]');
        const approvalStatuses = $('select[name="approval_statuses[]"]');
        const preferredBank = $('select[name="preferred_bank"]');

        // Validate bank IDs
        bankIds.each(function() {
            if ($(this).val() === '') {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Validate approval dates
        approvalDates.each(function() {
            if ($(this).val() === '') {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Validate approval statuses
        approvalStatuses.each(function() {
            if ($(this).val() === '') {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Validate preferred bank
        if (preferredBank.val() === '') {
            isValid = false;
            preferredBank.addClass('is-invalid');
        } else {
            preferredBank.removeClass('is-invalid');
        }

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please fill in all required fields.'
            });
            return;
        }

        $.ajax({
            url: $(this).attr('action'), // Use the updated form action
            type: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    $('#bankApprovalDateModal').modal('hide');
                    applicationTable.ajax.reload();
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Something went wrong!'
                });
            }
        });
    });

    // Add validation feedback for terms form
    $(document).on('click', '.term-btn', function() {
        const applicationId = $(this).data('id');
        $('#termsForm').data('application-id', applicationId); // Store ID on form

        // Reset validation state
        $('#validateSource, #validatePercentage').hide();
        $('#terms, #percentage').removeClass('is-invalid border-danger');
        $.ajax({
            url: `{{ url('application/edit') }}/${applicationId}`,
            type: 'GET',
            success: function(response) {
                const data = response.application;
                $('#terms').val(data.terms).trigger('change');
                $('#percentage').val(data.percentage);
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not fetch Application data.'
                });
            }
        });
    });

    $('#termsForm').on('submit', function(e) {
        e.preventDefault();

        // Reset validation state
        $('#validateSource, #validatePercentage').hide();
        $('#terms, #percentage').removeClass('is-invalid border-danger');

        // Get values
        const terms = $('#terms').val();
        const percentage = $('#percentage').val();
        const applicationId = $(this).data('application-id');

        // Validate
        let isValid = true;

        if (!terms) {
            $('#terms').addClass('is-invalid border-danger');
            $('#validateSource').show();
            isValid = false;
        }

        if (!percentage) {
            $('#percentage').addClass('is-invalid border-danger');
            $('#validatePercentage').show();
            isValid = false;
        }

        if (!isValid) {
            return;
        }

        // Submit if valid
        $.ajax({
            url: '{{ route('application.terms') }}',
            type: 'POST',
            data: {
                id: applicationId,
                terms: terms,
                percentage: percentage
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    $('#termsModal').modal('hide');
                    applicationTable.ajax.reload();
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Something went wrong!'
                });
            }
        });
    });

     // Compute Birthdate store to age
     $(document).ready(function () {
        // Attach an event listener to the birthdate field
        $('#edit_birthdate').on('change', function () {
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
                $('#edit_age').val(age);
            } else {
                // Clear the age field if the birthdate is invalid
                $('#edit_age').val('');
            }
        });
    });

    $(document).on('click', '.single-bank-btn', function() {
        const applicationId = $(this).data('id');

        $('#validateBank').hide();
        $('#bank_id').removeClass('is-invalid border-danger');
        $('bank_id').val();

        $.ajax({
            url: `{{ url('application/edit') }}/${applicationId}`,
            type: 'GET',
            success: function(response) {
                const data = response.application;
                $('#bank_application_id').val(applicationId);
                $('#bank_id').val(data.bank_id).trigger('change');
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not fetch Application data.'
                });
            }
        });

    });

    $('#addSingleBankForm').on('submit', function(e) {
        e.preventDefault();

         // Reset validation state
        $('#validateBank').hide();
        $('#bank_id').removeClass('is-invalid border-danger');

        // Get values
        const bankId = $('#bank_id').val();
        const applicationId = $('#application_id').val();

        // Validate
        let isValid = true;

        if (!bankId) {
            $('#bank_id').addClass('is-invalid border-danger');
            $('#validateBank').show();
            isValid = false;
        }

        if (!isValid) {
            return;
        }

        $.ajax({
            url: '{{ route('application.banks.update') }}',
            type: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    Swal.fire('Success', response.message, 'success');
                }
                $('#addSingleBankModal').modal('hide');
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong!', 'error');
            }
        })
    });

</script>


@endsection

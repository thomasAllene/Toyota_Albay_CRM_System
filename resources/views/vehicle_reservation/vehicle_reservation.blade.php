@extends('components.app')

@section('content')

<style>
    #vehicleReservationTable td{
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

    #vehicleReservationTable {
        text-transform: uppercase;
    }

    #availableUnitsTable {
        text-transform: uppercase;
    }

    #statusTable {
        text-transform: uppercase;
    }
</style>

{{-- Title Header --}}
<div class="card bg-dark shadow-none mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <i class='bx bxs-car text-white' style="font-size: 24px;">&nbsp;</i>
            <h4 class="text-white mb-0">Vehicle Reservation</h4>
        </div>
    </div>
</div>

{{-- Edit Reservation Modal --}}
<div class="modal fade" id="editReservationFormModal" tabindex="-1" aria-labelledby="largeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="largeModalLabel">Unit Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editReservationFormData">
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

                </div>
            </div>

            <div class="row">
                <div class="col-md d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-success" id="editReservationModalButton">Edit Details</button>
                    <button type="button" class="btn btn-label-danger d-none" id="cancelReservationModalButton">Cancel</button>
                    <button type="submit" class="btn btn-primary d-none" id="saveEditReservationModalButton">Save Changes</button>
                </div>
            </div>
          </form>
        </div>
      </div>
    </div>
</div>

<!-- Select CS Number Modal -->
<div class="modal fade" id="selectCSNumber" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form id="saveCSNumber">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Select CS Number</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                  </button>
              </div>
              <div class="modal-body">
                  <div class="mb-2">
                      <div class="">Customer: <b id="customerName"></b></div>
                      <div class="">Unit: <b id="unit"></b></div>
                      <div class="">Variant: <b id="variant"></b></div>
                      <div class="">Color: <b id="color"></b></div>
                  </div>
                  <div>
                     <input type="hidden" id="transaction_id" name="transaction_id">
                      <select class="form-select" id="csNumberSelect" name="cs_number" aria-label="Default select example">
                          <option selected>CS Number</option>
                      </select>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-dark ">Save changes</button>
              </div>
        </form>


      </div>
    </div>
</div>

{{-- Datatables --}}
<div class="row mb-4">
    <div class="col-md">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md">
                        <div class="card shadow-none border custom-card">
                            <div class="card-body">
                                <h5>Available Units</h5>
                                <div class="table-responsive">
                                    <table id="availableUnitsTable" class="table table-bordered table-hover" style="width:100%">
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="card shadow-none border custom-card">
                            <div class="card-body">
                                <h5>Status</h5>
                                <div class="table-responsive">
                                    <table id="statusTable" class="table table-bordered table-hover" style="width:100%">
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="card shadow-none bg-transparent w-100 h-100 d-flex justify-content-center align-items-center">
                            <div class="card-body text-center">
                                <h1 class="text-primary"><b>Total Vehicle Reservation</b></h1>
                                <h1 class="text-primary" style="font-size: clamp(15rem, 6vw, 3rem);"><b id="count" ></b></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- button group --}}
<div class="row mb-2">
    <div class="col">
        <div class="card custom-card">
            <div class="card-body">
                <div class="row">
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
                            <button type="button" class="btn btn-label-dark active" data-route="{{ route("vehicle.reservation.pending.list") }}">
                                Pending
                                <span id="pendingTabBadge" class="badge bg-danger rounded-circle ms-2" style="display:;">0</span>
                            </button>
                            <button type="button" class="btn btn-label-dark" data-route="{{ route("vehicle.reservation.list") }}">
                                Reservation
                                <span id="reservationTabBadge" class="badge bg-danger rounded-circle ms-2" style="display:;">0</span>
                            </button>
                        </div>
                    </div>
                </div>
                {{-- Default Table Without Scroll Bar --}}
                {{-- <div class="table-responsive">
                    <table id="vehicleReservationTable" class="table table-bordered table-hover" style="width:100%">
                        <tbody>
                        </tbody>
                    </table>
                </div> --}}

                {{-- Horizontal Scroll Bar with CSS --}}
                <div class="table-responsive-wrapper">
                    <div class="fixed-header-scroll">
                      <div class="table-responsive">
                        <table id="vehicleReservationTable" class="table table-bordered table-hover" style="width:100%">
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

    // button group
    $(document).ready(function() {
        $('.btn-group .btn.active').click();
    });


    function reservedCount() {
        $.ajax({
            url: '{{ route("vehicle.reservation.getReservedCount") }}', // Adjust the route as necessary
            type: 'GET',
            success: function(response) {
                if (response.count !== undefined) {
                    $('#count').text(response.count); // Update the count in the HTML
                }
            },
            error: function(xhr) {
                console.error('Error fetching transaction count:', xhr);
            }
        });
    }

    reservedCount();

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
                        vehicleReservationTable.ajax.reload(null, false);
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
                    vehicleReservationTable.ajax.reload(null, false); // Reload the tables
                });

                // Add event listener to close the calendar
                closeButton.addEventListener("click", function() {
                    instance.close(); // Close the flatpickr calendar
                });
            }
    });

    // DataTable initialization available units
    const availableUnitsTable = $('#availableUnitsTable').DataTable({
        processing: true,
        serverSide: true, // Use client-side processing since we're providing static data
        ajax: {
                url: '{{ route("vehicle.reservation.units.list") }}',

            },
        lengthMenu: [ [10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "All"] ],
        pageLength: 10,
        paging: true,
        responsive: true,
        dom: '<"top"lf>rt<"bottom"ip>',
        language: {
            search: "",
            searchPlaceholder: "Search...",
            info: "", // Remove "Showing X to Y of Z entries"
            infoEmpty: "", // Removes the message when there's no data
            infoFiltered: "", // Removes the "filtered from X entries" part
        },

        columns: [
            { data: 'unit', name: 'unit', title: 'Unit' },
            { data: 'quantity', name: 'quantity', title: 'Quantity' },
        ],
        order: [[0, 'desc']],  // Sort by 'unit' column by default
        columnDefs: [
            {
                targets: [0, 1], // Columns to apply additional formatting (if needed)
            }
        ],
    });

    // DataTable initialization status table
    const statusTable = $('#statusTable').DataTable({
        processing: true,
        serverSide: true, // Use client-side processing since we're providing static data
        ajax: {
            url: '{{ route("vehicle.reservation.reservationPerTeam") }}',
        },
        lengthMenu: [ [10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "All"] ],
        pageLength: 10,
        paging: true,
        responsive: false,
        dom: '<"top"lf>rt<"bottom"ip>',
        language: {
            search: "",
            searchPlaceholder: "Search...",
            info: "", // Remove "Showing X to Y of Z entries"
            infoEmpty: "", // Removes the message when there's no data
            infoFiltered: "", // Removes the "filtered from X entries" part
        },
        columns: [
            { data: 'team', name: 'team', title: 'Group' },
            { data: 'quantity', name: 'quantity', title: 'Quantity' },
        ],
        order: [[0, 'desc']],  // Sort by 'unit' column by default
        columnDefs: [
            {
                targets: [0, 1], // Columns to apply additional formatting (if needed)
            }
        ],
    });

    // DataTable initialization Vehicle Reservation Table
    const vehicleReservationTable = $('#vehicleReservationTable').DataTable({
        processing: true,
        serverSide: true, // Use client-side processing since we're providing static data
        ajax: {
                url: '{{ route("vehicle.reservation.pending.list") }}',
                data: function(d) {
                    d.date_range = $('#date-range-picker').val();
                },
            },
        lengthMenu: [ [10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "All"] ],
        pageLength: 10,
        paging: true,
        responsive: false,
        "ordering": true, // Ensures ordering is enabled
        "order": [[11, "desc"]], // Orders the first column in descending order
        dom: '<"top"lf>rt<"bottom"ip>',
        language: {
            search: "",
            searchPlaceholder: "Search..."
        },

        columns: [
            { data: 'client_name', name: 'client_name', title: 'Customer Name' }, //0
            { data: 'unit', name: 'unit', title: 'Unit' }, //1
            { data: 'year_model', name: 'year_model', title: 'Year Model', visible: false }, //2
            { data: 'variant', name: 'variant', title: 'Variant' }, //3
            { data: 'color', name: 'color', title: 'Color' }, //4
            {
                data: 'cs_number',
                name: 'cs_number',
                title: 'CS Number',
                orderable: false,
                searchable: false,
                visible: false,
                render: function(data, type, row) {
                    if (type === 'display') {
                        return `
                            <div class="d-flex">
                                <button type="button" class="badge btn me-2 btn-label-dark btn-csNumber" data-bs-toggle="modal" data-bs-target="#selectCSNumber"  data-vehicle-id="${row.vehicle_id}" data-transaction-id="${row.id}" data-unit="${row.unit}" data-variant="${row.variant}" data-color="${row.color}" data-client-name="${row.client_name}">
                                        ${data}
                                </button>
                            </div>
                        `;
                    }
                    return data; // Default display for other types like export, search, etc.
                }
            }, //5
            { data: 'transaction', name: 'transaction', title: 'Transaction' }, //66
            { data: 'trans_type', name: 'trans_type', title: 'Type' }, //7
            { data: 'trans_bank', name: 'trans_bank', title: 'Trans Bank' }, //8
            { data: 'agent', name: 'agent', title: 'Agent' }, //9
            { data: 'team', name: 'team', title: 'Group' }, //10
            { data: 'date_assigned', name: 'date_assigned', title: 'Date ' }, //11
            {
                data: 'application_id',
                name: 'application_id',
                title: 'Action',
                orderable: false,
                searchable: false,
                visible: false,
                render: function(data, type, row) {
                        let editButton = row.trans_type === 'Individual' ?
                        `<button type="button" class="btn btn-icon me-2 btn-success edit-btn" data-bs-toggle="modal" data-bs-target="#editReservationFormModal"  data-id="${data}">
                            <span class="tf-icons bx bxs-show bx-22px"></span>
                        </button>` : '';
                        return `<div class="d-flex">
                                    ${editButton}
                                    <button type="button" class="btn btn-icon me-2 btn-primary processing-pending-btn" data-id="${data}">
                                        <span class="tf-icons bx bxs-check-circle bx-22px"></span>
                                    </button>
                                    <button type="button" class="btn btn-icon me-2 btn-danger cancel-btn" data-id="${data}">
                                        <span class="tf-icons bx bxs-x-circle bx-22px"></span>
                                    </button>
                                </div>`;
                    }
            }, //12
            {
                data: 'application_id',
                name: 'application_id',
                title: 'Action',
                orderable: false,
                searchable: false,
                visible: false,
                render: function(data, type, row) {
                    let editButton = row.trans_type === 'Individual' ?
                        `<button type="button" class="btn btn-icon me-2 btn-success edit-btn" data-bs-toggle="modal" data-bs-target="#editReservationFormModal"  data-id="${data}">
                            <span class="tf-icons bx bxs-show bx-22px"></span>
                        </button>` : '';
                        return `<div class="d-flex">
                                    @if(auth()->user()->can('edit_unit'))
                                    ${editButton}
                                    @endif
                                    @if(auth()->user()->can('process_pending_reservation'))
                                    <button type="button" class="btn btn-icon me-2 btn-primary processing-pending-btn" data-id="${data}">
                                        <span class="tf-icons bx bxs-check-circle bx-22px"></span>
                                    </button>
                                    @endif
                                    @if(auth()->user()->can('cancel_pending_reservation'))
                                      <button type="button" class="btn btn-icon me-2 btn-danger cancel-pending-btn" data-id="${data}">
                                        <span class="tf-icons bx bxs-x-circle bx-22px"></span>
                                    </button>
                                    @endif

                                </div>`;
                    }
            }, //13
            {
                data: 'id',
                name: 'id',
                title: 'Action',
                orderable: false,
                searchable: false,
                visible: false,
                render: function(data, type, row) {
                    let editButton = row.trans_type === 'Individual' ?
                        `<button type="button" class="btn btn-icon me-2 btn-success edit-btn" data-bs-toggle="modal" data-bs-target="#editReservationFormModal"  data-id="${row.application_id}">
                            <span class="tf-icons bx bxs-show bx-22px"></span>
                        </button>` : '';
                        return `<div class="d-flex">
                                    @if(auth()->user()->can('edit_unit'))
                                    ${editButton}
                                    @endif
                                    <button type="button" class="btn btn-icon me-2 btn-primary processing-reserved-btn" data-id="${data}">
                                        <span class="tf-icons bx bxs-check-circle bx-22px"></span>
                                    </button>
                                </div>`;
                    }
            }, //14

        ],

    });


     // button group active tabs
     $('.btn-group .btn').on('click', function(e) {
        e.preventDefault();
        updateVehicleReservationBadge();
        $('#date-range-picker').val('');

        const buttonTitle = $(this).clone()    // Clone the button
        .children()                        // Get all child elements
        .remove()                          // Remove all child elements (including badge)
        .end()                            // Go back to original element
        .text()                           // Get remaining text
        .trim();          
        console.log(buttonTitle); // For debugging

        // Toggle column visibility based on the active tab
        const isReservationTab = buttonTitle === 'Reservation';
        vehicleReservationTable.column(2).visible(isReservationTab); // year_model
        @if(auth()->user()->can('add_cs_number') && auth()->user()->can('get_cs_number'))
        vehicleReservationTable.column(5).visible(isReservationTab); // cs_number
        @endif

        // vehicleReservationTable.column(12).visible(isReservationTab); // edit

        @if(auth()->user()->can('process_reserved_reservation'))
        vehicleReservationTable.column(14).visible(isReservationTab); // application_id
        @endif

        const isPendingTab = buttonTitle === 'Pending';
        @if(auth()->user()->can('process_pending_reservation') || auth()->user()->can('cancel_pending_reservation'))
        vehicleReservationTable.column(13).visible(isPendingTab); // id
        @endif

        var route = $(this).data('route');
        vehicleReservationTable.ajax.url(route).load();

      

        $.ajax({
            url: '{{ route("vehicle.reservation.updateNotifStatus") }}',
            type: 'POST',
            data: {
                buttonTitle: buttonTitle
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response){
                console.log(response);
            },
            error: function(xhr){
                console.log(xhr);
            }
        });


       
    });

    // Example usage when a CS number is selected
    $('#saveCSNumber').on('submit',  function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.ajax({
            url: '{{ route("vehicle.reservation.addCSNumber") }}',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire(
                        'Success!',
                        response.message,
                        'success'
                    );
                    reservedCount();
                    vehicleReservationTable.ajax.reload()
                    statusTable.ajax.reload()
                    availableUnitsTable.ajax.reload()
                    $('#selectCSNumber').modal('hide'); // Close the modal upon successful save
                } else {
                    Swal.fire(
                        'Error!',
                        response.message,
                        'error'
                    );
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

    $(document).on('click', '.btn-csNumber', function() {
        const vehicleId = $(this).data('vehicle-id');
        const transaction_id = $(this).data('transaction-id');
        const selectElement = $(this);

        const unit = selectElement.data('unit');
        const variant = selectElement.data('variant');
        const color = selectElement.data('color');
        const client_name = selectElement.data('client-name');

        $('#transaction_id').val(transaction_id);
        $('#customerName').text(client_name);
        $('#unit').text(unit);
        $('#variant').text(variant);
        $('#color').text(color);

        $.ajax({
            url: `/get-cs-number/${vehicleId}`,
            type: 'GET',
            data: {
                unit: unit,
                variant: variant,
                color: color
            },
            dataType: 'json',
            success: function(data) {
                let numberSelect = $('#csNumberSelect');
                numberSelect.empty();
                numberSelect.append('<option value="">Select CS Number...</option>');
                if (data.length > 0) {
                    data.forEach(function(item) {
                        numberSelect.append(`<option value="${item.CS_number}">${item.CS_number}</option>`);
                    });
                } else {
                    numberSelect.append('<option value="">Insufficient inventory for this unit</option>');
                }
            },
            error: function(error) {
                console.error('Error loading CS Number:', error);
            }
        });


    });


    // datatables button tabs
    $(document).ready(function() {
        $('.btn-group .btn').on('click', function() {
            // Remove 'active' class from all buttons in the group
            $('.btn-group .btn').removeClass('active');
            // Add 'active' class to the clicked button
            $(this).addClass('active');
            $('#date-range-picker').val(''); // Clear the date range input
            vehicleReservationTable.ajax.reload(null, false); // Reload the table without resetting the paging
            var route = $(this).data('route'); // Get the route from the clicked button
            vehicleReservationTable.ajax.url(route).load();
        });
    });

      //Process Data
    $(document).on('click', '.processing-pending-btn', function() {
        const appID = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to reserved this transaction?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, reserved it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("vehicle.reservation.processing_pending") }}',
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
                            vehicleReservationTable.ajax.reload();
                            statusTable.ajax.reload();
                            availableUnitsTable.ajax.reload();
                            reservedCount();
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

    $(document).on('click', '.processing-reserved-btn', function() {
        const appID = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to proceed this to pending for release?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("vehicle.reservation.processing_reserved") }}',
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
                            vehicleReservationTable.ajax.reload();
                            statusTable.ajax.reload();
                            availableUnitsTable.ajax.reload();
                            reservedCount();
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

    $(document).on('click', '.cancel-pending-btn', function() {
        const appID = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to cancel this application?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("vehicle.reservation.cancel.pending") }}', // Ensure this route is defined in your routes
                    type: 'POST',
                    data: {
                        id: appID,

                        _token: '{{ csrf_token() }}' // Include CSRF token
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Canceled!',
                                response.message,
                                'success'
                            );
                            vehicleReservationTable.ajax.reload(); // Reload the DataTable
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

    // Edit Reservation Modal Fields disabled state -> Editable State
    $(document).ready(function () {
        // Function to reset the modal to its initial uneditable state
        function resetModalToInitialState() {
            // Disable all input fields except the Edit button
            $("#editReservationFormData :input").not("#editReservationModalButton").prop("disabled", true);

            // Show the Edit button
            $("#editReservationModalButton").removeClass("d-none");

            // Hide the Save and Cancel buttons
            $("#saveEditReservationModalButton").addClass("d-none");
            $("#cancelReservationModalButton").addClass("d-none");
        }

        // Initially, reset the modal to its initial state when the page is ready
        resetModalToInitialState();

        // When the Edit button is clicked
        $("#editReservationModalButton").on("click", function () {
            // Enable all input fields except hidden fields
            $("#editReservationFormData :input").not("[type='hidden']").prop("disabled", false);

            // Hide the Edit button
            $(this).addClass("d-none");

            // Show the Save Changes and Cancel buttons
            $("#saveEditReservationModalButton").removeClass("d-none");
            $("#cancelReservationModalButton").removeClass("d-none");
        });

        // When the Cancel button is clicked
        $("#cancelReservationModalButton").on("click", function () {
            // Close the modal properly
            $("#editReservationFormModal").modal("hide");

            // Reset the modal to its initial uneditable state when reopened
            resetModalToInitialState();
        });

        // Reset the modal when it's closed (using Bootstrap modal `hidden.bs.modal` event)
        $("#editReservationFormModal").on("hidden.bs.modal", function () {
            resetModalToInitialState();
        });
    });

    //load unit
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


    $(document).on('click', '.edit-btn', function() {
        const applicationId = $(this).data('id');
        $.ajax({
            url: `{{ url('vehicle-reservation/edit') }}/${applicationId}`,
            type: 'GET',
            success: function(response) {
                const data = response.application;
                const statuses = response.statuses;
                const banks = response.banks;
                const inquiry = response.inquiry;
                const inquiry_type = response.inquirytype;
                const firstTransaction = response.firstTransaction;

                // Populate the form fields with the inquiry data
                $('#edit_car_unit').val(data.vehicle.unit).trigger('change');

                $.ajax({
                    url: '{{ route("leads.getVariants") }}',
                    type: 'GET',
                    data: { unit: data.vehicle.unit },
                    dataType: 'json',
                    success: function(variantsData) {
                        $('#edit_car_variant').val(data.vehicle.variant).trigger('change'); // Trigger change to update colors
                        // Close the loader after data is loaded

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
                });

                // Store original values
                originalValues = {
                    id: data.id,
                    carUnit: data.vehicle.unit,
                    carVariant: data.vehicle.variant,
                    carColor: data.vehicle.color,
                };
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

    // Validate form on submit
    $("#editReservationFormData").on("submit", function (e) {
        e.preventDefault();
        let isValid = true;

        // Validate required fields
        isValid = validateField('#edit_car_unit', 'Please Select Unit') && isValid;
        isValid = validateField('#edit_car_variant', 'Please Select Variant') && isValid;
        isValid = validateField('#edit_car_color', 'Please Select Color') && isValid;


        // Restore original values on invalid fields
        if (!isValid) {
            $('#edit_id').val(originalValues.id);
            $('#edit_car_unit').val(originalValues.carUnit);
            $('#edit_car_variant').val(originalValues.carVariant);
            $('#edit_car_color').val(originalValues.carColor);
            return; // Stop execution if validation fails
        }

        // Proceed with AJAX request if the form is valid
        const formData = $(this).serialize();
        const inquiryId = originalValues.id; // Assuming you set data-id on the form

        $.ajax({
            url: `/vehicle-reservation/update/${inquiryId}`, // Adjust URL as needed
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
                    vehicleReservationTable.ajax.reload();
                    $('#editReservationFormModal').modal('hide'); // Hide the modal
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


</script>


@endsection

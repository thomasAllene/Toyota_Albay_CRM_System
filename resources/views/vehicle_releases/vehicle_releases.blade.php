@extends('components.app')

@section('content')

<style>
    #vehicleReleasesTable td{
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


    #vehicleReleasesTable {
        text-transform: uppercase;
    }

    #releasedUnitsTable {
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
            <h4 class="text-white mb-0">Vehicle Releases</h4>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="releaseStatus" tabindex="-1" aria-labelledby="releaseStatusLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Released Status</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
            </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <div class="row mb-3">
                <div class="col-md">
                    <input type="hidden" name="id", id="statusTransactionID">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md d-flex flex-column">
                    <label for="select-date-released">Select Actual Date Released</label>
                    <input type="text" class="form-control" placeholder="YYYY-MM-DD" id="selectDateReleasedStatus" />
                </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Close</button>
          @if(auth()->user()->can('update_status'))
          <button type="button" class="btn btn-dark" id="saveStatusButton">Update Status</button>
          @endif
        </div>
      </div>
    </div>
</div>

<!-- Insurance Modal -->
<div class="modal fade" id="insuranceModal" tabindex="-1" aria-labelledby="releaseStatusLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Insurance</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
            </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <div class="row">
                <div class="col-md">
                    <input type="hidden" name="id", id="statusTransactionID">
                    <select class="form-control" id="insurance" name="insurance">
                        <option value="">Select Insurance</option>
                        <option value="FI">FI</option>
                        <option value="CI">CI</option>
                        <option value="POI">POI</option>
                        <option value="Others">Others</option>
                    </select>
                    <div id="insuranceError" style="color: red; display: none;"></div>
                </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Close</button>
          @if(auth()->user()->can('add_insurance'))
          <button type="button" class="btn btn-dark" id="saveInsuranceButton">Update</button>
          @endif
        </div>
      </div>
    </div>
</div>

{{-- Add Profit Modal --}}
<div class="modal fade" id="addProfitModal" tabindex="-1" aria-labelledby="addProfitModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Total Profit</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
            </button>
        </div>
        <div class="modal-body">
          <form action="profitForm">
            <div class="row mb-3">
                <div class="col-md">
                    <input type="hidden" id="profit-id" name="profit-id">
                    <label for="profit" class="form-label required">Add Total Profit</label>
                    <div class="d-flex align-items-center gap-2">
                        <b class="fs-4">₱</b>
                        <input type="text" class="form-control" id="profit" name="profit" required>
                    </div>
                    <div class="d-flex justify-content-end">
                        <small class="text-danger" id="validateProfit">Input Profit Amount</small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal" id="closeProfitFormModal">Close</button>
                    <button type="Submit" class="btn btn-dark" id="saveProfitFormModal">Enter</button>
                </div>
            </div>
          </form>
        </div>
      </div>
    </div>
</div>

{{-- LTO Remarks Modal --}}
<div class="modal fade" id="LtoRemarksModal" tabindex="-1" aria-labelledby="largeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header d-flex align-items-center gap-2">
          <i class='bx bxs-message-rounded-detail'></i>
          <h5 class="modal-title" id="largeModalLabel">LTO Remarks</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="ltoRemarksContent">
              <input type="hidden" name="id" id="id">
              <textarea class="form-control mb-2 d-none" id="ltoRemarksTextArea" name="remarks" rows="5" placeholder=""></textarea>
              <p class="fs-5 text-dark" id="remarksParagraph">
              </p>
          </div>
          <div class="d-flex justify-content-end gap-2">
              <button class="btn btn-label-success" id="editLtoRemarksButton">Edit</button>
              <button class="btn btn-dark d-none save-remark" id="saveEditLtoRemarksButton">Save</button>
          </div>
        </div>
      </div>
    </div>
</div>

{{-- Remarks Modal --}}
<div class="modal fade" id="releasedRemarksModal" tabindex="-1" aria-labelledby="largeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header d-flex align-items-center gap-2">
          <i class='bx bxs-message-rounded-detail'></i>
          <h5 class="modal-title" id="largeModalLabel">Released Remarks</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="releasedRemarksContent">
              <input type="hidden" name="id" id="id">
              <textarea class="form-control mb-2 d-none" id="releasedRemarksTextArea" name="released_remarks" rows="5" placeholder=""></textarea>
              <p class="fs-5 text-dark" id="releasedremarksParagraph">
              </p>
          </div>
          <div class="d-flex justify-content-end gap-2">
            @if(auth()->user()->can('update_released_remarks'))
              <button class="btn btn-label-success" id="editReleasedRemarksButton">Edit</button>
              <button class="btn btn-dark d-none save-remark-released" id="saveEditReleasedRemarksButton">Save</button>
            @endif
          </div>
        </div>
      </div>
    </div>
</div>

<!-- Add Folder Number Modal -->
<div class="modal fade" id="folderNumberModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="backDropModalTitle">Folder Number</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            {{-- <div class="col-md">
                <div class="d-flex justify-content-start">
                    <div class="">Customer:</div><div class="text-dark">&nbsp; John Doe</div>
                </div>
                <div class="d-flex justify-content-start">
                    <div class="">Unit:</div><div class="text-dark">&nbsp; sample</div>
                </div>
                <div class="d-flex justify-content-start">
                    <div class="">Variant:</div><div class="text-dark">&nbsp; sample</div>
                </div>
                <div class="d-flex justify-content-start">
                    <div class="">Color:</div><div class="text-dark">&nbsp; sample</div>
                </div>
                <div class="d-flex justify-content-start">
                    <div class="">CS Number:</div><div class="text-dark">&nbsp; sample</div>
                </div>
            </div> --}}
          </div>
          <div class="row">
            <div class="col-md">
                <input type="hidden" class="form-control" id="id" name="id"  />
                <label for="defaultFormControlInput" class="form-label">Add Folder Number</label>
                <input type="text" class="form-control" id="folderNumber" name="folder_number"  />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Close</button>
          @if(auth()->user()->can('process_vehicle_release'))
          <button type="button" class="btn btn-dark save-folder">Proceed</button>
          @endif
        </div>
      </form>
    </div>
  </div>


  {{-- Header Datatables --}}
  <div class="row mb-4">
    <div class="col-md">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="d-flex w-50 gap-2">
                        <div class="mb-3 d-flex align-items-center gap-1">
                            <i class='text-dark bx bx-calendar fs-2'></i>
                            <div class="input-group">
                                <input type="text" id="date-range-picker" class="form-control border" placeholder="Filter Date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md">
                        <div class="card shadow-none border custom-card">
                            <div class="card-body">
                                <h5>Releases Units</h5>
                                <div class="table-responsive">
                                    <table id="releasedUnitsTable" class="table table-bordered table-hover" style="width:100%">
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
                                <div class="table-responsive mb-2">
                                    <table id="statusTable" class="table table-bordered table-hover" style="width:100%">
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                @if (auth()->user()->usertype->name === 'SuperAdmin' || auth()->user()->usertype->name === 'Group Manager')

                                <div class="card bg-label-secondary shadow-none">
                                    <div class="card-body d-flex justify-content-center">
                                        <div class="d-flex gap-2"><div class="h2">Grand Total Profit:</div><div class="h2 fw-bold" id="grandTotalProfit">0</div></div>
                                    </div>
                                </div>

                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="card shadow-none bg-transparent border d-flex justify-content-center align-items-center mb-2">
                            <div class="card-body text-center">
                                <h3 class="text-primary"><b>Total Vehicle Released</b></h3>
                                <h1 class="text-primary" style="font-size: clamp(8rem, 6vw, 3rem);"><b id="releasedCount" >0</b></h1>
                            </div>
                        </div>
                        <div class="card shadow-none bg-transparent border d-flex justify-content-center align-items-center">
                            <div class="card-body text-center">
                                <h3 class="text-primary"><b>Pending Vehicle for Releases</b></h3>
                                <h1 class="text-primary" style="font-size: clamp(8rem, 6vw, 3rem);"><b id="pendingForReleaseCount" >0</b></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-2">
    <div class="col">
        <div class="card custom-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md">
                        <div class="btn-group w-100" role="group" aria-label="Basic example">
                            <button id="forRelease" type="button" class="btn btn-label-dark active" data-route="{{ route("vehicle.releases.pending.list") }}">
                                For Release Units
                                <span id="forReleaseTabBadge" class="badge bg-danger rounded-circle ms-2" style="display:;">1</span>
                            </button>
                            <button id="released" type="button" class="btn btn-label-dark" data-route="{{ route("vehicle.releases.list") }}">
                                Released Units
                                <span id="releasedTabBadge" class="badge bg-danger rounded-circle ms-2" style="display:;">1</span>
                            </button>
                        </div>
                    </div>
                </div>
                {{-- Default Table Without Scroll Bar --}}
                {{-- <div class="table-responsive">
                    <table id="vehicleReleasesTable" class="table table-bordered table-hover" style="width:100%">
                        <tbody>
                        </tbody>
                    </table>
                </div> --}}

                {{-- Horizontal Scroll Bar with CSS --}}
                <div class="table-responsive-wrapper">
                    <div class="fixed-header-scroll">
                      <div class="table-responsive">
                        <table id="vehicleReleasesTable" class="table table-bordered table-hover" style="width:100%">
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
                    vehicleReleasesTable.ajax.reload(null, false);
                    statusTable.ajax.reload(null, false);
                    releasedUnitsTable.ajax.reload(null, false);
                    getGrandTotalProfit();
                    releasedCount();
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
                vehicleReleasesTable.ajax.reload(null, false); // Reload the tables
                statusTable.ajax.reload(null, false);
                releasedUnitsTable.ajax.reload(null, false);
                getGrandTotalProfit();
                releasedCount();
            });

            // Add event listener to close the calendar
            closeButton.addEventListener("click", function() {
                instance.close(); // Close the flatpickr calendar
            });
        }
    });

    function getGrandTotalProfit(){
        $.ajax({
            url: '{{ route("vehicle.releases.GrandTotalProfit") }}',
            type: 'GET',
            data: {
                date_range: $('#date-range-picker').val() // Add the date range parameter
            },
            success: function(response) {
                $('#grandTotalProfit').text(response);
            }
        });
    }

    getGrandTotalProfit();

    function releasedCount() {
        $.ajax({
            url: '{{ route("vehicle.releases.getReleasedCount") }}', // Adjust the route as necessary
            type: 'GET',
            data: {
                date_range: $('#date-range-picker').val() // Add the date range parameter
            },
            success: function(response) {
                if (response.releasedCount !== undefined) {
                    $('#releasedCount').text(response.releasedCount); // Update the count in the HTML
                    $('#pendingForReleaseCount').text(response.pendingForReleaseCount); // Update the count in the HTML
                }
            },
            error: function(xhr) {
                console.error('Error fetching transaction count:', xhr);
            }
        });
    }

    releasedCount();

    // datatables button tabs
    $(document).ready(function() {
        $('.btn-group .btn').on('click', function() {
            updateVehicleReleaseBadge();

            // Remove 'active' class from all buttons in the group
            $('.btn-group .btn').removeClass('active');
            // Add 'active' class to the clicked button
            $(this).addClass('active');
        });
    });


    // DataTable initialization releasedn units table
    const releasedUnitsTable = $('#releasedUnitsTable').DataTable({
        processing: true,
        serverSide: true, // Use client-side processing since we're providing static data
        ajax: {
            url: '{{ route("vehicle.releases.units.list") }}',
            data: function(d) {
                d.date_range = $('#date-range-picker').val();
            },
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

    });

    const statusTable = $('#statusTable').DataTable({
        processing: true,
        serverSide: true, // Use client-side processing since we're providing static data
        ajax: {
            url: '{{ route("vehicle.releases.releasedPerTeam") }}',
            data: function(d) {
                d.date_range = $('#date-range-picker').val();
            },
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
            { data: 'team', name: 'team', title: 'Group' },
            { data: 'quantity', name: 'quantity', title: 'Quantity' },
            @if (auth()->user()->usertype->name === 'SuperAdmin' || auth()->user()->usertype->name === 'Group Manager')

            { data: 'total_profit', name: 'total_profit', title: 'Total Profit' },

            @endif
        ],
        order: [[0, 'desc']],  // Sort by 'unit' column by default
        columnDefs: [
            {
                targets: [0, 1], // Columns to apply additional formatting (if needed)
            },

        ],
    });

    const vehicleReleasesTable = $('#vehicleReleasesTable').DataTable({
        processing: true,
        serverSide: true, // Use client-side processing since we're providing static data
        ajax: {
            url: '{{ route("vehicle.releases.pending.list") }}',
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
        order:false,

        columns: [
            { data: 'folder_number', name: 'folder_number', title: 'Folder ' }, //0
            { data: 'customer_name', name: 'customer_name', title: 'Customer Name' }, //1
            { data: 'address', name: 'address', title: 'Address' }, //2
            { data: 'year_model', name: 'year_model', title: 'Year Model' }, //3
            { data: 'unit', name: 'unit', title: 'Unit' }, //4
            { data: 'variant', name: 'variant', title: 'Variant' }, //5
            { data: 'color', name: 'color', title: 'Color' }, //6
            { data: 'cs_number', name: 'cs_number', title: 'CS Number' }, //7
            { data: 'transaction', name: 'transaction', title: 'Transaction' }, //8
            {
                data: 'insurance',
                name: 'insurance',
                title: 'Insurance',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex">
                            <button type="button" class="badge btn me-2 btn-label-dark insurance-btn" data-bs-toggle="modal" data-bs-target="#insuranceModal" data-insurance="${data}" data-id="${row.id}">
                                 <span >${data}</pan>
                            </button>
                        </div>
                        `;
                }


             }, //9
            { data: 'trans_bank', name: 'trans_bank', title: 'Bank' }, //10
            { data: 'agent', name: 'agent', title: 'Agent' }, //11
            { data: 'team', name: 'team', title: 'Group' }, //12
            {
                data: 'id',
                name: 'id',
                title: 'Status',
                visible: false,
                render: function(data, type, row) {
                    return `
                        <div class="d-flex">
                            <button type="button" class="badge btn me-2 btn-label-dark status-btn" data-bs-toggle="modal" data-bs-target="#releaseStatus" data-id="${data}" data-status="${row.status}">
                                 <span > ${row.status}</pan>
                            </button>
                        </div>
                        `;
                }
            }, //13
            { data: 'category', name: 'category', title: 'Unit Type' }, //14
            {
                data: 'profit',
                name: 'profit',
                title: 'Profit',
                visible: false,
                render: function(data, type, row) {
                    return `
                        <div class="d-flex">

                            <button type="button" class="badge btn me-2 btn-label-dark profit-btn" data-bs-toggle="modal" data-bs-target="#addProfitModal" data-id="${row.id}" data-profit="${data}">
                                 <span > ${data}</span>
                            </button>
                        </div>`;
                }
            }, //15
            { data: 'other_profit', name: 'other_profit', title: 'Other Profit' }, //16
            { data: 'gender', name: 'gender', title: 'Gender' }, //17
            {
                data: 'released_remarks',
                name: 'released_remarks',
                title: 'Remarks',
                orderable: false,
                searchable: false,
                visible: false,
                render: function(data, type, row) {
                    return `
                            <button type="button" class="btn btn-icon me-2 btn-label-dark released-remarks-btn" data-id="${row.id}" data-bs-toggle="modal" data-bs-target="#releasedRemarksModal" data-remarks="${data}">
                                <span class="tf-icons bx bx-comment-detail bx-22px"></span>
                            </button>
                           `;
                }
            }, //18
            {
                data: 'lto_remarks',
                name: 'lto_remarks',
                title: 'LTO Remarks',
                orderable: false,
                searchable: false,
                visible: false,
                render: function(data, type, row) {
                    return `@if(auth()->user()->can('update_ltoremarks'))
                            <button type="button" class="btn btn-icon me-2 btn-label-dark lto-remarks-btn" data-id="${row.id}" data-bs-toggle="modal" data-bs-target="#LtoRemarksModal" data-remarks="${data}">
                                <span class="tf-icons bx bx-comment-detail bx-22px"></span>
                            </button>
                            @endif`;
                }
            }, //19

            { data: 'source', name: 'source', title: 'Source' }, //20
            { data: 'date_released', name: 'date_released', title: 'Date Released' }, //2
            {
                data: 'id',
                name: 'id',
                title: 'Action',
                orderable: false,
                searchable: false,
                visible:false,
                render: function(data, type, row) {
                        return `<div class="d-flex">
                                @if(auth()->user()->can('add_folder_number'))
                                <button type="button" class="btn btn-icon me-2 btn-dark folder-number-btn" data-id="${data}" data-folder="${row.folder_number}" data-bs-toggle="modal" data-bs-target="#folderNumberModal">
                                        <span class="tf-icons bx bx-folder-plus bx-22px"></span>
                                </button>
                                @endif

                                @if(auth()->user()->can('cancel_vehicle_release'))
                                <button type="button" class="btn btn-icon me-2 btn-danger cancel-btn" data-id="${data}">
                                    <span class="tf-icons bx bxs-x-circle bx-22px"></span>
                                </button>
                                @endif
                        </div>`;
                    }
            }, //22
            { data: 'status', name: 'status', title: 'status', visible:false }, //23
        ],

    });

    $(document).ready(function () {
        let currentButtonId = null;

        // Capture the button that opens the modal
        $('[data-bs-target="#releaseStatus"]').on('click', function () {
            currentButtonId = $(this).attr('id'); // Store the current button ID
        });


        // $('#saveStatusButton').on('click', function() {
        //     const selectedValue = $('#status').val();
        //     if (selectedValue) {
        //         $.ajax({
        //             url: '{{ route("vehicle.releases.updateStatus") }}', // Define this route in your controller
        //             type: 'POST',
        //             data: {
        //                 id: $('#statusTransactionID').val(),
        //                 status: selectedValue,
        //                 _token: '{{ csrf_token() }}' // Include CSRF token
        //             },
        //             success: function(response) {
        //                 if (response.success) {
        //                     $('#releaseStatus').modal('hide');
        //                     Swal.fire('Updated!', response.message, 'success');
        //                     vehicleReleasesTable.ajax.reload();
        //                     statusTable.ajax.reload();
        //                     releasedUnitsTable.ajax.reload();
        //                     releasedCount();
        //                     // Optionally reload the DataTable or update the UI
        //                 }
        //             },
        //             error: function(xhr) {
        //                 Swal.fire('Error!', xhr.responseJSON?.message || 'Something went wrong!', 'error');
        //             }
        //         });
        //     }
        // });


        $('#saveStatusButton').on('click', function() {
            const selectedValue = $('#status').val();
            const selectedDate = $('#selectDateReleasedStatus').val(); // Get Flatpickr-selected date

            if (selectedValue && selectedDate) {
                $.ajax({
                    url: '{{ route("vehicle.releases.updateStatus") }}',
                    type: 'POST',
                    data: {
                        id: $('#statusTransactionID').val(),
                        status: selectedValue,
                        released_date: selectedDate, // Pass the selected date
                        _token: '{{ csrf_token() }}' // Include CSRF token
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#releaseStatus').modal('hide');
                            Swal.fire('Updated!', response.message, 'success');
                            
                            // Reload the DataTable to reflect the updated date_released
                            vehicleReleasesTable.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Something went wrong!', 'error');
                    }
                });
            } else {
                Swal.fire('Warning!', 'Please select both status and release date!', 'warning');
            }
        });


        $(document).on('click', '.status-btn', function() {
            const id = $(this).data('id');
            const currentStatus = $(this).data('status'); // Get the current status from the button
            $('#statusTransactionID').val(id);

            $.ajax({
                url: '{{ route("vehicle.releases.getStatus") }}',
                type: 'GET',
                data: { id: id }, // Send the transaction ID to get the status
                success: function(response) {
                    let statusSelect = $('#status');
                    statusSelect.empty();
                    statusSelect.append('<option value="">Select Status...</option>');
                    response.forEach(function(item) {
                        statusSelect.append(`<option value="${item.id}" ${item.status == currentStatus ? 'selected' : ''}>${item.status}</option>`); // Set selected if it matches current status
                    });
                }
            });
        });
    });


    // button group active tabs
    $('.btn-group .btn').on('click', function(e) {
        e.preventDefault();

        const buttonTitle = $(this).clone()    // Clone the button
        .children()                        // Get all child elements
        .remove()                          // Remove all child elements (including badge)
        .end()                            // Go back to original element
        .text()                           // Get remaining text
        .trim();
        console.log(buttonTitle); // For debugging

        // Update Notification Status
        $.ajax({
            url: '{{ route("vehicle.releases.updateNotifStatus") }}',
            type: 'POST',
            data: { buttonTitle: buttonTitle },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response){
                console.log(response);
                updateVehicleReleaseBadge();
            },
            error: function(xhr){
                console.log(xhr);
            }
        });


        $('#date-range-picker').val('');

        // Toggle column visibility based on the active tab
        const isFoReleasedTab = buttonTitle === 'For Release Units';
        @if(auth()->user()->can('process_vehicle_release') || auth()->user()->can('cancel_vehicle_release'))
        vehicleReleasesTable.column(22).visible(isFoReleasedTab);
        @endif

        const isReleasedTab = buttonTitle === 'Released Units';
        @if(auth()->user()->can('get_status') && auth()->user()->can('update_status'))
        vehicleReleasesTable.column(13).visible(isReleasedTab);
        @endif

        @if(auth()->user()->can('update_profit'))
        vehicleReleasesTable.column(15).visible(isReleasedTab);
        @endif

        @if(auth()->user()->can('update_ltoremarks'))
        vehicleReleasesTable.column(19).visible(isReleasedTab);
        @endif

        vehicleReleasesTable.column(18).visible(isReleasedTab);



        var route = $(this).data('route');
        vehicleReleasesTable.ajax.url(route).load();
    });

    // datatables button tabs
    $(document).ready(function() {
        $('.btn-group .btn').on('click', function() {
            // Remove 'active' class from all buttons in the group
            $('.btn-group .btn').removeClass('active');
            // Add 'active' class to the clicked button
            $(this).addClass('active');

            $('#date-range-picker').val(''); // Clear the date range input
            vehicleReleasesTable.ajax.reload(null, false); // Reload the table without resetting the paging
            //  var route = $(this).data('route'); // Get the route from the clicked button
            // vehicleReleasesTable.ajax.url(route).load();
        });
    });

    //Process Data
    $(document).on('click', '.save-folder', function() {
        const ID = $('#id').val();
        const folderNum = $('#folderNumber').val();

        console.log(ID, folderNum);

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to proceed this transaction?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("vehicle.releases.processing") }}',
                    type: 'POST',
                    data: {
                       id: ID,
                       folder_number: folderNum ,
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
                             $('#folderNumberModal').modal('hide');
                            vehicleReleasesTable.ajax.reload();
                            statusTable.ajax.reload();
                            releasedUnitsTable.ajax.reload();
                            releasedCount();
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

    //Cancel Transaction
    $(document).on('click', '.cancel-btn', function() {
        const ID = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to cancel this unit?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("vehicle.releases.cancel") }}', // Ensure this route is defined in your routes
                    type: 'POST',
                    data: {
                        id: ID,
                        _token: '{{ csrf_token() }}' // Include CSRF token
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Canceled!',
                                response.message,
                                'success'
                            );
                            vehicleReleasesTable.ajax.reload();
                            statusTable.ajax.reload();
                            releasedUnitsTable.ajax.reload();
                            releasedCount();
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

    $(document).on('click', '.profit-btn', function() {
        const id = $(this).data('id');
        const profit = $(this).data('profit');
        $('#profit').val(profit);
        $('#profit-id').val(id);

    });

    $(document).on('click', '.folder-number-btn', function() {
        const id = $(this).data('id');
        const folder = $(this).data('folder');
        $('#folderNumber').val(folder);
        $('#id').val(id);
    });


    // profit form validation on border-danger
    $(document).ready(function () {
        const $profitInput = $('#profit');
        const $validateProfit = $('#validateProfit');
        $profitInput.on('input', function() {
            this.value = this.value.replace(/[^0-9.]/g, ''); // Allow only numbers and decimal point
        });

        $('#saveProfitFormModal').on('click', function (e) {
            e.preventDefault(); // Prevent default form submission
            if ($profitInput.val().trim() === '') {
                // Add border-danger class and show the validation message
                $profitInput.addClass('border-danger');
                $validateProfit.text('Input Profit Amount').show();
            } else {
                // Remove border-danger class and hide the validation message
                $profitInput.removeClass('border-danger');
                $validateProfit.hide();

                $.ajax({
                    url: '{{ route("vehicle.releases.updateProfit") }}',
                    type: 'POST',
                    data: {
                        id: $('#profit-id').val(),
                        profit: $profitInput.val()
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
                            vehicleReleasesTable.ajax.reload();
                            $('#addProfitModal').modal('hide');
                            statusTable.ajax.reload();
                            getGrandTotalProfit();


                            // releasedUnitsTable.ajax.reload();
                            // releasedCount();
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

        // Reset validation on modal close (optional)
        $('#closeProfitFormModal').on('click', function () {
            const $profitInput = $('#profit');
            const $validateProfit = $('#validateProfit');
            $profitInput.removeClass('border-danger').val('');
            $validateProfit.hide();
        });
    });

    // Edit LTO Remarks hide show
    $(document).ready(function () {
        $("#editLtoRemarksButton").on("click", function () {
            // Hide the remarks paragraph and edit button
            $("#remarksParagraph").addClass("d-none");
            $("#editLtoRemarksButton").addClass("d-none");


            // Show the textarea and save button
            $("#ltoRemarksTextArea").removeClass("d-none");
            $("#saveEditLtoRemarksButton").removeClass("d-none");
        });

        $("#editReleasedRemarksButton").on("click", function () {
            // Hide the remarks paragraph and edit button
            $("#releasedremarksParagraph").addClass("d-none");
            $("#editReleasedRemarksButton").addClass("d-none");


            // Show the textarea and save button
            $("#releasedRemarksTextArea").removeClass("d-none");
            $("#saveEditReleasedRemarksButton").removeClass("d-none");
        });


    });

    $(document).on('click', '.lto-remarks-btn', function() {
        const id = $(this).data('id');
        const remarks = $(this).data('remarks');
        $('#id').val(id);
        $('#ltoRemarksTextArea').val(remarks);
        $('#remarksParagraph').text(remarks);
    });

    $(document).on('click', '.released-remarks-btn', function() {
        const id = $(this).data('id');
        const remarks = $(this).data('remarks');
        $('#id').val(id);
        $('#releasedRemarksTextArea').val(remarks);
        $('#releasedremarksParagraph').text(remarks);
    });

    $(document).on('click', '.save-remark', function() {
        const id = $('#id').val();
        const remarks = $('#ltoRemarksTextArea').val();
        $.ajax({
            url: '{{ route("vehicle.releases.updateLTORemarks") }}',

            type: 'POST',
            data: { id: id, remarks: remarks },
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
                    $("#remarksParagraph").removeClass("d-none");
                    $("#editLtoRemarksButton").removeClass("d-none");
                    $("#ltoRemarksTextArea").addClass("d-none");
                    $("#saveEditLtoRemarksButton").addClass("d-none");
                    $('#LtoRemarksModal').modal('hide');
                    vehicleReleasesTable.ajax.reload();
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
    })

    $(document).on('click', '.save-remark-released', function() {
        const id = $('#id').val();
        const remarks = $('#releasedRemarksTextArea').val();
        $.ajax({
            url: '{{ route("vehicle.releases.updateReleasedRemarks") }}',
            type: 'POST',
            data: { id: id, remarks: remarks },
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
                    $("#releasedremarksParagraph").removeClass("d-none");
                    $("#editReleasedRemarksButton").removeClass("d-none");
                    $("#releasedRemarksTextArea").addClass("d-none");
                    $("#saveEditReleasedRemarksButton").addClass("d-none");
                    $('#releasedRemarksModal').modal('hide');
                    vehicleReleasesTable.ajax.reload();
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
    })

    $(document).on('click', '.insurance-btn', function() {
        const id = $(this).data('id');
        const insurance = $(this).data('insurance');
        $('#statusTransactionID').val(id);
        $('#insurance').val(insurance);


        $('#saveInsuranceButton').off('click').on('click', function() {
            const selectedValue = $('#insurance').val();

            if (selectedValue) {
                $.ajax({
                    url: '{{ route("vehicle.releases.addInsurance") }}', // Define this route in your controller
                    type: 'POST',
                    data: {
                        id: $('#statusTransactionID').val(),
                        insurance: selectedValue,
                        _token: '{{ csrf_token() }}' // Include CSRF token
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#insuranceModal').modal('hide');
                            Swal.fire('Updated!', response.message, 'success');
                            vehicleReleasesTable.ajax.reload();
                            // Optionally reload the DataTable or update the UI
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Something went wrong!', 'error');
                    }
                });
            }
            else {
                $('#insurance').css('border', '1px solid red');
                $('#insuranceError').text('Please select an insurance option.').show();
            }
        });

        // Remove red border when the user selects a value
        $('#insurance').on('change', function() {
            if ($(this).val()) {
                $(this).css('border', '');
                $('#insuranceError').hide();
            }
        });
        $('#insuranceModal').on('hide.bs.modal', function () {
            $('#insurance').css('border', '');
            $('#insuranceError').hide();
        });
    });

    var flatpickrDate = document.querySelector("#selectDateReleasedStatus");

    flatpickrDate.flatpickr({
        monthSelectorType: "static",
        static: true
    });

</script>


@endsection

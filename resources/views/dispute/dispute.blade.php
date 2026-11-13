@extends('components.app')
@section('content')

<style>
    #disputeTable {
        text-transform: uppercase;
    }
</style>

{{-- Page Title --}}
<div class="card bg-dark shadow-none mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <i class='bx bxs-x-square text-white' style="font-size: 24px;">&nbsp;</i>
            <h4 class="text-white mb-0">Disputes</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md">
        <div class="card">
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
                <div class="table-responsive">
                    <table id="disputeTable" class="table table-bordered table-hover" style="width:100%">
                        <tbody>
                        </tbody>
                    </table>
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
                    teamTable.ajax.reload(null, false);
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
                teamTable.ajax.reload(null, false); // Reload the tables
            });

            // Add event listener to close the calendar
            closeButton.addEventListener("click", function() {
                instance.close(); // Close the flatpickr calendar
            });
        }
    });

    // Datatable Initilization
    const teamTable = $('#disputeTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("dispute.getDisputes") }}',
            data: function(d) {
                d.date_range = $('#date-range-picker').val();
            },
        },
        lengthMenu: [ [10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "All"] ],
        pageLength: 10,
        columns: [
            { data: 'id', name: 'id', title: 'ID', visible: false },
            { data: 'client_name', name: 'client_name', title: 'Customer Name' },
            { data: 'agent', name: 'agent', title: 'Primary Agent' },
            { data: 'disputed_agent', name: 'disputed_agent', title: 'Disputed Agent' },
            { data: 'created_at', name: 'created_at', title: 'Created At' },
            { data: 'created_by', name: 'created_by', title: 'Created By' },
            { data: 'updated_at', name: 'updated_at', title: 'Updated At' },
            { data: 'updated_by', name: 'updated_by', title: 'Approve By' },
            { data: 'status', name: 'status', title: 'Status' },
             @if(auth()->user()->usertype->name === 'SuperAdmin')
            {
                data: 'id',
                name: 'id',
                title: 'Action',
                render: function(data) {
                    return `
                    <button type="button" class="btn btn-icon me-2 btn-success like-btn" data-id="${data}">
                        <span class="tf-icons bx bxs-like bx-22px"></span>
                    </button>
                    <button type="button" class="btn btn-icon me-2 btn-danger dislike-btn" data-id="${data}">
                        <span class="tf-icons bx bxs-dislike bx-22px"></span>
                    </button>
                `
                }
            }
            @endif
        ],
        "ordering": true, // Ensures ordering is enabled
        "order": [[4, "desc"]], // Orders the first column in descending order
    });

    $(document).on('click', '.dislike-btn', function() {
        const leadId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to mark this dispute as disapprove?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, mark it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("dispute.cancel") }}',
                    type: 'POST',
                    data: {
                        id: leadId
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
                            teamTable.ajax.reload();
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


    })

    $(document).on('click', '.like-btn', function() {
        const leadId = $(this).data('id');


        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to mark this dispute as approve?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, mark it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("dispute.approved") }}',
                    type: 'POST',
                    data: {
                        id: leadId
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
                            teamTable.ajax.reload();
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


    })




</script>
@endsection

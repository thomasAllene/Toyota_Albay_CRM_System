@extends('components.app')

@section('content')

{{-- Page Title --}}
<div class="card bg-dark shadow-none mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <i class='bx bxs-bank text-white' style="font-size: 24px;">&nbsp;</i>
            <h4 class="text-white mb-0">Banks</h4>
        </div>
    </div>
</div>

{{-- Edit Bank Modal --}}
<div class="modal fade" id="bankModal" tabindex="-1" aria-labelledby="bankModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bankModalLabel">Add Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editBankForm">
                    <input type="hidden" id="bank_id" name="id">
                    <div class="mb-3">
                        <label for="edit_bank_name" class="form-label required">Bank Name</label>
                        <input type="text" class="form-control" id="edit_bank_name" name="bank_name" required>
                        <small class="text-danger" id="validateBankName">Please enter bank name</small>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Add Bank Modal --}}
<div class="row mb-4">
    <div class="col-md">
        <div class="card" id="bankFormCard" style="display: none;">
            <div class="card-header">
                <h5 class="text-primary card-title">Bank Form</h5>
            </div>
            <div class="card-body">
                <form id="bankForm">
                    <div class="mb-3">
                        <label for="bank_name" class="form-label required">Bank Name</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name" required>
                        <small class="text-danger" id="validateBankName">Please enter bank name</small>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-label-danger" id="cancelBankFormButton">Cancel</button>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Add Bank Button --}}
<div class="row mb-2">
    <div class="col-md d-flex justify-content-end">
        <button class="btn btn-primary" id="addBankButton">
            Add New Bank
        </button>
    </div>
</div>

{{-- Datatables --}}
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex w-50 gap-2">
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="text" id="date-range-picker" class="form-control" placeholder="Select date range">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="banksTable" class="table table-bordered table-hover">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('components.specific_page_scripts')
<script>
    $(document).ready(function() {
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
                        banksTable.ajax.reload(null, false);
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
                    banksTable.ajax.reload(null, false); // Reload the tables
                });

                // Add event listener to close the calendar
                closeButton.addEventListener("click", function() {
                    instance.close(); // Close the flatpickr calendar
                });
            }
        });

        // Initialize DataTable
        const banksTable = $('#banksTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("banks.list") }}',
                data: function(d) {
                    // Include the date range in the AJAX request
                    d.date_range = $('#date-range-picker').val();
                },
            },
            lengthMenu: [ [10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "All"] ],
            pageLength: 10,
            columns: [
                { data: 'bank_name', name: 'bank_name', title: 'Bank Name' },
                { data: 'created_by', name: 'created_by', title: 'Created By' },
                { data: 'updated_by', name: 'updated_by', title: 'Updated By' },
                { data: 'created_at', name: 'created_at', title: 'Created At' },
                {
                    data: 'id',
                    title: 'Actions',
                    render: function(data) {
                        return `
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-icon btn-success edit-btn" data-id="${data}">
                                    <span class="tf-icons bx bxs-edit"></span>
                                </button>
                                <button type="button" class="btn btn-icon btn-danger delete-btn" data-id="${data}">
                                    <span class="tf-icons bx bxs-trash"></span>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[3, 'desc']]
        });

        // Form Submission
        $('#bankForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            $.ajax({
                url: '{{ route("banks.store") }}',
                type:'POST',
                data: formData,
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
                        $('#bankFormCard').hide();
                        $('#addBankButton').show();
                        banksTable.ajax.reload();
                        $('#bankForm')[0].reset();
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

        // Form Submission
        $('#editBankForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            const bankId = $('#bank_id').val();
            const method = 'PUT';

            $.ajax({
                url: `/banks/update/${bankId}`,
                type: method,
                data: formData,
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
                        $('#bankModal').modal('hide');
                        banksTable.ajax.reload();
                        $('#editBankForm')[0].reset();
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

        // Edit Bank
        $(document).on('click', '.edit-btn', function() {
            const bankId = $(this).data('id');

            $.ajax({
                url: `/banks/edit/${bankId}`,
                type: 'GET',
                success: function(data) {
                    $('#bank_id').val(data.bank_id);
                    $('#edit_bank_name').val(data.bank.bank_name);
                    $('#bankModalLabel').text('Edit Bank');
                    $('#bankModal').modal('show');
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Could not fetch bank data.'
                    });
                }
            });
        });

        // Delete Bank
        $(document).on('click', '.delete-btn', function() {
            const bankId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/banks/destroy/${bankId}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message,
                                    'success'
                                );
                                banksTable.ajax.reload();
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

        $('#addBankButton').click(function() {
            $('#bankFormCard').show();
            $('#addBankButton').hide();
        });

        $('#cancelBankFormButton').click(function() {
            $('#bankFormCard').hide();
            $('#addBankButton').show();
        });

        // Real-time Uppercase Transformation
        $("input[type='text'], textarea").on("input", function () {
            $(this).val($(this).val().toUpperCase());
        });

    });
</script>
@endsection

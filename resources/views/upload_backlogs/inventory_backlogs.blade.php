@extends('components.app')

@section('content')

<style>
    #inventoryBacklogsTable td{
        white-space: nowrap;
    }
</style>

{{-- Title Header --}}
<div class="card bg-dark shadow-none mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <i class='bx bxs-spreadsheet text-white' style="font-size: 24px;">&nbsp;</i>
            <h4 class="text-white mb-0">Upload Inventory Backlogs</h4>
        </div>
    </div>
</div>

<form id="uploadForm" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-md d-flex justify-content-end gap-2">
            <div class="mb-3">
                <input class="form-control" type="file" id="formFile" name="file" accept=".xls,.xlsx,.csv">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Upload File</button>
                <button type="button" id="transferToInventory" class="btn btn-danger d-none">Transfer to Inventory</button>
            </div>

        </div>
    </div>
</form>

<div class="row">
    <div class="col-md">
        <div class="card">
            <div class="card-body">
                {{-- Horizontal Scroll Bar with CSS --}}
                <div class="table-responsive-wrapper">
                    <div class="fixed-header-scroll">
                      <div class="table-responsive">
                        <table id="inventoryBacklogsTable" class="table table-bordered table-hover" style="width:100%">
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
    // Define the deleteInventoryBacklog function in the global scope
    function deleteInventoryBacklog(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("inventory.backlogs.delete") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id
                    },
                    success: function(response) {
                        $('#inventoryBacklogsTable').DataTable().ajax.reload();

                        Swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success'
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error deleting inventory backlog: ' + xhr.responseJSON.message,
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }

    $(document).ready(function() {
        var table = $('#inventoryBacklogsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("inventory.backlogs.list") }}',
            columns: [
                { data: 'id', name: 'id', title: 'id', visible: false },
                { data: 'unit', name: 'unit', title: 'unit' },
                { data: 'variant', name: 'variant', title: 'variant' },
                { data: 'category', name: 'category', title: 'category' },
                { data: 'color', name: 'color', title: 'color' },
                { data: 'year_model', name: 'year_model', title: 'Year Model' },
                { data: 'CS_number', name: 'CS_number', title: 'CS#' },
                { data: 'actual_invoice_date', name: 'actual_invoice_date', title: 'Actual Invoice Date' },
                { data: 'delivery_date', name: 'delivery_date', title: 'Delivery Date' },
                { data: 'invoice_number', name: 'invoice_number', title: 'Invoice Number' },
                { data: 'status', name: 'status', title: 'Status' },
                { data: 'incoming_status', name: 'incoming_status', title: 'Incoming Status' },
                { data: 'remarks', name: 'remarks', title: 'Remarks' },
                { data: 'action', name: 'action', title: 'Action',
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-label-danger btn-sm" onclick="deleteInventoryBacklog('${row.id}')">Remove</button>
                        `;
                    }
                },
            ],
            order: [[0, "desc"]],
            drawCallback: function(settings) {
                // Check if there is data in the table
                if (settings.json && settings.json.data && settings.json.data.length > 0) {
                    $('#transferToInventory').removeClass('d-none');
                } else {
                    $('#transferToInventory').addClass('d-none');
                }
            }
        });

        // Handle form submission
        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();

            // Create FormData object from the form
            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("inventory.backlogs.upload") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#inventoryBacklogsTable').DataTable().ajax.reload();

                    Swal.fire({
                        title: 'Success',
                        text: 'File uploaded successfully',
                        icon: 'success'
                    });

                },
                error: function(xhr) {
                    // Show error message
                    Swal.fire({
                        title: 'Error',
                        text: 'Error uploading file: ' + xhr.responseJSON.message,
                        icon: 'error'
                    });
                }
            });
        });

        $('#transferToInventory').on('click', function(e) {
            e.preventDefault();

            $.ajax({
                url: '{{ route("inventory.backlogs.transfer") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#inventoryBacklogsTable').DataTable().ajax.reload();

                    Swal.fire({
                        title: 'Success',
                        text: response.message,
                        icon: 'success'
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error transferring inventory: ' + xhr.responseJSON.message,
                        icon: 'error'
                    });
                }
            });

        });


    });
</script>

@endsection

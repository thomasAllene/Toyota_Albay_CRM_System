@extends('components.app')

@section('content')
{{-- Title Header --}}
<div class="card bg-dark shadow-none mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <i class='bx bxs-spreadsheet text-white' style="font-size: 24px;">&nbsp;</i>
            <h4 class="text-white mb-0">Upload Releases Backlogs</h4>
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
                <button type="button" id="transferToReleases" class="btn btn-danger d-none">Transfer to Releases</button>
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
                        <table id="releasesBacklogsTable" class="table table-bordered table-hover" style="width:100%">
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
    function deleteReleasesBacklog(id) {
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
        var table = $('#releasesBacklogsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("releases.backlogs.list") }}',
            columns: [
                { data: 'id', name: 'id', title: 'id', visible: false },
                { data: 'folder_number', name: 'folder_number', title: 'Folder Number' },
                { data: 'customer_name', name: 'customer_name', title: 'Customer Name' },
                { data: 'address', name: 'address', title: 'Address' },
                { data: 'year_model', name: 'year_model', title: 'Year Model' },
                { data: 'unit', name: 'unit', title: 'Unit' },
                { data: 'variant', name: 'variant', title: 'Variant' },
                { data: 'color', name: 'color', title: 'Color' },
                { data: 'cs_number', name: 'cs_number', title: 'CS#' },
                { data: 'transaction', name: 'transaction', title: 'Transaction' },
                { data: 'insurance', name: 'insurance', title: 'Insurance' },
                { data: 'other_profit', name: 'other_profit', title: 'Other Profit' },
                { data: 'trans_bank', name: 'trans_bank', title: 'Trans Bank' },
                { data: 'agent', name: 'agent', title: 'Agent' },
                { data: 'team', name: 'team', title: 'Team' },
                { data: 'status', name: 'status', title: 'Status' },
                { data: 'unit_type', name: 'unit_type', title: 'Unit Type' },
                { data: 'profit', name: 'profit', title: 'Profit' },
                { data: 'other_profit', name: 'other_profit', title: 'Other Profit' },
                { data: 'gender', name: 'gender', title: 'Gender' },
                { data: 'remarks', name: 'remarks', title: 'Remarks' },
                { data: 'lto_remarks', name: 'lto_remarks', title: 'LTO Remarks' },
                { data: 'source', name: 'source', title: 'Source' },
                { data: 'date_reserved', name: 'date_reserved', title: 'Date Reserved' },
                { data: 'action', name: 'action', title: 'Action',
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-danger" onclick="deleteReleasesBacklog('${row.id}')">Delete</button>
                        `;
                    }
                },
            ],
            order: [[0, "desc"]],
            drawCallback: function(settings) {
                // Check if there is data in the table
                if (settings.json && settings.json.data && settings.json.data.length > 0) {
                    $('#transferToReleases').removeClass('d-none');
                } else {
                    $('#transferToReleases').addClass('d-none');
                }
            }
        });
        
        


    });
</script>

@endsection

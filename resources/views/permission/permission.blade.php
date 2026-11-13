@extends('components.app')

@section('content')

{{-- Title Header --}}
<div class="card bg-dark shadow-none mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <i class='bx bxs-key text-white' style="font-size: 24px;">&nbsp;</i>
            <h4 class="text-white mb-0">Permissions</h4>
        </div>
    </div>
</div>

{{-- Add Permission Button --}}
<div class="row mb-2">
    <div class="col-md d-flex justify-content-end gap-2">
        <select class="form-select w-25" id="usertypeSelect">
            <option value="">Select User Type</option>
        </select>

        <button class="btn btn-success" id="updatePermissionButton">Save Permission Access</button>
        <button class="btn btn-primary" id="addPermissionButton">Add Permission</button>

    </div>
</div>

{{-- Permission Form --}}
<div class="row mb-4">
    <div class="col-md">
        <div class="card" id="permissionFormCard" style="display: none;">
            <div class="card-header">
                <h5 class="text-primary card-title">Permission Form</h5>
            </div>
            <div class="card-body">
                <form id="permissionFormData">
                    @csrf
                    <div class="row mb-2">
                        <div class="col-md">
                            <label for="name" class="form-label required">Name</label>
                            <input type="text" class="form-control" id="permissionName" name="permission_name" required />
                            <small class="text-danger" id="validateName">Please enter permission name</small>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="permissionDescription" name="permission_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-label-danger" id="cancelPermissionFormButton">Cancel</button>
                            <button type="submit" class="btn btn-dark">Add Permission</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Datatable --}}
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="permissionTable" class="table table-bordered table-hover" style="width:100%">
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
const permissionTable = $('#permissionTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: '{{ route("permissions.list") }}',
    },
    responsive: true,
    dom: '<"top"lf>rt',
    language: {
        search: "",
        searchPlaceholder: "Search..."
    },
    columns: [
        {data: 'id', name: 'id', title: 'ID', visible: false},
        {
            data: null,
            name: 'select_all',
            orderable: false,
            searchable: false,
            render: function(data, type, row) {
                return `<input type="checkbox" class="permission-checkbox" name="permissions[]" value="${row.id}">`;
            },
            title: `<input type="checkbox" id="selectAllPermissionsHeader" />`
        },
        { data: 'permission_name', name: 'permission_name', title: 'Name' },
        { data: 'permission_description', name: 'permission_description', title: 'Description' },

    ],
    order: [[1, 'asc']], // Order by name
    paging: false, // Disable pagination
    info: false, // Disable information text at the bottom
});



    // Handle select all checkbox
    $('#selectAllPermissionsHeader').on('change', function() {
        $('.permission-checkbox').prop('checked', this.checked);
    });

    // Form Show/Hide Logic
    $(document).ready(function() {
        $('#addPermissionButton').click(function() {
            $('#permissionFormCard').show();
            $('#addPermissionButton').hide();
        });

        $('#cancelPermissionFormButton').click(function() {
            $('#permissionFormData')[0].reset();
            $('#permissionFormCard').hide();
            $('#addPermissionButton').show();
        });
    });

    // Form Submission
    $(document).ready(function() {
        $('#permissionFormData').on('submit', function(e) {
            e.preventDefault();

            let formData = {
                name: $('#permissionName').val(),
                description: $('#permissionDescription').val(),
            };

            $.ajax({
                url: '{{ route("permissions.store") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                        }).then(() => {
                            $('#permissionFormCard').hide();
                            $('#permissionFormData')[0].reset();
                            $('#addPermissionButton').show();
                            permissionTable.ajax.reload();
                        });
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
    });

    // Load user types
    $(document).ready(function() {
        // Load user types
        $.ajax({
            url: '{{ route("permissions.user-types") }}',
            type: 'GET',
            success: function(response) {
                const select = $('#usertypeSelect');
                response.forEach(function(userType) {
                    select.append(new Option(userType.name, userType.id));
                });
            }
        });

        // Add change event handler for usertype select
        $('#usertypeSelect').on('change', function() {
            const usertypeId = $(this).val();

            // Clear all checkboxes first
            $('.permission-checkbox').prop('checked', false);
            $('#selectAllPermissionsHeader').prop('checked', false);

            if (usertypeId) {
                // Fetch and check permissions for selected usertype
                $.ajax({
                    url: `/permissions/usertype/${usertypeId}`,
                    type: 'GET',
                    success: function(permissionIds) {
                        permissionIds.forEach(function(permissionId) {
                            $(`.permission-checkbox[value="${permissionId}"]`).prop('checked', true);
                        });

                        // Update "select all" checkbox state
                        const allChecked = $('.permission-checkbox:checked').length === $('.permission-checkbox').length;
                        $('#selectAllPermissionsHeader').prop('checked', allChecked);
                    }
                });
            }
        });
    });

    // Update the save permissions button click handler
    $('#updatePermissionButton').click(function() {
        const selectedPermissions = [];
        const usertypeId = $('#usertypeSelect').val();

        $('.permission-checkbox:checked').each(function() {
            selectedPermissions.push($(this).val());
        });

        if (!usertypeId) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select a user type',
            });
            return;
        }

        if (selectedPermissions.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select at least one permission',
            });
            return;
        }

        $.ajax({
            url: '{{ route("permissions.update") }}',
            type: 'POST',
            data: {
                permissions: selectedPermissions,
                usertype_id: usertypeId
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                    });
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




</script>
@endsection

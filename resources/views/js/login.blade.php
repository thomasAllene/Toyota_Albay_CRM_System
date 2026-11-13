<script>
    $('#loginForm').on('submit', function(event) {
        event.preventDefault();
        $.ajax({
            url: '{{ route('login.user') }}',
            method: 'POST',
            data: {
            email: $('#email').val(),
            password: $('#password').val(),
            _token: '{{ csrf_token() }}'
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
                Swal.close(); // Hide loader
            if (response.success) {
                window.location.href = response.redirect;
            } else {
                if(response.message){
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message,
                    showConfirmButton: true,
                });
                } else {
                var errors = response.errors;
                Object.keys(errors).forEach(function(key) {
                    var inputField = $('#loginForm [name=' + key + ']');
                    inputField.addClass('is-invalid');
                    $('#loginForm #' + key + 'Error').text(errors[key][0]);
                });
                }
            }
            },
            error: function() {
                Swal.close(); // Hide loader
            Swal.fire({
                icon: 'error',
                title: 'Failed!',
                text: 'Something went wrong.',
                showConfirmButton: true,
            });
            }
        });
    });


    $('#loginForm').find('input, select').on('keyup change', function() {
        $(this).removeClass('is-invalid');
        var errorId = $(this).attr('name') + 'Error';
        $('#' + errorId).text('');
    });
</script>

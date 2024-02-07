// forgot-password.js
$(document).ready(function () {
    $('#forgotPasswordForm').submit(function (e) {
        e.preventDefault();

        $.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Password Reset Link Sent',
                        text: response.message,
                        icon: 'success',
                        showConfirmButton: true
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message,
                        icon: 'error',
                        showConfirmButton: true
                    });
                }
            }
        });
    });
});

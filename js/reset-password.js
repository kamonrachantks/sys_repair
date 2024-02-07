// reset-password.js
$(document).ready(function () {
    $('#resetPasswordForm').submit(function (e) {
        e.preventDefault();

        $.ajax({
            type: $(this).attr('method'),
            url: 'update-password.php', // URL to the server script for updating the password
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Password Updated',
                        text: response.message,
                        icon: 'success',
                        showConfirmButton: true
                    }).then(() => {
                        window.location.href = 'login.php'; // Redirect to login page after successful password update
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

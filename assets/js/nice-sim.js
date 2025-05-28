/**
 * Nice SIM Plugin JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Form submission
        $('#nice-sim-form').on('submit', function(e) {
            e.preventDefault();

            // Get form data
            var phoneNumber = $('#nice-sim-dien-thoai').val();
            var dayBirth = $('#nice-sim-ngay-sinh').val();
            var monthBirth = $('#nice-sim-thang-sinh').val();
            var yearBirth = $('#nice-sim-nam-sinh').val();
            var gender = $('#nice-sim-gioi-tinh').val();
            var hourBirth = $('#nice-sim-gio-sinh').val();

            // Validate form
            if (!phoneNumber || !dayBirth || !monthBirth || !yearBirth || !gender || !hourBirth) {
                showError('Vui lòng điền đầy đủ các trường bắt buộc.');
                return;
            }

            // Show loading indicator
            $('.nice-sim-loading').show();
            $('.nice-sim-result-container').hide();
            $('.nice-sim-error').hide();

            // Make AJAX request
            $.ajax({
                url: NiceSim.ajax_url,
                type: 'POST',
                data: {
                    action: 'nice_sim_check',
                    nonce: NiceSim.nonce,
                    dien_thoai: phoneNumber,
                    ngay_sinh: dayBirth,
                    thang_sinh: monthBirth,
                    nam_sinh: yearBirth,
                    gioi_tinh: gender,
                    gio_sinh: hourBirth
                },
                success: function(response) {
                    // Hide loading indicator
                    $('.nice-sim-loading').hide();

                    // Check if response is successful
                    if (response.success) {
                        // Show result
                        $('.nice-sim-result-container').show();
                        $('.nice-sim-result-content').html(response.data.data.data);
                    } else {
                        // Show error
                        showError(response.data.message || 'Đã xảy ra lỗi. Vui lòng thử lại.');
                    }
                },
                error: function() {
                    // Hide loading indicator
                    $('.nice-sim-loading').hide();

                    // Show error
                    showError('Đã xảy ra lỗi. Vui lòng thử lại.');
                }
            });
        });

        // Function to show error
        function showError(message) {
            $('.nice-sim-error').show();
            $('.nice-sim-error-message').text(message);
        }

        // Set default values for date fields (current user's birthday if available)
        var today = new Date();
        $('#nice-sim-ngay-sinh').val('24');
        $('#nice-sim-thang-sinh').val('9');
        $('#nice-sim-nam-sinh').val('1989');
    });

})(jQuery); 
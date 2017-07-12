jQuery(function() {
    jQuery('#start-job').on('click', function() {
        jQuery('#new_batch_upload_job_form').submit();
    });

    jQuery('#cancel-job').on('click', function() {
        if (!confirm(jQuery(this).data('confirm-message'))) {
            return false;
        }
    });
});

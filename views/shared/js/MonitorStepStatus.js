function monitorJob(jobUrl, currentStep) {
    setInterval(function() {
        jQuery.ajax({
            url: jobUrl,
            method: 'GET',
            success: function(data) {
                if (data.step != currentStep || data.finished) {
                    location.reload(true);
                }
            }
        });
    }, 2000);
}

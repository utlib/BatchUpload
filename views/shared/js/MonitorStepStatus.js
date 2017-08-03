function monitorJob(jobUrl, currentStep, targetUrl) {
    setInterval(function() {
        jQuery.ajax({
            url: jobUrl,
            method: 'GET',
            success: function(data) {
                if (data.step != currentStep || data.finished) {
                    window.location.href = targetUrl;
                }
            }
        });
    }, 2000);
}

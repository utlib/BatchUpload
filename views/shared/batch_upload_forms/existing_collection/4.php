<script>
jQuery(function() {
    jQuery('#fileinput-button').click(function() {
        jQuery('#fileupload').click();
    });
    jQuery('#fileupload').fileupload({
        url: <?php echo js_escape($processing_path) ?>,
        dataType: 'json',
        done: function (e, data) {
            jQuery.each(data.result.files, function (index, file) {
                jQuery('.file-block[data-filename=' + JSON.stringify(file.name) + ']').remove();
            });
            if (data.result.finished) {
                location.reload(true);
            }
        },
        progressall: function (e, data) {
            jQuery('#progress').attr('value', data.loaded).attr('max', data.total);
        }
    }).prop('disabled', !jQuery.support.fileInput)
        .parent().addClass(jQuery.support.fileInput ? undefined : 'disabled');
});
</script>

<button id="fileinput-button" class="green button"><?php echo __("Upload Files"); ?></button>

<input id="fileupload" type="file" name="files[]" multiple="multiple" style="position: fixed; left: -9999px;">
<!-- The global progress bar -->
<progress id="progress" style="width:100%;" value="0" max="<?php echo count($file_rows); ?>"></progress>

<p><?php echo __("Please upload the following files mentioned in your CSV input."); ?></p>

<div id="files-block">
    <?php foreach ($file_rows as $fileRow): ?>
    <li class="file-block" data-filename="<?php echo html_escape($fileRow['file']); ?>"><?php echo html_escape($fileRow['file']); ?></li>
    <?php endforeach; ?>
</div>
<script>
jQuery(function() {
    jQuery('#fileinput-button').click(function() {
        jQuery('#fileupload').click();
    });
    jQuery('#fileupload').fileupload({
        url: <?php echo js_escape($processing_path) ?>,
        dataType: 'json',
        sequentialUploads: true,
        done: function (e, data) {
            jQuery.each(data.result.files, function (index, file) {
                jQuery('.file-block[data-filename=' + JSON.stringify(file.name) + ']').remove();
                jQuery('<li class="file-block">').data('filename', file.name).text(file.name).appendTo('#success-files-block');
            });
            jQuery.each(data.result.fails, function (index, fail) {
                jQuery('<li class="file-block">').data('filename', fail.name).text(fail.name + ' (' + fail.reason + ')').prependTo('#fail-files-block');
            });
            jQuery('#files-count').text(jQuery('#files-block .file-block').length);
            jQuery('#success-files-count').text(jQuery('#success-files-block .file-block').length);
            jQuery('#fail-files-count').text(jQuery('#fail-files-block .file-block').length);
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

<p><?php echo __("You have indicated that there are files associated with your batch upload job. Please select them now."); ?></p>

<button id="fileinput-button" class="green button"><?php echo __("Select files to upload..."); ?></button>

<input id="fileupload" type="file" name="files[]" multiple="multiple" style="position: fixed; left: -9999px;">
<!-- The global progress bar -->
<progress id="progress" style="width:100%;" value="0" max="<?php echo count($file_rows); ?>"></progress>

<div class="three columns alpha">
    <h4><?php echo __("Expected Files"); ?> (<span id="files-count"><?php echo count($file_rows); ?></span>)</h4>
    <ul id="files-block">
        <?php foreach ($file_rows as $fileRow): ?>
        <li class="file-block" data-filename="<?php echo html_escape($fileRow['file']); ?>"><?php echo html_escape($fileRow['file']); ?></li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="three columns">
    <h4><?php echo __("Successful Files"); ?> (<span id="success-files-count">0</span>)</h4>
    <ul id="success-files-block">
    </ul>
</div>

<div class="four columns omega">
    <h4><?php echo __("Failed Files"); ?> (<span id="fail-files-count">0</span>)</h4>
    <ul id="fail-files-block">
    </ul>
</div>
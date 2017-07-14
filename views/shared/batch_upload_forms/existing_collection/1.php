<script>
jQuery(function() {
    jQuery('#next-step').click(function() {
        jQuery('#collection_id_selector').submit();
    });
});
</script>

<section class="seven columns alpha">
<?php
echo $form;
?>
</section>

<section class="three columns omega">
    <div id="save" class="panel">
        <a id="next-step" class="big green button"><?php echo __("Next Step"); ?></a>
        <a href="<?php echo html_escape(admin_url(array('controller' => 'jobs', 'id' => $batch_upload_job->id, 'action' => 'delete-confirm'), 'batchupload_id')); ?>" class="big red button delete-confirm"><?php echo __("Cancel Job"); ?></a>
    </div>
</section>
<?php
queue_js_file('MappingEditor', 'js');
echo head(array(
    'title' => __('Batch Uploader') . ' | ' . __('Add New Mapping Template'),
));
require(__DIR__ . "/../shared/nav.php");
echo flash();
?>

<form method="post">
    <?php require(__DIR__ . "/mapping-sets-form.php"); ?>
    <section class="three columns omega">
        <div id="save" class="panel">
            <input type="submit" value="<?php echo html_escape(__("Add Mapping Set")); ?>" class="big green button">
            <a href="<?php echo html_escape(admin_url(array(), 'batchupload_root')); ?>" class="big red button"><?php echo __("Cancel"); ?></a>
        </div>
    </section>
</form>

<?php
echo foot();

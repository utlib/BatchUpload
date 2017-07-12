<?php
queue_css_file('general');
queue_js_file('NewJob', 'js');
echo head(array(
    'title' => __('Batch Uploader'),
));
require(__DIR__ . "/../shared/nav.php");
echo flash();
?>

<section class="seven columns alpha">
    <?php echo $form; ?>
</section>

<section class="three columns omega">
    <div id="save" class="panel">
        <a id="start-job" class="big green button"><?php echo __("Start Job"); ?></a>
        <a id="cancel-job" class="big red button" data-confirm-message="<?php echo html_escape(__("Are you sure that you want to cancel this Job?")); ?>"><?php echo __("Cancel Job"); ?></a>
    </div>
</section>

<?php
echo foot();

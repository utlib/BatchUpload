<form method="POST">
    <section class="seven columns alpha">
    <?php
    $form->removeDecorator('form');
    echo $form;
    ?>
    </section>

    <section class="three columns omega">
        <div id="save" class="panel">
            <button class="big green button"><?php echo __("Next Step"); ?></button>
            <a href="<?php echo html_escape(admin_url(array('controller' => 'jobs', 'id' => $batch_upload_job->id, 'action' => 'delete-confirm'), 'batchupload_id')); ?>" class="big red button delete-confirm"><?php echo __("Cancel Job"); ?></a>
        </div>
    </section>
</form>
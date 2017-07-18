<?php
queue_css_file('general');
echo head(array(
    'title' => __('Batch Uploader') . ($page_title ? (' | ' . html_escape($page_title)) : ''),
));
require(__DIR__ . "/../shared/nav.php");
echo flash();
?>

<?php echo $partial; ?>

<?php
echo foot();
?>

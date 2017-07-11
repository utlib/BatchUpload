<nav id="section-nav" class="navigation vertical">
<?php
echo nav(array(
    array(
        'label' => __('Jobs'),
        'uri' => url('batch-upload/jobs'),
    ),
    array(
        'label' => __('Mappings'),
        'uri' => url('batch-upload/mapping-sets'),
    ),
), 'admin_navigation_settings');
?>
</nav>
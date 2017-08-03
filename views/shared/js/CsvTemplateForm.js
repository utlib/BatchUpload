jQuery(function() {
    jQuery('#csv-template-form').on('change', '.csv-template-filefield', function() {
        var jqf = jQuery('#csv-template-form'),
            jqt = jQuery(this),
            mapping_id = jqt.data('mapping-id');
        jqf.find('.csv-template-fileentry[data-mapping-id=' + mapping_id + ']').remove();
        jQuery.each(jqt.get(0).files, function() {
            jQuery('<input type="hidden" class="csv-template-fileentry">')
                    .attr('data-mapping-id', mapping_id)
                    .attr('name', 'filespecs[' + mapping_id + '][]')
                    .val(this.name)
                    .appendTo(jqf);
        });
    });
});

jQuery(function() {
    // Variables
    var csvResults = {data:[]},
        currentRow = null,
        countRow = null;
        
    // Hide elements that should appear only after a file is loaded
    jQuery('#phase2').hide();
    jQuery('#example-nav').hide();
    
    // Functions
    var entityMap = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '/': '&#x2F;',
        '`': '&#x60;',
        '=': '&#x3D;'
    };
    var maxWidth = 30;
    function escapeHtml(string) {
        return String(string).replace(/[&<>"'`=\/]/g, function (s) {
            return entityMap[s];
        });
    }
    function refreshNavigator(papaData, useHeaders, current, count) {
        if (current === null || !count) {
            jQuery('#example-nav').hide();
        } else {
            jQuery('#mapping-current-index').text(current+1);
            jQuery('#mapping-current-count').text(count);
            jQuery('#mapping-goto-first').attr('disabled', (current === 0) ? 'disabled' : null);
            jQuery('#mapping-goto-previous').attr('disabled', (current === 0) ? 'disabled' : null);
            jQuery('#mapping-goto-random').attr('disabled', (count <= 1) ? 'disabled' : null);
            jQuery('#mapping-goto-next').attr('disabled', (current >= count-1) ? 'disabled' : null);
            jQuery('#mapping-goto-last').attr('disabled', (current >= count-1) ? 'disabled' : null);
            refreshHeader(papaData, useHeaders, current);
        }
    }
    function refreshTable(papaData, useHeaders) {
        var jqtbody = jQuery('#metadata-table tbody'),
            options = jQuery('#metadata-table').data('selectcontent'),
            premaps = jQuery('#metadata-table').data('shortcutmappings'),
            i = 0;
        jqtbody.html('');
        if (papaData.data.length > 0) {
            if (currentRow === null) {
                currentRow = 0;
            }
            countRow = papaData.data.length;
            if (currentRow >= countRow) {
                currentRow = countRow-1;
            }
            if (useHeaders) {
                jQuery.each(papaData.data[0], function(k, v) {
                    var _ks = escapeHtml(k.slice(0, maxWidth)) + (k.length > maxWidth ? '&hellip;' : ''),
                        _vs = escapeHtml(v.slice(0, maxWidth)) + (v.length > maxWidth ? '&hellip;' : ''),
                        _k = escapeHtml(k),
                        _v = escapeHtml(v);
                    jqtbody.append('<tr><td>' + _ks + '</td><td>' + _vs + '</td><td><select name="metadata[' + i + '][property]">' + options + '</select></td><td><input type="checkbox" name="metadata[' + i + '][html]"><input type="hidden" name="metadata[' + i + '][header]" value="' + _k + '"></td></tr>');
                    if (premaps.hasOwnProperty(k)) {
                        jqtbody.find('select[name="metadata[' + i + '][property]"]').val(premaps[k]);
                    }
                    i++;
                });
            } else {
                jQuery.each(papaData.data[0], function(_, v) {
                    var _vs = escapeHtml(v.slice(0, maxWidth)) + (v.length > maxWidth ? '&hellip;' : ''),
                        _v = escapeHtml(v);
                    jqtbody.append('<tr><td>[' + (i+1) + ']</td><td>' + _vs + '</td><td><select name="metadata[' + i + '][property]">' + options + '</select></td><td><input type="checkbox" name="metadata[' + i + '][html]"><input type="hidden" name="metadata[' + i + '][header]" value="' + (i+1) + '"></td></tr>');
                    i++;
                });
            }
            jQuery('#next-step').attr('disabled', null);
            refreshNavigator(papaData, jQuery('#has_headers').is(':checked'), currentRow, countRow);
            jQuery('#example-nav').show(200);
        } else {
            currentRow = null;
            countRow = null;
            jQuery('#phase2').hide();
            jQuery('#next-step').attr('disabled', true);
            jQuery('#example-nav').hide(200);
        }
    };
    function refreshHeader(papaData, useHeaders, entryNum) {
        var jqtbody = jQuery('#metadata-table tbody'),
            options = jQuery('#metadata-table').data('selectcontent'),
            premaps = jQuery('#metadata-table').data('shortcutmappings'),
            entry = papaData.data[entryNum];
        jqtbody.find('tr').each(function(i) {
            _jqtr = jQuery(this);
            if (useHeaders) {
                var k = (Object.keys(entry))[i],
                    v = entry[k],
                    _ks = (k.slice(0, maxWidth)) + (k.length > maxWidth ? '\u2026' : ''),
                    _vs = (v.slice(0, maxWidth)) + (v.length > maxWidth ? '\u2026' : '');
                _jqtr.find('td:first').text(_ks);
                _jqtr.find('td:eq(1)').text(_vs);
                _jqtr.find('input[name="metadata[' + i + '][header]"]').val(v);
            } else {
                var v = entry[i],
                    _vs = (v.slice(0, maxWidth)) + (v.length > maxWidth ? '\u2026' : '');
                _jqtr.find('td:first').text('[' + (i+1) + ']');
                _jqtr.find('td:eq(1)').text(_vs);
                _jqtr.find('input[name="metadata[' + i + '][header]"]').val(v);
            }
        });
    }
    
    // Read the CSV when a file is successfully loaded
    jQuery('#csv_file').change(function() {
        jQuery('#phase2').hide();
        jQuery('#example-nav').hide();
        jQuery('#next-step').attr('disabled', 'disabled');
        jQuery('#csv-data').val('');
        jQuery(this).parse({
            config: {
                header: jQuery('#has_headers').is(':checked'),
                skipEmptyLines: true,
                complete: function(results, file) {
                    csvResults = results;
                    console.log("This file done: ", file, results);
                    refreshTable(csvResults, jQuery('#has_headers').is(':checked'));
                    jQuery('#phase2').show();
                    jQuery('#csv-data').val(JSON.stringify(csvResults.data));
                }
            }
        });
    });
    
    // Flip between using headers and not
    jQuery('#has_headers').change(function() {
        jQuery('#csv-data').val('');
        if (csvResults.data.length > 0) {
            jQuery('#csv_file').parse({
                config: {
                    header: jQuery(this).is(':checked'),
                    skipEmptyLines: true,
                    complete: function(results, file) {
                        csvResults = results;
                        console.log("This file done: ", file, results);
                        countRow = csvResults.data.length;
                        if (currentRow >= countRow) {
                            currentRow = countRow-1;
                        }
                        refreshHeader(csvResults, jQuery(this).is(':checked'), 0);
                        jQuery('#csv-data').val(JSON.stringify(csvResults.data));
                    }
                }
            });
        }
    });
    
    // Navigation buttons
    jQuery('#mapping-goto-first').click(function() {
        currentRow = 0;
        refreshHeader(csvResults, jQuery('#has_headers').is(':checked'), currentRow);
        refreshNavigator(csvResults, jQuery('#has_headers').is(':checked'), currentRow, countRow);
    });
    jQuery('#mapping-goto-last').click(function() {
        currentRow = countRow-1;
        refreshHeader(csvResults, jQuery('#has_headers').is(':checked'), currentRow);
        refreshNavigator(csvResults, jQuery('#has_headers').is(':checked'), currentRow, countRow);
    });
    jQuery('#mapping-goto-previous').click(function() {
        currentRow--;
        refreshHeader(csvResults, jQuery('#has_headers').is(':checked'), currentRow);
        refreshNavigator(csvResults, jQuery('#has_headers').is(':checked'), currentRow, countRow);
    });
    jQuery('#mapping-goto-next').click(function() {
        currentRow++;
        refreshHeader(csvResults, jQuery('#has_headers').is(':checked'), currentRow);
        refreshNavigator(csvResults, jQuery('#has_headers').is(':checked'), currentRow, countRow);
    });
    jQuery('#mapping-goto-random').click(function() {
        oldRow = currentRow;
        do {
            currentRow = Math.floor(Math.random() * countRow);
        } while (oldRow === currentRow)
        refreshHeader(csvResults, jQuery('#has_headers').is(':checked'), currentRow);
        refreshNavigator(csvResults, jQuery('#has_headers').is(':checked'), currentRow, countRow);
    });
});

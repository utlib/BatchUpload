jQuery(function() {
    // Variables
    var csvResults = {data:[]},
        currentRow = null,
        countRow = null,
        hasHeaderChecked = jQuery('#has_headers').is(':checked');
        
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
            refreshNavigator(papaData, hasHeaderChecked, currentRow, countRow);
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
                _jqtr.find('input[name="metadata[' + i + '][header]"]').val(k);
            } else {
                var k = '[' + (i+1) + ']',
                    v = entry[i],
                    _vs = (v.slice(0, maxWidth)) + (v.length > maxWidth ? '\u2026' : '');
                _jqtr.find('td:first').text(k);
                _jqtr.find('td:eq(1)').text(_vs);
                _jqtr.find('input[name="metadata[' + i + '][header]"]').val(k);
            }
        });
    }
    function refreshHiddenCsvData() {
        jQuery('#csv-data').val('');
        jQuery('#next-step').attr('disabled', true);
        jQuery('#csv_file').parse({
            config: {
                header: false,
                skipEmptyLines: true,
                complete: function(results, file) {
                    if (hasHeaderChecked) {
                        jQuery('#csv-data').val(JSON.stringify(results.data.slice(1)));
                    } else {
                        jQuery('#csv-data').val(JSON.stringify(results.data));
                    }
                    if (results.data.length > 0) {
                        jQuery('#next-step').attr('disabled', null);
                    }
                }
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
                header: hasHeaderChecked,
                skipEmptyLines: true,
                complete: function(results, file) {
                    csvResults = results;
                    console.log("This file done: ", file, results);
                    refreshTable(csvResults, hasHeaderChecked);
                    jQuery('#phase2').show();
                    refreshHiddenCsvData();
                }
            }
        });
    });
    
    // Flip between using headers and not
    jQuery('#has_headers').change(function() {
        hasHeaderChecked = jQuery(this).is(':checked');
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
                        refreshHeader(csvResults, hasHeaderChecked, 0);
                        refreshHiddenCsvData();
                    }
                }
            });
        }
    });
    
    // Navigation buttons
    jQuery('#mapping-goto-first').click(function() {
        currentRow = 0;
        refreshHeader(csvResults, hasHeaderChecked, currentRow);
        refreshNavigator(csvResults, hasHeaderChecked, currentRow, countRow);
    });
    jQuery('#mapping-goto-last').click(function() {
        currentRow = countRow-1;
        refreshHeader(csvResults, hasHeaderChecked, currentRow);
        refreshNavigator(csvResults, hasHeaderChecked, currentRow, countRow);
    });
    jQuery('#mapping-goto-previous').click(function() {
        currentRow--;
        refreshHeader(csvResults, hasHeaderChecked, currentRow);
        refreshNavigator(csvResults, hasHeaderChecked, currentRow, countRow);
    });
    jQuery('#mapping-goto-next').click(function() {
        currentRow++;
        refreshHeader(csvResults, hasHeaderChecked, currentRow);
        refreshNavigator(csvResults, hasHeaderChecked, currentRow, countRow);
    });
    jQuery('#mapping-goto-random').click(function() {
        oldRow = currentRow;
        do {
            currentRow = Math.floor(Math.random() * countRow);
        } while (oldRow === currentRow)
        refreshHeader(csvResults, hasHeaderChecked, currentRow);
        refreshNavigator(csvResults, hasHeaderChecked, currentRow, countRow);
    });
    
    // "Saving as new mapping template" button
    jQuery('#save-new-template').click(function() {
        var jqt = jQuery(this),
            name = prompt(jqt.data('prompt'), ""), // Get name of new mapping set
            mappingsData = [];
        if (name !== null) {
            // Capture values from mappings
            jQuery('#metadata-table tbody tr').each(function(index) {
                var elem = jQuery(this);
                mappingsData.push({
                    header: elem.find("input[name*='header']").val(),
                    order: index+1,
                    property: parseInt(elem.find("select[name*='property']").val()),
                    html: elem.find("input[name*='html']").is(':checked') ? 1 : 0
                });
            });
            // Send request to AJAX callback
            // See BatchUpload_Wizard_ExistingCollection::step2Ajax for details
            jQuery.ajax({
                url: jqt.data('url'),
                method: 'post',
                dataType: 'json',
                data: {
                    action: 'save-mapping',
                    set_name: name,
                    mappings: mappingsData
                },
                success: function(data) {
                    jQuery('#mapping-set-template').append('<option value="' + data.mapping_set + '">' + jQuery('<div />').text(name).html() + '</option>');
                    alert(data.message);
                }
            });
        }
    });
    
    // "Apply" button
    jQuery('#apply-template').click(function() {
        jQuery.ajax({
            url: jQuery(this).data('url'),
            method: 'post',
            dataType: 'json',
            data: {
                action: 'apply-mapping',
                mapping_set: parseInt(jQuery('#mapping-set-template').val())
            },
            success: function(data) {
                if (data.success) {
                    if (hasHeaderChecked) {
                        jQuery.each(data.mappings, function(i, v) {
                            var row = null;
                            jQuery('#metadata-table tbody tr').each(function(ii) {
                                if (jQuery(this).find("input[name*='header']").val() === v.header) {
                                    row = jQuery('#metadata-table tbody tr:eq(' + ii + ')');
                                    return false;
                                }
                            });
                            if (row) {
                                row.find("select[name*='property']").val(v.property);
                                row.find("input[name*='html']").attr('checked', v.html ? true : null);
                            }
                        });
                    } else {
                        jQuery.each(data.mappings, function(i, v) {
                            var row = jQuery('#metadata-table tbody tr:eq(' + i + ')');
                            row.find("input[name*='header']").val(v.header);
                            row.find("select[name*='property']").val(v.property);
                            row.find("input[name*='html']").val(v.html);
                        });
                    }
                }
            }
        });
    });
});

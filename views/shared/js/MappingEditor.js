jQuery(function() {
    // Activate each mappings editor
    jQuery('.batchupload-mappings-editor').each(function() {
        var _jQueryThis = jQuery(this),
            _i = _jQueryThis.find('.mapping-row').length,
            selectTemplate = jQuery(this).data('selectcontent'),
            rowTemplate = function(i, order) {
                return '<tr class="mapping-row" data-count="' + i + '">' +
                            '<td><input type="text" name="mappings[' + i + '][header]"></td>' +
                            '<td><span class="mappings-order-label">' + order + '</span><input type="hidden" name="mappings[' + i + '][order]" value="' + order + '"></td>' +
                            '<td><select name="mappings[' + i + '][property]">' + selectTemplate + '</select></td>' +
                            '<td><input type="hidden" value="0" name="mappings[' + i + '][html]"><input type="checkbox" value="1" name="mappings[' + i + '][html]"></td>' +
                            '<td>' +
                                '<button type="button" class="small green button mappings-add-button">+</button>' +
                                '<button type="button" class="small blue button mappings-up-button">↑</button>' +
                                '<button type="button" class="small blue button mappings-down-button">↓</button>' +
                                '<button type="button" class="small red button mappings-delete-button">×</button>' +
                            '</td>' +
                        '</tr>';
            },
            manageButtons = function() {
                // Disable delete button iff there is only one mapping left
                if (_jQueryThis.find('.mapping-row').length <= 1) {
                    _jQueryThis.find('.mappings-delete-button').attr('disabled', true);
                } else {
                    _jQueryThis.find('.mappings-delete-button').attr('disabled', null);
                }
                // Disable first row's up
                _jQueryThis.find('.mapping-row .mappings-up-button').attr('disabled', null);
                _jQueryThis.find('.mapping-row:first .mappings-up-button').attr('disabled', true);
                // Disable last row's down
                _jQueryThis.find('.mapping-row .mappings-down-button').attr('disabled', null);
                _jQueryThis.find('.mapping-row:last .mappings-down-button').attr('disabled', true);
            };
        manageButtons();
        // Activate buttons within this mappings editor
        // The "Add" button
        _jQueryThis.on('click', '.mappings-add-button', function() {
            // Find the row clicked and the order of the new row
            var clickedRow = jQuery(this).parent().parent(),
                newOrder = 1+parseInt(jQuery('input[name="mappings[' + clickedRow.data('count') + '][order]"]').val());
            // Increment the count on all rows that'll come after the new row
            _jQueryThis.find('.mapping-row').each(function() {
                var hiddenField = jQuery(this).find('input[name="mappings[' + jQuery(this).data('count') + '][order]"]'),
                    orderVal = parseInt(hiddenField.val());
                if (orderVal >= newOrder) {
                    orderVal++;
                    hiddenField.val(orderVal);
                    jQuery(this).find('.mappings-order-label').text(orderVal);
                }
            });
            // Add the new row
            clickedRow.after(rowTemplate(_i++, newOrder));
            // Update buttons
            manageButtons();
        });
        // The "Up" button
        _jQueryThis.on('click', '.mappings-up-button', function() {
            // Find the row clicked and the row before it
            var clickedRow = jQuery(this).parent().parent(),
                theOrder = parseInt(jQuery('input[name="mappings[' + clickedRow.data('count') + '][order]"]').val()),
                beforeRow = _jQueryThis.find('.mapping-row:eq(' + (theOrder-2) + ')');
            // Swap their order attributes
            clickedRow.find('.mappings-order-label').text(theOrder-1);
            beforeRow.find('input[name="mappings[' + beforeRow.data('count') + '][order]"]').val(theOrder);
            beforeRow.find('.mappings-order-label').text(theOrder);
            clickedRow.find('input[name="mappings[' + clickedRow.data('count') + '][order]"]').val(theOrder-1);
            // Swap the two rows
            beforeRow.before(clickedRow.detach());
            // Update buttons
            manageButtons();
        });
        // The "Down" button
        _jQueryThis.on('click', '.mappings-down-button', function() {
            // Find the row clicked and the row after it
            var clickedRow = jQuery(this).parent().parent(),
                theOrder = parseInt(jQuery('input[name="mappings[' + clickedRow.data('count') + '][order]"]').val()),
                afterRow = _jQueryThis.find('.mapping-row:eq(' + (theOrder) + ')');
            // Swap their order attributes
            clickedRow.find('.mappings-order-label').text(theOrder+1);
            afterRow.find('input[name="mappings[' + afterRow.data('count') + '][order]"]').val(theOrder);
            afterRow.find('.mappings-order-label').text(theOrder);
            clickedRow.find('input[name="mappings[' + clickedRow.data('count') + '][order]"]').val(theOrder+1);
            // Swap the two rows
            afterRow.after(clickedRow.detach());
            // Update buttons
            manageButtons();
        });
        // The "Delete" button
        _jQueryThis.on('click', '.mappings-delete-button', function() {
            // Find the row clicked
            var clickedRow = jQuery(this).parent().parent(),
                theOrder = parseInt(jQuery('input[name="mappings[' + clickedRow.data('count') + '][order]"]').val());
            // Decrement the count on all rows that'll come after the new row
            _jQueryThis.find('.mapping-row').each(function() {
                var hiddenField = jQuery(this).find('input[name="mappings[' + jQuery(this).data('count') + '][order]"]'),
                    orderVal = parseInt(hiddenField.val());
                if (orderVal > theOrder) {
                    orderVal--;
                    hiddenField.val(orderVal);
                    jQuery(this).find('.mappings-order-label').text(orderVal);
                }
            });
            // Remove and leave residual hidden fields
            clickedRow.find('input[name="mappings[' + clickedRow.data('count') + '][_id]"]').detach().appendTo(_jQueryThis);
            _jQueryThis.append('<input type="hidden" name="mappings[' + clickedRow.data('count') + '][_delete]" value="1">');
            clickedRow.remove();
            // Update buttons
            manageButtons();
        });
    });
});

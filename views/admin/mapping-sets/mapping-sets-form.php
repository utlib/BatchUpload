<section class="seven columns alpha">
    <div class="field">
        <div id="name-label" class="two columns alpha">
            <label for="name" class="required"><?php echo __("Name"); ?></label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"><?php echo __("The name of this mapping set."); ?></p>
            <input type="text" name="name" id="name" value="<?php echo html_escape($batch_upload_mapping_set->name); ?>">
        </div>
    </div>

    <?php
    $selectContent = '';
    foreach ($available_properties as $elementSetName => $elements)
    {
        $selectContent .= '<optgroup label="' . html_escape($elementSetName) . '">';
        foreach ($elements as $elementId => $elementName)
        {
            $selectContent .= '<option value="' . $elementId . '">' . html_escape($elementName) . '</option>';
        }
        $selectContent .= '</optgroup>';
    }
    ?>
    <table class="batchupload-mappings-editor" data-selectcontent="<?php echo html_escape($selectContent); ?>">
        <thead>
            <tr>
                <th><?php echo __("Header"); ?></th>
                <th><?php echo __("Order"); ?></th>
                <th><?php echo __("Relationship"); ?></th>
                <th><?php echo __("HTML?"); ?></th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mappings_array as $i => $mapping): ?>
                <tr class="mapping-row" data-count="<?php echo $i; ?>">
                    <td><input type="text" name="mappings[<?php echo $i; ?>][header]" value="<?php echo html_escape($mapping['header']); ?>"></td>
                    <td><span class="mappings-order-label"><?php echo $mapping['order']; ?></span><input type="hidden" name="mappings[<?php echo $i; ?>][order]" value="<?php echo html_escape($mapping['order']); ?>"></td>
                    <td><select name="mappings[<?php echo $i; ?>][property]">
                        <?php foreach ($available_properties as $elementSetName => $elements): ?>
                            <optgroup label="<?php echo html_escape($elementSetName); ?>">
                                <?php foreach ($elements as $elementId => $elementName): ?>
                                    <option value="<?php echo $elementId; ?>"<?php echo ($elementId == $mapping['property']) ? ' selected="selected"' : ''; ?>><?php echo html_escape($elementName); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select></td>
                    <td><input type="hidden" value="0" name="mappings[<?php echo $i; ?>][html]"><input type="checkbox" value="1" name="mappings[<?php echo $i; ?>][html]"<?php echo $mapping['html'] ? ' checked="checked"' : '' ?>></td>
                    <td>
                        <?php if (isset($mapping['_id'])): ?>
                            <input type="hidden" name="mappings[<?php echo $i; ?>][_id]" value="<?php echo $mapping['_id']; ?>">
                        <?php endif; ?>
                        <button type="button" class="small green button mappings-add-button">+</button><button type="button" class="small blue button mappings-up-button">↑</button><button type="button" class="small blue button mappings-down-button">↓</button><button type="button" class="small red button mappings-delete-button">×</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php

class RadioClass extends FormItemMultiple{
    public function generate($compound,$property,$item_id,$index_id) {
        $compound_id = $compound['id'];
        $property_id = $property['id'];
        if ($property_id == 0) {
            $property = $compound;
        }
        $autoValidate = false;
        $hasDefaultValue = (isset($property['metas']['socialdb_property_default_value']) && $property['metas']['socialdb_property_default_value']!='') ? $property['metas']['socialdb_property_default_value'] : false;
        $values = ($this->value && is_array($this->getValues($this->value[$index_id][$property_id]))) ? $this->getValues($this->value[$index_id][$property_id]) : false;
        $values = (!$values && $hasDefaultValue) ? [$hasDefaultValue] : $values;
        $this->isRequired = ($property['metas'] && $property['metas']['socialdb_property_required'] && $property['metas']['socialdb_property_required'] != 'false') ? true : false;
        $isView = $this->viewValue($property,$values,'term');
        if($isView){
            return true;
        }
        ?>
        <?php if ($this->isRequired): ?>
        <div class="form-group"
             id="validation-<?php echo $compound['id'] ?>-<?php echo $property_id ?>-<?php echo $index_id; ?>"
             style="border-bottom:none;">
                <?php endif; ?>
                <?php if($property['has_children'] && is_array($property['has_children'])): ?>
                    <?php foreach ($property['has_children'] as $child):
                        $is_selected = ($values && in_array($child->term_id,$values)) ? 'selected' : '';
                        if(!$autoValidate)
                            $autoValidate = ($values && in_array($child->term_id,$values)) ? true : false;
                        ?>
                        <input type="radio"
                               <?php echo $is_selected ?>
                               name="radio-field-<?php echo $compound_id ?>-<?php echo $property_id ?>-<?php echo $index_id; ?>[]"
                               value="<?php echo $child->term_id ?>">&nbsp;<?php echo $child->name ?>
                    <?php endforeach; ?>
                <?php endif;
        if ($this->isRequired): ?>
                <span style="display: none;" class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>
                <span style="display: none;" class="glyphicon glyphicon-ok form-control-feedback" aria-hidden="true"></span>
                <span id="input2Status" class="sr-only">(status)</span>
                <input type="hidden"
                       <?php if($property_id !== 0): ?>
                       compound="<?php echo $compound['id'] ?>"
                       <?php endif; ?>
                      property="<?php echo $property['id'] ?>"
                       class="validate-class validate-compound-<?php echo $compound['id'] ?>"
                       value="<?php echo ($autoValidate) ? 'true' : 'false' ?>">
        </div>
        <?php elseif($property_id !== 0): ?>
        <input  type="hidden"
                compound="<?php echo $compound['id'] ?>"
                property="<?php echo $property['id'] ?>"
                id="validation-<?php echo $compound['id'] ?>-<?php echo $property_id ?>-<?php echo $index_id; ?>"
                class="compound-one-field-should-be-filled-<?php echo $compound['id'] ?>"
                value="<?php echo ($autoValidate) ? 'true' : 'false' ?>">
        <?php endif;
        $this->initScriptsRadioBoxClass($compound_id, $property_id, $item_id, $index_id);
        if(!$this->value && $hasDefaultValue): ?>
        <script>
            $('input[name="radio-field-<?php echo $compound_id ?>-<?php echo $property_id ?>-<?php echo $index_id; ?>[]"]').trigger('change');
        </script>
        <?php
        endif;
    }

    /**
     *
     * @param type $property
     * @param type $item_id
     * @param type $index
     */
    public function initScriptsRadioBoxClass($compound_id,$property_id, $item_id, $index_id) {
        ?>
        <script>
            $('input[name="radio-field-<?php echo $compound_id ?>-<?php echo $property_id ?>-<?php echo $index_id; ?>[]"]').change(function(){
                Hook.call('appendCategoryMetadata',[$(this).val(), <?php echo $item_id ?>, '#appendCategoryMetadata_<?php echo $compound_id; ?>_0_0']);
                $.ajax({
                    url: $('#src').val() + '/controllers/object/form_item_controller.php',
                    type: 'POST',
                    data: {
                        operation: 'saveValue',
                        type:'term',
                        value: $(this).val(),
                        item_id: $('#item-multiple-selected').val().trim(),
                        compound_id:'<?php echo $compound_id ?>',
                        property_children_id: '<?php echo $property_id ?>',
                        index: <?php echo $index_id ?>,
                        indexCoumpound: 0
                    }
                });
                <?php if($this->isRequired):  ?>
                    Hook.call('validateFieldsMetadataText',[$(this).val(),'<?php echo $compound_id ?>','<?php echo $property_id ?>','<?php echo $index_id ?>']);
                    //validateFieldsMetadataText($(this).val(),'<?php echo $compound_id ?>','<?php echo $property_id ?>','<?php echo $index_id ?>')
                <?php endif; ?>
            });
            
            Hook.register(
                'get_single_item_value',
                function ( args ) {
                    $.ajax({
                        url: $('#src').val() + '/controllers/object/form_item_controller.php',
                        type: 'POST',
                        data: {
                            operation: 'getDataValue',
                            compound_id:'<?php echo $compound_id ?>',
                            property_children_id: '<?php echo $property_id ?>',
                            index: <?php echo $index_id ?>,
                            item_id:args[0]
                        }
                    }).done(function (result) {
                        var json = JSON.parse(result);
                        if(json.value){
                            $.each($('input[name="radio-field-<?php echo $compound_id ?>-<?php echo $property_id ?>-<?php echo $index_id; ?>[]"]'),function(index,value){
                                if(json.value.indexOf($(value).val())>=0){
                                    $(value).attr('checked','checked');
                                }
                            });
                        }
                    });
            });
            
            Hook.register(
                'get_multiple_item_value',
                function ( args ) {
                    $.each($('input[name="radio-field-<?php echo $compound_id ?>-<?php echo $property_id ?>-<?php echo $index_id; ?>[]"]'),function(index,value){
                        $(value).removeAttr('checked');
                    });
            });
        </script>
        <?php
    }
}

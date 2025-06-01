<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo form_open_multipart(admin_url('custom_estimation/products/product' . (isset($product) ? '/' . $product->id : '')), ['id' => 'product-form']); ?>
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo $title; ?>
                        </h4>
                        <hr class="hr-panel-heading" />

                        <?php $value = (isset($product) ? $product->name : ''); ?>
                        <?php echo render_input('name', 'custom_product_name', $value, 'text', ['required' => true]); ?>

                        <?php
                        $selected_category = (isset($product) ? $product->category_id : '');
                        echo render_select('category_id', $categories, ['id', 'name'], 'custom_product_category', $selected_category, [], [], '', '', true); // Allow deselect
                        ?>

                        <?php $value = (isset($product) ? $product->description : ''); ?>
                        <?php echo render_textarea('description', 'custom_product_description', $value, ['rows' => 3]); ?>

                        <?php $value = (isset($product) ? $product->long_description : ''); ?>
                        <?php echo render_textarea('long_description', 'custom_product_long_description', $value, ['rows' => 6, 'class' => 'tinymce']); ?>
                        
                        <hr/>
                        <div class="row">
                            <div class="col-md-6">
                                <?php $value = (isset($product) ? $product->unit_price : ''); ?>
                                <?php echo render_input('unit_price', 'custom_product_unit_price', $value, 'number', ['step' => 'any', 'required' => true]); ?>
                            </div>
                            <div class="col-md-6">
                                <?php $value = (isset($product) ? $product->unit : ''); ?>
                                <?php echo render_input('unit', 'custom_product_unit', $value); ?>
                            </div>
                        </div>
                         <div class="row">
                            <div class="col-md-6">
                                <?php $value = (isset($product) ? $product->material : ''); ?>
                                <?php echo render_input('material', 'custom_product_material', $value); ?>
                            </div>
                            <div class="col-md-6">
                                <?php $value = (isset($product) ? $product->product_range : ''); ?>
                                <?php echo render_input('product_range', 'custom_product_range', $value); ?>
                            </div>
                        </div>

                        <?php
                        $formulas = [
                            ['id' => 'nos', 'name' => _l('custom_product_formula_nos')],
                            ['id' => 'sft', 'name' => _l('custom_product_formula_sft')],
                            ['id' => 'rft', 'name' => _l('custom_product_formula_rft')],
                            // Add other formulas as needed
                        ];
                        $selected_formula = (isset($product) ? $product->formula : 'nos');
                        echo render_select('formula', $formulas, ['id', 'name'], 'custom_product_formula', $selected_formula, [], [], 'mtop15');
                        ?>

                        <h5 class="mtop15"><?php echo _l('custom_product_dimensions_ft'); ?></h5>
                        <div class="row">
                            <div class="col-md-4">
                                <?php $value = (isset($product) ? $product->dimensions_length : ''); ?>
                                <?php echo render_input('dimensions_length', 'custom_product_length_ft', $value, 'number', ['step' => 'any']); ?>
                            </div>
                            <div class="col-md-4">
                                <?php $value = (isset($product) ? $product->dimensions_width : ''); ?>
                                <?php echo render_input('dimensions_width', 'custom_product_width_ft', $value, 'number', ['step' => 'any']); ?>
                            </div>
                            <div class="col-md-4">
                                <?php $value = (isset($product) ? $product->dimensions_height : ''); ?>
                                <?php echo render_input('dimensions_height', 'custom_product_height_ft', $value, 'number', ['step' => 'any']); ?>
                            </div>
                        </div>
                        
                        <hr/>
                        <div class="form-group">
                            <label for="product_image" class="control-label"><?php echo _l('custom_product_image'); ?></label>
                            <input type="file" name="product_image" class="form-control" id="product_image">
                            <?php if (isset($product) && $product->image): ?>
                                <div class="mtop10">
                                    <img src="<?php echo base_url('uploads/custom_estimation_module/products/' . $product->image); ?>"
                                         alt="<?php echo htmlspecialchars($product->name); ?>" class="img img-responsive img-thumbnail" style="max-width:150px;">
                                    <div class="checkbox checkbox-danger">
                                        <input type="checkbox" name="remove_image" id="remove_image" value="1">
                                        <label for="remove_image"><?php echo _l('remove_image'); ?></label>
                                    </div>
                                    <input type="hidden" name="old_image" value="<?php echo $product->image; ?>">
                                </div>
                            <?php endif; ?>
                        </div>


                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary" data-loading-text="<?php echo _l('wait_text'); ?>" data-form="#product-form">
                                <?php echo _l('submit'); ?>
                            </button>
                            <a href="<?php echo admin_url('custom_estimation/products'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        appValidateForm($('#product-form'), {
            name: 'required',
            unit_price: {
                required: true,
                number: true
            },
            category_id: 'required'
        });
        // Init tinymce for long description
        // Ensure tinymce is loaded by Perfex on this page or enqueue it
        if(typeof(init_editor) == 'function'){ // Perfex's way of initializing tinymce
            init_editor('.tinymce');
        } else if (typeof tinymce !== 'undefined') { // Standard tinymce check
             tinymce.init({
                selector: '.tinymce',
                height: 250,
                menubar: false,
                plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste code help wordcount'
                ],
                toolbar: 'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help'
            });
        }
    });
</script>
</body>
</html>
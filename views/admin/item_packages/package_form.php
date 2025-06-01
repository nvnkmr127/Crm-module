<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo form_open(admin_url('custom_estimation/item_packages/package' . (isset($package) ? '/' . $package->id : '')), ['id' => 'item-package-form']); ?>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo $title; ?>
                        </h4>
                        <hr class="hr-panel-heading" />

                        <?php $value = (isset($package) ? $package->name : ''); ?>
                        <?php echo render_input('name', 'item_package_name', $value, 'text', ['required' => true]); ?>

                        <?php $value = (isset($package) ? $package->description : ''); ?>
                        <?php echo render_textarea('description', 'item_package_description', $value); ?>

                        <hr class="hr-panel-heading" />
                        <h5 class="font-medium mbot15"><?php echo _l('package_items_heading'); ?></h5>

                        {/* Product Selection to Add to Package */}
                        <div class="row">
                            <div class="col-md-5">
                                <?php
                                $product_options_for_package = [];
                                if(isset($products) && is_array($products)){ // All available products passed from controller
                                    foreach ($products as $product_item) {
                                        // Ensure $product_item is an array and has the necessary keys
                                        $product_id = isset($product_item['id']) ? $product_item['id'] : null;
                                        $product_name_display = isset($product_item['name']) ? $product_item['name'] : 'Unnamed Product';
                                        $product_price_display = isset($product_item['unit_price']) ? app_format_money($product_item['unit_price'], get_base_currency()) : app_format_money(0, get_base_currency());

                                        if ($product_id) {
                                            $product_options_for_package[] = [
                                                'id' => $product_id,
                                                'name' => $product_name_display . ' (' . $product_price_display . ')',
                                                // Store original data as data attributes for JS
                                                'data-name' => isset($product_item['name']) ? $product_item['name'] : '',
                                                'data-description' => isset($product_item['description']) ? $product_item['description'] : '',
                                                'data-long_description' => isset($product_item['long_description']) ? $product_item['long_description'] : '',
                                                'data-unit_price' => isset($product_item['unit_price']) ? $product_item['unit_price'] : '0.00'
                                            ];
                                        }
                                    }
                                }
                                echo render_select('package_item_select_product', $product_options_for_package, ['id', 'name'], 'add_item_select_product_label', '', ['data-none-selected-text' => _l('dropdown_non_selected_tex')], ['data-live-search' => 'true'], 'no-margin');
                                ?>
                            </div>
                            <div class="col-md-2">
                                 <input type="number" id="package_item_default_quantity_input" class="form-control" value="1" min="0.01" step="any" placeholder="<?php echo _l('estimate_table_quantity_heading'); ?>">
                            </div>
                            <div class="col-md-2">
                                 <input type="number" id="package_item_default_price_input" class="form-control" value="" min="0" step="any" placeholder="<?php echo _l('package_item_default_unit_price'); ?>">
                                 <small class="text-muted"><?php echo _l('leave_blank_for_product_price'); ?></small>
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="add_product_to_package_btn" class="btn btn-info btn-block">
                                    <i class="fa fa-plus"></i> <?php echo _l('add_item'); ?>
                                </button>
                            </div>
                        </div>
                        <hr/>


                        {/* Table for Items in the Package */}
                        <div class="table-responsive s_table">
                            <table class="table package-items-table items table-main-package-edit no-mtop">
                                <thead>
                                    <tr>
                                        <th width="5%"></th> <th width="20%"><?php echo _l('custom_product_name'); ?></th>
                                        <th width="30%"><?php echo _l('package_item_default_description'); ?></th>
                                        <th width="10%"><?php echo _l('package_item_default_quantity'); ?></th>
                                        <th width="15%"><?php echo _l('package_item_default_unit_price'); ?></th>
                                        <th width="10%" class="text-center"><?php echo _l('complimentary'); ?></th>
                                        <th width="10%"><i class="fa fa-remove"></i></th>
                                    </tr>
                                </thead>
                                <tbody class="ui-sortable">
                                    <?php
                                    $p_i = 0; // package item index
                                    if (isset($package_items) && count($package_items) > 0) {
                                        foreach ($package_items as $p_item) {
                                            // $p_item should have: product_id, product_name (for display),
                                            // default_description, default_quantity, default_unit_price, is_complimentary, item_order
                                    ?>
                                    <tr class="sortable package-item" data-product-id="<?php echo $p_item['product_id']; ?>">
                                        <td class="dragger"><i class="fa fa-bars" aria-hidden="true"></i></td>
                                        <td>
                                            <?php echo $p_item['product_name']; // Display product name for reference ?>
                                            <input type="hidden" name="items[<?php echo $p_i; ?>][product_id]" value="<?php echo $p_item['product_id']; ?>">
                                            <input type="hidden" name="items[<?php echo $p_i; ?>][item_order]" value="<?php echo isset($p_item['item_order']) ? $p_item['item_order'] : $p_i; ?>" class="item_order">
                                        </td>
                                        <td><textarea name="items[<?php echo $p_i; ?>][default_description]" class="form-control" rows="2" placeholder="<?php echo _l('package_item_default_description_ph'); ?>"><?php echo htmlspecialchars(isset($p_item['description']) ? $p_item['description'] : ''); ?></textarea></td>
                                        <td><input type="number" name="items[<?php echo $p_i; ?>][default_quantity]" class="form-control" value="<?php echo htmlspecialchars(isset($p_item['quantity']) ? $p_item['quantity'] : '1'); ?>" min="0.01" step="any"></td>
                                        <td><input type="number" name="items[<?php echo $p_i; ?>][default_unit_price]" class="form-control" value="<?php echo htmlspecialchars(isset($p_item['unit_price']) ? $p_item['unit_price'] : ''); ?>" min="0" step="any" placeholder="<?php echo _l('leave_blank_for_product_price'); ?>"></td>
                                        <td class="text-center">
                                            <div class="checkbox">
                                                <input type="checkbox" name="items[<?php echo $p_i; ?>][is_complimentary]" value="1" id="pkg_complimentary_<?php echo $p_i; ?>" <?php if(isset($p_item['is_complimentary']) && $p_item['is_complimentary'] == 1) echo 'checked'; ?>>
                                                <label for="pkg_complimentary_<?php echo $p_i; ?>"></label>
                                            </div>
                                        </td>
                                        <td><a href="#" class="btn btn-danger btn-xs" onclick="remove_package_item_row(this); return false;"><i class="fa fa-times"></i></a></td>
                                    </tr>
                                    <?php
                                            $p_i++;
                                        }
                                    } else {
                                    ?>
                                        <tr class="package-item-placeholder">
                                            <td colspan="7" class="text-center">
                                                <p><?php echo _l('no_items_in_package'); ?></p>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="removed-package-items"></div> {/* For tracking removed existing items during edit */}


                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary" data-loading-text="<?php echo _l('wait_text'); ?>" data-form="#item-package-form">
                                <?php echo _l('submit'); ?>
                            </button>
                            <a href="<?php echo admin_url('custom_estimation/item_packages'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        // Initialize form validation
        appValidateForm($('#item-package-form'), {
            name: 'required'
            // You might add custom validation rules for the items array if needed
        });

        // Make package items sortable (requires jQuery UI)
        if (typeof($('.package-items-table tbody').sortable) == 'function') {
            $('.package-items-table tbody').sortable({
                helper: 'clone',
                axis: 'y',
                handle: '.dragger',
                update: function(event, ui) {
                    // Update item_order hidden inputs after sorting
                    $('.package-items-table tbody tr.package-item').each(function(index) {
                        $(this).find('input.item_order').val(index);
                    });
                }
            });
        }

        // Add product to package items table
        $('#add_product_to_package_btn').on('click', function() {
            var selected_product_option = $('#package_item_select_product').find('option:selected');
            var product_id = $('#package_item_select_product').val();
            var default_quantity = $('#package_item_default_quantity_input').val();
            var default_price = $('#package_item_default_price_input').val(); // Can be empty

            if (!product_id) {
                alert_float('warning', "<?php echo _l('please_select_a_product'); ?>");
                return;
            }
            if (!default_quantity || parseFloat(default_quantity) <= 0) {
                alert_float('warning', "<?php echo _l('please_enter_valid_quantity'); ?>");
                return;
            }

            var product_exists = false;
            $('.package-items-table tbody tr.package-item').each(function() {
                if ($(this).data('product-id') == product_id) {
                    product_exists = true;
                    return false; 
                }
            });

            if (product_exists) {
                alert_float('info', "<?php echo _l('product_already_in_package'); ?>");
                return;
            }

            var product_name = selected_product_option.data('name') || selected_product_option.text().split('(')[0].trim();
            var product_master_description = selected_product_option.data('description') || '';
            // Use product_master_description as the initial default_description for the package item
            var default_item_description = product_master_description; 
            var product_master_price = selected_product_option.data('unit_price') || '';
            var unit_price_for_package = (default_price !== '') ? default_price : product_master_price;


            var p_i = $('.package-items-table tbody tr.package-item').length; 

            var new_row_html = `
                <tr class="sortable package-item" data-product-id="${product_id}">
                    <td class="dragger"><i class="fa fa-bars" aria-hidden="true"></i></td>
                    <td>
                        ${product_name}
                        <input type="hidden" name="items[${p_i}][product_id]" value="${product_id}">
                        <input type="hidden" name="items[${p_i}][item_order]" value="${p_i}" class="item_order">
                    </td>
                    <td><textarea name="items[${p_i}][default_description]" class="form-control" rows="2" placeholder="<?php echo _l('package_item_default_description_ph'); ?>">${default_item_description}</textarea></td>
                    <td><input type="number" name="items[${p_i}][default_quantity]" class="form-control" value="${default_quantity}" min="0.01" step="any"></td>
                    <td><input type="number" name="items[${p_i}][default_unit_price]" class="form-control" value="${unit_price_for_package}" min="0" step="any" placeholder="<?php echo _l('leave_blank_for_product_price'); ?>"></td>
                    <td class="text-center">
                        <div class="checkbox">
                            <input type="checkbox" name="items[${p_i}][is_complimentary]" value="1" id="pkg_complimentary_${p_i}">
                            <label for="pkg_complimentary_${p_i}"></label>
                        </div>
                    </td>
                    <td><a href="#" class="btn btn-danger btn-xs" onclick="remove_package_item_row(this); return false;"><i class="fa fa-times"></i></a></td>
                </tr>
            `;

            $('.package-items-table tbody .package-item-placeholder').remove(); 
            $('.package-items-table tbody').append(new_row_html);

            if($.fn.selectpicker) {
                 $('#package_item_select_product').selectpicker('val', '');
            } else {
                 $('#package_item_select_product').val('');
            }
            $('#package_item_default_quantity_input').val('1');
            $('#package_item_default_price_input').val('');
        });

        // Remove package item row (made global for onclick)
        window.remove_package_item_row = function(link) {
            var $row = $(link).closest('tr.package-item');
            $row.remove();
            if ($('.package-items-table tbody tr.package-item').length === 0) {
                 $('.package-items-table tbody').append('<tr class="package-item-placeholder"><td colspan="7" class="text-center"><p><?php echo _l("no_items_in_package"); ?></p></td></tr>');
            }
            // Re-index item_order after removal
            $('.package-items-table tbody tr.package-item').each(function(index) {
                $(this).find('input.item_order').val(index);
            });
        }

        // Initialize select pickers
        if($.fn.selectpicker) {
            $('#package_item_select_product').selectpicker();
        }
    });
</script>
</body>
</html>

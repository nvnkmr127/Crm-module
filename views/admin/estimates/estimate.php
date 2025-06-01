<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo form_open(admin_url('custom_estimation/estimates/estimate' . (isset($estimate) ? '/' . $estimate->id : '')), ['id' => 'custom-estimate-form']); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo $title; ?>
                            <?php if (isset($estimate) && isset($estimate->hash) && $estimate->hash) { ?>
                                <small class="pull-right" style="margin-left:10px;">
                                    <a href="<?php echo site_url('custom_estimation/custom_estimate_public/view/' . $estimate->id . '/' . $estimate->hash); ?>" target="_blank">
                                        <?php echo _l('view_estimate_url'); ?> (<?php echo _l('custom_estimate_public_view'); ?>)
                                    </a>
                                </small>
                            <?php } ?>
                             <?php if (isset($estimate) && isset($estimate->id) && $estimate->id) { ?>
                                <small class="pull-right">
                                    <a href="<?php echo admin_url('custom_estimation/estimates/pdf/' . $estimate->id); ?>" target="_blank" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" title="<?php echo _l('download_pdf'); ?>" data-placement="bottom">
                                        <i class="fa fa-file-pdf-o"></i>
                                    </a>
                                </small>
                            <?php } ?>
                        </h4>
                        <hr class="hr-panel-heading" />

                        <div class="row">
                            <div class="col-md-6">
                                <?php /* Subject field removed */ ?>
                                <?php // $value = (isset($estimate) ? $estimate->subject : ''); ?>
                                <?php // echo render_input('subject', 'custom_estimate_subject', $value); ?>

                                <?php
                                $selected_lead = (isset($estimate) ? $estimate->lead_id : '');
                                $leads_options = (isset($leads) && is_array($leads)) ? $leads : [];
                                // Add 'required' => true for client-side indication, server-side validation is key
                                echo render_select('lead_id', $leads_options, ['id', ['name', 'company']], 'custom_estimate_lead', $selected_lead, ['required' => true], [], '', 'ajax-search');
                                ?>

                                <?php
                                $selected_status = (isset($estimate) ? $estimate->status : 'draft');
                                $status_options = (isset($statuses) && is_array($statuses)) ? $statuses : [];
                                echo render_select('status', $status_options, ['id', 'name'], 'custom_estimate_status', $selected_status);
                                ?>
                            </div>
                            <div class="col-md-6">
                                <?php $current_date = date('Y-m-d'); $value = (isset($estimate) && isset($estimate->datecreated) ? _d($estimate->datecreated) : _d($current_date)); ?>
                                <?php echo render_date_input('datecreated_display', 'custom_estimate_date', $value, ['disabled' => true]); ?>
                                <input type="hidden" name="datecreated" value="<?php echo (isset($estimate) && isset($estimate->datecreated) ? $estimate->datecreated : date('Y-m-d H:i:s')); ?>">


                                <?php $value = (isset($estimate) && isset($estimate->valid_until) ? _d($estimate->valid_until) : ''); ?>
                                <?php // Add 'required' => true for client-side indication, server-side validation is key
                                echo render_date_input('valid_until', 'custom_estimate_valid_until', $value, ['required' => true]); 
                                ?>
                                
                                <?php
                                $current_estimate_id = isset($id) ? $id : ''; 
                                $selected_pdf_template = (isset($estimate) ? $estimate->pdf_template_slug : ''); 
                                if(empty($current_estimate_id) && empty($selected_pdf_template) && isset($pdf_templates) && is_array($pdf_templates)){ 
                                    foreach($pdf_templates as $tpl){
                                        if(isset($tpl['is_default']) && $tpl['is_default'] == 1){
                                            $selected_pdf_template = $tpl['slug']; 
                                            break;
                                        }
                                    }
                                }
                                $pdf_template_options = (isset($pdf_templates) && is_array($pdf_templates)) ? $pdf_templates : [];
                                echo render_select('pdf_template_slug', $pdf_template_options, ['slug', 'name'], 'pdf_template_name', $selected_pdf_template, [], [], '', '', false);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="no-mtop mbot15"><?php echo _l('custom_estimate_items_heading'); ?></h4>
                            </div>
                            <div class="col-md-5 mbot10">
                                <?php
                                $package_options_select = []; 
                                if(isset($item_packages) && is_array($item_packages)){ 
                                    foreach ($item_packages as $package_item_select) { 
                                        $package_options_select[] = ['id' => isset($package_item_select['id']) ? $package_item_select['id'] : null, 'name' => isset($package_item_select['name']) ? $package_item_select['name'] : 'Unnamed Package'];
                                    }
                                }
                                echo render_select('item_package_select', $package_options_select, ['id', 'name'], 'select_item_package_label', '', ['data-none-selected-text' => _l('dropdown_non_selected_tex')], ['data-live-search' => 'true'], 'no-margin');
                                ?>
                            </div>
                            <div class="col-md-2 mbot10">
                                <button type="button" id="load_package_items_btn" class="btn btn-default btn-block"><?php echo _l('load_package_items_button'); ?></button>
                            </div>
                            <div class="col-md-5 mbot10">
                                <?php
                                $product_options_for_select = []; 
                                if(isset($products) && is_array($products)){ 
                                    foreach ($products as $product_item_select) { 
                                        $product_options_for_select[] = [
                                            'id' => $product_item_select['id'],
                                            'name' => $product_item_select['name'] . ' (' . app_format_money($product_item_select['unit_price'], get_base_currency()) . ')',
                                            'data-description' => $product_item_select['description'] ?? '',
                                            'data-long_description' => $product_item_select['long_description'] ?? '',
                                            'data-unit_price' => $product_item_select['unit_price'] ?? '0.00',
                                            'data-unit' => $product_item_select['unit'] ?? '', 
                                            'data-formula' => $product_item_select['formula'] ?? 'nos', 
                                            'data-dimension-l' => $product_item_select['dimensions_length'] ?? '',
                                            'data-dimension-w' => $product_item_select['dimensions_width'] ?? '',
                                            'data-dimension-h' => $product_item_select['dimensions_height'] ?? '',
                                            'data-material' => $product_item_select['material'] ?? '',      
                                            'data-range' => $product_item_select['product_range'] ?? '', 
                                        ];
                                    }
                                }
                                echo render_select('item_select_product', $product_options_for_select, ['id', 'name'], 'add_item_select_product_label', '', ['data-none-selected-text' => _l('dropdown_non_selected_tex')], ['data-live-search' => 'true'], 'no-margin');
                                ?>
                            </div>
                             <div class="col-md-2 mbot10 pull-right"> 
                                <button type="button" id="add_new_item_to_custom_estimate_btn" class="btn btn-info btn-block"><i class="fa fa-plus"></i> <?php echo _l('add_item'); ?></button>
                            </div>
                            <div class="clearfix"></div> 
                            <hr class="mtop0 mbot10" />
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table estimate-items-table items table-main-estimate-edit no-mtop">
                                        <thead>
                                            <tr>
                                                <th width="3%"></th> 
                                                <th width="15%"><?php echo _l('estimate_table_item_heading'); ?></th>
                                                <th width="20%"><?php echo _l('estimate_table_item_description'); ?></th>
                                                <th width="25%"><?php echo _l('package_item_dimensions'); ?>, Mat, Range, Formula</th> 
                                                <th width="8%" class="qty"><?php echo _l('estimate_table_quantity_heading'); ?></th>
                                                <th width="8%"><?php echo _l('custom_product_unit'); ?></th>
                                                <th width="10%" class="rate"><?php echo _l('estimate_table_rate_heading'); ?></th>
                                                <th width="10%" class="discount_percent"><?php echo _l('estimate_table_discount_heading'); ?> (%)</th>
                                                <th width="8%" class="text-right item_amount"><?php echo _l('estimate_table_amount_heading'); ?></th>
                                                <th width="3%" align="center"><i class="fa fa-cog"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody class="ui-sortable">
                                            <?php
                                            $i = 0;
                                            $items_indicator = 'items';
                                            if (isset($estimate) && isset($estimate->items) && count($estimate->items) > 0) { 
                                                foreach ($estimate->items as $item_obj) { 
                                                    $item = (array) $item_obj; 
                                            ?>
                                                    <tr class="main" data-item-id="<?php echo isset($item['id']) ? $item['id'] : 'new'.uniqid(); ?>">
                                                        <td class="dragger"><i class="fa fa-bars"></i></td>
                                                        <td>
                                                            <input type="text" name="<?php echo $items_indicator; ?>[<?php echo $i; ?>][description]" class="form-control item-description" value="<?php echo htmlspecialchars(isset($item['description']) ? $item['description'] : ''); ?>">
                                                            <input type="hidden" name="<?php echo $items_indicator; ?>[<?php echo $i; ?>][product_id]" value="<?php echo isset($item['product_id']) ? $item['product_id'] : ''; ?>">
                                                        </td>
                                                        <td><textarea name="<?php echo $items_indicator; ?>[<?php echo $i; ?>][long_description]" class="form-control item-long-description" rows="2"><?php echo htmlspecialchars(isset($item['long_description']) ? $item['long_description'] : ''); ?></textarea></td>
                                                        <td> 
                                                            <div class="row">
                                                                <div class="col-xs-4"><input type="number" name="<?php echo $items_indicator; ?>[<?php echo $i; ?>][custom_dim_length]" class="form-control item-dimension item-dim-length" value="<?php echo htmlspecialchars(isset($item['custom_dim_length']) ? $item['custom_dim_length'] : ''); ?>" placeholder="<?php echo _l('package_item_length'); ?>" step="any"></div>
                                                                <div class="col-xs-4"><input type="number" name="<?php echo $items_indicator; ?>[<?php echo $i; ?>][custom_dim_width]" class="form-control item-dimension item-dim-width" value="<?php echo htmlspecialchars(isset($item['custom_dim_width']) ? $item['custom_dim_width'] : ''); ?>" placeholder="<?php echo _l('package_item_width'); ?>" step="any"></div>
                                                                <div class="col-xs-4"><input type="number" name="<?php echo $items_indicator; ?>[<?php echo $i; ?>][custom_dim_height]" class="form-control item-dimension item-dim-height" value="<?php echo htmlspecialchars(isset($item['custom_dim_height']) ? $item['custom_dim_height'] : ''); ?>" placeholder="<?php echo _l('package_item_height'); ?>" step="any"></div>
                                                            </div>
                                                            <input type="text" name="<?php echo $items_indicator; ?>[<?php echo $i; ?>][formula]" class="form-control item-formula mtop5" value="<?php echo htmlspecialchars(isset($item['formula']) ? $item['formula'] : 'nos'); ?>" placeholder="<?php echo _l('custom_product_formula'); ?>">
                                                            <input type="text" name="<?php echo $items_indicator; ?>[<?php echo $i; ?>][material]" class="form-control item-material mtop5" value="<?php echo htmlspecialchars(isset($item['material']) ? $item['material'] : ''); ?>" placeholder="<?php echo _l('custom_product_material'); ?>">
                                                            <input type="text" name="<?php echo $items_indicator; ?>[<?php echo $i; ?>][range]" class="form-control item-range mtop5" value="<?php echo htmlspecialchars(isset($item['range']) ? $item['range'] : ''); ?>" placeholder="<?php echo _l('custom_product_range'); ?>">
                                                        </td>
                                                        <td><input type="number" name="<?php echo $items_indicator; ?>[<?php echo $i; ?>][quantity]" class="form-control item-quantity" value="<?php echo isset($item['quantity']) ? $item['quantity'] : '1'; ?>" min="0" step="any"></td>
                                                        <td><input type="text" name="<?php echo $items_indicator; ?>[<?php echo $i; ?>][unit]" class="form-control item-unit" value="<?php echo isset($item['unit']) ? htmlspecialchars($item['unit']) : ''; ?>"></td>
                                                        <td><input type="number" name="<?php echo $items_indicator; ?>[<?php echo $i; ?>][unit_price]" class="form-control item-unit-price" value="<?php echo isset($item['unit_price']) ? $item['unit_price'] : '0.00'; ?>" min="0" step="any"></td>
                                                        <td><input type="number" name="<?php echo $items_indicator; ?>[<?php echo $i; ?>][discount_percentage]" class="form-control item-discount-percentage" value="<?php echo isset($item['discount_percentage']) ? $item['discount_percentage'] : '0'; ?>" min="0" max="100" step="any"></td>
                                                        <td class="item_amount_display text-right">
                                                            <?php /* Calculated by JS */ ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="#" class="btn btn-danger btn-xs pull-left" onclick="remove_estimate_item(this); return false;"><i class="fa fa-times"></i></a>
                                                            <div class="checkbox" style="margin-left: 35px; margin-top: 5px;">
                                                                <input type="checkbox" id="complimentary_<?php echo $i; ?>" name="<?php echo $items_indicator; ?>[<?php echo $i; ?>][is_complimentary]" value="1" <?php if(isset($item['is_complimentary']) && $item['is_complimentary'] == 1){echo 'checked';} ?>>
                                                                <label for="complimentary_<?php echo $i; ?>"><?php echo _l('complimentary'); ?></label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                            <?php
                                                    $i++;
                                                }
                                            } else {
                                            ?>
                                                <tr class="main item-placeholder"><td colspan="10" class="text-center"><p><?php echo _l('no_items_in_custom_estimate'); ?></p></td></tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-8 col-md-offset-4">
                                    <table class="table text-right"><tbody><tr id="subtotal"><td><span class="bold"><?php echo _l('estimate_subtotal'); ?> :</span></td><td class="subtotal"><?php echo app_format_money((isset($estimate) ? $estimate->subtotal : 0), get_base_currency()); ?></td></tr><tr id="discount_area"><td><div class="row"><div class="col-md-7"><span class="bold"><?php echo _l('estimate_discount'); ?></span></div><div class="col-md-5"><div class="input-group"><input type="number" value="<?php echo (isset($estimate) && isset($estimate->total_discount_percentage) ? $estimate->total_discount_percentage : 0); ?>" class="form-control pull-left input-discount-percent" min="0" max="100" name="total_discount_percentage" step="any"><div class="input-group-addon"><div class="dropdown"><a class="dropdown-toggle" id="discount_type_dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" href="#"><span class="discount-type-selected"><?php echo (isset($estimate) && isset($estimate->total_discount_amount) && $estimate->total_discount_amount > 0 && (!isset($estimate->total_discount_percentage) || $estimate->total_discount_percentage == 0) ? _l('discount_fixed_amount') : '%'); ?></span><span class="caret"></span></a><ul class="dropdown-menu" id="discount_type_actions" aria-labelledby="discount_type_dropdown"><li><a href="#" class="discount-type" data-type="percentage">%</a></li><li><a href="#" class="discount-type" data-type="fixed_amount"><?php echo _l('discount_fixed_amount'); ?></a></li></ul></div></div></div><input type="hidden" name="discount_type" value="<?php echo (isset($estimate) && isset($estimate->total_discount_amount) && $estimate->total_discount_amount > 0 && (!isset($estimate->total_discount_percentage) || $estimate->total_discount_percentage == 0) ? 'fixed_amount' : 'percentage'); ?>"><input type="number" name="total_discount_amount" class="form-control <?php echo (isset($estimate) && isset($estimate->total_discount_amount) && $estimate->total_discount_amount > 0 && (!isset($estimate->total_discount_percentage) || $estimate->total_discount_percentage == 0) ? '' : 'hide'); ?>" value="<?php echo (isset($estimate) && isset($estimate->total_discount_amount) ? $estimate->total_discount_amount : 0); ?>" step="any"></div></div></td><td class="discount-total"><?php /* Calculated by JS */ ?></td></tr><tr><td><span class="bold"><?php echo _l('estimate_total'); ?> :</span></td><td class="total"><?php echo app_format_money((isset($estimate) ? $estimate->total : 0), get_base_currency()); ?></td></tr></tbody></table>
                                </div>
                                <div id="removed-items"></div>
                            </div>
                        </div>
                        <hr />
                        <?php $value = (isset($estimate) ? $estimate->notes : ''); ?>
                        <?php echo render_textarea('notes', 'custom_estimate_notes', $value, [], [], 'mtop15'); ?>
                        <?php $value = (isset($estimate) ? $estimate->terms_and_conditions : ''); ?>
                        <?php echo render_textarea('terms_and_conditions', 'custom_estimate_terms_and_conditions', $value, [], [], 'mtop15'); ?>
                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary" data-loading-text="<?php echo _l('wait_text'); ?>" data-form="#custom-estimate-form"><?php echo _l('save_custom_estimate'); ?></button>
                            <a href="<?php echo admin_url('custom_estimation/estimates'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>
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
    $(function(){
        if($.fn.selectpicker) {
             $('#item_select_product, #item_package_select, #status, #lead_id, select[name="pdf_template_slug"]').selectpicker('refresh');
        }
        var initial_discount_type_script = $('input[name="discount_type"]').val();
        if (initial_discount_type_script === 'percentage') {
            $('input[name="total_discount_amount"]').addClass('hide');
            $('input[name="total_discount_percentage"]').removeClass('hide');
        } else { 
            $('input[name="total_discount_percentage"]').addClass('hide');
            $('input[name="total_discount_amount"]').removeClass('hide');
        }

        if (typeof calculate_custom_estimate_totals === 'function' && $('.estimate-items-table tbody tr.main').not('.item-placeholder').length > 0) {
            calculate_custom_estimate_totals(); 
        }
    });
</script>
</body>
</html>
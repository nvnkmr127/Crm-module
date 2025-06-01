// This code should be placed in your custom_estimation.js file
// or within <script> tags in estimate.php

$(function() {
    // Ensure this code only runs on the custom estimate add/edit page
    if ($('#custom-estimate-form').length === 0) {
        return;
    }

    var estimate_items_table_body = $('.estimate-items-table tbody'); 

    // --- Helper Functions ---
    function _l_js(str_key, ...args) {
        if (typeof window._l === 'function') {
            try {
                return window._l(str_key, ...args);
            } catch (e) {
                console.warn('Error calling window._l for key:', str_key, e);
                let s = str_key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                if (args.length > 0) { args.forEach((arg, index) => { s = s.replace(new RegExp(`\\{${index}\\}|%s|%d`, 'g'), arg); }); }
                return s;
            }
        } else {
            // console.warn('_l function not defined. Using basic string transformation for:', str_key);
            let s = str_key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            if (args.length > 0) { args.forEach((arg, index) => { s = s.replace(new RegExp(`\\{${index}\\}|%s|%d`, 'g'), arg); }); }
            return s;
        }
    }

    function app_decimal_places() {
        return (typeof window.CURRENCY_INFO !== 'undefined' && typeof window.CURRENCY_INFO.decimal_places !== 'undefined') 
               ? window.CURRENCY_INFO.decimal_places 
               : 2; 
    }

    function format_money(total, currency_symbol_param) {
        var currency_symbol = currency_symbol_param || (typeof window.CURRENCY_INFO !== 'undefined' ? window.CURRENCY_INFO.symbol : '$');
        var decimal_separator = (typeof window.CURRENCY_INFO !== 'undefined' ? window.CURRENCY_INFO.decimal_separator : '.');
        var thousand_separator = (typeof window.CURRENCY_INFO !== 'undefined' ? window.CURRENCY_INFO.thousand_separator : ',');
        var currency_placement = (typeof window.CURRENCY_INFO !== 'undefined' ? window.CURRENCY_INFO.placement : 'before');
        
        total = parseFloat(total);
        if (isNaN(total)) { total = 0; }

        if (typeof window.accounting !== 'undefined' && typeof window.currencyData !== 'undefined') { // Perfex's currencyData
            return accounting.formatMoney(total, {
                symbol: currency_symbol, decimal: currencyData.decimal_separator, thousand: currencyData.thousand_separator,
                precision: app_decimal_places(), format: currencyData.placement === 'after' ? '%v %s' : '%s%v',
            });
        }
        var num_decimal_places = app_decimal_places();
        var formatted_total = total.toFixed(num_decimal_places);
        if (decimal_separator !== '.') { formatted_total = formatted_total.replace('.', decimal_separator); }
        var parts = formatted_total.split(decimal_separator);
        parts[0] = parts[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1' + thousand_separator);
        formatted_total = parts.join(decimal_separator);
        return currency_placement === 'after' ? formatted_total + ' ' + currency_symbol : currency_symbol + formatted_total;
    }

    function calculate_item_total($row) {
        var quantity = parseFloat($row.find('input.item-quantity').val()) || 0;
        var unit_price = parseFloat($row.find('input.item-unit-price').val()) || 0;
        var discount_percentage = parseFloat($row.find('input.item-discount-percentage').val()) || 0;
        var item_total = quantity * unit_price;
        if (discount_percentage > 0 && discount_percentage <= 100) {
            item_total -= (item_total * (discount_percentage / 100));
        }
        $row.find('.item_amount_display').text(format_money(item_total));
        return item_total;
    }

    window.calculate_custom_estimate_totals = function() { 
        var subtotal = 0;
        var overall_discount_percentage = parseFloat($('input[name="total_discount_percentage"]').val()) || 0;
        var overall_discount_amount_input = parseFloat($('input[name="total_discount_amount"]').val()) || 0;
        var discount_type = $('input[name="discount_type"]').val();

        estimate_items_table_body.find('tr.main').not('.item-placeholder').each(function() {
            var $row = $(this);
            if (!$row.find('input[name*="[is_complimentary]"]').is(':checked')) {
                 subtotal += calculate_item_total($row);
            } else {
                $row.find('.item_amount_display').text(format_money(0));
            }
        });
        $('.subtotal').text(format_money(subtotal));
        var overall_discount_calculated = 0;
        if (discount_type === 'percentage') {
            if (overall_discount_percentage > 0 && overall_discount_percentage <= 100) {
                overall_discount_calculated = (subtotal * (overall_discount_percentage / 100));
            }
            $('input[name="total_discount_amount"]').val(overall_discount_calculated.toFixed(app_decimal_places())); 
        } else if (discount_type === 'fixed_amount') {
            overall_discount_calculated = overall_discount_amount_input;
             if (subtotal > 0) { // Calculate percentage only if subtotal is not zero
                var perc = (overall_discount_calculated / subtotal) * 100;
                $('input[name="total_discount_percentage"]').val(perc.toFixed(app_decimal_places())); 
             } else {
                $('input[name="total_discount_percentage"]').val(0);
             }
        }
        $('.discount-total').text('-'.concat(format_money(overall_discount_calculated)));
        var total = subtotal - overall_discount_calculated;
        $('.total').text(format_money(total));
    }

    function get_next_item_index() {
        var max_index = -1;
        estimate_items_table_body.find('tr.main').not('.item-placeholder').each(function() {
            var name = $(this).find('input.item-description').attr('name'); // e.g. items[0][description]
            if(name) {
                var match = name.match(/items\[(\d+)\]/);
                if (match && match[1]) {
                    var current_index = parseInt(match[1]);
                    if (current_index > max_index) {
                        max_index = current_index;
                    }
                }
            }
        });
        return max_index + 1;
    }
    
    function htmlspecialchars_js(str) { 
        if (typeof str === 'string') {
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }
        return str === null || typeof str === 'undefined' ? '' : str; 
    }

    window.add_new_item_row = function(data = {}) { 
        var i = get_next_item_index();
        var description = data.description || '';
        var product_id_val = data.product_id || '';
        var long_description = data.long_description || '';
        var quantity = data.quantity || '1'; 
        var unit_price = data.unit_price || '0.00'; 
        var unit = data.unit || ''; 
        var formula = data.formula || 'nos'; 
        var dim_l = data.dimension_l || ''; 
        var dim_w = data.dimension_w || ''; 
        var dim_h = data.dimension_h || ''; 
        var material = data.material || '';
        var range = data.range || ''; 
        var discount_percentage = data.discount_percentage || '0';
        var is_complimentary_checked = (data.is_complimentary == 1 || data.is_complimentary === true) ? 'checked' : '';

        console.log("Data received by add_new_item_row:", data); 
        
        var new_row_html = `
            <tr class="main" data-item-id="${htmlspecialchars_js(data.id || ('new-' + i))}">
                <td class="dragger"><i class="fa fa-bars"></i></td>
                <td>
                    <input type="text" name="items[${i}][description]" class="form-control item-description" value="${htmlspecialchars_js(description)}">
                    <input type="hidden" name="items[${i}][product_id]" value="${htmlspecialchars_js(product_id_val)}">
                </td>
                <td>
                    <textarea name="items[${i}][long_description]" class="form-control item-long-description" rows="2">${htmlspecialchars_js(long_description)}</textarea>
                </td>
                <td>
                    <div class="row">
                        <div class="col-xs-4"><input type="number" name="items[${i}][custom_dim_length]" class="form-control item-dimension item-dim-length" value="${htmlspecialchars_js(dim_l)}" placeholder="${_l_js('package_item_length')}" step="any"></div>
                        <div class="col-xs-4"><input type="number" name="items[${i}][custom_dim_width]" class="form-control item-dimension item-dim-width" value="${htmlspecialchars_js(dim_w)}" placeholder="${_l_js('package_item_width')}" step="any"></div>
                        <div class="col-xs-4"><input type="number" name="items[${i}][custom_dim_height]" class="form-control item-dimension item-dim-height" value="${htmlspecialchars_js(dim_h)}" placeholder="${_l_js('package_item_height')}" step="any"></div>
                    </div>
                    <input type="text" name="items[${i}][formula]" class="form-control item-formula mtop5" value="${htmlspecialchars_js(formula)}" placeholder="${_l_js('custom_product_formula')}">
                    <input type="text" name="items[${i}][material]" class="form-control item-material mtop5" value="${htmlspecialchars_js(material)}" placeholder="${_l_js('custom_product_material')}">
                    <input type="text" name="items[${i}][range]" class="form-control item-range mtop5" value="${htmlspecialchars_js(range)}" placeholder="${_l_js('custom_product_range')}">
                </td>
                <td>
                    <input type="number" name="items[${i}][quantity]" class="form-control item-quantity" value="${htmlspecialchars_js(quantity)}" min="0" step="any">
                </td>
                <td>
                    <input type="text" name="items[${i}][unit]" class="form-control item-unit" value="${htmlspecialchars_js(unit)}">
                </td>
                <td>
                    <input type="number" name="items[${i}][unit_price]" class="form-control item-unit-price" value="${htmlspecialchars_js(unit_price)}" min="0" step="any">
                </td>
                <td>
                    <input type="number" name="items[${i}][discount_percentage]" class="form-control item-discount-percentage" value="${htmlspecialchars_js(discount_percentage)}" min="0" max="100" step="any">
                </td>
                <td class="item_amount_display text-right">
                    ${format_money(0)}
                </td>
                <td class="text-center">
                    <a href="#" class="btn btn-danger btn-xs pull-left" onclick="remove_estimate_item(this); return false;"><i class="fa fa-times"></i></a>
                    <div class="checkbox" style="margin-left: 35px; margin-top: 5px;">
                        <input type="checkbox" id="complimentary_${i}" name="items[${i}][is_complimentary]" value="1" ${is_complimentary_checked}>
                        <label for="complimentary_${i}">${_l_js('complimentary')}</label>
                    </div>
                </td>
            </tr>
        `;
        estimate_items_table_body.find('.item-placeholder').remove();
        estimate_items_table_body.append(new_row_html);
        // Reinitialize selectpickers if any were added in new_row_html (not typical for these inputs)
        // if($.fn.selectpicker) { $(new_row_html).find('.selectpicker').selectpicker('refresh'); }
        calculate_custom_estimate_totals(); 
    }

    window.remove_estimate_item = function(link) { 
        var $row = $(link).closest('tr.main');
        var item_id = $row.data('item-id');
        if (item_id && String(item_id).indexOf('new-') === -1) { 
            $('#removed-items').append('<input type="hidden" name="removed_items[]" value="' + item_id + '">');
        }
        $row.remove();
        if (estimate_items_table_body.find('tr.main').length === 0) {
             estimate_items_table_body.append('<tr class="main item-placeholder"><td colspan="10" class="text-center"><p>' + _l_js('no_items_in_custom_estimate') + '</p></td></tr>');
        }
        calculate_custom_estimate_totals();
    }
    
    $('body').on('click', '#add_new_item_to_custom_estimate_btn', function(e) {
        e.preventDefault();
        var selected_product_option = $('#item_select_product').find('option:selected');
        var product_id = $('#item_select_product').val();

        console.log("DEBUG: Add item button clicked. Product ID:", product_id); 
        if (selected_product_option.length > 0 && product_id && product_id !== '') {
            var product_data = {
                product_id: product_id,
                description: selected_product_option.data('description') || selected_product_option.text().split('(')[0].trim(),
                long_description: selected_product_option.data('long_description') || '',
                unit_price: selected_product_option.data('unit_price') || '0.00',
                unit: selected_product_option.data('unit') || '',
                formula: selected_product_option.data('formula') || 'nos',
                dimension_l: selected_product_option.data('dimension-l') || '',
                dimension_w: selected_product_option.data('dimension-w') || '',
                dimension_h: selected_product_option.data('dimension-h') || '',
                material: selected_product_option.data('material') || '', 
                range: selected_product_option.data('range') || '',       
                quantity: '1', 
                is_complimentary: 0 
            };
            console.log("DEBUG: Product data from select:", product_data); 
            add_new_item_row(product_data);
            if($.fn.selectpicker) { 
                $('#item_select_product').selectpicker('val', ''); 
                // $('#item_select_product').selectpicker('refresh'); // Not strictly needed for val('')
            } else {
                $('#item_select_product').val('');
            }
        } else {
            console.log("DEBUG: No product selected, adding blank row."); 
            add_new_item_row(); 
        }
    });
    
    $('body').on('click', '#load_package_items_btn', function() { 
        var package_id = $('#item_package_select').val();
        if (!package_id) {
            alert_float('warning', _l_js('please_select_an_item_package'));
            return;
        }
        if (confirm(_l_js('confirm_load_package'))) {
            $.ajax({
                url: admin_url + 'custom_estimation/estimates/get_package_items_ajax/' + package_id,
                type: 'GET', dataType: 'json',
                beforeSend: function() { $('#load_package_items_btn').prop('disabled', true).prepend('<i class="fa fa-spinner fa-spin"></i> '); },
                complete: function() { $('#load_package_items_btn').prop('disabled', false).find('.fa-spinner').remove(); },
                success: function(response) {
                    if (response.success && response.items && response.items.length > 0) {
                        response.items.forEach(function(item_data) { add_new_item_row(item_data); });
                        alert_float('success', _l_js('package_items_loaded_info'));
                        if($.fn.selectpicker){ $('#item_package_select').selectpicker('val', ''); } else { $('#item_package_select').val('');}
                    } else if(response.success && response.items && response.items.length === 0){
                         alert_float('info', _l_js('no_items_in_package'));
                    }else { alert_float('danger', response.message || _l_js('custom_estimate_add_fail')); }
                },
                error: function(jqXHR, textStatus, errorThrown) { console.error("AJAX error loading package items:", textStatus, errorThrown, jqXHR.responseText); alert_float('danger', 'Error occurred while loading package items.');}
            });
        }
    });

    estimate_items_table_body.on('change keyup', 'input.item-quantity, input.item-unit-price, input.item-discount-percentage, input[name*="[is_complimentary]"], input.item-dimension', function() {
        calculate_custom_estimate_totals();
    });

    $('input[name="total_discount_percentage"], input[name="total_discount_amount"]').on('change keyup', function() {
        var $this = $(this);
        // If percentage is changed, clear fixed amount and vice versa
        if ($this.attr('name') === 'total_discount_percentage' && $this.val() !== '') {
            $('input[name="total_discount_amount"]').val('');
             $('input[name="discount_type"]').val('percentage');
             $('.discount-type-selected').text('%');
        } else if ($this.attr('name') === 'total_discount_amount' && $this.val() !== '') {
            $('input[name="total_discount_percentage"]').val('');
            $('input[name="discount_type"]').val('fixed_amount');
            $('.discount-type-selected').text(_l_js('discount_fixed_amount'));
        }
        calculate_custom_estimate_totals();
    });

    $('body').on('click', '.discount-type', function(e) { 
        e.preventDefault();
        var new_type = $(this).data('type');
        var $percentage_input = $('input[name="total_discount_percentage"]');
        var $amount_input = $('input[name="total_discount_amount"]');

        $('input[name="discount_type"]').val(new_type);
        $('.discount-type-selected').html($(this).text());

        if (new_type === 'percentage') {
            $amount_input.val('').addClass('hide');
            $percentage_input.removeClass('hide');
        } else { // fixed_amount
            $percentage_input.val('').addClass('hide');
            $amount_input.removeClass('hide');
        }
        calculate_custom_estimate_totals();
    });
    // Initialize visibility of discount inputs based on current discount_type
    var initial_discount_type = $('input[name="discount_type"]').val();
    if (initial_discount_type === 'percentage') {
        $('input[name="total_discount_amount"]').addClass('hide');
        $('input[name="total_discount_percentage"]').removeClass('hide');
    } else {
        $('input[name="total_discount_percentage"]').addClass('hide');
        $('input[name="total_discount_amount"]').removeClass('hide');
    }


    if (estimate_items_table_body.find('tr.main').not('.item-placeholder').length > 0) {
        calculate_custom_estimate_totals(); 
    } else if (estimate_items_table_body.find('tr.main.item-placeholder').length === 0 && estimate_items_table_body.find('tr.main').length === 0) {
        estimate_items_table_body.append('<tr class="main item-placeholder"><td colspan="10" class="text-center"><p>' + _l_js('no_items_in_custom_estimate') + '</p></td></tr>');
    }

    if (typeof(estimate_items_table_body.sortable) == 'function') {
        estimate_items_table_body.sortable({ 
            helper: 'clone', axis: 'y', handle: '.dragger',
            update: function(event, ui) { calculate_custom_estimate_totals(); }
        });
    }

    if($.fn.selectpicker) {
        $('#item_select_product, #item_package_select, #status, #lead_id, select[name="pdf_template_slug"]').selectpicker();
    }

    if (typeof window.CURRENCY_INFO === 'undefined' && typeof currencyData !== 'undefined') {
         window.CURRENCY_INFO = currencyData; 
    } else if (typeof window.CURRENCY_INFO === 'undefined') {
        console.warn("CURRENCY_INFO not defined. Using default currency formatting.");
        window.CURRENCY_INFO = { symbol: '$', decimal_separator: '.', thousand_separator: ',', placement: 'before', decimal_places: 2 };
    }
    if (typeof window.app_decimal_places === 'undefined') {
        window.app_decimal_places = function() { return (typeof CURRENCY_INFO !== 'undefined' && typeof CURRENCY_INFO.decimal_places !== 'undefined') ? CURRENCY_INFO.decimal_places : 2; };
    }
});

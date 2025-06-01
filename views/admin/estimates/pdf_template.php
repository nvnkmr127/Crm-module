<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <style type="text/css">
        body {
            font-family: sans-serif; /* Or your preferred PDF font */
            font-size: 10pt;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .estimate-details, .company-details, .client-details {
            margin-bottom: 20px;
            width: 100%;
        }
        .estimate-details td, .company-details td, .client-details td {
            padding: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f0f0f0;
        }
        .text-right {
            text-align: right;
        }
        .bold {
            font-weight: bold;
        }
        .col-50 {
            width: 50%;
            float: left;
        }
        .clearfix::after {
          content: "";
          clear: both;
          display: table;
        }
        /* Add more styles as needed for TCPDF compatibility */
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo _l('custom_estimate'); // Or your custom title ?> #<?php echo format_custom_estimate_number($estimate->id); ?></h1>
            <p><?php echo _l('custom_estimate_status'); ?>: <?php echo ucfirst($estimate->status); // You might want to format this nicely ?></p>
        </div>

        <table class="company-details" width="100%">
            <tr>
                <td width="50%" valign="top">
                    <h2 style="margin-top:0;"><?php echo get_option('companyname'); ?></h2>
                    <?php echo get_option('company_address'); ?><br>
                    <?php echo get_option('company_city'); ?>, <?php echo get_option('company_state'); ?> <?php echo get_option('company_zip'); ?><br>
                    <?php if(get_option('company_country_code') != ''){ echo get_option('company_country_code') . '<br>'; } ?>
                    <?php if(get_option('company_phone') != ''){ echo _l('client_phonenumber') . ': ' . get_option('company_phone') . '<br>'; } ?>
                    <?php if(get_option('company_vat') != ''){ echo _l('company_vat_number') . ': ' . get_option('company_vat') . '<br>'; } ?>
                    <?php
                    // Custom fields for company (if any)
                    // $custom_company_fields = get_custom_fields('company',array('show_on_pdf'=>1));
                    // foreach($custom_company_fields as $field){
                    //    $value = get_custom_field_value(0,$field['id'],'company');
                    //    if($value == ''){continue;}
                    //    echo $field['name'] . ': ' . $value . '<br />';
                    // }
                    ?>
                </td>
                <td width="50%" valign="top" class="text-right">
                    <?php // Add company logo if needed
                    $logo_url = get_dark_logo_url(); // Or get_option('company_logo_dark') etc.
                    // echo '<img src="' . $logo_url . '" style="max-width: 150px; max-height:100px;">'; // TCPDF needs path or embedded
                    ?>
                </td>
            </tr>
        </table>
        <hr/>

        <table class="client-details" width="100%">
            <tr>
                <td width="50%" valign="top">
                    <h4 style="margin-bottom:5px;"><?php echo _l('estimate_bill_to'); ?>:</h4>
                    <?php
                    // Assuming $estimate->lead_id is populated and you can fetch lead details
                    // This part needs proper data fetching. For now, a placeholder.
                    if (isset($estimate->lead_id)) {
                        $CI = &get_instance();
                        if (!class_exists('Leads_model', false)) {
                           $CI->load->model('leads_model');
                        }
                        $lead = $CI->leads_model->get($estimate->lead_id);
                        if ($lead) {
                            echo $lead->name . '<br>';
                            if(!empty($lead->company)) echo $lead->company . '<br>';
                            if(!empty($lead->address)) echo $lead->address . '<br>';
                            if(!empty($lead->city) || !empty($lead->state)) echo (!empty($lead->city) ? $lead->city : '') . (!empty($lead->state) ? ', '.$lead->state : '') . '<br>';
                            if(!empty($lead->country_name)) echo $lead->country_name . (!empty($lead->zip) ? ' '.$lead->zip : '') . '<br>';
                            if(!empty($lead->phonenumber)) echo _l('client_phonenumber') . ': ' . $lead->phonenumber . '<br>';
                            if(!empty($lead->email)) echo _l('client_email') . ': ' . $lead->email . '<br>';
                            // Lead custom fields if needed
                        } else {
                             echo 'Lead details not available.';
                        }
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </td>
                 <td width="50%" valign="top" class="text-right">
                    <p><strong><?php echo _l('custom_estimate_date'); ?>:</strong> <?php echo _d($estimate->datecreated); ?></p>
                    <p><strong><?php echo _l('custom_estimate_valid_until'); ?>:</strong> <?php echo $estimate->valid_until ? _d($estimate->valid_until) : '-'; ?></p>
                 </td>
            </tr>
        </table>


        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-left"><?php echo _l('estimate_table_item_heading'); ?></th>
                    <th class="text-left"><?php echo _l('estimate_table_item_description'); ?></th>
                    <th class="text-right"><?php echo _l('estimate_table_quantity_heading'); ?></th>
                    <th class="text-left"><?php echo _l('custom_product_unit'); ?></th>
                    <th class="text-right"><?php echo _l('estimate_table_rate_heading'); ?></th>
                    <th class="text-right"><?php echo _l('estimate_table_amount_heading'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $subtotal = 0;
                if (isset($estimate->items) && count($estimate->items) > 0) {
                    foreach ($estimate->items as $item) {
                        $item_amount = $item->quantity * $item->unit_price;
                        // Consider item discounts here if you have them per item
                        // For now, assuming item_amount is pre-calculated or simple product of qty * rate
                        $subtotal += $item_amount; // This is simplified; proper subtotal comes from estimate object
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item->description); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($item->long_description)); ?></td>
                    <td class="text-right"><?php echo format_quantity($item->quantity); // Perfex helper ?></td>
                    <td class="text-left"><?php echo htmlspecialchars($item->unit); ?></td>
                    <td class="text-right"><?php echo app_format_money($item->unit_price, get_base_currency()); ?></td>
                    <td class="text-right"><?php echo app_format_money($item_amount, get_base_currency()); ?></td>
                </tr>
                <?php
                    }
                }
                ?>
            </tbody>
        </table>

        <table width="100%" style="margin-top:20px;">
            <tr>
                <td width="60%" valign="top">
                    <?php if (!empty($estimate->notes)) : ?>
                        <div class="notes">
                            <h4 style="margin-bottom:5px;"><?php echo _l('custom_estimate_notes'); ?></h4>
                            <?php echo nl2br(htmlspecialchars($estimate->notes)); ?>
                        </div>
                    <?php endif; ?>
                     <?php if (!empty($estimate->terms_and_conditions)) : ?>
                        <div class="terms" style="margin-top:10px;">
                            <h4 style="margin-bottom:5px;"><?php echo _l('custom_estimate_terms_and_conditions'); ?></h4>
                            <?php echo nl2br(htmlspecialchars($estimate->terms_and_conditions)); ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td width="40%" valign="top" class="text-right">
                    <table class="totals-table" width="100%">
                        <tr>
                            <td class="bold" width="60%"><?php echo _l('estimate_subtotal'); ?></td>
                            <td width="40%"><?php echo app_format_money($estimate->subtotal, get_base_currency()); ?></td>
                        </tr>
                        <?php if ($estimate->total_discount_amount > 0 || $estimate->total_discount_percentage > 0) : ?>
                        <tr>
                            <td class="bold">
                                <?php
                                echo _l('estimate_discount');
                                if ($estimate->total_discount_percentage > 0) {
                                    echo ' (' . $estimate->total_discount_percentage . '%)';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                $discount_value = $estimate->total_discount_amount;
                                if($estimate->total_discount_percentage > 0){
                                     $discount_value = ($estimate->subtotal * $estimate->total_discount_percentage) / 100;
                                }
                                echo '-' . app_format_money($discount_value, get_base_currency());
                                ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php // Add Tax rows here if you implement taxes ?>
                        <tr>
                            <td class="bold" style="font-size:1.1em;"><?php echo _l('estimate_total'); ?></td>
                            <td style="font-size:1.1em;"><?php echo app_format_money($estimate->total, get_base_currency()); ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div class="footer">
            <p><?php // echo _l('pdf_generated_on') . ' ' . _dt(date('Y-m-d H:i:s')); ?></p>
        </div>
    </div>
</body>
</html>
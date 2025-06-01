<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 
    // This view now expects a variable $parsed_estimate_html from the controller,
    // which contains the full HTML document string, already processed with CSS and data.
    // No need for <html>, <head>, <body> tags here if $parsed_estimate_html includes them.
    // The parse_custom_estimate_template() helper is designed to produce a full HTML string.
    
    if (isset($parsed_estimate_html)) {
        echo $parsed_estimate_html;
    } else {
        // Fallback content if $parsed_estimate_html is not provided for some reason
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title><?php echo isset($title) ? $title : _l('custom_estimate_online_view_title'); ?></title>
    <style type="text/css">
        body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #333; background-color: #f4f6f8; padding: 20px; }
        .estimate-container { max-width: 900px; margin: 0 auto; background-color: #fff; padding: 30px; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        .alert-danger { color: #a94442; background-color: #f2dede; border-color: #ebccd1; padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        h1 { margin-top: 0; font-size: 24px; }
    </style>
</head>
<body class="<?php if(isset($bodyclass)){echo $bodyclass; } ?>">
    <div class="estimate-container">
        <div class="alert alert-danger">
            <?php echo _l('custom_estimate_not_found'); // Or a more generic error like "Content not available" ?>
        </div>
    </div>
</body>
</html>
<?php
    }
?>
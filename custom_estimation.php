<?php



/**

 * Ensures that the module init file can't be accessed directly, only within the application.

 */

defined('BASEPATH') or exit('No direct script access allowed');



/* Module Name: Custom Estimation Module

Description: A custom module for creating and managing detailed project estimates.

Version: 1.0.0

Requires at least: 2.3.0

Author: Your Name/Company Name

Author URI: Your Website

*/



define('CUSTOM_ESTIMATION_MODULE_NAME', 'custom_estimation');

define('CUSTOM_ESTIMATION_TABLE_ESTIMATES', db_prefix() . 'custom_estimates');

define('CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS', db_prefix() . 'custom_estimate_items');

define('CUSTOM_ESTIMATION_TABLE_PRODUCTS', db_prefix() . 'custom_products');

define('CUSTOM_ESTIMATION_TABLE_PRODUCT_CATEGORIES', db_prefix() . 'custom_product_categories');

define('CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGES', db_prefix() . 'custom_item_packages'); 

define('CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS', db_prefix() . 'custom_item_package_items'); 

define('CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES', db_prefix() . 'custom_estimate_pdf_templates'); 

/**

 * Register activation module hook

 */

register_activation_hook(CUSTOM_ESTIMATION_MODULE_NAME, 'custom_estimation_activation_hook');


function custom_estimation_activation_hook()

{

    $CI = &get_instance();

    require_once(__DIR__ . '/install.php'); 

}



/**

 * Register deactivation module hook

 */

register_deactivation_hook(CUSTOM_ESTIMATION_MODULE_NAME, 'custom_estimation_deactivation_hook');



function custom_estimation_deactivation_hook()

{

    // Placeholder for any deactivation tasks

}



/**

 * Register uninstall module hook

 */

register_uninstall_hook(CUSTOM_ESTIMATION_MODULE_NAME, 'custom_estimation_uninstall_hook');



function custom_estimation_uninstall_hook()

{

    $CI = &get_instance();

    // Drop tables in an order that respects foreign keys

    $CI->db->query("DROP TABLE IF EXISTS " . CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS . ";");

    $CI->db->query("DROP TABLE IF EXISTS " . CUSTOM_ESTIMATION_TABLE_ESTIMATES . ";");

    $CI->db->query("DROP TABLE IF EXISTS " . CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . ";"); 

    $CI->db->query("DROP TABLE IF EXISTS " . CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGES . ";");

    $CI->db->query("DROP TABLE IF EXISTS " . CUSTOM_ESTIMATION_TABLE_PRODUCTS . ";");

    $CI->db->query("DROP TABLE IF EXISTS " . CUSTOM_ESTIMATION_TABLE_PRODUCT_CATEGORIES . ";");

    $CI->db->query("DROP TABLE IF EXISTS " . CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES . ";"); 

    

    delete_option('custom_estimation_option_example');

    delete_option('custom_estimation_default_pdf_template'); 

}



/**

 * Init module menu items

 */

hooks()->add_action('admin_init', 'custom_estimation_module_init_menu_items');



function custom_estimation_module_init_menu_items()

{

    $CI = &get_instance();



    if (isset($CI->app_menu) && $CI->app_menu) {

        if (is_admin()) {

            $CI->app_menu->add_sidebar_menu_item(CUSTOM_ESTIMATION_MODULE_NAME, [

                'name'     => _l('custom_estimation_menu_title'),

                'href'     => admin_url(CUSTOM_ESTIMATION_MODULE_NAME . '/reports'), 

                'position' => 20,

                'icon'     => 'fa fa-calculator',

            ]);



            $CI->app_menu->add_sidebar_children_item(CUSTOM_ESTIMATION_MODULE_NAME, [

                'slug'     => 'custom_estimation_reports', 

                'name'     => _l('custom_estimation_submenu_reports'),

                'href'     => admin_url(CUSTOM_ESTIMATION_MODULE_NAME . '/reports'), 

                'position' => 3, 

                'icon'     => 'fa fa-bar-chart',

            ]);



            $CI->app_menu->add_sidebar_children_item(CUSTOM_ESTIMATION_MODULE_NAME, [

                'slug'     => 'custom_estimation_estimates',

                'name'     => _l('custom_estimation_submenu_estimates'),

                'href'     => admin_url(CUSTOM_ESTIMATION_MODULE_NAME . '/estimates'),

                'position' => 5,

                'icon'     => 'fa fa-list',

            ]);

            $CI->app_menu->add_sidebar_children_item(CUSTOM_ESTIMATION_MODULE_NAME, [

                'slug'     => 'custom_estimation_products',

                'name'     => _l('custom_estimation_submenu_products'),

                'href'     => admin_url(CUSTOM_ESTIMATION_MODULE_NAME . '/products'),

                'position' => 10,

                'icon'     => 'fa fa-cube',

            ]);

            $CI->app_menu->add_sidebar_children_item(CUSTOM_ESTIMATION_MODULE_NAME, [

                'slug'     => 'custom_estimation_categories',

                'name'     => _l('custom_estimation_submenu_categories'),

                'href'     => admin_url(CUSTOM_ESTIMATION_MODULE_NAME . '/categories'),

                'position' => 15,

                'icon'     => 'fa fa-tags',

            ]);

            if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'view')) { 

                $CI->app_menu->add_sidebar_children_item(CUSTOM_ESTIMATION_MODULE_NAME, [

                    'slug'     => 'custom_estimation_item_packages',

                    'name'     => _l('custom_estimation_submenu_item_packages'),

                    'href'     => admin_url(CUSTOM_ESTIMATION_MODULE_NAME . '/item_packages'),

                    'position' => 18,

                    'icon'     => 'fa fa-archive',

                ]);

            }

            

            if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'manage_pdf_templates')) {

                 $CI->app_menu->add_sidebar_children_item(CUSTOM_ESTIMATION_MODULE_NAME, [

                    'slug'     => 'custom_estimation_pdf_templates',

                    'name'     => _l('custom_estimation_submenu_pdf_templates'),

                    'href'     => admin_url(CUSTOM_ESTIMATION_MODULE_NAME . '/pdf_templates'), 

                    'position' => 22, 

                    'icon'     => 'fa fa-file-image-o', 

                ]);

            }

        }

    }

}



/**

 * Register permissions

 */

hooks()->add_action('staff_permissions', 'custom_estimation_permissions');

function custom_estimation_permissions($permissions) {

    $all_permissions = [

        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',

        'create' => _l('permission_create'),

        'edit'   => _l('permission_edit'),

        'delete' => _l('permission_delete'),

        'edit_product_values' => _l('custom_estimation_permission_edit_product_values'),

        'approve' => _l('custom_estimation_permission_approve'),

        'manage_packages' => _l('custom_estimation_permission_manage_packages'),

        'manage_pdf_templates' => _l('custom_estimation_permission_manage_pdf_templates'), 

    ];

    $permissions[CUSTOM_ESTIMATION_MODULE_NAME] = [

        'name'         => _l('custom_estimation_module_name'),

        'capabilities' => $all_permissions,

    ];

    return $permissions;

}



/**

 * Add tab link to lead profile for custom estimates using the 'after_lead_lead_tabs' action hook.

 */

hooks()->add_action('after_lead_lead_tabs', 'custom_estimation_echo_lead_profile_tab_link', 10); 



function custom_estimation_echo_lead_profile_tab_link($data_from_hook) {

    error_reporting(E_ALL); 

    ini_set('display_errors', 1);

    echo "<pre style='background:#fff0f0; color:black; border:1px solid red; padding:10px; margin:10px; z-index: 9999 !important; position:relative !important;'>";

    echo "DEBUG: custom_estimation_echo_lead_profile_tab_link CALLED.\n";

    echo "Data from hook (\$data_from_hook):\n";

    var_dump($data_from_hook);

    echo "</pre>";

    // die("Stopped in custom_estimation_echo_lead_profile_tab_link to inspect \$data_from_hook."); // UNCOMMENT THIS TO STOP AND SEE



    $lead = null;

    if (is_object($data_from_hook) && isset($data_from_hook->id) && isset($data_from_hook->name)) { 

        $lead = $data_from_hook;

    } 

    elseif (is_array($data_from_hook) && isset($data_from_hook['lead']) && is_object($data_from_hook['lead']) && isset($data_from_hook['lead']->id)) {

        $lead = $data_from_hook['lead'];

    }



    if (!$lead || !isset($lead->id)) {

        return; 

    }

    

    if (function_exists('has_permission') && has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'view')) {

        $tab_name = function_exists('_l') ? _l('lead_profile_custom_estimates_tab') : 'Custom Estimates';

        if (empty($tab_name) || (strpos($tab_name, 'lead_profile_custom_estimates_tab') !== false && $tab_name == 'lead_profile_custom_estimates_tab') ) {

            $tab_name = 'Custom Estimates'; 

        }



        $tab_content_id = 'custom_estimates_for_lead_content_' . $lead->id; 

        $tab_href = '#' . $tab_content_id; 



        echo '<li role="presentation">';

        echo '  <a href="' . $tab_href . '" aria-controls="' . $tab_content_id . '" role="tab" data-toggle="tab">';

        echo '      <i class="fa fa-calculator menu-icon"></i> ';

        echo        htmlspecialchars($tab_name); 

        echo '  </a>';

        echo '</li>';

    }

}



/**

 * Hook to add content pane for the custom estimates tab.

 */

hooks()->add_action('after_lead_profile_tab_content', 'custom_estimation_lead_profile_tab_content', 10);



function custom_estimation_lead_profile_tab_content($data_from_hook){

    error_reporting(E_ALL);

    ini_set('display_errors', 1);

    echo "<pre style='background:#f0f0ff; color:black; border:1px solid blue; padding:10px; margin:10px; z-index: 9999 !important; position:relative !important;'>";

    echo "DEBUG: custom_estimation_lead_profile_tab_content CALLED.\n";

    echo "Data from hook (\$data_from_hook):\n";

    var_dump($data_from_hook);

    // die("Stopped in custom_estimation_lead_profile_tab_content to inspect \$data_from_hook."); // UNCOMMENT THIS TO STOP HERE

    

    $lead = null;

    if (is_object($data_from_hook) && isset($data_from_hook->id) && isset($data_from_hook->name)) { 

        $lead = $data_from_hook;

    }

    elseif (is_array($data_from_hook) && isset($data_from_hook['lead']) && is_object($data_from_hook['lead']) && isset($data_from_hook['lead']->id)) {

        $lead = $data_from_hook['lead'];

    } 



    if (!$lead || !isset($lead->id)) {

        echo "DEBUG: Could not determine valid lead object for tab content.\n</pre>";

        return; 

    }



    if (function_exists('has_permission') && has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'view')) {

        echo "DEBUG: User has permission for tab content. Lead ID: " . $lead->id . "\n</pre>"; // End debug pre before echoing tab pane

        $tab_content_id = 'custom_estimates_for_lead_content_' . $lead->id; 

        

        echo '<div role="tabpanel" class="tab-pane" id="' . $tab_content_id . '">';

        

        $data_for_view['lead'] = $lead; 

        $view_path = 'custom_estimation/admin/leads/profile_tab_custom_estimates';

        if (file_exists(module_dir_path(CUSTOM_ESTIMATION_MODULE_NAME) . 'views/admin/leads/profile_tab_custom_estimates.php')) {

            echo get_instance()->load->view($view_path, $data_for_view, true);

        } else {

            echo "<p>Error: View file for custom estimates tab not found: " . htmlspecialchars($view_path) . "</p>";

        }

        echo '</div>';

    } else {

        echo "DEBUG: No view permission for tab content.\n</pre>";

    }

}





/**

 * Inject CSS

 */

hooks()->add_action('app_admin_head', 'custom_estimation_add_head_components');

if (!function_exists('custom_estimation_add_head_components')) {

    function custom_estimation_add_head_components()

    {

        $CI = &get_instance();

        $controller = strtolower($CI->router->fetch_class()); 

        $method = strtolower($CI->router->fetch_method()); 

        $module_controllers = [ 

            'categories', 'products', 'estimates',

            'item_packages', 'pdf_templates', 'reports', 

            'custom_estimation' 

        ];

        

        if (in_array($controller, $module_controllers) || 

           ($controller == 'leads' && ($method == 'index' || $method == 'lead' || $method == 'profile')) || 

           ($controller == 'dashboard' && is_admin())) { 

           echo '<link href="' . module_dir_url(CUSTOM_ESTIMATION_MODULE_NAME, 'assets/css/style.css') . '?v=' . $CI->app_scripts->core_version().'"  rel="stylesheet" type="text/css" />';

        }

    }

}



/**

 * Inject JS

 */

hooks()->add_action('app_admin_footer', 'custom_estimation_load_js');

if (!function_exists('custom_estimation_load_js')) {

    function custom_estimation_load_js()

    {

        $CI = &get_instance();

        $controller = strtolower($CI->router->fetch_class());

        $method = strtolower($CI->router->fetch_method());

        $module_controllers = [

            'categories', 'products', 'estimates',

            'item_packages', 'pdf_templates', 'reports', 

            'custom_estimation' 

        ];

        

        if (in_array($controller, $module_controllers) || 

           ($controller == 'leads' && ($method == 'index' || $method == 'lead' || $method == 'profile')) || 

           ($controller == 'dashboard' && is_admin())) { 

           echo '<script src="' . module_dir_url(CUSTOM_ESTIMATION_MODULE_NAME, 'assets/js/custom_estimation.js') . '?v=' . $CI->app_scripts->core_version().'"></script>';

        }

    }

}



/**

 * Register language files

 */

register_language_files(CUSTOM_ESTIMATION_MODULE_NAME, [CUSTOM_ESTIMATION_MODULE_NAME]);



/**

 * Init dashboard widgets

 */

hooks()->add_action('before_dashboard_render', 'custom_estimation_init_dashboard_widgets');

if (!function_exists('custom_estimation_init_dashboard_widgets')) {

    function custom_estimation_init_dashboard_widgets($dashboard_widgets)

    {

        if(!is_array($dashboard_widgets)){ $dashboard_widgets = []; }

        if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'view')) {

            $dashboard_widgets[] = [

                'path'      => 'custom_estimation/widgets/estimation_summary_widget', 

                'container' => 'left-column', 

                'order'     => 5, 

            ];

        }

        return $dashboard_widgets;

    }

}



/**

 * Autoload helpers

 */

$CI = &get_instance();

if (file_exists(module_dir_path(CUSTOM_ESTIMATION_MODULE_NAME) . 'helpers/custom_estimation_template_helper.php')) {

    $CI->load->helper(CUSTOM_ESTIMATION_MODULE_NAME . '/custom_estimation_template');

}




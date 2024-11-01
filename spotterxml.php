<?php

/* Plugin Name: XML feed for spotter.com.mt
  Plugin URI: http://spotter.com.mt
  Description: XML feed creator for spotter.com.mt
  Version: 1.0.5
  Author: spotter.com.mt
  Author URI: https://spotter.com.mt
  License: GPLv3 or later
  WC tested up to: 5.1.0
 */
/*
Based on original plugin "skroutz.gr & Bestprice.gr XML Feed for w By emspace.gr"
*/
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
load_plugin_textdomain('spotter-w-feed', false, dirname(plugin_basename(__FILE__)) . '/languages/');

function spotter_w_spotterxml_activate()
{
    if (!wp_next_scheduled('spotter_xml_twicedaily_event')) {
        wp_schedule_event(time(), 'twicedaily', 'spotter_xml_twicedaily_event');
    }
}

register_activation_hook(__FILE__, 'spotter_w_spotterxml_activate');

function spotter_xml_admin_menu()
{

    /* add new top level */
    add_menu_page(
        __('spotter.com.mt', 'spotter-w-feed'), __('spotter.com.mt', 'spotter-w-feed'), 'manage_options', 'spotter_xml_admin_menu', 'spotter_xml_admin_page', plugins_url('/', __FILE__) . '/images/xml-icon.png'
    );

    /* add the submenus */
    add_submenu_page(
        'spotter_xml_admin_menu', __('Create XML Feed', 'spotter-w-feed'), __('Create XML Feed', 'spotter-w-feed'), 'manage_options', 'spotter_xml_create_page', 'spotter_xml_create_page'
    );
}

add_action('admin_menu', 'spotter_xml_admin_menu');
add_action('admin_init', 'spotter_register_mysettings');


function spotter_xml_admin_page()
{

    add_action('wp', 'spotter_xml_setup_schedule');
    $skicon = plugins_url('/', __FILE__) . '/images/spotter.png';

    echo '<div id="main">';
    echo '<div>';
    echo '</br>';
    echo '<h2>' . __('Settings for XML Feeds for spotter.com.mt', 'spotter-w-feed') . '</h2>';
    echo '</div>';

    global $w;
    $attribute_taxonomies = wc_get_attribute_taxonomies();
    $taxonomies = get_taxonomies();
    $meta_keys = get_meta_keys();

    echo '<form method="post" action="options.php">';
    settings_fields('spotter-group');
    do_settings_sections('spotter-group');
    echo '<table class="form-table spotter_bestprice">';
    echo '<tr valign="top">';
    echo '<th scope="row">' . __('When in Stock Availability', 'spotter-w-feed') . '</th><td>';
    $options = get_option('instockavailability');
    $items = array(
        __('Available in store / Delivery 1 to 3 days', 'spotter-w-feed'),
        __('Delivery 1 to 3 days', 'spotter-w-feed'),
        __('Delivery 4 to 10 days', 'spotter-w-feed'),
        __('Attribute: Availability', 'spotter-w-feed'),
        __('Custom Availability', 'spotter-w-feed')
    );
    echo "<select id='drop_down1' name='instockavailability'>";
    foreach ($items as $key => $item) {
        $selected = ($options == $key) ? 'selected="selected"' : '';
        echo "<option value='" . esc_html($key) . "' $selected>" . esc_html($item) . "</option>";
    }
    echo "</select>";
    echo "</br></br> <em>" . __('Select <strong>Attribute: Availability</strong> only if you have added an attribute with name "Availability"', 'spotter-w-feed') . "</em>";
    echo '</td>';
    echo '</tr>';


    echo '<tr valign="top">';
    echo '<th scope="row">' . __('If a Product is out of Stock', 'spotter-w-feed') . '</th>';
    echo '<td>';

    $options2 = get_option('ifoutofstock');

    $items = array(__('Include as out of Stock or Upon Request', 'spotter-w-feed'), __('Exclude from feed', 'spotter-w-feed'));
    echo "<select id='drop_down2' name='ifoutofstock'>";
    foreach ($items as $key => $item) {
        $selected = ($options2 == $key) ? 'selected="selected"' : '';
        echo "<option value='" . esc_html($key) . "' $selected>" . esc_html($item) . "</option>";
    }
    echo "</select>";
    echo '</td>';
    echo '</tr>';
    $include_tax = get_option('include_tax', true);

    echo '<tr valign="top">';
    echo '<th> <label for="include_tax">' . __('Auto Calculate Price with Tax (use this only if you enter your products without tax)', 'spotter-w-feed') . '</label></th>';
    echo '<td><input style="margin-left:10px;" id="include_tax"  class="include_tax" type="checkbox" name="include_tax" value="1" ' . ($include_tax == 0 ? "" : "checked") . ' /></td>';
    echo "</tr>";

    $group_variations = get_option('group_variations', false);

    echo '<tr valign="top">';
    echo '<th> <label for="group_variations">' . __('Split variable products by color', 'spotter-w-feed') . '</label></th>';
    echo '<td><input style="margin-left:10px;" id="group_variations"  class="group_variations" type="checkbox" name="group_variations" value="1" ' . ($group_variations == 1 ? "checked" : "") . ' /></td>';
    echo "</tr>";

    $custom_productId = get_option('custom_productId');
    echo '<tr>';
    echo '<th> <label for="custom_product_id">' . __('Custom Product Id', 'spotter-w-feed') . '</label></th>';
    echo '<td><select name="custom_productId" class="autocomplete" tabindex="-1">';
    echo "<option value='' " . selected($selected, true, false) . ">" . __('-Default-', 'spotter-w-feed') . "</option>";

    foreach ($meta_keys as $key => $metaKey) {
        $selected = false;
        if ($custom_productId == $metaKey) {
            $selected = true;
        }

        echo "<option value='" . esc_html($metaKey) . "' " . selected($selected, true, false) . ">" . esc_html($metaKey) . "</option>";
    }
    echo '</select>';
    echo '</td>';
    echo '</tr>';

    echo '<tr valign="top">';
    echo '<th scope="row">' . __('Select shipping method to calculate cost', 'spotter-w-feed') . '</th><td>';
    $shippingMethods = WC()->shipping->load_shipping_methods();
    $finalShippingMethods = array();
    foreach ($shippingMethods as $item) {
        $finalShippingMethods[$item->id] = $item->method_title;
    }
    $options = get_option('shipping_method');
    echo "<select id='drop_down_shipping' name='shipping_method'>";
    foreach ($finalShippingMethods as $key => $item) {
        $selected = ($options == $key) ? 'selected="selected"' : '';
        echo "<option value='" . esc_html($key) . "' $selected>" . esc_html($item) . "</option>";
    }
    echo "</select>";
    echo '</td>';
    echo '</tr>';


    foreach ($attribute_taxonomies as $tax) {
        $term = wc_attribute_taxonomy_name($tax->attribute_name);
        $attribute_terms[$tax->attribute_id] = '';
        if (taxonomy_exists($term)) {
            $attribute_terms[$tax->attribute_id] = $term;
        }
    }

    echo "<tr>";
    echo '<th scope="row">' . __('Attributes', 'spotter-w-feed') . '</th>';
    echo "<td>";
    $spotter_atts_color = get_option('spotter_atts_color', 'pa_color');
    $spotter_atts_size = get_option('spotter_atts_size', 'pa_size');
    $spotter_atts_manuf = get_option('spotter_atts_manuf', 'pa_brand');

    echo '<label>' . __('Size', 'spotter-w-feed') . ': <select name="spotter_atts_size">';
    echo "<option value='' " . selected($selected, true, false) . ">" . __('-Empty-', 'spotter-w-feed') . "</option>";

    foreach ($attribute_taxonomies as $tax) {
        $selected = false;
        if ($spotter_atts_size == $attribute_terms[$tax->attribute_id]) {
            $selected = true;
        }

        echo "<option value='" . esc_html($attribute_terms[$tax->attribute_id]) . "' " . selected($selected, true, false) . ">" . esc_html($tax->attribute_label) . "</option>";
    }
    echo '</select></label>&nbsp;&nbsp;';
    echo '<label>' . __('Color', 'spotter-w-feed') . ': <select name="spotter_atts_color">';
    echo "<option value='' " . selected($selected, true, false) . ">" . __('-Empty-', 'spotter-w-feed') . "</option>";

    foreach ($attribute_taxonomies as $tax) {
        $selected = false;
        if ($spotter_atts_color == $attribute_terms[$tax->attribute_id]) {
            $selected = true;
        }

        echo "<option value='" . esc_html($attribute_terms[$tax->attribute_id]) . "' " . selected($selected, true, false) . ">" . esc_html($tax->attribute_label) . "</option>";
    }
    echo '</select></label>&nbsp;&nbsp;';
    echo '<label>' . __('Manufacturer', 'spotter-w-feed') . ': <select name="spotter_atts_manuf">';
    if ($spotter_atts_manuf == '') {
        $selected = true;
    }
    echo "<option value='' " . selected($selected, true, false) . ">" . __('-Empty-', 'spotter-w-feed') . "</option>";
    $hasAttributeBrand = false;
    foreach ($attribute_taxonomies as $tax) {
        $selected = false;
        if ($spotter_atts_manuf == $attribute_terms[$tax->attribute_id]) {
            $selected = true;
        }
        if ($attribute_terms[$tax->attribute_id] === 'brand' || $attribute_terms[$tax->attribute_id] === 'pa_brand') {
            $hasAttributeBrand = true;
        }
        echo "<option value='" . esc_html($attribute_terms[$tax->attribute_id]) . "' " . selected($selected, true, false) . ">" . esc_html($tax->attribute_label) . "</option>";
    }
    if (!$hasAttributeBrand) {
        foreach ($taxonomies as $tax) {
            $selected = false;
            if (strpos($tax, 'brand') === false) continue;
            if ($spotter_atts_manuf == $tax) {
                $selected = true;
            }

            echo "<option value='" . esc_html($tax) . "' " . selected($selected, true, false) . ">" . esc_html($tax) . "</option>";
        }
    }
    echo '</select></label>';
    echo "</td>";
    echo "</tr>";

    echo '<tr>';
    echo '<th>' . __('Exclude categories from XML', 'spotter-w-feed') . '</th>';
    echo '<td>';
    $cats_excluded = get_option('exclude_cats');

    echo '<select class="autocomplete" id="cat_drop" name="exclude_cats[]" multiple="multiple">';
    $avail_cats = get_terms('product_cat', array('get' => 'all'));

    foreach ($avail_cats as $cat) {
        $selected = false;
        if (is_array($cats_excluded) && in_array($cat->term_id, $cats_excluded)) {
            $selected = true;
        }

        echo '<option value="' . $cat->term_id . '" ' . selected($selected, true, false) . ' >' . $cat->name . '</option>';
    }
    echo '</select>';
    echo '';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th>' . __('Exclude products if the final price is lower than €XX.XX (leave empty to not limit)', 'spotter-w-feed') . '</th>';
    echo '<td>';
    $exclude_products_min_price = get_option('exclude_products_min_price');

    echo '<input placeholder="€XX.XX" step="0.01" style="margin-left:10px;" id="exclude_products_min_price" class="" type="number" name="exclude_products_min_price" value="' . $exclude_products_min_price . '"/></td>';

    echo '';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th>' . __('Free shipping for products with price above €XX.XX (leave empty to not offer free shipping)', 'spotter-w-feed') . '</th>';
    echo '<td>';
    $offer_free_shipping = get_option('offer_free_shipping');

    echo '<input placeholder="€XX.XX" step="0.01" style="margin-left:10px;" id="offer_free_shipping" class="" type="number" name="offer_free_shipping" value="' . $offer_free_shipping . '"/></td>';

    echo '';
    echo '</td>';
    echo '</tr>';


    $enable_gtin = get_option('enable_gtin');
    $gtin_label = get_option('gtin_label');
    $gtin_value = get_option('gtin_value');
    $gtin_plugins = array(
        'hwp_product_gtin' => 'w UPC, EAN, and ISBN',
        '_wpm_gtin_code' => 'Product GTIN (EAN, UPC, ISBN) for w',
    );
    $gtin_values = array();
    foreach ($meta_keys as $key => $metaKey) {
        if (strpos($metaKey, 'gtin') !== false
            || strpos($metaKey, 'ean') !== false
            || strpos($metaKey, 'isbn') !== false
            || strpos($metaKey, 'upc') !== false
            || strpos($metaKey, 'barcode') !== false
            || strpos($metaKey, 'sku') !== false
        ) {
            if (isset($gtin_plugins[$metaKey]) != null) {
                $gtin_values[$metaKey] = $gtin_plugins[$metaKey];
            } else {
                $gtin_values[$metaKey] = $metaKey;
            }
        }
    }

    echo '<tr valign="top">';
    echo '<th> <label for="toggle_gtin">' . __('Enable GTIN Feed', 'spotter-w-feed') . ' </label></th>';
    echo '<td><input style="margin-left:10px;" id="toggle_gtin" class="toggle_gtin" type="checkbox" name="enable_gtin" value="1" ' . ($enable_gtin == 1 ? "checked" : "") . ' /></td>';

    echo "</tr>";
    echo '<tr class="gtin" style="' . ($enable_gtin == 1 ? '' : 'display:none') . '" valign="top">';
    echo '<th>' . __('GTIN settings', 'spotter-w-feed') . '</th>';
    echo '<td>';
    echo '<label>' . __('XML Tag Name', 'spotter-w-feed') . ': ';
    echo '<select name="gtin_label">';
    echo '<option value="ean" ' . selected('ean' == $gtin_label, true, false) . '>Ean</option>';
    echo '<option value="barcode"' . selected('barcode' == $gtin_label, true, false) . '>Barcode</option>';
    echo '<option value="isbn"' . selected('isbn' == $gtin_label, true, false) . '>ISBN</option>';
    echo '</select></label> &nbsp;&nbsp;';

    echo '<label>' . __('GTIN Source Plugin', 'spotter-w-feed') . ': ';
    echo '<select name="gtin_value">';
    echo "<option value='' " . selected($selected, true, false) . ">" . __('-Empty-', 'spotter-w-feed') . "</option>";
    foreach ($gtin_values as $key => $gtin) {
        $selected = false;
        if ($key == $gtin_value) {
            $selected = true;
        }
        echo '<option value="' . esc_html($key) . '" ' . selected($selected, true, false) . '>' . esc_html($gtin) . '</option>';
    }
    echo '</select></label> &nbsp;&nbsp;';
    echo '</td>';
    echo '</tr>';


    echo ' </table>';
    submit_button();
    echo '</form>';

    if (get_option('last_update') != "") {
        echo '<div class="feedsUrl" style="display: flex; flex-direction: column; max-width: 500px; margin: 0px 0 30px; padding: 10px 0; align-content: center;">';
        echo '<h3 style="margin: 0.3em 0;">' . __('XML Feed Urls:', 'spotter-w-feed') . '</h3>';
        echo '<p style="">' . __('Last generated XML Feed time: ', 'spotter-w-feed') . get_option('last_update') . '</p>';
        echo __('spotter.com.mt XML Url: ', 'spotter-w-feed') . ' <a href="' . wp_upload_dir()['baseurl'] . '/spotter/spotter.xml" target="_blank">' . wp_upload_dir()['baseurl'] . '/spotter/spotter.xml</a></br>';
        echo '</div>';
    }

    echo '<a class="button button-primary" href="' . get_admin_url() . 'admin.php?page=spotter_xml_create_page">' . __('Create XML Feeds', 'spotter-w-feed') . '</a>';
    echo '</div>';

    wc_enqueue_js('
    $(".toggle_gtin").change(function() {
        if($(".toggle_gtin:checked").length) {
            $(".gtin").show();
        } else {
            $(".gtin").hide();

        }
      });');
}

function spotter_register_mysettings()
{ // whitelist options
    register_setting('spotter-group', 'instockavailability', 'spotter_sanitize_options');
    register_setting('spotter-group', 'ifoutofstock', 'spotter_sanitize_options');
    register_setting('spotter-group', 'include_tax');
    register_setting('spotter-group', 'group_variations');
    register_setting('spotter-group', 'features', 'spotter_sanitize_options_multi');
    //register_setting('spotter-group', 'spotter_atts', 'spotter_sanitize_options_multi');
    register_setting('spotter-group', 'spotter_atts_color', 'spotter_sanitize_options');
    register_setting('spotter-group', 'spotter_atts_manuf', 'spotter_sanitize_options');
    register_setting('spotter-group', 'spotter_atts_size', 'spotter_sanitize_options');
    register_setting('spotter-group', 'enable_gtin');
    register_setting('spotter-group', 'gtin_label', 'spotter_sanitize_options');
    register_setting('spotter-group', 'gtin_value', 'spotter_sanitize_options');
    register_setting('spotter-group', 'exclude_cats', 'spotter_sanitize_options_multi');
    register_setting('spotter-group', 'exclude_products_min_price', 'spotter_sanitize_money');
    register_setting('spotter-group', 'offer_free_shipping', 'spotter_sanitize_money');
    register_setting('spotter-group', 'custom_productId', 'spotter_sanitize_options');
    register_setting('spotter-group', 'last_update', 'spotter_sanitize_options');
    register_setting('spotter-group', 'shipping_method', 'spotter_sanitize_options');

}

function spotter_sanitize_money($input)
{
    return $input;
    return bcdiv($input, 1, 2);
}

function spotter_sanitize_options($input)
{

    return esc_html($input);
}

function spotter_sanitize_options_multi($input)
{

    $output = array();

    foreach ($input as $in_value) {
        $output[] = esc_html($in_value);
    }


    return $output;
}

function spotter_xml_create_page()
{

    $skicon = plugins_url('/', __FILE__) . '/images/spotter.jpg';
    echo '<div><img src="' . $skicon . '" height="150px">';
    echo '<div>';
    echo '<h2>' . __('Create Feed for spotter.com.mt', 'spotter-w-feed') . '</h2>';
    echo '</div>';

    settings_fields('spotter-group');
    do_settings_sections('spotter-group');
    spotter_generate_products_xml_data_new();
    if (!wp_next_scheduled('spotter_xml_twicedaily_event')) {
        wp_schedule_event(time(), 'twicedaily', 'spotter_xml_twicedaily_event');
    }
}

add_action('spotter_xml_twicedaily_event', 'spotter_xml_do_this_twicedaily');
function spotter_is_parent($var)
{
    return $var->parent == 0;
}

function spotter_is_subcategory($var)
{
    return $var->parent != 0;
}

/**
 * On the scheduled action hook, run a function.
 */
function spotter_xml_do_this_twicedaily()
{
    spotter_generate_products_xml_data_new();
    if (!wp_next_scheduled('spotter_xml_twicedaily_event')) {
        wp_schedule_event(time(), 'twicedaily', 'spotter_xml_twicedaily_event');
    }
}

function spotter_xml_schema($prod, $group_variations, $instockavailability, $avaibilities, $availabilityST, $ifoutofstock, $custom_productId, $spotter_atts_color, $spotter_atts_size, $spotter_atts_manuf, $enable_gtin, $include_tax, $gtin_label, $gtin_value, $variation_atts, $attributes, $variable_extra = [], $parent = [], $minimumPrice = null, $freeShippingPrice = null)
{

    $product_id = $prod->get_id();
    include_once ABSPATH . '/wp-content/plugins/woocommerce/includes/wc-cart-functions.php';
    include_once ABSPATH . '/wp-content/plugins/woocommerce/includes/class-wc-cart.php';

    if (null === WC()->session) {
        $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');
        WC()->session = new $session_class();
        WC()->session->init();
        WC()->session->set('chosen_shipping_methods', array('wbs'));
    }
    if (null === WC()->customer) {
        $data = array(
            'date_created' => null,
            'date_modified' => null,
            'email' => 'spottercommtXML@spotter.com.mt',
            'first_name' => 'spottercommtXML',
            'last_name' => 'spottercommtXML',
            'display_name' => 'spottercommtXML',
            'role' => 'customer',
            'username' => 'spottercommtXML',
            'billing' => array(
                'first_name' => '',
                'last_name' => '',
                'company' => '',
                'address_1' => '',
                'address_2' => '',
                'city' => '',
                'state' => '',
                'postcode' => '',
                'country' => '',
                'email' => '',
                'phone' => '',
            ),
            'shipping' => array(
                'first_name' => '',
                'last_name' => '',
                'company' => '',
                'address_1' => '',
                'address_2' => '',
                'city' => '',
                'state' => '',
                'postcode' => '',
                'country' => 'MT',
            ),
            'is_paying_customer' => true,
        );
        WC()->customer = new WC_Customer($data, false);
        WC()->customer->set_shipping_country("MT");
    }
    if (null === WC()->cart) {
        WC()->cart = new WC_Cart();
    }
    WC()->cart->empty_cart();
    WC()->cart->add_to_cart($product_id);
    $tempShipping = WC()->shipping->calculate_shipping(WC()->cart->get_shipping_packages());
    $selectedShipping = get_option('shipping_method');
    $tempShippingCost = null;
    if ($tempShipping[0] && $tempShipping[0]['rates'] && $selectedShipping) {
        $shippingCostList = array();
        foreach ($tempShipping[0]['rates'] as $key => $rate) {
            if ($rate->get_method_id() === $selectedShipping) {
                $shippingCostList[] = (float)$rate->get_cost();
            }
        }
        sort($shippingCostList);
    }
    if ($shippingCostList[0]){
        $tempShippingCost = $shippingCostList[0];
    }

    if (!empty($custom_productId)) {
        $_id = get_post_meta($prod->get_id(), $custom_productId, 1);

        if (!empty($_id)) {
            $product_id = $_id;
        }
    }

    $split_color_variation = false;

    if (!empty($variable_extra)) {
        $split_color_variation = true;
        $product_id = $variable_extra['id'] ? $variable_extra['id'] : $prod->get_id();
        $colorTerm = get_term_by('slug', $variable_extra[$spotter_atts_color], $spotter_atts_color);
    }

    $format_price = false;
    if (function_exists('wc_get_price_decimal_separator') && function_exists('wc_get_price_thousand_separator') && function_exists('wc_get_price_decimals')) {
        $decimal_separator = wc_get_price_decimal_separator();
        $thousand_separator = wc_get_price_thousand_separator();
        $decimals = wc_get_price_decimals();
        $format_price = true;
    }
    $xml_rows = array();

    $stockstatus_ds = $prod->get_stock_status();
    if ((strcmp($stockstatus_ds, "outofstock") == 0) & ($ifoutofstock == 1)) {
        return;
    }
    $onfeed = $prod->get_meta('onfeed');
    if (strcmp($onfeed, "no") == 0) {
        return;
    }
    $xml_rows[$product_id] = array(
        'onfeed' => $onfeed,
        'stockstatus' => $stockstatus_ds,
        'attributes' => $attributes
    );
    switch ($instockavailability) {
        case 3:
            $_product_attributes_ser_ds = $attributes;

            if (is_serialized($_product_attributes_ser_ds)) {
                $_product_attributes = unserialize($_product_attributes_ser_ds);
                foreach ($_product_attributes as $key => $attr) {
                    if ($attr['name'] == 'Διαθεσιμότητα') {
                        $availabilityST = $attr['value'];
                        break;
                    }
                }
            } else if (is_array($_product_attributes_ser_ds) && !empty($_product_attributes_ser_ds)) {

                foreach ($_product_attributes_ser_ds as $key => $attr) {
                    if ($key == 'pa_availability') {
                        $availabilityST = $prod->get_attribute($key);
                        break;
                    }
                }
            }
            break;
        case 4:
            $tmp_availability = $prod->get_meta('_custom_availability');
            if ($tmp_availability != '') {
                $availabilityST = $tmp_availability;
            }
            break;
        default:
            break;
    }
    $xml_rows[$product_id]['availabilityST'] = $availabilityST == 'attribute' ? '' : $availabilityST;


    $tax = 0;
    if ($include_tax) {
        $price = wc_get_price_excluding_tax($prod);
        $price = floatval($price);
        $tax_rates = WC_Tax::get_base_tax_rates($prod->get_tax_class());
        $taxes = WC_Tax::calc_tax($price, $tax_rates, false, false);
        if (!empty($tax_rates)) {
            foreach ($taxes as $taxes => $tax) {
                $price += $tax;
            }
        } else {
            $price = $prod->get_price();
        }

    } else {
        $price = $prod->get_price();
    }

    $xml_rows[$product_id]['price_raw'] = $price;
    $doNotAddThis = false;
    if ($minimumPrice !== null && (float)$xml_rows[$product_id]['price_raw'] < $minimumPrice) {
        $doNotAddThis = true;
    }
    if ($format_price && $price != '') {
        $price = number_format(floatval($price), $decimals, '.', '');
    }
    $xml_rows[$product_id]['shipping_cost'] = null;
    if ($freeShippingPrice !== null && (float)$xml_rows[$product_id]['price_raw'] >= $freeShippingPrice) {
        $xml_rows[$product_id]['shipping_cost'] = 0;
    } elseif ($tempShippingCost !== null){
        $xml_rows[$product_id]['shipping_cost'] = number_format(floatval($tempShippingCost), $decimals, '.', '');
    }
    $xml_rows[$product_id]['price'] = addslashes($price);
    $image_ds = get_the_post_thumbnail_url($prod->get_id(), 'shop_catalog');
    $xml_rows[$product_id]['image_ds'] = $image_ds;
    $image_big = get_the_post_thumbnail_url($prod->get_id(), 'shop_single_image_size');
    $xml_rows[$product_id]['image_big'] = $image_big;
    $skus_ds = $prod->get_sku();
    $xml_rows[$product_id]['skus_ds'] = $skus_ds;
    $categories_ds = $prod->get_category_ids();
    $_weight_ds = $prod->get_weight();
    $xml_rows[$product_id]['_weight_ds'] = $_weight_ds;

    if ($enable_gtin && !empty($gtin_value)) {
        $val = get_post_meta($prod->get_id(), $gtin_value, 1);
        $xml_rows[$product_id]['gtin'][$gtin_label] = !empty($val) ? $val : '';
    }
    $gallery_ids = $prod->get_gallery_image_ids('view');
    if (!empty($gallery_ids)) {
        $xml_rows[$product_id]['additional_image'] = array();
        foreach ($gallery_ids as $id) {
            $xml_rows[$product_id]['additional_image'][] = wp_get_attachment_url($id, 'full');
        }
    }
    $sizestring = '';
    $xml_rows[$product_id]['sizes'] = array();

    if ($split_color_variation) {
        if (isset($variable_extra['image']) && !empty($variable_extra['image'])) {
            $var_image = $variable_extra['image'];
        } else {
            $var_image = wp_get_attachment_url($prod->get_image_id());
        }
        $xml_rows[$product_id]['image_big'] = $var_image;

        if (isset($variable_extra[$spotter_atts_size])) {
            $sizes_temp = array();
            foreach ($variable_extra[$spotter_atts_size] as $size_term) {
                $termObj = get_term_by('slug', $size_term, $spotter_atts_size);
                $sizes_temp[] = spotter_format_number_spotter($termObj->name);
            }
            $xml_rows[$product_id]['sizes'] = array_unique($sizes_temp);
            $sizestring = implode(', ', $xml_rows[$product_id]['sizes']);
        }
    } else {
        if (count($variation_atts[$spotter_atts_size]) > 0) {
            $sizes_temp = array();
            foreach ($variation_atts[$spotter_atts_size] as $size_term) {
                $termObj = get_term_by('slug', $size_term, $spotter_atts_size);
                $sizes_temp[] = spotter_format_number_spotter($termObj->name);
            }
            $xml_rows[$product_id]['sizes'] = array_unique($sizes_temp);
            $sizestring = implode(', ', $xml_rows[$product_id]['sizes']);
        } else {
            if (isset($attributes[$spotter_atts_size]) && $attributes[$spotter_atts_size] != null) {
                $sizes = $attributes[$spotter_atts_size]->get_terms();
                $sizes_temp = array();
                foreach ($sizes as $i => $size_term) {
                    $sizes_temp[] = spotter_format_number_spotter($size_term->name);
                }
                $xml_rows[$product_id]['sizes'] = array_unique($sizes_temp);
                $sizestring = implode(', ', $xml_rows[$product_id]['sizes']);
            }
        }
    }
    $xml_rows[$product_id]['sizestring'] = $sizestring;
    $man = '';

    if (isset($attributes[$spotter_atts_manuf]) && $attributes[$spotter_atts_manuf] != null) {
        $brands = $attributes[$spotter_atts_manuf]->get_terms();
        foreach ($brands as $brand_term) {
            $man = $brand_term->name;
        }
    } else if ($spotter_atts_manuf !== 'brand') {
        $terms = wp_get_object_terms($prod->get_id(), $spotter_atts_manuf, array("fields" => "all"));
        if (!is_wp_error($terms)) {
            if (!empty($terms)) {
                $man = $terms[0]->name;
            }
        }

    }

    $xml_rows[$product_id]['manufacturer'] = $man;
    $colorRes = '';
    $xml_rows[$product_id]['colors'] = array();

    if ($split_color_variation) {
        $xml_rows[$product_id]['colorstring'] = $colorTerm->name;
    } else {
        if (count($variation_atts[$spotter_atts_color]) > 0) {
            $colors_temp = array();
            foreach ($variation_atts[$spotter_atts_color] as $color_term) {
                $colorTerm = get_term_by('slug', $color_term, $spotter_atts_color);
                $colors_temp[] = $colorTerm->name;
            }
            $xml_rows[$product_id]['colors'] = array_unique($colors_temp);
            $colorRes = implode(', ', $xml_rows[$product_id]['colors']);
        } else {
            if (isset($attributes[$spotter_atts_color]) && $attributes[$spotter_atts_color] != null) {
                $colors = $attributes[$spotter_atts_color]->get_terms();
                $colors_temp = array();

                foreach ($colors as $color_term) {
                    $colors_temp[] = $color_term->name;
                    // $colorRes .= $color_term->name . ', ';
                }
                $xml_rows[$product_id]['colors'] = array_unique($colors_temp);
                $colorRes = implode(', ', $xml_rows[$product_id]['colors']);

            }
        }
        $xml_rows[$product_id]['colorstring'] = $colorRes;
    }
    $xml_rows[$product_id]['terms'] = array();

//    foreach ($featureslist as $key => $feature) {
//        $xml_rows[$product_id]['terms'][$key] = array();
//        if (isset($attributes[$feature])) {
//            $prod_terms = $attributes[$feature]->get_terms();
//            if (is_array($prod_terms)) {
//                foreach ($prod_terms as $the_term) {
//                    $xml_rows[$product_id]['terms'][$feature][$the_term->slug] = $the_term->name;
//                }
//            }
//        }
//    }
    $xml_rows[$product_id]['categories'] = array();
    $category_path = '';
    $categories_list = array();

    $prod_category_tree = get_the_terms($prod->get_id(), 'product_cat');
    if (empty($prod_category_tree) && $split_color_variation && !empty($parent)) {

        $prod_category_tree = get_the_terms($parent->get_id(), 'product_cat');
    }

    if (!empty($prod_category_tree)) {
        array_push($categories_list, __('Home', 'spotter-w-feed'));
        $subcategories = array_filter($prod_category_tree, "spotter_is_subcategory");

        if (!empty($subcategories)) {
            $only_one_cat = end($subcategories);
        } else {
            $only_one_cat = end($prod_category_tree);
        }

        $get_tree = array_reverse(get_ancestors($only_one_cat->term_id, 'product_cat', 'taxonomy'));

        foreach ($get_tree as $key => $parentCat) {
            $term = get_term_by('id', $parentCat, 'product_cat');
            array_push($categories_list, $term->name);
        }
        array_push($categories_list, $only_one_cat->name);
        $category_path = implode(', ', $categories_list);
        $xml_rows[$product_id]['category_id'] = $only_one_cat->term_id;
    }
    $xml_rows[$product_id]['category_path'] = $category_path;
    $title = str_replace("'", " ", $prod->get_title());
    $title = str_replace("&", "+", $title);
    $title = strip_tags($title);
    if ($split_color_variation) {
        $xml_rows[$product_id]['title'] = $title . ' ' . $colorTerm->name;
        $xml_rows[$product_id]['link'] = $variable_extra['link'];
    } else {
        $xml_rows[$product_id]['title'] = $title;
        $xml_rows[$product_id]['link'] = get_permalink($prod->get_id());
    }
    $backorder = $prod->get_backorders();
    $xml_rows[$product_id]['backorder'] = $backorder;
    $xml_rows[$product_id]['descr'] = $prod->get_short_description();
    if (empty($xml_rows[$product_id]['descr']) && !empty($parent)) {
        $xml_rows[$product_id]['descr'] = $parent->get_short_description();
    }
    if ($doNotAddThis === true) {
        return null;
    }
    return $xml_rows;
}

function spotter_generate_products_xml_data_new()
{
    //********* Start of initialization of xml files *****************/
    if (!defined('ABSPATH'))
        exit; // Exit if accessed directly
    require_once('simplexml.php');
    global $wpdb;

    $now = date('Y-n-j G:i');

    /*******************************
     ******** spotter.com.mt *********
     ******************************/

    if (!file_exists(wp_upload_dir()['basedir'] . '/spotter')) {
        wp_mkdir_p(wp_upload_dir()['basedir'] . '/spotter');
    }

    if (!file_exists(wp_upload_dir()['basedir'] . '/spotter/spotter.xml')) {
        touch(wp_upload_dir()['basedir'] . '/spotter/spotter.xml');
    }

    if (file_exists(wp_upload_dir()['basedir'] . '/spotter/spotter.xml')) {
        $xmlFilespotter = wp_upload_dir()['basedir'] . '/spotter/spotter.xml';
    } else {
        echo "Could not create spotter file.";
    }

    $xmlspotter = new spottercommt_SimpleXMLExtended('<?xml version="1.0" encoding="utf-8"?><webstore/>');
    // $now = date('Y-n-j G:i');
    $xmlspotter->addChild('created_at', "$now");
    $productsspotter = $xmlspotter->addChild('products');


    //********* End of initialization of xml files *****************/

    $xml_rows = array();
    $instockavailability = get_option('instockavailability');

    $avaibilities = array(
        __('Available in store / Delivery 1 to 3 days', 'spotter-w-feed'),
        __('Delivery 1 to 3 days', 'spotter-w-feed'),
        __('Delivery 4 to 10 days', 'spotter-w-feed'),
        __('attribute', 'spotter-w-feed'));

    $availabilityST = $avaibilities[$instockavailability];
    $ifoutofstock = get_option('ifoutofstock');
    $format_price = false;
    if (function_exists('wc_get_price_decimal_separator') && function_exists('wc_get_price_thousand_separator') && function_exists('wc_get_price_decimals')) {
        $decimal_separator = wc_get_price_decimal_separator();
        $thousand_separator = wc_get_price_thousand_separator();
        $decimals = wc_get_price_decimals();
        $format_price = true;
    }

    $spotter_atts_color = get_option('spotter_atts_color', 'pa_color');
    $spotter_atts_size = get_option('spotter_atts_size', 'pa_size');
    $spotter_atts_manuf = get_option('spotter_atts_manuf', 'brand');
    $enable_gtin = get_option('enable_gtin', false);
    $include_tax = get_option('include_tax', false);
    $group_variations = get_option('group_variations', false);
    $gtin_label = get_option('gtin_label', 'ean');
    $gtin_value = get_option('gtin_value', '');
    $cats_excluded = get_option('exclude_cats', []);
    $prods_excluded_by_price = get_option('exclude_products_min_price', null);
    $offer_free_shipping = get_option('offer_free_shipping', null);
    $custom_productId = get_option('custom_productId');
    $minimumPrice = null;
    if ($prods_excluded_by_price !== null) {
        $minimumPrice = (float)$prods_excluded_by_price;
    }
    $freeShippingPrice = null;
    if ($offer_free_shipping !== null) {
        $freeShippingPrice = (float)$offer_free_shipping;
    }
    $i = 1;
    try {
        do {
            $queryParams = array(
                'status' => array('publish'),
                'limit' => 300,
                'paginate' => true,
                'page' => $i,
            );
            $query = new WC_Product_Query($queryParams);


            if (count($cats_excluded) > 0) {
                $query->set('tax_query', array(array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $cats_excluded,
                    'operator' => ('NOT IN'))));
            }
            $result = $query->get_products();
            $color_term_ids = array();
            foreach ($result->products as $index => $prod) {
                $availabilityST = $avaibilities[$instockavailability];
                $available_variations = array();
                $attributes = $prod->get_attributes();
                $group_colors = false;
                $variable_products = [];

                if ($prod->get_type() == 'variable') {
                    $available_variations = $prod->get_available_variations();
                    $variation_prices = $prod->get_variation_prices();
                    if (isset($attributes[$spotter_atts_color]) && !empty($attributes[$spotter_atts_color])) {
                        $group_colors = count($attributes[$spotter_atts_color]['data']['options']) > 1 ? true : false;
                    }
                }

                $variation_atts = array($spotter_atts_color => array(), $spotter_atts_size => array());

                foreach ($available_variations as $var) {
                    $var_product = wc_get_product($var['variation_id']);
                    $var_stock_status = $var_product->get_stock_status();

                    if (isset($var_stock_status) && $var_stock_status == 'outofstock') {
                        continue;
                    }
                    // old one - legacy
                    if (isset($var['stock_status']) && $var['stock_status'] == 'outofstock') {
                        continue;
                    }

                    $atts = $var['attributes'];
                    if (isset($atts['attribute_' . $spotter_atts_size]) && $atts['attribute_' . $spotter_atts_size] != '') {
                        $variation_atts[$spotter_atts_size][] = $atts['attribute_' . $spotter_atts_size];
                    }

                    if (isset($atts['attribute_' . $spotter_atts_color]) && $atts['attribute_' . $spotter_atts_color] != '') {
                        $variation_atts[$spotter_atts_color][] = $atts['attribute_' . $spotter_atts_color];
                    }
                    if ($group_variations && $group_colors) {
                        if (isset($var['attributes']['attribute_' . $spotter_atts_color])) {
                            if (!isset($color_term_ids[$var['attributes']['attribute_' . $spotter_atts_color]])) {
                                $color_term_ids[$var['attributes']['attribute_' . $spotter_atts_color]] = get_term_by('slug', $var['attributes']['attribute_' . $spotter_atts_color], $spotter_atts_color)->term_id;
                            }
                            $varId = $prod->get_id() . '-' . $color_term_ids[$var['attributes']['attribute_' . $spotter_atts_color]];
                            if (!isset($variable_products[$varId])) {
                                $variable_products[$varId]['id'] = $varId;
                                $variable_products[$varId][$spotter_atts_color] = $var['attributes']['attribute_' . $spotter_atts_color];

                                if (isset($var['attributes']['attribute_' . $spotter_atts_size])) {
                                    $variable_products[$varId][$spotter_atts_size][] = $var['attributes']['attribute_' . $spotter_atts_size];

                                }

                                if (!empty($var['image'])) {
                                    $variable_products[$varId]['image'] = $var['image']['url'];
                                }
                                $variable_products[$varId]['price'] = $var['display_price']; //needs to check for taxes
                                $variable_products[$varId]['link'] = get_permalink($prod->get_id()) . '?attribute_' . $spotter_atts_color . '=' . $var['attributes']['attribute_' . $spotter_atts_color];
                                $variable_products[$varId]['product'] = $var_product;

                            } else {
                                if (isset($var['attributes']['attribute_' . $spotter_atts_size])) {
                                    $variable_products[$varId][$spotter_atts_size][] = $var['attributes']['attribute_' . $spotter_atts_size];
                                }
                                if (!isset($variable_products[$varId]['image']) && !empty($var['image'])) {
                                    $variable_products[$varId]['image'] = $var['image']['url'];
                                }

                                if ($variable_products[$varId]['price'] > $var['display_price']) {
                                    $variable_products[$varId]['price'] = $var['display_price'];
                                    $variable_products[$varId]['product'] = $var_product;
                                }
                            }

                        }
                    }
                }
                if (!empty($variable_products)) {
                    foreach ($variable_products as $key => $product) {
                        $xml = spotter_xml_schema($product['product'], $group_variations, $instockavailability, $avaibilities, $availabilityST, $ifoutofstock, $custom_productId, $spotter_atts_color, $spotter_atts_size, $spotter_atts_manuf, $enable_gtin, $include_tax, $gtin_label, $gtin_value, $variation_atts, $attributes, $product, $prod, $minimumPrice, $freeShippingPrice);
                        if ($xml !== null) {
                            spotter_write_spotter_xml($xml, $productsspotter);
                        }
//                        write_bestprice_xml($xml, $productsBestprice, $featureslist);
                    }
                    continue; // no need to do somthing more
                }
                $xml = spotter_xml_schema($prod, $group_variations, $instockavailability, $avaibilities, $availabilityST, $ifoutofstock, $custom_productId, $spotter_atts_color, $spotter_atts_size, $spotter_atts_manuf, $enable_gtin, $include_tax, $gtin_label, $gtin_value, $variation_atts, $attributes, [], [], $minimumPrice, $freeShippingPrice);
                if ($xml !== null) {
                    spotter_write_spotter_xml($xml, $productsspotter);
                }
//                write_bestprice_xml($xml, $productsBestprice, $featureslist);

            }
            $i++;
        } while ($i <= $result->max_num_pages);
    } catch (Exception $e) {
        echo 'Caught exception: ', $e->getMessage(), "\n";
    }

    /**** end processes of xml creation  */
    echo '</br>' . __('SUCCESSFUL CREATION OF spotter.com.mt XML', 'spotter-w-feed') . '</br>';
    $xmlspotter->saveXML($xmlFilespotter);
    echo __('The file is located at', 'spotter-w-feed') . ' <a href="' . wp_upload_dir()['baseurl'] . '/spotter/spotter.xml" target="_blank">' . wp_upload_dir()['baseurl'] . '/spotter/spotter.xml</a>';
    update_option('last_update', date('d-m-Y H:i'));

    return $xml_rows;

}

function spotter_write_spotter_xml($prod, $products)
{
    foreach ($prod as $prod_id => $row) {
        $product = $products->addChild('product');

        $product->sku = NULL;
        $product->sku->addCData(addslashes(trim($row['skus_ds']) == '' ? $prod_id : $row['skus_ds']));

        if (isset($row['gtin'])) {
            $label = array_keys($row['gtin'])[0];
            $product->addChild($label)->addCData($row['gtin'][$label]);
        }

        $product->addChild('uid', $prod_id);
        $product->name = NULL;
        $product->name->addCData($row['title']);
        $product->link = NULL;
        $product->link->addCData($row['link']);

        $product->image = NULL;
        $product->image->addCData($row['image_big']);

        $product->category = NULL;
        $product->category->addCData($row['category_path']);
        if (isset($row['additional_image'])) {
            foreach ($row['additional_image'] as $id) {
                $product->addChild('additional_image')->addCData($id);
            }
        }
        $product->addChild('price_with_vat', $row['price']);

//        $product->shipping_cost = NULL;
        if ($row['shipping_cost'] !== null) {
            $product->addChild('shipping_cost', $row['shipping_cost']);

        }


        if (strcmp($row['stockstatus'], "instock") == 0) {
            $product->addChild('instock', "Y");
            $product->addChild('availability', $row['availabilityST']);
        } else {

            if (strcmp($row['backorder'], "notify") == 0) {
                $product->addChild('instock', "N");
                $product->addChild('availability', __('Upon order', 'spotter-w-feed'));
            } else if (strcmp($row['backorder'], "yes") == 0) {
                $product->addChild('instock', "Y");
                $product->addChild('availability', $row['availabilityST']);
            } else {
                $product->addChild('instock', "N");
            }
        }
        $product->addChild('size', $row['sizestring']);

        $product->manufacturer = NULL;
        $product->manufacturer->addCData($row['manufacturer']);

        if ($row['colorstring'] != '') {
            $product->color = NULL;
            $product->color->addCData($row['colorstring']);
        }
        $product->addChild('weight', floatval($row['_weight_ds']) > 0 ? round(floatval($row['_weight_ds']) * 1000) : 0);
    }
}

function spotter_format_number_spotter($pa_size)
{
    return str_replace(',', '.', $pa_size);
}

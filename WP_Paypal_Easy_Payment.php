<?php
/*
Plugin Name: Wordprress Paypal Easy Payment
Version: 1.0
Plugin URI: https://github.com/Last-Hash/wordpress-easy-paypal-payment
Author: Shiv Singh
Author URI: https://www.shivsingh.net
Description: Easy to use Wordpress plugin to accept paypal payment for a service or product or donation in one click. Can be used in the sidebar, posts and pages.
License: GPL2
*/

//Slug - wppep

if (!defined('ABSPATH')) {//Exit if accessed directly
    exit;
}

define('WP_PAYPAL_EASY_PAYMENT_PLUGIN_VERSION', '1.0');
define('WP_PAYPAL_EASY_PAYMENT_PLUGIN_URL', plugins_url('', __FILE__));

include_once('shortcode_view.php');
include_once('wppep_admin_menu.php');
include_once('wppep_paypal_utility.php');

function wppep_plugin_install()
{
    // Some default options
    add_option('wppep_payment_email', get_bloginfo('admin_email'));
    add_option('paypal_payment_currency', 'USD');
    add_option('wppep_payment_subject', 'Plugin Service Payment');
    add_option('wppep_payment_item1', 'Basic Service - $10');
    add_option('wppep_payment_value1', '10');
    add_option('wppep_payment_item2', 'Gold Service - $20');
    add_option('wppep_payment_value2', '20');
    add_option('wppep_payment_item3', 'Platinum Service - $30');
    add_option('wppep_payment_value3', '30');
    add_option('wp_paypal_widget_title_name', 'Paypal Payment');
    add_option('payment_button_type', 'https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif');
    add_option('wppep_show_other_amount', '-1');
    add_option('wppep_show_ref_box', '1');
    add_option('wppep_ref_title', 'Your Email Address');
    add_option('wppep_return_url', home_url());
}

register_activation_hook(__FILE__, 'wppep_plugin_install');

add_shortcode('wp_easy_paypal_payment_box_for_any_amount', 'wppep_buy_now_any_amt_handler');

function wppep_buy_now_any_amt_handler($args)
{
    $output = wppp_render_paypal_button_with_other_amt($args);
    return $output;
}

add_shortcode('wp_easy_paypal_payment_box', 'wppep_buy_now_button_shortcode');

function wppep_buy_now_button_shortcode($args)
{
    ob_start();
    wppp_render_paypal_button_form($args);
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

function Paypal_payment_accept()
{
    $paypal_email = get_option('wppep_payment_email');
    $payment_currency = get_option('paypal_payment_currency');
    $paypal_subject = get_option('wppep_payment_subject');

    $itemName1 = get_option('wppep_payment_item1');
    $value1 = get_option('wppep_payment_value1');
    $itemName2 = get_option('wppep_payment_item2');
    $value2 = get_option('wppep_payment_value2');
    $itemName3 = get_option('wppep_payment_item3');
    $value3 = get_option('wppep_payment_value3');
    $itemName4 = get_option('wppep_payment_item4');
    $value4 = get_option('wppep_payment_value4');
    $itemName5 = get_option('wppep_payment_item5');
    $value5 = get_option('wppep_payment_value5');
    $itemName6 = get_option('wppep_payment_item6');
    $value6 = get_option('wppep_payment_value6');
    $payment_button = get_option('payment_button_type');
    $wppep_show_other_amount = get_option('wppep_show_other_amount');
    $wppep_show_ref_box = get_option('wppep_show_ref_box');
    $wppep_ref_title = get_option('wppep_ref_title');
    $wppep_return_url = get_option('wppep_return_url');

    /* === Paypal form === */
    $output = '';
    $output .= '<div id="accept_paypal_payment_form">';
    $output .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="wp_accept_pp_button_form_classic">';
    $output .= '<input type="hidden" name="cmd" value="_xclick" />';
    $output .= '<input type="hidden" name="business" value="' . esc_attr($paypal_email) . '" />';
    $output .= '<input type="hidden" name="item_name" value="' . esc_attr($paypal_subject) . '" />';
    $output .= '<select name="currency_code">
<option value="USD">USD US Dollar</option>
<option value="GBP">GBP Pound Sterling</option>
<option value="EUR">EUR Euro</option>
<option value="AUD">AUD Australian Dollar</option>
<option value="CAD">CAD Canadian Dollar</option>
<option value="NZD">NZD New Zealand Dollar</option>
<option value="HKD">HKD Hong Kong Dollar </option>
</select>';
    $output .= '<div class="wppep_payment_subject"><span class="payment_subject"><strong>' . esc_attr($paypal_subject) . '</strong></span></div>';
    $output .= '<select id="amount" name="amount" class="">';
    $output .= '<option value="' . esc_attr($value1) . '">' . esc_attr($itemName1) . '</option>';
    if (!empty($value2)) {
        $output .= '<option value="' . esc_attr($value2) . '">' . esc_attr($itemName2) . '</option>';
    }
    if (!empty($value3)) {
        $output .= '<option value="' . esc_attr($value3) . '">' . esc_attr($itemName3) . '</option>';
    }
    if (!empty($value4)) {
        $output .= '<option value="' . esc_attr($value4) . '">' . esc_attr($itemName4) . '</option>';
    }
    if (!empty($value5)) {
        $output .= '<option value="' . esc_attr($value5) . '">' . esc_attr($itemName5) . '</option>';
    }
    if (!empty($value6)) {
        $output .= '<option value="' . esc_attr($value6) . '">' . esc_attr($itemName6) . '</option>';
    }

    $output .= '</select>';

    // Show other amount text box
    if ($wppep_show_other_amount == '1') {
        $output .= '<div class="wppep_other_amount_label"><strong>Other Amount:</strong></div>';
        $output .= '<div class="wppep_other_amount_input"><input type="number" min="1" step="any" name="other_amount" title="Other Amount" value="" class="wppep_other_amt_input" style="max-width:80px;" /></div>';
    }

    // Show the reference text box
    if ($wppep_show_ref_box == '1') {
        $output .= '<div class="wppep_ref_title_label"><strong>' . esc_attr($wppep_ref_title) . ':</strong></div>';
        $output .= '<input type="hidden" name="on0" value="' . apply_filters('wppep_button_reference_name', 'Reference') . '" />';
        $output .= '<div class="wppep_ref_value"><input type="text" name="os0" maxlength="60" value="' . apply_filters('wppep_button_reference_value', '') . '" class="wppep_button_reference" /></div>';
    }

    $output .= '<input type="hidden" name="no_shipping" value="0" /><input type="hidden" name="no_note" value="1" /><input type="hidden" name="bn" value="TipsandTricks_SP" />';

    if (!empty($wppep_return_url)) {
        $output .= '<input type="hidden" name="return" value="' . esc_url($wppep_return_url) . '" />';
    } else {
        $output .= '<input type="hidden" name="return" value="' . home_url() . '" />';
    }

    $output .= '<div class="wppep_payment_button">';
    $output .= '<input type="image" src="' . esc_url($payment_button) . '" name="submit" alt="Make payments with payPal - it\'s fast, free and secure!" />';
    $output .= '</div>';

    $output .= '</form>';
    $output .= '</div>';
    $output .= <<<EOT
<script type="text/javascript">
jQuery(document).ready(function($) {
    $('.wp_accept_pp_button_form_classic').submit(function(e){
        var form_obj = $(this);
        var other_amt = form_obj.find('input[name=other_amount]').val();
        if (!isNaN(other_amt) && other_amt.length > 0){
            options_val = other_amt;
            //insert the amount field in the form with the custom amount
            $('<input>').attr({
                type: 'hidden',
                id: 'amount',
                name: 'amount',
                value: options_val
            }).appendTo(form_obj);
        }		
        return;
    });
});
</script>
EOT;
    /* = end of paypal form = */
    return $output;
}

function wppepp_process($content)
{
    if (strpos($content, "<!-- wp_easy_paypal_payment -->") !== FALSE) {
        $content = preg_replace('/<p>\s*<!--(.*)-->\s*<\/p>/i', "<!--$1-->", $content);
        $content = str_replace('<!-- wp_easy_paypal_payment -->', Paypal_payment_accept(), $content);
    }
    return $content;
}

function show_wp_easy_paypal_payment_widget($args)
{
    extract($args);

    $wp_easy_paypal_payment_widget_title_name_value = get_option('wp_paypal_widget_title_name');
    echo $before_widget;
    echo $before_title . $wp_easy_paypal_payment_widget_title_name_value . $after_title;
    echo Paypal_payment_accept();
    echo $after_widget;
}

function wp_easy_paypal_payment_widget_control()
{
    ?>
    <p>
        <? _e("Set the Plugin Settings from the Settings menu"); ?>
    </p>
    <?php
}

function wp_easy_paypal_payment_init()
{
    wp_register_style('wppep-styles', WP_PAYPAL_EASY_PAYMENT_PLUGIN_URL . '/wppep-styles.css');
    wp_enqueue_style('wppep-styles');

    //Widget code
    $widget_options = array('classname' => 'widget_wp_easy_paypal_payment', 'description' => __("Display WP Paypal Payment."));
    wp_register_sidebar_widget('wp_easy_paypal_payment_widgets', __('WP Paypal Payment'), 'show_wp_easy_paypal_payment_widget', $widget_options);
    wp_register_widget_control('wp_easy_paypal_payment_widgets', __('WP Paypal Payment'), 'wp_easy_paypal_payment_widget_control');

    //Listen for IPN and validate it
    if (isset($_REQUEST['wppep_paypal_ipn']) && $_REQUEST['wppep_paypal_ipn'] == "process") {
        wppep_validate_paypl_ipn();
        exit;
    }
}

function wppep_shortcode_plugin_enqueue_jquery()
{
    wp_enqueue_script('jquery');
}

add_filter('the_content', 'wppepp_process');
add_shortcode('wp_easy_paypal_payment', 'Paypal_payment_accept');
if (!is_admin()) {
    add_filter('widget_text', 'do_shortcode');
}

add_action('init', 'wppep_shortcode_plugin_enqueue_jquery');
add_action('init', 'wp_easy_paypal_payment_init');

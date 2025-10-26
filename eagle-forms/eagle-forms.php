<?php
/**
 * Plugin Name: Eagle Forms
 * Plugin URI:  https://example.com/eagle-forms
 * Description: Form builder inspired by WPForms that lets you create custom contact forms and manage entries.
 * Version:     1.0.0
 * Author:      Eagle Team
 * Author URI:  https://example.com
 * Text Domain: eagle-forms
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'EAGLE_FORMS_PATH' ) ) {
    define( 'EAGLE_FORMS_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'EAGLE_FORMS_URL' ) ) {
    define( 'EAGLE_FORMS_URL', plugin_dir_url( __FILE__ ) );
}

require_once EAGLE_FORMS_PATH . 'includes/class-eagle-forms.php';

/**
 * Bootstrap the plugin.
 */
function eagle_forms() {
    return \EagleForms\Plugin::instance();
}

eagle_forms();

<?php
/**
 * Plugin Name: Nice SIM
 * Plugin URI: https://danhtrong.com
 * Description: Plugin tra cứu sim số đẹp theo phong thủy - Yêu cầu giấy phép hợp lệ để sử dụng
 * Version: 1.0.0
 * Author: Trần Danh Trọng
 * Author URI: https://danhtrong.com
 * Text Domain: nice-sim
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('NICE_SIM_VERSION', '1.0.0');
define('NICE_SIM_PATH', plugin_dir_path(__FILE__));
define('NICE_SIM_URL', plugin_dir_url(__FILE__));

// Uncomment this line during development to bypass file checksum validation
// define('NICE_SIM_DEV_MODE', true);

// Include security file first
require_once NICE_SIM_PATH . 'includes/plugin-security.php';

// Include files
require_once NICE_SIM_PATH . 'includes/class-nice-sim.php';
require_once NICE_SIM_PATH . 'includes/class-nice-sim-license.php';
require_once NICE_SIM_PATH . 'includes/class-nice-sim-updater.php';

// Initialize the license manager
function nice_sim_license_init() {
    return Nice_SIM_License::instance();
}
add_action('plugins_loaded', 'nice_sim_license_init', 9);

// Initialize the updater
function nice_sim_updater_init() {
    return Nice_SIM_Updater::instance();
}
add_action('plugins_loaded', 'nice_sim_updater_init', 9);

// Initialize the plugin
function nice_sim_init() {
    $plugin = new Nice_SIM();
    $plugin->init();
}
add_action('plugins_loaded', 'nice_sim_init', 10);

// Activation hook
register_activation_hook(__FILE__, 'nice_sim_activate');
function nice_sim_activate() {
    // Create tables or perform other activation tasks
    
    // Store file checksums for security validation
    nice_sim_store_file_checksums();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'nice_sim_deactivate');
function nice_sim_deactivate() {
    // Clean up tasks on deactivation
    Nice_SIM_Updater::deactivate();
}

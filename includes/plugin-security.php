<?php
/**
 * Plugin security functions
 *
 * @package Nice_SIM
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit('Truy cập trực tiếp bị từ chối.');
}

/**
 * Validates the plugin environment and license.
 * 
 * @return bool
 */
function nice_sim_validate_environment() {
    // Basic validation checks
    if (!function_exists('add_action')) {
        return false;
    }
    
    // Check for potential hacking attempts
    if (nice_sim_detect_tampering()) {
        return false;
    }
    
    return true;
}

/**
 * Detect if critical plugin files have been tampered with.
 * 
 * @return bool True if tampering detected, false otherwise.
 */
function nice_sim_detect_tampering() {
    // Calculate checksums of critical files
    $main_file = NICE_SIM_PATH . 'nice-sim.php';
    $license_file = NICE_SIM_PATH . 'includes/class-nice-sim-license.php';
    
    // If files don't exist, that's a problem
    if (!file_exists($main_file) || !file_exists($license_file)) {
        return true;
    }
    
    // If we're in development mode, don't check file checksums
    if (defined('NICE_SIM_DEV_MODE') && NICE_SIM_DEV_MODE) {
        return false;
    }
    
    // Get current checksums
    $main_checksum = md5_file($main_file);
    $license_checksum = md5_file($license_file);
    
    // Get stored checksums (these would be set during plugin activation)
    $stored_main_checksum = get_option('nice_sim_main_checksum');
    $stored_license_checksum = get_option('nice_sim_license_checksum_file');
    
    // If we don't have stored checksums yet, store them now
    if (empty($stored_main_checksum) || empty($stored_license_checksum)) {
        update_option('nice_sim_main_checksum', $main_checksum);
        update_option('nice_sim_license_checksum_file', $license_checksum);
        return false;
    }
    
    // Check if the files have been modified
    return ($main_checksum !== $stored_main_checksum || $license_checksum !== $stored_license_checksum);
}

/**
 * Disable plugin functionality if security checks fail.
 */
function nice_sim_maybe_disable_plugin() {
    // If our environment validation fails, disable the plugin
    if (!nice_sim_validate_environment()) {
        // Remove shortcode functionality
        add_shortcode('nice_sim_search', 'nice_sim_disabled_shortcode');
        
        // Add admin notice
        add_action('admin_notices', 'nice_sim_security_notice');
        
        // Disable AJAX endpoints
        remove_action('wp_ajax_nice_sim_check', array('Nice_SIM', 'process_sim_check'));
        remove_action('wp_ajax_nopriv_nice_sim_check', array('Nice_SIM', 'process_sim_check'));
        
        return true;
    }
    
    return false;
}

/**
 * Fallback shortcode when plugin is disabled.
 *
 * @return string
 */
function nice_sim_disabled_shortcode() {
    return '<div class="nice-sim-security-warning">Plugin này đã bị vô hiệu hóa do vấn đề bảo mật. Vui lòng liên hệ quản trị viên plugin.</div>';
}

/**
 * Display admin notice about security issues.
 */
function nice_sim_security_notice() {
    ?>
    <div class="notice notice-error">
        <p><strong><?php esc_html_e('Cảnh báo bảo mật Nice SIM Plugin', 'nice-sim'); ?></strong></p>
        <p><?php esc_html_e('Plugin đã phát hiện các vấn đề bảo mật tiềm ẩn. Chức năng plugin đã bị vô hiệu hóa. Vui lòng cài đặt lại plugin hoặc liên hệ hỗ trợ.', 'nice-sim'); ?></p>
    </div>
    <?php
}

/**
 * Display admin notice about license issues.
 */
function nice_sim_license_notice() {
    // Don't show on the license settings page
    $screen = get_current_screen();
    if ($screen && $screen->id === 'settings_page_nice-sim-license') {
        return;
    }
    
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><strong><?php esc_html_e('Nice SIM Plugin yêu cầu giấy phép', 'nice-sim'); ?></strong></p>
        <p>
            <?php esc_html_e('Vui lòng kích hoạt giấy phép để sử dụng plugin Nice SIM.', 'nice-sim'); ?> 
            <a href="<?php echo esc_url(admin_url('options-general.php?page=nice-sim-license')); ?>" class="button button-small">
                <?php esc_html_e('Kích hoạt giấy phép', 'nice-sim'); ?>
            </a>
        </p>
    </div>
    <?php
}

// Run security checks
add_action('plugins_loaded', 'nice_sim_maybe_disable_plugin', 5);

// Add license notice if needed
function nice_sim_check_license_status() {
    // If the plugin is loaded and license is not valid, show notice
    if (class_exists('Nice_SIM_License')) {
        $license = Nice_SIM_License::instance();
        if (!$license->is_license_valid()) {
            add_action('admin_notices', 'nice_sim_license_notice');
        }
    }
}
add_action('admin_init', 'nice_sim_check_license_status');

/**
 * Store file checksums during plugin activation.
 */
function nice_sim_store_file_checksums() {
    $main_file = NICE_SIM_PATH . 'nice-sim.php';
    $license_file = NICE_SIM_PATH . 'includes/class-nice-sim-license.php';
    
    if (file_exists($main_file) && file_exists($license_file)) {
        update_option('nice_sim_main_checksum', md5_file($main_file));
        update_option('nice_sim_license_checksum_file', md5_file($license_file));
    }
} 
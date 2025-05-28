<?php
/**
 * Updater class for Nice SIM plugin
 *
 * @package Nice_SIM
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin updater class.
 */
class Nice_SIM_Updater {
    /**
     * The single instance of the class.
     *
     * @var Nice_SIM_Updater
     */
    protected static $_instance = null;

    /**
     * Main instance.
     *
     * @return Nice_SIM_Updater
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        // Schedule daily license check
        if (!wp_next_scheduled('nice_sim_daily_license_check')) {
            wp_schedule_event(time(), 'daily', 'nice_sim_daily_license_check');
        }
        
        add_action('nice_sim_daily_license_check', array($this, 'check_license_validity'));
        
        // Check for updates
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_plugin_update'));
    }

    /**
     * Check if license has expired.
     */
    public function check_license_validity() {
        $license = Nice_SIM_License::instance();
        
        // If license is not valid, notify admin
        if (!$license->is_license_valid()) {
            $this->notify_admin_if_needed();
        }
    }

    /**
     * Notify admin about license issues if needed.
     */
    private function notify_admin_if_needed() {
        // Get the last notification time
        $last_notification = get_option('nice_sim_last_license_notification', 0);
        
        // If we've already notified within the past week, don't notify again
        if ((time() - $last_notification) < (7 * DAY_IN_SECONDS)) {
            return;
        }
        
        // Get admin email
        $admin_email = get_option('admin_email');
        
        // Send notification
        $subject = __('Cảnh báo giấy phép Nice SIM', 'nice-sim');
        $message = __('Giấy phép Nice SIM của bạn không hợp lệ hoặc đã hết hạn. Vui lòng cập nhật mã giấy phép của bạn để tiếp tục sử dụng plugin.', 'nice-sim');
        $message .= "\n\n";
        $message .= admin_url('options-general.php?page=nice-sim-license');
        
        wp_mail($admin_email, $subject, $message);
        
        // Update the last notification time
        update_option('nice_sim_last_license_notification', time());
    }

    /**
     * Check for plugin updates.
     *
     * @param object $transient Update transient.
     * @return object
     */
    public function check_for_plugin_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        // Get the plugin path
        $plugin_path = plugin_basename(NICE_SIM_PATH . 'nice-sim.php');
        
        // Get the current version
        $current_version = NICE_SIM_VERSION;
        
        // In a real-world scenario, you would query your server for updates
        // For this example, we'll just hardcode some values
        $update_data = $this->get_update_data();
        
        // If there's an update and our license is valid
        if (version_compare($current_version, $update_data['version'], '<') && Nice_SIM_License::instance()->is_license_valid()) {
            $transient->response[$plugin_path] = (object) array(
                'slug'        => 'nice-sim',
                'new_version' => $update_data['version'],
                'url'         => $update_data['url'],
                'package'     => $update_data['package'],
            );
        }
        
        return $transient;
    }

    /**
     * Get update data.
     * 
     * @return array
     */
    private function get_update_data() {
        // In a real-world scenario, this data would come from your server
        return array(
            'version' => '1.0.1',
            'url'     => 'https://example.com/nice-sim',
            'package' => 'https://example.com/nice-sim/download/nice-sim-1.0.1.zip',
        );
    }
    
    /**
     * Cleanup on plugin deactivation.
     */
    public static function deactivate() {
        // Clear the scheduled event
        wp_clear_scheduled_hook('nice_sim_daily_license_check');
    }
} 
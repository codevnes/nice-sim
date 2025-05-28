<?php
/**
 * License manager class for Nice SIM plugin
 *
 * @package Nice_SIM
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * License manager class.
 */
class Nice_SIM_License {
    /**
     * The single instance of the class.
     *
     * @var Nice_SIM_License
     */
    protected static $_instance = null;

    /**
     * API endpoint URL.
     *
     * @var string
     */
    private $api_url = 'https://license.danhtrong.com/wp-json/acm/v1/validate';

    /**
     * Main instance.
     *
     * @return Nice_SIM_License
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
        // Add admin menu item
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add settings link to plugins page
        add_filter('plugin_action_links_' . plugin_basename(NICE_SIM_PATH . 'nice-sim.php'), array($this, 'add_settings_link'));
    }

    /**
     * Add settings menu item.
     */
    public function add_admin_menu() {
        add_options_page(
            __('Giấy phép Nice SIM', 'nice-sim'),
            __('Giấy phép Nice SIM', 'nice-sim'),
            'manage_options',
            'nice-sim-license',
            array($this, 'render_license_page')
        );
    }

    /**
     * Register settings.
     */
    public function register_settings() {
        register_setting('nice_sim_license', 'nice_sim_license_key', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
        
        register_setting('nice_sim_license', 'nice_sim_app_id', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
        
        register_setting('nice_sim_license', 'nice_sim_api_key', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
    }

    /**
     * Add settings link to plugins page.
     *
     * @param array $links Plugin action links.
     * @return array
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=nice-sim-license') . '">' . __('Cài đặt giấy phép', 'nice-sim') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Render license page.
     */
    public function render_license_page() {
        // Check if we need to clear the license cache
        if (isset($_GET['clear_cache']) && $_GET['clear_cache'] == '1') {
            delete_transient('nice_sim_license_status');
            wp_redirect(remove_query_arg('clear_cache', wp_unslash($_SERVER['REQUEST_URI'])));
            exit;
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Cài đặt giấy phép Nice SIM', 'nice-sim'); ?></h1>
            
            <?php if (isset($_GET['settings-updated'])) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Cài đặt giấy phép đã được cập nhật thành công.', 'nice-sim'); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="options.php">
                <?php settings_fields('nice_sim_license'); ?>
                <?php do_settings_sections('nice_sim_license'); ?>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Mã giấy phép', 'nice-sim'); ?></th>
                        <td>
                            <input type="text" name="nice_sim_license_key" value="<?php echo esc_attr(get_option('nice_sim_license_key')); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Nhập mã giấy phép để kích hoạt plugin (định dạng: ABCD-EFGH-IJKL-MNOP).', 'nice-sim'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('App ID', 'nice-sim'); ?></th>
                        <td>
                            <input type="text" name="nice_sim_app_id" value="<?php echo esc_attr(get_option('nice_sim_app_id')); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Nhập App ID của bạn để xác thực giấy phép.', 'nice-sim'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('API Key', 'nice-sim'); ?></th>
                        <td>
                            <input type="password" name="nice_sim_api_key" value="<?php echo esc_attr(get_option('nice_sim_api_key')); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Nhập API Key của bạn để xác thực giấy phép.', 'nice-sim'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Trạng thái giấy phép', 'nice-sim'); ?></th>
                        <td>
                            <?php
                            $license_status = $this->get_license_status();
                            if ($license_status['status']) : ?>
                                <span class="dashicons dashicons-yes-alt" style="color: green;"></span> 
                                <?php esc_html_e('Đã kích hoạt', 'nice-sim'); ?>
                                <p class="description">
                                    <?php 
                                    if (!empty($license_status['data']['customer_name'])) {
                                        echo sprintf(esc_html__('Cấp phép cho: %s', 'nice-sim'), esc_html($license_status['data']['customer_name']));
                                        echo '<br>';
                                    }
                                    if (!empty($license_status['data']['expiry_date'])) {
                                        echo sprintf(esc_html__('Hết hạn ngày: %s', 'nice-sim'), esc_html($license_status['data']['expiry_date']));
                                    }
                                    ?>
                                </p>
                            <?php else : ?>
                                <span class="dashicons dashicons-no-alt" style="color: red;"></span> 
                                <?php esc_html_e('Chưa kích hoạt', 'nice-sim'); ?>
                                <?php if (!empty($license_status['message'])) : ?>
                                    <p class="description error"><?php echo esc_html($license_status['message']); ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                            <p>
                                <a href="<?php echo esc_url(add_query_arg('clear_cache', '1')); ?>" class="button">
                                    <?php esc_html_e('Làm mới trạng thái giấy phép', 'nice-sim'); ?>
                                </a>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Lưu thay đổi', 'nice-sim')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Check if license is valid.
     *
     * @return bool
     */
    public function is_license_valid() {
        $license_status = $this->get_license_status();
        return $license_status['status'];
    }

    /**
     * Get the license status from cache or API.
     *
     * @return array License status information.
     */
    public function get_license_status() {
        // Check if we have a cached result
        $cached_status = get_transient('nice_sim_license_status');
        if (false !== $cached_status) {
            return $cached_status;
        }

        // Get the license key and API credentials
        $license_key = get_option('nice_sim_license_key');
        $app_id = get_option('nice_sim_app_id');
        $api_key = get_option('nice_sim_api_key');
        
        // Default response if validation fails
        $default_response = array(
            'status' => false,
            'message' => __('Thiếu thông tin giấy phép.', 'nice-sim'),
            'data' => array(),
        );
        
        // If any required field is not set, return false
        if (empty($license_key) || empty($app_id) || empty($api_key)) {
            set_transient('nice_sim_license_status', $default_response, 12 * HOUR_IN_SECONDS);
            return $default_response;
        }
        
        // Get the current domain
        $domain = $this->get_site_domain();
        
        // Make the API request
        $response = wp_remote_get(add_query_arg(array(
            'code' => $license_key,
            'domain' => $domain,
            'app_id' => $app_id,
            'api_key' => $api_key
        ), $this->api_url));
        
        // Check if the request was successful
        if (is_wp_error($response)) {
            $result = array(
                'status' => false,
                'message' => $response->get_error_message(),
                'data' => array(),
            );
            set_transient('nice_sim_license_status', $result, HOUR_IN_SECONDS); // Shorter cache time for errors
            return $result;
        }
        
        // Parse the response
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        // Check if we got a valid response
        if (!$body || !isset($body['status'])) {
            $result = array(
                'status' => false,
                'message' => __('Phản hồi không hợp lệ từ máy chủ giấy phép.', 'nice-sim'),
                'data' => array(),
            );
            set_transient('nice_sim_license_status', $result, HOUR_IN_SECONDS); // Shorter cache time for errors
            return $result;
        }
        
        // Format the result
        $result = array(
            'status' => (bool) $body['status'],
            'message' => isset($body['message']) ? $body['message'] : '',
            'data' => isset($body['data']) ? $body['data'] : array(),
        );
        
        // Cache the result - longer time for valid licenses, shorter for invalid
        $cache_time = $result['status'] ? DAY_IN_SECONDS : 3 * HOUR_IN_SECONDS;
        set_transient('nice_sim_license_status', $result, $cache_time);
        
        return $result;
    }

    /**
     * Get the current site domain.
     *
     * @return string
     */
    private function get_site_domain() {
        $site_url = get_site_url();
        $domain = parse_url($site_url, PHP_URL_HOST);
        return $domain;
    }
} 
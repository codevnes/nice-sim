<?php
/**
 * Main class for Nice SIM plugin
 *
 * @package Nice_SIM
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class.
 */
class Nice_SIM {
    /**
     * Initialize the plugin.
     */
    public function init() {
        // Load text domain for translations
        add_action('init', array($this, 'load_textdomain'));
        
        // Register assets
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        
        // Register shortcodes
        $this->register_shortcodes();
        
        // Register AJAX handlers
        $this->register_ajax_handlers();
    }
    
    /**
     * Load plugin text domain.
     */
    public function load_textdomain() {
        load_plugin_textdomain('nice-sim', false, basename(NICE_SIM_PATH) . '/languages');
    }
    
    /**
     * Register shortcodes.
     */
    private function register_shortcodes() {
        add_shortcode('nice_sim_search', array($this, 'sim_search_shortcode'));
    }
    
    /**
     * Register AJAX handlers.
     */
    private function register_ajax_handlers() {
        add_action('wp_ajax_nice_sim_check', array($this, 'process_sim_check'));
        add_action('wp_ajax_nopriv_nice_sim_check', array($this, 'process_sim_check'));
    }
    
    /**
     * Register assets.
     */
    public function register_assets() {
        // Register CSS
        wp_register_style(
            'nice-sim-style',
            NICE_SIM_URL . 'assets/css/nice-sim.css',
            array(),
            NICE_SIM_VERSION
        );
        
        // Register JS
        wp_register_script(
            'nice-sim-script',
            NICE_SIM_URL . 'assets/js/nice-sim.js',
            array('jquery'),
            NICE_SIM_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('nice-sim-script', 'NiceSim', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('nice-sim-nonce'),
        ));
    }
    
    /**
     * Sim search shortcode callback.
     *
     * @param array $atts Shortcode attributes.
     * @return string Shortcode output.
     */
    public function sim_search_shortcode($atts) {
        // Check if license is valid
        $license = Nice_SIM_License::instance();
        if (!$license->is_license_valid()) {
            return $this->get_license_notice();
        }
        
        // Enqueue assets
        wp_enqueue_style('nice-sim-style');
        wp_enqueue_script('nice-sim-script');
        
        // Start output buffering
        ob_start();
        
        // Include form template
        include NICE_SIM_PATH . 'templates/sim-search-form.php';
        
        // Return the buffered content
        return ob_get_clean();
    }
    
    /**
     * Process SIM check AJAX request.
     */
    public function process_sim_check() {
        // Check if license is valid
        $license = Nice_SIM_License::instance();
        if (!$license->is_license_valid()) {
            wp_send_json_error(array('message' => __('Giấy phép không hợp lệ. Vui lòng kích hoạt giấy phép của bạn.', 'nice-sim')));
        }
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nice-sim-nonce')) {
            wp_send_json_error(array('message' => __('Kiểm tra bảo mật thất bại', 'nice-sim')));
        }
        
        // Get form data
        $phone_number = isset($_POST['dien_thoai']) ? sanitize_text_field($_POST['dien_thoai']) : '';
        $day = isset($_POST['ngay_sinh']) ? absint($_POST['ngay_sinh']) : '';
        $month = isset($_POST['thang_sinh']) ? absint($_POST['thang_sinh']) : '';
        $year = isset($_POST['nam_sinh']) ? absint($_POST['nam_sinh']) : '';
        $gender = isset($_POST['gioi_tinh']) ? sanitize_text_field($_POST['gioi_tinh']) : '';
        $birth_hour = isset($_POST['gio_sinh']) ? sanitize_text_field($_POST['gio_sinh']) : '';
        
        // Validate data
        if (empty($phone_number) || empty($day) || empty($month) || empty($year) || empty($gender)) {
            wp_send_json_error(array('message' => __('Vui lòng điền đầy đủ thông tin', 'nice-sim')));
        }
        
        // Make the remote API request
        $api_url = 'https://flatsome.dominhhai.com/wp-admin/admin-ajax.php';
        
        $response = wp_remote_post($api_url, array(
            'body' => array(
                'action' => 'ptmh1_xem_sim_so_dep',
                'dien_thoai' => $phone_number,
                'ngay_sinh' => $day,
                'thang_sinh' => $month,
                'nam_sinh' => $year,
                'gioi_tinh' => $gender,
                'gio_sinh' => $birth_hour
            )
        ));
        
        // Check for errors
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }
        
        // Get the response body
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Send response
        if (isset($data['success']) && $data['success']) {
            wp_send_json_success($data);
        } else {
            wp_send_json_error(array('message' => __('Lỗi khi xử lý yêu cầu', 'nice-sim')));
        }
        
        wp_die();
    }
    
    /**
     * Get license notice message.
     *
     * @return string
     */
    private function get_license_notice() {
        ob_start();
        ?>
        <div class="nice-sim-license-notice">
            <h3><?php esc_html_e('Nice SIM - Yêu cầu giấy phép', 'nice-sim'); ?></h3>
            <p><?php esc_html_e('Vui lòng nhập mã giấy phép hợp lệ để sử dụng plugin này.', 'nice-sim'); ?></p>
            <?php if (current_user_can('manage_options')) : ?>
                <p><a href="<?php echo esc_url(admin_url('options-general.php?page=nice-sim-license')); ?>" class="button button-primary"><?php esc_html_e('Nhập mã giấy phép', 'nice-sim'); ?></a></p>
            <?php endif; ?>
        </div>
        <style>
            .nice-sim-license-notice {
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
                padding: 20px;
                border-radius: 4px;
                margin: 20px 0;
                text-align: center;
            }
            
            .nice-sim-license-notice h3 {
                margin-top: 0;
                color: #721c24;
            }
        </style>
        <?php
        return ob_get_clean();
    }
} 
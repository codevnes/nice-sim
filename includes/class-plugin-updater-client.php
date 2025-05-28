
<?php
/**
 * Plugin Updater Client Class
 * 
 * This class can be included in other plugins to enable updating from the Plugin Updater system
 */

if ( ! class_exists( 'Plugin_Updater_Client' ) ) :

class Plugin_Updater_Client {
    /**
     * Plugin basename (plugin-folder/plugin-file.php)
     *
     * @var string
     */
    private $plugin_basename;
    
    /**
     * Plugin slug
     *
     * @var string
     */
    private $plugin_slug;
    
    /**
     * Current version
     *
     * @var string
     */
    private $current_version;
    
    /**
     * API endpoint URL
     *
     * @var string
     */
    private $api_url;
    
    /**
     * Constructor
     *
     * @param string $plugin_basename Plugin basename (plugin-folder/plugin-file.php)
     * @param string $plugin_slug Plugin slug
     * @param string $current_version Current version
     */
    public function __construct( $plugin_basename, $plugin_slug, $current_version ) {
        $this->plugin_basename = $plugin_basename;
        $this->plugin_slug = $plugin_slug;
        $this->current_version = $current_version;
        
        // Get WordPress site URL
        $site_url = get_site_url();
        // Set API URL to the current site's REST API endpoint
        $this->api_url = trailingslashit( $site_url ) . 'wp-json/plugin-updater/v1';
        
        // Add filters for plugin updates
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
        add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
        add_action( 'in_plugin_update_message-' . $this->plugin_basename, array( $this, 'show_upgrade_notification' ), 10, 2 );
    }
    
    /**
     * Check for updates when WordPress checks for plugin updates
     *
     * @param object $transient Transient data for plugin updates
     * @return object
     */
    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }
        
        // Get update data from API
        $update_data = $this->get_update_data();
        
        if ( $update_data && version_compare( $this->current_version, $update_data->version, '<' ) ) {
            $plugin_update = new stdClass();
            $plugin_update->id = $this->plugin_slug;
            $plugin_update->slug = $this->plugin_slug;
            $plugin_update->plugin = $this->plugin_basename;
            $plugin_update->new_version = $update_data->version;
            $plugin_update->url = isset( $update_data->url ) ? $update_data->url : '';
            $plugin_update->package = $update_data->download_url;
            $plugin_update->tested = isset( $update_data->tested ) ? $update_data->tested : '';
            $plugin_update->requires = isset( $update_data->requires ) ? $update_data->requires : '';
            $plugin_update->requires_php = isset( $update_data->requires_php ) ? $update_data->requires_php : '';
            
            $transient->response[$this->plugin_basename] = $plugin_update;
        }
        
        return $transient;
    }
    
    /**
     * Filter for plugins_api to provide info for plugin information screen
     *
     * @param false|object|array $result The result object/array
     * @param string $action The API action being performed
     * @param object $args Plugin API arguments
     * @return false|object
     */
    public function plugins_api_filter( $result, $action, $args ) {
        // Only filter requests for our plugin
        if ( 'plugin_information' != $action || $args->slug != $this->plugin_slug ) {
            return $result;
        }
        
        // Get plugin info from API
        $plugin_info = $this->get_plugin_info();
        
        if ( ! $plugin_info ) {
            return $result;
        }
        
        return $plugin_info;
    }
    
    /**
     * Show upgrade notification with changelog
     *
     * @param array $plugin_data Plugin data
     * @param object $response Update response data
     */
    public function show_upgrade_notification( $plugin_data, $response ) {
        // Get plugin info from API
        $plugin_info = $this->get_plugin_info();
        
        if ( ! empty( $plugin_info->sections ) && ! empty( $plugin_info->sections['changelog'] ) ) {
            echo '<div class="update-message">';
            echo '<p><strong>' . __( 'Changelog:', 'plugin-updater' ) . '</strong></p>';
            echo '<div class="plugin-update-changelog">' . $plugin_info->sections['changelog'] . '</div>';
            echo '</div>';
        }
    }
    
    /**
     * Get update data from API
     *
     * @return object|false
     */
    private function get_update_data() {
        // Build API URL for check-update endpoint
        $api_url = $this->api_url . '/check-update';
        
        // Add plugin slug and current version as parameters
        $api_url = add_query_arg( array(
            'slug' => $this->plugin_slug,
            'version' => $this->current_version
        ), $api_url );
        
        // Make API request
        $response = wp_remote_get( $api_url, array(
            'timeout' => 10,
            'sslverify' => false
        ) );
        
        // Check for errors
        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }
        
        // Decode response
        $response_data = json_decode( wp_remote_retrieve_body( $response ) );
        
        // Check if update is available
        if ( ! isset( $response_data->success ) || ! $response_data->success || ! isset( $response_data->data ) ) {
            return false;
        }
        
        return $response_data->data;
    }
    
    /**
     * Get plugin information from API
     *
     * @return object|false
     */
    private function get_plugin_info() {
        // Build API URL for plugin info endpoint
        $api_url = $this->api_url . '/plugin/' . $this->plugin_slug;
        
        // Make API request
        $response = wp_remote_get( $api_url, array(
            'timeout' => 10,
            'sslverify' => false
        ) );
        
        // Check for errors
        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }
        
        // Decode response
        $plugin_info = json_decode( wp_remote_retrieve_body( $response ) );
        
        if ( empty( $plugin_info ) ) {
            return false;
        }
        
        // Convert to stdClass with required properties for plugins_api
        $api_response = new stdClass();
        $api_response->name = $plugin_info->name;
        $api_response->slug = $plugin_info->slug;
        $api_response->version = $plugin_info->version;
        $api_response->requires = isset( $plugin_info->requires ) ? $plugin_info->requires : '';
        $api_response->requires_php = isset( $plugin_info->requires_php ) ? $plugin_info->requires_php : '';
        $api_response->tested = isset( $plugin_info->tested ) ? $plugin_info->tested : '';
        $api_response->download_link = $plugin_info->download_url;
        $api_response->author = '';
        $api_response->author_profile = '';
        $api_response->last_updated = isset( $plugin_info->last_updated ) ? $plugin_info->last_updated : '';
        $api_response->homepage = '';
        $api_response->sections = array(
            'description' => isset( $plugin_info->sections->description ) ? $plugin_info->sections->description : '',
            'changelog' => isset( $plugin_info->sections->changelog ) ? $plugin_info->sections->changelog : '',
        );
        
        return $api_response;
    }
}

endif; // class_exists
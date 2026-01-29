<?php
/**
 * Plugin Name: SuperSeeded Upload
 * Plugin URI: https://superseeded.ai
 * Description: Embeddable file upload widget for SuperSeeded data enrichment platform
 * Version: 1.0.0
 * Author: SuperSeeded
 * Author URI: https://superseeded.ai
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: superseeded-upload
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SUPERSEEDED_VERSION', '1.0.0' );
define( 'SUPERSEEDED_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SUPERSEEDED_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

class SuperSeeded_Upload {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_scripts' ) );
        add_action( 'init', array( $this, 'register_block' ) );
        add_shortcode( 'superseeded_upload', array( $this, 'render_shortcode' ) );

        // REST API endpoint for token proxy
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
    }

    public function init() {
        load_plugin_textdomain( 'superseeded-upload', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Register admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __( 'SuperSeeded Upload', 'superseeded-upload' ),
            __( 'SuperSeeded Upload', 'superseeded-upload' ),
            'manage_options',
            'superseeded-upload',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting( 'superseeded_settings', 'superseeded_api_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ) );

        register_setting( 'superseeded_settings', 'superseeded_merchant_id', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ) );

        register_setting( 'superseeded_settings', 'superseeded_theme', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'light',
        ) );

        register_setting( 'superseeded_settings', 'superseeded_allowed_file_types', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '.csv,.xlsx,.xls',
        ) );

        register_setting( 'superseeded_settings', 'superseeded_max_file_size', array(
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 104857600, // 100MB
        ) );

        add_settings_section(
            'superseeded_main_section',
            __( 'API Configuration', 'superseeded-upload' ),
            array( $this, 'render_section_description' ),
            'superseeded-upload'
        );

        add_settings_field(
            'superseeded_api_key',
            __( 'Platform API Key', 'superseeded-upload' ),
            array( $this, 'render_api_key_field' ),
            'superseeded-upload',
            'superseeded_main_section'
        );

        add_settings_field(
            'superseeded_merchant_id',
            __( 'Default Merchant ID', 'superseeded-upload' ),
            array( $this, 'render_merchant_id_field' ),
            'superseeded-upload',
            'superseeded_main_section'
        );

        add_settings_section(
            'superseeded_display_section',
            __( 'Display Settings', 'superseeded-upload' ),
            null,
            'superseeded-upload'
        );

        add_settings_field(
            'superseeded_theme',
            __( 'Theme', 'superseeded-upload' ),
            array( $this, 'render_theme_field' ),
            'superseeded-upload',
            'superseeded_display_section'
        );

        add_settings_field(
            'superseeded_allowed_file_types',
            __( 'Allowed File Types', 'superseeded-upload' ),
            array( $this, 'render_file_types_field' ),
            'superseeded-upload',
            'superseeded_display_section'
        );

        add_settings_field(
            'superseeded_max_file_size',
            __( 'Max File Size (bytes)', 'superseeded-upload' ),
            array( $this, 'render_max_file_size_field' ),
            'superseeded-upload',
            'superseeded_display_section'
        );
    }

    public function render_section_description() {
        echo '<p>' . esc_html__( 'Enter your SuperSeeded API credentials. Get your API key from the SuperSeeded dashboard.', 'superseeded-upload' ) . '</p>';
    }

    public function render_api_key_field() {
        $value = get_option( 'superseeded_api_key', '' );
        echo '<input type="password" name="superseeded_api_key" value="' . esc_attr( $value ) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( 'Your Platform API key (kept server-side, never exposed to frontend)', 'superseeded-upload' ) . '</p>';
    }

    public function render_merchant_id_field() {
        $value = get_option( 'superseeded_merchant_id', '' );
        echo '<input type="text" name="superseeded_merchant_id" value="' . esc_attr( $value ) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( 'Default merchant ID for uploads (can be overridden in shortcode)', 'superseeded-upload' ) . '</p>';
    }

    public function render_theme_field() {
        $value = get_option( 'superseeded_theme', 'light' );
        ?>
        <select name="superseeded_theme">
            <option value="light" <?php selected( $value, 'light' ); ?>><?php esc_html_e( 'Light', 'superseeded-upload' ); ?></option>
            <option value="dark" <?php selected( $value, 'dark' ); ?>><?php esc_html_e( 'Dark', 'superseeded-upload' ); ?></option>
        </select>
        <?php
    }

    public function render_file_types_field() {
        $value = get_option( 'superseeded_allowed_file_types', '.csv,.xlsx,.xls' );
        echo '<input type="text" name="superseeded_allowed_file_types" value="' . esc_attr( $value ) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( 'Comma-separated list of file extensions', 'superseeded-upload' ) . '</p>';
    }

    public function render_max_file_size_field() {
        $value = get_option( 'superseeded_max_file_size', 104857600 );
        echo '<input type="number" name="superseeded_max_file_size" value="' . esc_attr( $value ) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( 'Maximum file size in bytes (default: 104857600 = 100MB)', 'superseeded-upload' ) . '</p>';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <form action="options.php" method="post">
                <?php
                settings_fields( 'superseeded_settings' );
                do_settings_sections( 'superseeded-upload' );
                submit_button( __( 'Save Settings', 'superseeded-upload' ) );
                ?>
            </form>

            <hr />

            <h2><?php esc_html_e( 'Usage', 'superseeded-upload' ); ?></h2>

            <h3><?php esc_html_e( 'Shortcode', 'superseeded-upload' ); ?></h3>
            <p><?php esc_html_e( 'Add the upload widget to any page or post using the shortcode:', 'superseeded-upload' ); ?></p>
            <code>[superseeded_upload]</code>

            <h4><?php esc_html_e( 'Shortcode Attributes', 'superseeded-upload' ); ?></h4>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Attribute', 'superseeded-upload' ); ?></th>
                        <th><?php esc_html_e( 'Description', 'superseeded-upload' ); ?></th>
                        <th><?php esc_html_e( 'Default', 'superseeded-upload' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>merchant_id</code></td>
                        <td><?php esc_html_e( 'Override the default merchant ID', 'superseeded-upload' ); ?></td>
                        <td><?php esc_html_e( 'Settings value', 'superseeded-upload' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>theme</code></td>
                        <td><?php esc_html_e( 'Widget theme (light/dark)', 'superseeded-upload' ); ?></td>
                        <td><?php esc_html_e( 'Settings value', 'superseeded-upload' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>class</code></td>
                        <td><?php esc_html_e( 'Additional CSS classes', 'superseeded-upload' ); ?></td>
                        <td>-</td>
                    </tr>
                </tbody>
            </table>

            <h4><?php esc_html_e( 'Example', 'superseeded-upload' ); ?></h4>
            <code>[superseeded_upload merchant_id="acme-corp" theme="dark" class="my-custom-class"]</code>

            <h3><?php esc_html_e( 'Gutenberg Block', 'superseeded-upload' ); ?></h3>
            <p><?php esc_html_e( 'Search for "SuperSeeded Upload" in the block inserter to add the widget via the block editor.', 'superseeded-upload' ); ?></p>
        </div>
        <?php
    }

    /**
     * Register REST API routes for token proxy
     */
    public function register_rest_routes() {
        register_rest_route( 'superseeded/v1', '/token', array(
            'methods' => 'POST',
            'callback' => array( $this, 'get_upload_token' ),
            'permission_callback' => array( $this, 'check_token_permission' ),
        ) );
    }

    /**
     * Permission callback for token endpoint
     */
    public function check_token_permission() {
        // Allow authenticated users or check nonce for frontend requests
        return wp_verify_nonce(
            isset( $_SERVER['HTTP_X_WP_NONCE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_WP_NONCE'] ) ) : '',
            'wp_rest'
        ) || is_user_logged_in();
    }

    /**
     * Get upload token from SuperSeeded API
     */
    public function get_upload_token( $request ) {
        $api_key = get_option( 'superseeded_api_key', '' );
        $merchant_id = $request->get_param( 'merchant_id' ) ?: get_option( 'superseeded_merchant_id', '' );

        if ( empty( $api_key ) ) {
            return new WP_Error( 'missing_api_key', __( 'SuperSeeded API key not configured', 'superseeded-upload' ), array( 'status' => 500 ) );
        }

        if ( empty( $merchant_id ) ) {
            return new WP_Error( 'missing_merchant_id', __( 'Merchant ID not provided', 'superseeded-upload' ), array( 'status' => 400 ) );
        }

        $response = wp_remote_post( 'https://api.superseeded.ai/v1/auth/delegate-upload', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode( array( 'merchant_id' => $merchant_id ) ),
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'api_error', $response->get_error_message(), array( 'status' => 500 ) );
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $status_code !== 200 ) {
            return new WP_Error(
                'token_fetch_failed',
                isset( $body['message'] ) ? $body['message'] : __( 'Failed to fetch upload token', 'superseeded-upload' ),
                array( 'status' => $status_code )
            );
        }

        return rest_ensure_response( array( 'token' => $body['token'] ) );
    }

    /**
     * Maybe enqueue scripts based on shortcode/block usage
     */
    public function maybe_enqueue_scripts() {
        global $post;

        if ( ! is_a( $post, 'WP_Post' ) ) {
            return;
        }

        // Check if shortcode is present
        if ( has_shortcode( $post->post_content, 'superseeded_upload' ) || has_block( 'superseeded/upload', $post ) ) {
            $this->enqueue_scripts();
        }
    }

    /**
     * Enqueue embed script
     */
    private function enqueue_scripts() {
        wp_enqueue_script(
            'superseeded-embed',
            'https://cdn.superseeded.ai/v1/embed.min.js',
            array(),
            SUPERSEEDED_VERSION,
            true
        );

        wp_enqueue_script(
            'superseeded-init',
            SUPERSEEDED_PLUGIN_URL . 'js/init.js',
            array( 'superseeded-embed' ),
            SUPERSEEDED_VERSION,
            true
        );

        wp_localize_script( 'superseeded-init', 'superseededConfig', array(
            'tokenEndpoint' => rest_url( 'superseeded/v1/token' ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'theme' => get_option( 'superseeded_theme', 'light' ),
            'allowedFileTypes' => explode( ',', get_option( 'superseeded_allowed_file_types', '.csv,.xlsx,.xls' ) ),
            'maxFileSize' => (int) get_option( 'superseeded_max_file_size', 104857600 ),
            'merchantId' => get_option( 'superseeded_merchant_id', '' ),
        ) );
    }

    /**
     * Render shortcode
     */
    public function render_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'merchant_id' => '',
            'theme' => '',
            'class' => '',
        ), $atts, 'superseeded_upload' );

        // Ensure scripts are enqueued
        $this->enqueue_scripts();

        $container_id = 'superseeded-upload-' . wp_rand( 1000, 9999 );
        $classes = 'superseeded-upload-container';
        if ( ! empty( $atts['class'] ) ) {
            $classes .= ' ' . esc_attr( $atts['class'] );
        }

        $data_attrs = '';
        if ( ! empty( $atts['merchant_id'] ) ) {
            $data_attrs .= ' data-merchant-id="' . esc_attr( $atts['merchant_id'] ) . '"';
        }
        if ( ! empty( $atts['theme'] ) ) {
            $data_attrs .= ' data-theme="' . esc_attr( $atts['theme'] ) . '"';
        }

        return sprintf(
            '<div id="%s" class="%s"%s></div>',
            esc_attr( $container_id ),
            esc_attr( $classes ),
            $data_attrs
        );
    }

    /**
     * Register Gutenberg block
     */
    public function register_block() {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        wp_register_script(
            'superseeded-block-editor',
            SUPERSEEDED_PLUGIN_URL . 'js/block.js',
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
            SUPERSEEDED_VERSION,
            true
        );

        register_block_type( 'superseeded/upload', array(
            'editor_script' => 'superseeded-block-editor',
            'render_callback' => array( $this, 'render_block' ),
            'attributes' => array(
                'merchantId' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'theme' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'className' => array(
                    'type' => 'string',
                    'default' => '',
                ),
            ),
        ) );
    }

    /**
     * Render Gutenberg block
     */
    public function render_block( $attributes ) {
        return $this->render_shortcode( array(
            'merchant_id' => $attributes['merchantId'] ?? '',
            'theme' => $attributes['theme'] ?? '',
            'class' => $attributes['className'] ?? '',
        ) );
    }
}

// Initialize plugin
SuperSeeded_Upload::get_instance();

<?php
namespace EagleForms;

/**
 * Main plugin bootstrap class.
 */
class Plugin {
    /**
     * Singleton instance.
     *
     * @var Plugin
     */
    protected static $instance;

    /**
     * Admin handler.
     *
     * @var Admin
     */
    protected $admin;

    /**
     * Frontend handler.
     *
     * @var Frontend
     */
    protected $frontend;

    /**
     * Retrieve the singleton instance.
     *
     * @return Plugin
     */
    public static function instance() {
        if ( null === static::$instance ) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Plugin constructor.
     */
    protected function __construct() {
        $this->define_hooks();
    }

    /**
     * Register core hooks.
     */
    protected function define_hooks() {
        register_activation_hook( EAGLE_FORMS_PATH . 'eagle-forms.php', [ $this, 'activate' ] );
        register_deactivation_hook( EAGLE_FORMS_PATH . 'eagle-forms.php', [ $this, 'deactivate' ] );

        add_action( 'plugins_loaded', [ $this, 'init' ] );
        add_action( 'init', [ $this, 'register_post_types' ] );
    }

    /**
     * Plugin initialization.
     */
    public function init() {
        load_plugin_textdomain( 'eagle-forms', false, dirname( plugin_basename( EAGLE_FORMS_PATH . 'eagle-forms.php' ) ) . '/languages' );

        require_once EAGLE_FORMS_PATH . 'includes/class-eagle-forms-admin.php';
        require_once EAGLE_FORMS_PATH . 'includes/class-eagle-forms-frontend.php';

        $this->admin    = new Admin();
        $this->frontend = new Frontend();
    }

    /**
     * Handle activation tasks.
     */
    public function activate() {
        $this->register_post_types();
        flush_rewrite_rules();
    }

    /**
     * Handle deactivation tasks.
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Register custom post types.
     */
    public function register_post_types() {
        register_post_type(
            'eagle_form',
            [
                'labels' => [
                    'name'          => __( 'Forms', 'eagle-forms' ),
                    'singular_name' => __( 'Form', 'eagle-forms' ),
                ],
                'public'              => false,
                'show_ui'             => true,
                'capability_type'     => 'post',
                'supports'            => [ 'title' ],
                'menu_icon'           => 'dashicons-feedback',
                'show_in_menu'        => false,
                'rewrite'             => false,
            ]
        );

        register_post_type(
            'eagle_entry',
            [
                'labels' => [
                    'name'          => __( 'Entries', 'eagle-forms' ),
                    'singular_name' => __( 'Entry', 'eagle-forms' ),
                ],
                'public'          => false,
                'show_ui'         => false,
                'capability_type' => 'post',
                'supports'        => [ 'title' ],
                'rewrite'         => false,
            ]
        );
    }
}

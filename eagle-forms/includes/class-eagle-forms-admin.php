<?php
namespace EagleForms;

use WP_Post;

/**
 * Handles admin functionality for Eagle Forms.
 */
class Admin {
    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'add_meta_boxes', [ $this, 'register_meta_boxes' ] );
        add_action( 'save_post_eagle_form', [ $this, 'save_form_fields' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /**
     * Register admin menu.
     */
    public function register_menu() {
        add_menu_page(
            __( 'Eagle Forms', 'eagle-forms' ),
            __( 'Eagle Forms', 'eagle-forms' ),
            'manage_options',
            'eagle-forms',
            [ $this, 'render_forms_page' ],
            'dashicons-feedback',
            58
        );

        add_submenu_page(
            'eagle-forms',
            __( 'All Forms', 'eagle-forms' ),
            __( 'All Forms', 'eagle-forms' ),
            'manage_options',
            'edit.php?post_type=eagle_form'
        );

        add_submenu_page(
            'eagle-forms',
            __( 'Add New', 'eagle-forms' ),
            __( 'Add New', 'eagle-forms' ),
            'manage_options',
            'post-new.php?post_type=eagle_form'
        );

        add_submenu_page(
            'eagle-forms',
            __( 'Entries', 'eagle-forms' ),
            __( 'Entries', 'eagle-forms' ),
            'manage_options',
            'eagle-forms-entries',
            [ $this, 'render_entries_page' ]
        );
    }

    /**
     * Register meta boxes for form builder.
     */
    public function register_meta_boxes() {
        add_meta_box(
            'eagle-forms-builder',
            __( 'Form Builder', 'eagle-forms' ),
            [ $this, 'render_builder_meta_box' ],
            'eagle_form',
            'normal',
            'default'
        );

        add_meta_box(
            'eagle-forms-shortcode',
            __( 'Shortcode', 'eagle-forms' ),
            [ $this, 'render_shortcode_meta_box' ],
            'eagle_form',
            'side',
            'default'
        );
    }

    /**
     * Render the builder meta box.
     *
     * @param WP_Post $post Post object.
     */
    public function render_builder_meta_box( WP_Post $post ) {
        wp_nonce_field( 'eagle_forms_save_form', 'eagle_forms_nonce' );

        $fields = get_post_meta( $post->ID, '_eagle_form_fields', true );
        if ( empty( $fields ) || ! is_array( $fields ) ) {
            $fields = [];
        }

        $field_types = [
            'text'     => __( 'Text', 'eagle-forms' ),
            'email'    => __( 'Email', 'eagle-forms' ),
            'textarea' => __( 'Textarea', 'eagle-forms' ),
            'select'   => __( 'Dropdown', 'eagle-forms' ),
            'checkbox' => __( 'Checkbox', 'eagle-forms' ),
            'number'   => __( 'Number', 'eagle-forms' ),
        ];
        ?>
        <div id="eagle-forms-builder" class="eagle-forms-builder" data-field-types='<?php echo wp_json_encode( $field_types ); ?>'>
            <p><?php esc_html_e( 'Add fields to your form and customize their labels, placeholders, and required state.', 'eagle-forms' ); ?></p>
            <button type="button" class="button button-primary" id="eagle-forms-add-field"><?php esc_html_e( 'Add Field', 'eagle-forms' ); ?></button>
            <ul class="eagle-forms-fields" id="eagle-forms-fields">
                <?php foreach ( $fields as $index => $field ) :
                    $field = wp_parse_args(
                        $field,
                        [
                            'label'       => '',
                            'name'        => '',
                            'type'        => 'text',
                            'required'    => false,
                            'placeholder' => '',
                            'options'     => '',
                        ]
                    );
                    ?>
                    <li class="eagle-forms-field" data-index="<?php echo esc_attr( $index ); ?>">
                        <span class="dashicons dashicons-move"></span>
                        <div class="eagle-forms-field-inner">
                            <p>
                                <label>
                                    <?php esc_html_e( 'Label', 'eagle-forms' ); ?><br />
                                    <input type="text" name="eagle_forms_fields[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $field['label'] ); ?>" />
                                </label>
                            </p>
                            <p>
                                <label>
                                    <?php esc_html_e( 'Field Name (unique)', 'eagle-forms' ); ?><br />
                                    <input type="text" name="eagle_forms_fields[<?php echo esc_attr( $index ); ?>][name]" value="<?php echo esc_attr( $field['name'] ); ?>" />
                                </label>
                            </p>
                            <p>
                                <label>
                                    <?php esc_html_e( 'Type', 'eagle-forms' ); ?><br />
                                    <select name="eagle_forms_fields[<?php echo esc_attr( $index ); ?>][type]">
                                        <?php foreach ( $field_types as $type => $label ) : ?>
                                            <option value="<?php echo esc_attr( $type ); ?>" <?php selected( $field['type'], $type ); ?>><?php echo esc_html( $label ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </p>
                            <p>
                                <label>
                                    <?php esc_html_e( 'Placeholder / Options', 'eagle-forms' ); ?><br />
                                    <textarea name="eagle_forms_fields[<?php echo esc_attr( $index ); ?>][placeholder]" rows="3"><?php echo esc_textarea( $field['placeholder'] ); ?></textarea>
                                </label>
                            </p>
                            <p>
                                <label>
                                    <input type="checkbox" name="eagle_forms_fields[<?php echo esc_attr( $index ); ?>][required]" value="1" <?php checked( ! empty( $field['required'] ) ); ?> />
                                    <?php esc_html_e( 'Required field', 'eagle-forms' ); ?>
                                </label>
                            </p>
                            <button type="button" class="button-link-delete eagle-forms-remove-field"><?php esc_html_e( 'Remove', 'eagle-forms' ); ?></button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Render shortcode meta box.
     */
    public function render_shortcode_meta_box( WP_Post $post ) {
        $shortcode = sprintf( '[eagle_form id="%d"]', $post->ID );
        ?>
        <p><?php esc_html_e( 'Use the shortcode below to embed this form in pages or posts.', 'eagle-forms' ); ?></p>
        <code><?php echo esc_html( $shortcode ); ?></code>
        <?php
    }

    /**
     * Save form fields meta.
     *
     * @param int $post_id Post ID.
     */
    public function save_form_fields( $post_id ) {
        if ( ! isset( $_POST['eagle_forms_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['eagle_forms_nonce'] ), 'eagle_forms_save_form' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $fields = isset( $_POST['eagle_forms_fields'] ) ? wp_unslash( $_POST['eagle_forms_fields'] ) : [];

        $sanitized = [];
        foreach ( (array) $fields as $field ) {
            $name = isset( $field['name'] ) ? sanitize_key( $field['name'] ) : '';
            if ( empty( $name ) ) {
                continue;
            }

            $sanitized[] = [
                'label'       => isset( $field['label'] ) ? sanitize_text_field( $field['label'] ) : '',
                'name'        => $name,
                'type'        => isset( $field['type'] ) ? sanitize_key( $field['type'] ) : 'text',
                'required'    => ! empty( $field['required'] ),
                'placeholder' => isset( $field['placeholder'] ) ? sanitize_textarea_field( $field['placeholder'] ) : '',
            ];
        }

        update_post_meta( $post_id, '_eagle_form_fields', $sanitized );
    }

    /**
     * Render forms overview page.
     */
    public function render_forms_page() {
        wp_safe_redirect( admin_url( 'edit.php?post_type=eagle_form' ) );
        exit;
    }

    /**
     * Render entries page.
     */
    public function render_entries_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'eagle-forms' ) );
        }

        $entries = get_posts(
            [
                'post_type'      => 'eagle_entry',
                'posts_per_page' => 20,
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
            ]
        );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Form Entries', 'eagle-forms' ); ?></h1>
            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Date', 'eagle-forms' ); ?></th>
                        <th><?php esc_html_e( 'Form', 'eagle-forms' ); ?></th>
                        <th><?php esc_html_e( 'Data', 'eagle-forms' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $entries ) ) : ?>
                        <tr>
                            <td colspan="3"><?php esc_html_e( 'No entries found yet.', 'eagle-forms' ); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $entries as $entry ) :
                            $form_id = (int) get_post_meta( $entry->ID, '_eagle_form_id', true );
                            $form    = get_post( $form_id );
                            $data    = get_post_meta( $entry->ID, '_eagle_entry_data', true );
                            ?>
                            <tr>
                                <td><?php echo esc_html( get_the_date( '', $entry ) ); ?></td>
                                <td><?php echo esc_html( $form ? $form->post_title : __( 'Deleted form', 'eagle-forms' ) ); ?></td>
                                <td>
                                    <ul>
                                        <?php foreach ( (array) $data as $field => $value ) : ?>
                                            <li><strong><?php echo esc_html( $field ); ?>:</strong> <?php echo esc_html( is_array( $value ) ? implode( ', ', $value ) : $value ); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Enqueue admin assets.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_assets( $hook ) {
        if ( in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
            $screen = get_current_screen();
            if ( isset( $screen->post_type ) && 'eagle_form' === $screen->post_type ) {
                wp_enqueue_style( 'eagle-forms-admin', EAGLE_FORMS_URL . 'assets/admin.css', [], '1.0.0' );
                wp_enqueue_script( 'eagle-forms-admin', EAGLE_FORMS_URL . 'assets/admin.js', [ 'jquery', 'jquery-ui-sortable' ], '1.0.0', true );
            }
        }
    }
}

<?php
namespace EagleForms;

/**
 * Handles frontend rendering and submission.
 */
class Frontend {
    /**
     * Constructor.
     */
    public function __construct() {
        add_shortcode( 'eagle_form', [ $this, 'render_shortcode' ] );
        add_action( 'init', [ $this, 'handle_submission' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /**
     * Render form shortcode.
     *
     * @param array $atts Shortcode attributes.
     *
     * @return string
     */
    public function render_shortcode( $atts ) {
        $atts = shortcode_atts(
            [
                'id' => 0,
            ],
            $atts,
            'eagle_form'
        );

        $form_id = absint( $atts['id'] );
        if ( ! $form_id ) {
            return '';
        }

        $form = get_post( $form_id );
        if ( ! $form || 'eagle_form' !== $form->post_type ) {
            return '';
        }

        $fields = get_post_meta( $form_id, '_eagle_form_fields', true );
        if ( empty( $fields ) ) {
            return '';
        }

        ob_start();

        if ( isset( $_GET['eagle_forms_submitted'] ) && (int) $_GET['eagle_forms_submitted'] === $form_id ) {
            echo '<div class="eagle-forms-notice success">' . esc_html__( 'Thanks! Your submission has been received.', 'eagle-forms' ) . '</div>';
        }

        ?>
        <form method="post" class="eagle-form" data-form-id="<?php echo esc_attr( $form_id ); ?>">
            <?php wp_nonce_field( 'eagle_forms_submit_' . $form_id, 'eagle_forms_nonce' ); ?>
            <input type="hidden" name="eagle_form_id" value="<?php echo esc_attr( $form_id ); ?>" />
            <?php foreach ( $fields as $field ) :
                $field = wp_parse_args(
                    $field,
                    [
                        'label'       => '',
                        'name'        => '',
                        'type'        => 'text',
                        'required'    => false,
                        'placeholder' => '',
                    ]
                );

                $field_slug = sanitize_html_class( $field['name'] );
                $field_id   = 'eagle-field-' . $field_slug;
                $field_name = 'eagle_fields[' . $field_slug . ']';
                ?>
                <p class="eagle-form-field eagle-form-field-<?php echo esc_attr( $field['type'] ); ?>">
                    <label for="<?php echo esc_attr( $field_id ); ?>">
                        <?php echo esc_html( $field['label'] ); ?>
                        <?php if ( ! empty( $field['required'] ) ) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <?php echo $this->render_field_input( $field, $field_id, $field_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </p>
            <?php endforeach; ?>
            <p class="eagle-form-actions">
                <button type="submit"><?php esc_html_e( 'Submit', 'eagle-forms' ); ?></button>
            </p>
        </form>
        <?php

        return ob_get_clean();
    }

    /**
     * Render field input markup.
     *
     * @param array  $field     Field configuration.
     * @param string $field_id  Field ID attribute.
     * @param string $field_name Field name attribute.
     *
     * @return string
     */
    protected function render_field_input( $field, $field_id, $field_name ) {
        $required = ! empty( $field['required'] ) ? 'required' : '';
        $placeholder = isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '';

        switch ( $field['type'] ) {
            case 'email':
            case 'number':
            case 'text':
                return sprintf(
                    '<input type="%1$s" id="%2$s" name="%3$s" placeholder="%4$s" %5$s />',
                    esc_attr( $field['type'] ),
                    esc_attr( $field_id ),
                    esc_attr( $field_name ),
                    $placeholder,
                    $required
                );
            case 'textarea':
                return sprintf(
                    '<textarea id="%1$s" name="%2$s" placeholder="%3$s" %4$s rows="5"></textarea>',
                    esc_attr( $field_id ),
                    esc_attr( $field_name ),
                    $placeholder,
                    $required
                );
            case 'select':
                $options = array_map( 'trim', explode( "\n", (string) $field['placeholder'] ) );
                $options = array_filter( $options );
                $options_markup = '';
                foreach ( $options as $option ) {
                    $options_markup .= sprintf( '<option value="%1$s">%2$s</option>', esc_attr( $option ), esc_html( $option ) );
                }
                return sprintf(
                    '<select id="%1$s" name="%2$s" %3$s>%4$s</select>',
                    esc_attr( $field_id ),
                    esc_attr( $field_name ),
                    $required,
                    $options_markup
                );
            case 'checkbox':
                return sprintf(
                    '<label class="eagle-form-checkbox"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> %4$s</label>',
                    esc_attr( $field_id ),
                    esc_attr( $field_name ),
                    $required,
                    esc_html__( 'Yes', 'eagle-forms' )
                );
            default:
                return sprintf(
                    '<input type="text" id="%1$s" name="%2$s" placeholder="%3$s" %4$s />',
                    esc_attr( $field_id ),
                    esc_attr( $field_name ),
                    $placeholder,
                    $required
                );
        }
    }

    /**
     * Handle form submissions.
     */
    public function handle_submission() {
        if ( empty( $_POST['eagle_form_id'] ) || empty( $_POST['eagle_fields'] ) ) {
            return;
        }

        $form_id = absint( $_POST['eagle_form_id'] );
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eagle_forms_nonce'] ?? '' ) ), 'eagle_forms_submit_' . $form_id ) ) {
            return;
        }

        $form = get_post( $form_id );
        if ( ! $form || 'eagle_form' !== $form->post_type ) {
            return;
        }

        $fields = get_post_meta( $form_id, '_eagle_form_fields', true );
        $raw    = wp_unslash( $_POST['eagle_fields'] );
        $data   = [];
        $errors = [];

        foreach ( (array) $fields as $field ) {
            $name     = isset( $field['name'] ) ? $field['name'] : '';
            $label    = isset( $field['label'] ) ? $field['label'] : $name;
            $required = ! empty( $field['required'] );
            $value    = $raw[ $name ] ?? '';

            switch ( $field['type'] ) {
                case 'email':
                    $value = sanitize_email( $value );
                    if ( $required && empty( $value ) ) {
                        $errors[] = sprintf( __( '%s is required.', 'eagle-forms' ), $label );
                    } elseif ( ! empty( $value ) && ! is_email( $value ) ) {
                        $errors[] = sprintf( __( '%s must be a valid email address.', 'eagle-forms' ), $label );
                    }
                    break;
                case 'number':
                    $value = is_numeric( $value ) ? $value : '';
                    if ( $required && '' === $value ) {
                        $errors[] = sprintf( __( '%s must be a number.', 'eagle-forms' ), $label );
                    }
                    break;
                case 'checkbox':
                    $value = ! empty( $value ) ? '1' : '0';
                    if ( $required && '1' !== $value ) {
                        $errors[] = sprintf( __( '%s must be checked.', 'eagle-forms' ), $label );
                    }
                    break;
                default:
                    $value = sanitize_textarea_field( $value );
                    if ( $required && '' === $value ) {
                        $errors[] = sprintf( __( '%s is required.', 'eagle-forms' ), $label );
                    }
                    break;
            }

            $data[ $label ] = $value;
        }

        if ( ! empty( $errors ) ) {
            foreach ( $errors as $error ) {
                add_filter(
                    'the_content',
                    static function ( $content ) use ( $error ) {
                        $notice = '<div class="eagle-forms-notice error">' . esc_html( $error ) . '</div>';
                        return $notice . $content;
                    }
                );
            }
            return;
        }

        $entry_id = wp_insert_post(
            [
                'post_type'   => 'eagle_entry',
                'post_status' => 'publish',
                'post_title'  => sprintf( __( 'Entry for %s', 'eagle-forms' ), $form->post_title ),
            ]
        );

        if ( $entry_id && ! is_wp_error( $entry_id ) ) {
            update_post_meta( $entry_id, '_eagle_form_id', $form_id );
            update_post_meta( $entry_id, '_eagle_entry_data', $data );
        }

        $admin_email = get_option( 'admin_email' );
        if ( $admin_email ) {
            $subject = sprintf( __( 'New entry for %s', 'eagle-forms' ), $form->post_title );
            $body    = "";
            foreach ( $data as $label => $value ) {
                $body .= sprintf( "%s: %s\n", $label, is_array( $value ) ? implode( ', ', $value ) : $value );
            }
            wp_mail( $admin_email, $subject, $body );
        }

        wp_safe_redirect( add_query_arg( 'eagle_forms_submitted', $form_id, wp_get_referer() ) );
        exit;
    }

    /**
     * Enqueue frontend assets.
     */
    public function enqueue_assets() {
        wp_enqueue_style( 'eagle-forms-frontend', EAGLE_FORMS_URL . 'assets/frontend.css', [], '1.0.0' );
    }
}

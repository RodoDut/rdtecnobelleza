<?php

namespace Hostinger\AiTheme\Shortcodes;

/**
 * Contact Form Shortcode
 */
class ContactForm {
    private bool $shortcode_used = false;

    public function __construct() {
        add_shortcode( 'hostinger_contact_form', [ $this, 'render_contact_form' ] );
        add_action( 'wp_ajax_submit_contactform', [ $this, 'handle_contact_submit' ] );
        add_action( 'wp_ajax_nopriv_submit_contactform', [ $this, 'handle_contact_submit' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ] );
        add_action( 'wp_footer', [ $this, 'enqueue_scripts' ] );

    }

    public function register_scripts() {
        wp_register_script( 'hostinger-contact-form', get_template_directory_uri()
                                                      . '/assets/js/contacts-form.min.js', [ 'jquery' ], wp_get_theme()->get( 'Version' ), true );
        wp_localize_script( 'hostinger-contact-form', 'hostinger_contact_form', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'submit_contactform' ),
            'error'    => __( 'Ha ocurrido un error. Por favor, intente nuevamente.', 'hostinger-ai-theme' ),
        ] );
    }

    public function enqueue_scripts(): void {
        if ( $this->shortcode_used ) {
            wp_enqueue_script( 'hostinger-contact-form' );
        }
    }

    public function render_contact_form( array $atts ): string {
        $this->shortcode_used = true;

        $attributes = shortcode_atts( [
            'privacy_policy' => sprintf(
                '%s %s%s%s %s',
                __('Autorizo el uso de mis datos personales provistos, para que pueda ser respondida la consulta que estoy realizando.', 'hostinger-ai-theme'),
                '<a href="' . esc_url(get_privacy_policy_url()) . '>',
                __('Politica de privacidad', 'hostinger-ai-theme'),
                '</a>',
                __('La cual he leído. Puedo retirar mi consentimiento en cualquier momento.', 'hostinger-ai-theme')
            ),
        ], $atts );

        ob_start();
        ?>
        <section class="hts-section hts-page hts-contact-form">
            <div class="hts-details">
                <div class="hts-contact-details hts-contacts">
                    <form id="contact-form">
                        <?php
                        wp_nonce_field( 'submit_contactform', 'contactform_nonce' ); ?>

                        <label for="contact-name"><?php
                            esc_html_e( 'Nombre y apellido', 'hostinger-ai-theme' ); ?></label>
                        <input type="text"
                               id="contact-name"
                               name="name"
                               placeholder="<?php
                               esc_attr_e( 'Juan Perez', 'hostinger-ai-theme' ); ?>"
                               required>

                        <label for="contact-email"><?php
                            esc_html_e( 'Email', 'hostinger-ai-theme' ); ?></label>
                        <input type="email"
                               id="contact-email"
                               name="email"
                               placeholder="<?php
                               esc_attr_e( 'ejemplo@gmail.com', 'hostinger-ai-theme' ); ?>"
                               required>

                        <label for="contact-message"><?php
                            esc_html_e( 'Mensaje', 'hostinger-ai-theme' ); ?></label>
                        <textarea id="contact-message"
                                  name="message"
                                  placeholder="<?php
                                  esc_attr_e( 'Escriba aquí su mensajito', 'hostinger-ai-theme' ); ?>"
                                  required></textarea>

                        <div class="validate-message"></div>

                        <?php
//Casilla CheckBox para verificar si acepta o no la política de privacidad.
                        $privacy_checked = isset($_GET['terms_accepted']) && $_GET['terms_accepted'] == 1 ? 'checked' : '';
                        ?>
                        <div class="hts-privacy-agree">
                            <label class="hts-form-control">
                                <input type="checkbox"
                                       id="privacy-policy-checkbox"
                                       name="privacy_policy"
                                       required>
                                       <?php echo $privacy_checked; ?>>
                                <span><?php
                                    echo wp_kses_post( $attributes['privacy_policy'] ); ?></span>
                            </label>
                        </div>

                        <input type="submit"
                               class="btn primary"
                               value="<?php
                               esc_attr_e( 'Enviar mensaje', 'hostinger-ai-theme' ); ?>"/>
                    </form>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle contact form submission
     */
    public function handle_contact_submit() {
        check_ajax_referer('submit_contactform', 'nonce');

        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $privacy_policy = isset($_POST['privacy_policy']) ? sanitize_text_field($_POST['privacy_policy']) : '';
        $form_message = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';

        // Validate privacy policy
        if ($privacy_policy !== 'on') {
            wp_send_json_error(['message' => __('Please agree with privacy policy.', 'hostinger-ai-theme')]);
        }

        // Validate email
        if (!is_email($email)) {
            wp_send_json_error(['message' => __('Please enter a valid email address.', 'hostinger-ai-theme')]);
        }

        // Set email subject
        $subject = __('New Contact Form Submission', 'hostinger-ai-theme');

        // Build HTML message
        $message = sprintf(
            '<!DOCTYPE html>
        <html>
        <body>
        <p>%s</p>
        <p>%s</p>
        <p><strong>%s</strong></p>
        <div>
            <div><strong>%s</strong> %s</div>
            <div><strong>%s</strong> %s</div>
            <div><strong>%s</strong> %s</div>
        </div>
        </body>
        </html>',
            __('Hello,', 'hostinger-ai-theme'),
            __('You have received a new message through your website\'s contact form.', 'hostinger-ai-theme'),
            __('Details:', 'hostinger-ai-theme'),
            __('Name:', 'hostinger-ai-theme'), esc_html($name),
            __('Email:', 'hostinger-ai-theme'), esc_html($email),
            __('Message:', 'hostinger-ai-theme'), esc_html($form_message)
        );

        // Set email headers
        $headers = [
            'From: ' . get_bloginfo('name') . ' <info@' . parse_url(home_url(), PHP_URL_HOST) . '>',
            'Reply-To: ' . $name . ' <' . $email . '>',
            'Content-Type: text/html; charset=UTF-8'
        ];

        // Send email to admin
        $admin_email = get_option('admin_email');
        $send_to = $admin_email;

        if (is_email($send_to)) {
            $mail_sent = wp_mail($send_to, $subject, $message, $headers);
            if ($mail_sent) {
                wp_send_json_success(['message' => __('Successfully submitted!', 'hostinger-ai-theme')]);
            } else {
                wp_send_json_error(['message' => __('Failed to send email. Please try again later.', 'hostinger-ai-theme')]);
            }
        } else {
            wp_send_json_error(['message' => __('Not valid recipient email.', 'hostinger-ai-theme')]);
        }
    }
}

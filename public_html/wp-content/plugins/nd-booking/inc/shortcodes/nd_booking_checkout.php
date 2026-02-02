<?php

if ( ! function_exists( 'loft1325_nd_booking_order_button_text' ) ) {
    /**
     * Customize the WooCommerce checkout button label. 
     *
     * @param string $text Default order button text.
     *
     * @return string
     */
    function loft1325_nd_booking_order_button_text( $text ) {
        $locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
        $is_french = 0 === strpos( $locale, 'fr' );

        return $is_french ? 'Réserver maintenant' : 'Book Now';
    }
}

add_filter( 'woocommerce_order_button_text', 'loft1325_nd_booking_order_button_text', 20 );

function nd_booking_create_guest_id_attachment( $upload ) {
    if ( empty( $upload['file'] ) || empty( $upload['type'] ) ) {
        return 0;
    }

    $attachment = [
        'post_mime_type' => $upload['type'],
        'post_title'     => sanitize_file_name( basename( $upload['file'] ) ),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];

    $attachment_id = wp_insert_attachment( $attachment, $upload['file'] );

    if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    $attach_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
    wp_update_attachment_metadata( $attachment_id, $attach_data );

    return $attachment_id;
}

function nd_booking_get_upload_data_from_token( $token ) {
    if ( empty( $token ) ) {
        return [
            'url' => '',
            'id'  => 0,
        ];
    }

    $payload = get_transient( 'nd_booking_upload_' . $token );
    $uploads = wp_upload_dir();
    $base    = wp_normalize_path( $uploads['basedir'] );

    delete_transient( 'nd_booking_upload_' . $token );

    if ( is_array( $payload ) ) {
        return [
            'url' => isset( $payload['url'] ) ? esc_url_raw( $payload['url'] ) : '',
            'id'  => isset( $payload['id'] ) ? absint( $payload['id'] ) : 0,
        ];
    }

    if ( $payload ) {
        $file = wp_normalize_path( $payload );
        if ( strpos( $file, $base ) === 0 ) {
            $relative = ltrim( substr( $file, strlen( $base ) ), '/' );
            $url      = trailingslashit( $uploads['baseurl'] ) . str_replace( DIRECTORY_SEPARATOR, '/', $relative );

            return [
                'url' => esc_url_raw( $url ),
                'id'  => 0,
            ];
        }
    }

    error_log( 'nd_booking: invalid upload token ' . $token );

    return [
        'url' => '',
        'id'  => 0,
    ];
}

//START  nd_booking_checkout
function nd_booking_shortcode_checkout() {

    $nd_booking_shortcode_result = '';

    $nd_booking_tax_lodging = 0.0;
    $nd_booking_tax_gst     = 0.0;
    $nd_booking_tax_qst     = 0.0;

    if ( ! headers_sent() ) {
        if ( function_exists( 'session_status' ) ) {
            if ( PHP_SESSION_NONE === session_status() ) {
                session_start();
            }
        } elseif ( ! session_id() ) {
            session_start();
        }
    }

    if ( isset( $_SESSION ) && is_array( $_SESSION ) ) {
        if ( isset( $_SESSION['nd_booking_tax_lodging'] ) ) {
            $nd_booking_tax_lodging = round( floatval( $_SESSION['nd_booking_tax_lodging'] ), 2 );
        }
        if ( isset( $_SESSION['nd_booking_tax_gst'] ) ) {
            $nd_booking_tax_gst = round( floatval( $_SESSION['nd_booking_tax_gst'] ), 2 );
        }
        if ( isset( $_SESSION['nd_booking_tax_qst'] ) ) {
            $nd_booking_tax_qst = round( floatval( $_SESSION['nd_booking_tax_qst'] ), 2 );
        }
    }

    $nd_booking_total_tax_amount = round( $nd_booking_tax_lodging + $nd_booking_tax_gst + $nd_booking_tax_qst, 2 );

    if( isset( $_POST['nd_booking_form_booking_arrive'] ) ) {  $nd_booking_form_booking_arrive = sanitize_text_field($_POST['nd_booking_form_booking_arrive']); }else{ $nd_booking_form_booking_arrive = '';}
    if( isset( $_POST['nd_booking_form_checkout_arrive'] ) ) {  $nd_booking_form_checkout_arrive = sanitize_text_field($_POST['nd_booking_form_checkout_arrive']); }else{ $nd_booking_form_checkout_arrive = '';}


    //ARRIVE FROM BOOKING FORM
    if ( $nd_booking_form_booking_arrive == 1 ) {


        //get value
        $nd_booking_booking_form_final_price = sanitize_text_field($_POST['nd_booking_booking_form_final_price']);
        if( isset( $_POST['nd_booking_booking_form_base_price'] ) ) {  $nd_booking_booking_form_base_price = sanitize_text_field($_POST['nd_booking_booking_form_base_price']); }else{ $nd_booking_booking_form_base_price = '';}
        $nd_booking_booking_form_date_from = sanitize_text_field($_POST['nd_booking_booking_form_date_from']);
        $nd_booking_booking_form_date_to = sanitize_text_field($_POST['nd_booking_booking_form_date_to']);
        $nd_booking_booking_form_guests = sanitize_text_field($_POST['nd_booking_booking_form_guests']);
        $nd_booking_booking_form_name = sanitize_text_field($_POST['nd_booking_booking_form_name']);
        $nd_booking_booking_form_surname = sanitize_text_field($_POST['nd_booking_booking_form_surname']);
        $nd_booking_booking_form_email = sanitize_email($_POST['nd_booking_booking_form_email']);
        $nd_booking_booking_form_phone = sanitize_text_field($_POST['nd_booking_booking_form_phone']);
        $nd_booking_booking_form_address = sanitize_text_field($_POST['nd_booking_booking_form_address']);
        $nd_booking_booking_form_city = sanitize_text_field($_POST['nd_booking_booking_form_city']);
        $nd_booking_booking_form_country = sanitize_text_field($_POST['nd_booking_booking_form_country']);
        $nd_booking_booking_form_zip = sanitize_text_field($_POST['nd_booking_booking_form_zip']);
        $nd_booking_booking_form_requests = sanitize_text_field($_POST['nd_booking_booking_form_requests']);
        $nd_booking_booking_form_arrival = sanitize_text_field($_POST['nd_booking_booking_form_arrival']);
        $nd_booking_booking_form_coupon = sanitize_text_field($_POST['nd_booking_booking_form_coupon']);
        $nd_booking_booking_form_term = sanitize_text_field($_POST['nd_booking_booking_form_term']);
        $nd_booking_booking_form_post_id = sanitize_text_field($_POST['nd_booking_booking_form_post_id']);
        $nd_booking_booking_form_post_title = sanitize_text_field($_POST['nd_booking_booking_form_post_title']);
        $nd_booking_booking_form_services = sanitize_text_field($_POST['nd_booking_booking_checkbox_services_id']);

        $nd_booking_booking_form_final_price = floatval( $nd_booking_booking_form_final_price );
        $nd_booking_booking_form_base_price = floatval( $nd_booking_booking_form_base_price );

        $nd_booking_booking_original_price = $nd_booking_booking_form_final_price;
        $nd_booking_booking_original_base_price = $nd_booking_booking_form_base_price;
        $nd_booking_coupon_value = 0;
        if ( '' !== $nd_booking_booking_form_coupon ) {
            $nd_booking_coupon_value = floatval( nd_booking_get_coupon_value( $nd_booking_booking_form_coupon ) );
            if ( $nd_booking_coupon_value > 0 ) {
                $nd_booking_discount_multiplier = max( 0, 1 - ( $nd_booking_coupon_value / 100 ) );
                $nd_booking_booking_form_base_price = round( $nd_booking_booking_form_base_price * $nd_booking_discount_multiplier, 2 );
                $nd_booking_booking_form_final_price = round( $nd_booking_booking_form_final_price * $nd_booking_discount_multiplier, 2 );
            }
        }

        if ( isset( $_POST['guest_id_number'] ) ) {
            $nd_booking_guest_id_number = sanitize_text_field( $_POST['guest_id_number'] );
        } else {
            $nd_booking_guest_id_number = '';
        }
        if ( isset( $_POST['guest_id_type'] ) ) {
            $nd_booking_guest_id_type = sanitize_text_field( $_POST['guest_id_type'] );
        } else {
            $nd_booking_guest_id_type = '';
        }

        $nd_booking_guest_id_front = '';
        $nd_booking_guest_id_back = '';
        $front = [];
        $back = [];

        if ( ! empty( $_FILES['guest_id_front']['name'] ) || ! empty( $_FILES['guest_id_back']['name'] ) ) {
            if ( ! function_exists( 'wp_handle_upload' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            $upload_overrides = [
                'test_form' => false,
                'mimes'     => [
                    'jpg|jpeg|jpe' => 'image/jpeg',
                    'png'          => 'image/png',
                ],
            ];

            $max_size = 2 * 1024 * 1024; // 2MB

            if ( ! empty( $_FILES['guest_id_front']['name'] ) && $_FILES['guest_id_front']['size'] <= $max_size ) {
                $front = wp_handle_upload( $_FILES['guest_id_front'], $upload_overrides );
                if ( empty( $front['error'] ) ) {
                    $nd_booking_guest_id_front = $front['url'];
                }
            }
            if ( ! empty( $_FILES['guest_id_back']['name'] ) && $_FILES['guest_id_back']['size'] <= $max_size ) {
                $back = wp_handle_upload( $_FILES['guest_id_back'], $upload_overrides );
                if ( empty( $back['error'] ) ) {
                    $nd_booking_guest_id_back = $back['url'];
                }
            }
        }

        $nd_booking_guest_id_front_token = '';
        if ( $nd_booking_guest_id_front ) {
            $nd_booking_guest_id_front_token = wp_generate_password( 12, false );
            $nd_booking_guest_id_front_id = nd_booking_create_guest_id_attachment( $front );
            set_transient(
                'nd_booking_upload_' . $nd_booking_guest_id_front_token,
                [
                    'url' => $nd_booking_guest_id_front,
                    'id'  => $nd_booking_guest_id_front_id,
                ],
                HOUR_IN_SECONDS
            );
        }

        $nd_booking_guest_id_back_token = '';
        if ( $nd_booking_guest_id_back ) {
            $nd_booking_guest_id_back_token = wp_generate_password( 12, false );
            $nd_booking_guest_id_back_id = nd_booking_create_guest_id_attachment( $back );
            set_transient(
                'nd_booking_upload_' . $nd_booking_guest_id_back_token,
                [
                    'url' => $nd_booking_guest_id_back,
                    'id'  => $nd_booking_guest_id_back_id,
                ],
                HOUR_IN_SECONDS
            );
        }

        $nd_booking_guest_id_front = $nd_booking_guest_id_front_token;
        $nd_booking_guest_id_back  = $nd_booking_guest_id_back_token;

        //ids
        $nd_booking_booking_form_post_id = sanitize_text_field($_POST['nd_booking_booking_form_post_id']);
        $nd_booking_ids_array = explode('-', $nd_booking_booking_form_post_id ); 
        $nd_booking_booking_form_post_id = $nd_booking_ids_array[0];
        $nd_booking_id_room = $nd_booking_ids_array[1];

        $nd_booking_tax_base_amount = $nd_booking_booking_form_base_price;
        if ( $nd_booking_tax_base_amount <= 0 && isset( $_SESSION['nd_booking_tax_base'] ) ) {
            $nd_booking_tax_base_amount = floatval( $_SESSION['nd_booking_tax_base'] );
        }
        if ( $nd_booking_tax_base_amount <= 0 && $nd_booking_total_tax_amount > 0 ) {
            $nd_booking_tax_base_amount = round( $nd_booking_booking_form_final_price - $nd_booking_total_tax_amount, 2 );
        }

        if ( $nd_booking_tax_base_amount > 0 ) {
            $nd_booking_tax_breakdown = nd_booking_calculate_tax_breakdown( $nd_booking_tax_base_amount );
        } else {
            $nd_booking_tax_breakdown = nd_booking_calculate_tax_breakdown_from_total( $nd_booking_booking_form_final_price );
        }

        $nd_booking_tax_base_amount = $nd_booking_tax_breakdown['base'];
        $nd_booking_tax_lodging = isset( $nd_booking_tax_breakdown['taxes']['lodging'] ) ? $nd_booking_tax_breakdown['taxes']['lodging']['amount'] : 0.0;
        $nd_booking_tax_gst = isset( $nd_booking_tax_breakdown['taxes']['gst'] ) ? $nd_booking_tax_breakdown['taxes']['gst']['amount'] : 0.0;
        $nd_booking_tax_qst = isset( $nd_booking_tax_breakdown['taxes']['qst'] ) ? $nd_booking_tax_breakdown['taxes']['qst']['amount'] : 0.0;
        $nd_booking_total_tax_amount = $nd_booking_tax_breakdown['total_tax'];
        $nd_booking_booking_form_final_price = $nd_booking_tax_breakdown['total'];

        if ( isset( $_SESSION ) && is_array( $_SESSION ) ) {
            $_SESSION['nd_booking_tax_base'] = $nd_booking_tax_base_amount;
            $_SESSION['nd_booking_tax_lodging'] = $nd_booking_tax_lodging;
            $_SESSION['nd_booking_tax_gst'] = $nd_booking_tax_gst;
            $_SESSION['nd_booking_tax_qst'] = $nd_booking_tax_qst;
            $_SESSION['nd_booking_tax_total'] = $nd_booking_total_tax_amount;
            $_SESSION['nd_booking_final_price'] = $nd_booking_booking_form_final_price;
        }

        include realpath(dirname( __FILE__ ).'/include/checkout/nd_booking_checkout_left_content.php');
        include realpath(dirname( __FILE__ ).'/include/checkout/nd_booking_checkout_right_content.php');
        include realpath(dirname( __FILE__ ).'/include/checkout/nd_booking_checkout_payment_options.php');

        if ( isset( $nd_booking_conditions_markup ) ) {
            $nd_booking_shortcode_right_content .= $nd_booking_conditions_markup;
        }

        $nd_booking_shortcode_right_content .= '</div>';

        $nd_booking_checkin_timestamp  = $nd_booking_booking_form_date_from ? strtotime( $nd_booking_booking_form_date_from ) : false;
        $nd_booking_checkout_timestamp = $nd_booking_booking_form_date_to ? strtotime( $nd_booking_booking_form_date_to ) : false;

        $nd_booking_meta_data = array(
            'check_in'  => $nd_booking_checkin_timestamp ? date_i18n( get_option( 'date_format', 'M j, Y' ), $nd_booking_checkin_timestamp ) : '',
            'check_out' => $nd_booking_checkout_timestamp ? date_i18n( get_option( 'date_format', 'M j, Y' ), $nd_booking_checkout_timestamp ) : '',
            'nights'    => '',
            'guests'    => '',
        );

        $nd_booking_language = 'fr';
        if ( function_exists( 'trp_get_current_language' ) ) {
            $nd_booking_language = (string) trp_get_current_language();
        } elseif ( function_exists( 'determine_locale' ) ) {
            $nd_booking_language = (string) determine_locale();
        } else {
            $nd_booking_language = (string) get_locale();
        }

        $nd_booking_language = strtolower( substr( $nd_booking_language, 0, 2 ) );
        $nd_booking_is_english = ( 'en' === $nd_booking_language );
        $nd_booking_request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $nd_booking_request_path = $nd_booking_request_uri ? wp_parse_url( $nd_booking_request_uri, PHP_URL_PATH ) : '';
        if ( $nd_booking_request_path ) {
            $nd_booking_request_path = trim( $nd_booking_request_path, '/' );
            if ( '' !== $nd_booking_request_path ) {
                $nd_booking_request_segments = explode( '/', $nd_booking_request_path );
                if ( isset( $nd_booking_request_segments[0] ) && 'en' === strtolower( $nd_booking_request_segments[0] ) ) {
                    $nd_booking_is_english = true;
                }
            }
        }

        $nd_booking_header_title = $nd_booking_is_english ? 'Reserve the room' : 'Réservez la chambre';
        $nd_booking_header_subtitle = $nd_booking_is_english ? 'Reserve your stay with confidence.' : 'Réservez votre séjour en toute confiance.';
        $nd_booking_header_timer = $nd_booking_is_english ? '9:41 remaining to secure this rate.' : '9:41 restantes pour sécuriser ce tarif.';
        $nd_booking_label_checkin = $nd_booking_is_english ? 'Check-in:' : 'Arrivée :';
        $nd_booking_label_checkout = $nd_booking_is_english ? 'Check-out:' : 'Départ :';
        $nd_booking_label_nights = $nd_booking_is_english ? 'Nights:' : 'Nuits :';
        $nd_booking_label_guests = $nd_booking_is_english ? 'Guests:' : 'Invités :';
        $nd_booking_label_total = $nd_booking_is_english ? 'Total' : 'Total';
        $nd_booking_label_badge = $nd_booking_is_english ? 'Best rate guaranteed' : 'Meilleur tarif garanti';
        $nd_booking_label_taxes = $nd_booking_is_english ? 'Taxes included' : 'Taxes incluses';
        $nd_booking_label_guest_info = $nd_booking_is_english ? 'Guest information' : 'Informations client';
        $nd_booking_label_secure = $nd_booking_is_english ? 'Secure SSL payment (256-bit)' : 'Paiement sécurisé SSL (256-bit)';

        $nd_booking_total_nights = absint( nd_booking_get_number_night( $nd_booking_booking_form_date_from, $nd_booking_booking_form_date_to ) );
        if ( $nd_booking_total_nights > 0 ) {
            $nd_booking_meta_data['nights'] = sprintf(
                $nd_booking_is_english ? _n( '%s night', '%s nights', $nd_booking_total_nights, 'nd-booking' ) : _n( '%s nuit', '%s nuits', $nd_booking_total_nights, 'nd-booking' ),
                number_format_i18n( $nd_booking_total_nights )
            );
        }

        $nd_booking_total_guests = absint( $nd_booking_booking_form_guests );
        if ( $nd_booking_total_guests > 0 ) {
            $nd_booking_meta_data['guests'] = sprintf(
                $nd_booking_is_english ? _n( '%s guest', '%s guests', $nd_booking_total_guests, 'nd-booking' ) : _n( '%s invité', '%s invités', $nd_booking_total_guests, 'nd-booking' ),
                number_format_i18n( $nd_booking_total_guests )
            );
        }

        $nd_booking_price_total      = floatval( $nd_booking_booking_form_final_price );
        $nd_booking_price_per_night  = $nd_booking_total_nights > 0 ? $nd_booking_price_total / $nd_booking_total_nights : $nd_booking_price_total;
        $nd_booking_price_total_form = nd_booking_format_decimal( $nd_booking_price_total );
        $nd_booking_price_night_form = nd_booking_format_decimal( $nd_booking_price_per_night );
        $nd_booking_currency         = nd_booking_get_currency();

        $nd_booking_booking_title = get_the_title();
        $nd_booking_image_src      = get_the_post_thumbnail_url( get_the_ID(), 'large' );
        $nd_booking_image_alt      = $nd_booking_booking_title ? $nd_booking_booking_title : __( 'Room', 'nd-booking' );

        
        $nd_booking_checkout_form_markup = $nd_booking_shortcode_right_content;
        remove_shortcode( 'nd_booking_form_checkout' );
        add_shortcode(
            'nd_booking_form_checkout',
            function () use ( $nd_booking_checkout_form_markup ) {
                return $nd_booking_checkout_form_markup;
            }
        );

        ob_start();
        ?>
        <div class="loft-checkout-wrapper">
          <div class="checkout-header">
            <div class="checkout-countdown">
              <span class="timer-icon">⏳</span>
              <span class="timer-text"><?php echo esc_html( $nd_booking_header_timer ); ?></span>
            </div>
            <h2><?php echo esc_html( $nd_booking_header_title ); ?></h2>
            <p><?php echo esc_html( $nd_booking_header_subtitle ); ?></p>
          </div>

          <div class="checkout-reviews">
            <?php echo do_shortcode( '[trustindex no-registration=google]' ); ?>
          </div>

          <div class="checkout-trust">
            <div class="trust-item">
              <span class="trust-icon">⭐</span>
              <div>
                <strong><?php echo esc_html( $nd_booking_is_english ? 'Five-star comfort' : 'Confort cinq étoiles' ); ?></strong>
                <p><?php echo esc_html( $nd_booking_is_english ? 'Architect-designed lofts with curated hotel services.' : 'Lofts conçus par architecte avec services hôteliers.' ); ?></p>
              </div>
            </div>
            <div class="trust-item">
              <span class="trust-icon">🛎️</span>
              <div>
                <strong><?php echo esc_html( $nd_booking_is_english ? 'Digital key delivery' : 'Clé numérique envoyée' ); ?></strong>
                <p><?php echo esc_html( $nd_booking_is_english ? 'Key created digitally and sent to your mobile and email for access.' : 'Clé créée numériquement et envoyée à votre mobile et votre courriel pour accéder.' ); ?></p>
              </div>
            </div>
            <div class="trust-item">
              <span class="trust-icon">🔐</span>
              <div>
                <strong><?php echo esc_html( $nd_booking_is_english ? 'Secure payment' : 'Paiement sécurisé' ); ?></strong>
                <p><?php echo esc_html( $nd_booking_is_english ? 'Your card is encrypted with bank-grade security.' : 'Votre carte est chiffrée avec sécurité bancaire.' ); ?></p>
              </div>
            </div>
          </div>

          <div class="checkout-main">
            <div class="checkout-summary">
              <div class="summary-card">
                <?php if ( $nd_booking_image_src ) : ?>
                <img src="<?php echo esc_url( $nd_booking_image_src ); ?>" alt="<?php echo esc_attr( $nd_booking_image_alt ); ?>" class="summary-image">
                <?php endif; ?>
                <div class="summary-details">
                  <h3><?php echo esc_html( $nd_booking_booking_title ); ?></h3>
                  <ul class="summary-list">
                    <?php if ( ! empty( $nd_booking_meta_data['check_in'] ) ) : ?>
                    <li><strong><?php echo esc_html( $nd_booking_label_checkin ); ?></strong> <?php echo esc_html( $nd_booking_meta_data['check_in'] ); ?></li>
                    <?php endif; ?>
                    <?php if ( ! empty( $nd_booking_meta_data['check_out'] ) ) : ?>
                    <li><strong><?php echo esc_html( $nd_booking_label_checkout ); ?></strong> <?php echo esc_html( $nd_booking_meta_data['check_out'] ); ?></li>
                    <?php endif; ?>
                    <?php if ( ! empty( $nd_booking_meta_data['nights'] ) ) : ?>
                    <li><strong><?php echo esc_html( $nd_booking_label_nights ); ?></strong> <?php echo esc_html( $nd_booking_meta_data['nights'] ); ?></li>
                    <?php endif; ?>
                    <?php if ( ! empty( $nd_booking_meta_data['guests'] ) ) : ?>
                    <li><strong><?php echo esc_html( $nd_booking_label_guests ); ?></strong> <?php echo esc_html( $nd_booking_meta_data['guests'] ); ?></li>
                    <?php endif; ?>
                  </ul>
                  <div class="summary-total">
                    <div class="summary-badge"><?php echo esc_html( $nd_booking_label_badge ); ?></div>
                    <p><?php echo esc_html( $nd_booking_label_total ); ?></p>
                    <h2><?php echo esc_html( $nd_booking_price_total_form ); ?> <?php echo esc_html( $nd_booking_currency ); ?></h2>
                    <p class="per-night"><?php echo esc_html( $nd_booking_label_taxes ); ?></p>
                  </div>
                </div>
              </div>
            </div>

            <div class="checkout-form">
              <div class="secure-banner">
                <span class="lock-icon">🔒</span> <?php echo esc_html( $nd_booking_label_secure ); ?>
              </div>
              <div class="card-logos">
                <span class="card-logo">Visa</span>
                <span class="card-logo">Mastercard</span>
                <span class="card-logo">Amex</span>
                <span class="card-logo">Interac</span>
              </div>
              <h3><?php echo esc_html( $nd_booking_label_guest_info ); ?></h3>
              <?php echo do_shortcode( '[nd_booking_form_checkout]' ); ?>
            </div>
          </div>
        </div>

        <style>
        body {
          background: #f4f9fb;
          font-family: "Inter", "Poppins", sans-serif;
          color: #0b1b2b;
        }

        .loft-checkout-wrapper {
          position: relative;
          padding: 28px 18px 90px;
          background: radial-gradient(circle at top, rgba(118, 177, 196, 0.22) 0%, #f8fafc 50%, #eef2f7 100%);
          overflow: hidden;
        }

        .loft-checkout-wrapper::before {
          content: "";
          position: absolute;
          inset: -140px auto auto -140px;
          width: 300px;
          height: 300px;
          background: radial-gradient(circle, rgba(118, 177, 196, 0.3), rgba(118, 177, 196, 0));
          pointer-events: none;
        }

        .checkout-header {
          text-align: left;
          margin: 24px 0 20px;
        }

        .checkout-header h2 {
          font-size: 28px;
          font-weight: 700;
          letter-spacing: 0.4px;
          color: #0b1b2b;
        }

        .checkout-header p {
          color: #52606d;
          font-size: 14px;
          margin-bottom: 14px;
        }

        .checkout-countdown {
          display: inline-flex;
          align-items: center;
          gap: 8px;
          background: #0f172a;
          color: #e2f3f7;
          padding: 10px 16px;
          border-radius: 999px;
          box-shadow: 0 16px 30px rgba(118, 177, 196, 0.25);
          font-weight: 600;
          font-size: 12px;
          margin-bottom: 12px;
        }

        .checkout-reviews {
          max-width: 1100px;
          margin: 0 auto 18px;
        }

        .checkout-trust {
          display: grid;
          grid-template-columns: 1fr;
          gap: 12px;
          max-width: 1100px;
          margin: 0 auto 28px;
        }

        .trust-item {
          display: flex;
          gap: 12px;
          align-items: flex-start;
          background: rgba(255, 255, 255, 0.9);
          border-radius: 16px;
          padding: 14px 16px;
          border: 1px solid rgba(148, 163, 184, 0.25);
          box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
        }

        .trust-icon {
          font-size: 20px;
        }

        .trust-item strong {
          display: block;
          font-size: 14px;
          color: #0b1b2b;
          margin-bottom: 4px;
        }

        .trust-item p {
          margin: 0;
          font-size: 12px;
          color: #64748b;
        }

        .checkout-main {
          display: grid;
          grid-template-columns: 1fr;
          gap: 26px;
          max-width: 1100px;
          margin: 0 auto;
        }

        .checkout-summary {
          width: 100%;
        }

        .summary-card {
          background: #ffffff;
          border-radius: 20px;
          box-shadow: 0 24px 50px rgba(15, 23, 42, 0.08);
          overflow: hidden;
          border: 1px solid rgba(148, 163, 184, 0.25);
        }

        .summary-image {
          width: 100%;
          height: auto;
          display: block;
        }

        .summary-details {
          padding: 22px;
        }

        .summary-details h3 {
          font-size: 18px;
          font-weight: 700;
          margin-bottom: 10px;
          color: #0b1b2b;
        }

        .summary-list {
          list-style: none;
          padding: 0;
          margin: 0 0 18px;
        }

        .summary-list li {
          display: flex;
          justify-content: space-between;
          gap: 10px;
          font-size: 13px;
          color: #425466;
          margin-bottom: 6px;
        }

        .summary-list li strong {
          color: #0b1b2b;
        }

        .summary-total {
          background: linear-gradient(135deg, rgba(118, 177, 196, 0.18), rgba(118, 177, 196, 0.05));
          border: 1px solid rgba(118, 177, 196, 0.45);
          border-radius: 14px;
          text-align: center;
          padding: 16px 14px;
        }

        .summary-total h2 {
          margin: 6px 0 2px;
          font-size: 24px;
          color: #0b1b2b;
          letter-spacing: 0.3px;
        }

        .summary-total p {
          margin: 0;
        }

        .summary-total .per-night {
          font-size: 12px;
          color: #475569;
          margin-top: 6px;
        }

        .summary-badge {
          display: inline-block;
          background: #76b1c4;
          color: #ffffff;
          font-weight: 600;
          padding: 6px 16px;
          border-radius: 999px;
          margin-bottom: 10px;
          text-transform: uppercase;
          letter-spacing: 0.18em;
          font-size: 10px;
        }

        .checkout-form {
          background: #ffffff;
          border-radius: 22px;
          box-shadow: 0 30px 60px rgba(15, 23, 42, 0.08);
          padding: 28px;
          border: 1px solid rgba(148, 163, 184, 0.25);
        }

        .secure-banner {
          background: #0f172a;
          color: #e2f3f7;
          font-weight: 600;
          border-radius: 14px;
          padding: 12px 16px;
          display: inline-flex;
          align-items: center;
          box-shadow: 0 12px 24px rgba(118, 177, 196, 0.25);
          margin-bottom: 16px;
          gap: 8px;
          font-size: 12px;
        }

        .card-logos {
          display: flex;
          gap: 8px;
          margin-bottom: 22px;
          flex-wrap: wrap;
        }

        .card-logo {
          padding: 8px 12px;
          border-radius: 999px;
          background: #f8fafc;
          border: 1px solid rgba(148, 163, 184, 0.4);
          font-size: 12px;
          font-weight: 600;
          letter-spacing: 0.4px;
          color: #1b1b1b;
        }

        .checkout-form h3 {
          font-size: 20px;
          font-weight: 700;
          color: #0b1b2b;
          margin-bottom: 20px;
        }

        .checkout-form input,
        .checkout-form select,
        .checkout-form textarea,
        .checkout-form #card-element {
          width: 100%;
          border: 1px solid rgba(148, 163, 184, 0.45);
          border-radius: 14px;
          padding: 12px 14px;
          margin-bottom: 16px;
          font-size: 15px;
          background: #f8fafc;
          transition: border-color 0.2s ease, box-shadow 0.2s ease;
          box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.05);
        }

        .checkout-form input:focus,
        .checkout-form select:focus,
        .checkout-form textarea:focus,
        .checkout-form #card-element:focus-within {
          border-color: #76b1c4;
          outline: none;
          box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
        }

        .checkout-form .button-primary,
        .checkout-form button[type="submit"] {
          background: linear-gradient(135deg, #76b1c4, #5a93a7);
          color: #ffffff;
          border: none;
          border-radius: 999px;
          padding: 16px 20px;
          width: 100%;
          font-weight: 700;
          text-transform: uppercase;
          cursor: pointer;
          transition: 0.3s ease;
          letter-spacing: 0.2em;
          box-shadow: 0 18px 30px rgba(118, 177, 196, 0.35);
        }

        .checkout-form .button-primary:hover,
        .checkout-form button[type="submit"]:hover,
        .checkout-form .button-primary:focus,
        .checkout-form button[type="submit"]:focus {
          background: linear-gradient(135deg, #8cc3d4, #5a93a7);
          box-shadow: 0 22px 38px rgba(118, 177, 196, 0.45);
        }

        .loft-section-payment,
        .section {
          border-radius: 18px;
          background: #f9fafb;
          padding: 18px;
          border: 1px solid rgba(148, 163, 184, 0.25);
        }

        .loft-payment-card {
          border-radius: 16px;
          background: #ffffff;
          padding: 16px;
          border: 1px solid rgba(148, 163, 184, 0.25);
        }

        .loft-card-errors {
          color: #b91c1c;
          font-weight: 600;
          margin-top: 10px;
        }

        @media (min-width: 900px) {
          .loft-checkout-wrapper {
            padding: 48px 32px 120px;
          }

          .checkout-header {
            text-align: center;
            margin: 48px 0 32px;
          }

          .checkout-header h2 {
            font-size: 40px;
          }

          .checkout-trust {
            grid-template-columns: repeat(3, minmax(0, 1fr));
          }

          .checkout-main {
            grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
            gap: 36px;
          }

          .checkout-summary {
            position: sticky;
            top: 24px;
            align-self: start;
          }

          .checkout-form {
            padding: 38px;
          }
        }
        </style>
        <?php
        $nd_booking_shortcode_result .= ob_get_clean();

    //START PAYMENT ON CHECKOUT PAGE
    }elseif ( $nd_booking_form_checkout_arrive == 1 OR isset($_GET['tx']) OR $nd_booking_form_checkout_arrive == 2 ) {

        $nd_booking_guest_id_front = '';
        $nd_booking_guest_id_back = '';
        $nd_booking_guest_id_front_id = 0;
        $nd_booking_guest_id_back_id = 0;

        //START BUILT VARIABLES DEPENDING ON PAYMENT METHODS
        if ( $nd_booking_form_checkout_arrive == 1 ) {

            //transaction TX id
            $nd_booking_paypal_tx = rand(100000000,999999999);

            //get current date
            $nd_booking_date = date('H:m:s F j Y');

            //get currency
            $nd_booking_booking_form_currency = nd_booking_get_currency();

            $nd_booking_paypal_error = 0;
        
            //get value
            $nd_booking_booking_form_date_from = sanitize_text_field($_POST['nd_booking_checkout_form_date_from']);
            $nd_booking_booking_form_date_to = sanitize_text_field($_POST['nd_booking_checkout_form_date_top']);
            $nd_booking_booking_form_guests = sanitize_text_field($_POST['nd_booking_checkout_form_guests']);
            $nd_booking_booking_form_final_price = sanitize_text_field($_POST['nd_booking_checkout_form_final_price']);
            if( isset( $_POST['nd_booking_checkout_form_base_price'] ) ) { $nd_booking_checkout_form_base_price = sanitize_text_field($_POST['nd_booking_checkout_form_base_price']); }else{ $nd_booking_checkout_form_base_price = ''; }
            $nd_booking_checkout_form_post_id = sanitize_text_field($_POST['nd_booking_checkout_form_post_id']);
            $nd_booking_checkout_form_post_title = sanitize_text_field($_POST['nd_booking_checkout_form_post_title']);
            $nd_booking_booking_form_name = sanitize_text_field($_POST['nd_booking_checkout_form_name']);
            $nd_booking_booking_form_surname = sanitize_text_field($_POST['nd_booking_checkout_form_surname']);
            $nd_booking_booking_form_email = sanitize_email($_POST['nd_booking_checkout_form_email']);
            $nd_booking_booking_form_phone = sanitize_text_field($_POST['nd_booking_checkout_form_phone']);
            $nd_booking_booking_form_address = sanitize_text_field($_POST['nd_booking_checkout_form_address']);
            $nd_booking_booking_form_city = sanitize_text_field($_POST['nd_booking_checkout_form_city']);
            $nd_booking_booking_form_country = sanitize_text_field($_POST['nd_booking_checkout_form_country']);
            $nd_booking_booking_form_zip = sanitize_text_field($_POST['nd_booking_checkout_form_zip']);
            $nd_booking_booking_form_requests = sanitize_text_field($_POST['nd_booking_checkout_form_requets']);
            $nd_booking_booking_form_arrival = sanitize_text_field($_POST['nd_booking_checkout_form_arrival']);
            $nd_booking_booking_form_coupon = sanitize_text_field($_POST['nd_booking_checkout_form_coupon']);
            $nd_booking_booking_form_term = sanitize_text_field($_POST['nd_booking_checkout_form_term']);
            $nd_booking_booking_form_services = sanitize_text_field($_POST['nd_booking_booking_form_services']);
            $nd_booking_booking_form_action_type = sanitize_text_field($_POST['nd_booking_booking_form_action_type']);
              $nd_booking_booking_form_payment_status = sanitize_text_field($_POST['nd_booking_booking_form_payment_status']);
              $nd_booking_guest_id_number = sanitize_text_field($_POST['nd_booking_checkout_form_guest_id_number']);
              $nd_booking_guest_id_type   = sanitize_text_field($_POST['nd_booking_checkout_form_guest_id_type']);
              $front_token = sanitize_text_field($_POST['nd_booking_checkout_form_guest_id_front']);
              $back_token  = sanitize_text_field($_POST['nd_booking_checkout_form_guest_id_back']);
              $front_upload = nd_booking_get_upload_data_from_token( $front_token );
              $back_upload  = nd_booking_get_upload_data_from_token( $back_token );
              $nd_booking_guest_id_front = $front_upload['url'];
              $nd_booking_guest_id_back  = $back_upload['url'];
              $nd_booking_guest_id_front_id = $front_upload['id'];
              $nd_booking_guest_id_back_id  = $back_upload['id'];

            //ids
            $nd_booking_checkout_form_post_id = sanitize_text_field($_POST['nd_booking_checkout_form_post_id']);
            $nd_booking_ids_array = explode('-', $nd_booking_checkout_form_post_id );
            $nd_booking_checkout_form_post_id = $nd_booking_ids_array[0];
            $nd_booking_id_room = $nd_booking_ids_array[1];

            $nd_booking_booking_form_final_price = floatval( $nd_booking_booking_form_final_price );
            $nd_booking_checkout_form_base_price = floatval( $nd_booking_checkout_form_base_price );

            $nd_booking_tax_base_amount = $nd_booking_checkout_form_base_price;
            if ( $nd_booking_tax_base_amount <= 0 && isset( $_SESSION['nd_booking_tax_base'] ) ) {
                $nd_booking_tax_base_amount = floatval( $_SESSION['nd_booking_tax_base'] );
            }
            if ( $nd_booking_tax_base_amount <= 0 && $nd_booking_total_tax_amount > 0 ) {
                $nd_booking_tax_base_amount = round( $nd_booking_booking_form_final_price - $nd_booking_total_tax_amount, 2 );
            }

            if ( $nd_booking_tax_base_amount > 0 ) {
                $nd_booking_tax_breakdown = nd_booking_calculate_tax_breakdown( $nd_booking_tax_base_amount );
            } else {
                $nd_booking_tax_breakdown = nd_booking_calculate_tax_breakdown_from_total( $nd_booking_booking_form_final_price );
            }

            $nd_booking_tax_base_amount = $nd_booking_tax_breakdown['base'];
            $nd_booking_tax_lodging = isset( $nd_booking_tax_breakdown['taxes']['lodging'] ) ? $nd_booking_tax_breakdown['taxes']['lodging']['amount'] : 0.0;
            $nd_booking_tax_gst = isset( $nd_booking_tax_breakdown['taxes']['gst'] ) ? $nd_booking_tax_breakdown['taxes']['gst']['amount'] : 0.0;
            $nd_booking_tax_qst = isset( $nd_booking_tax_breakdown['taxes']['qst'] ) ? $nd_booking_tax_breakdown['taxes']['qst']['amount'] : 0.0;
            $nd_booking_total_tax_amount = $nd_booking_tax_breakdown['total_tax'];
            $nd_booking_booking_form_final_price = $nd_booking_tax_breakdown['total'];

            if ( isset( $_SESSION ) && is_array( $_SESSION ) ) {
                $_SESSION['nd_booking_tax_base'] = $nd_booking_tax_base_amount;
                $_SESSION['nd_booking_tax_lodging'] = $nd_booking_tax_lodging;
                $_SESSION['nd_booking_tax_gst'] = $nd_booking_tax_gst;
                $_SESSION['nd_booking_tax_qst'] = $nd_booking_tax_qst;
                $_SESSION['nd_booking_tax_total'] = $nd_booking_total_tax_amount;
                $_SESSION['nd_booking_final_price'] = $nd_booking_booking_form_final_price;
            }



        //START STRIPE
        }elseif ( $nd_booking_form_checkout_arrive == 2 ) {

            //default
            $nd_booking_paypal_tx = rand(100000000,999999999);
            $nd_booking_date = date('H:m:s F j Y');
            $nd_booking_booking_form_currency = nd_booking_get_currency();
           
            //get datas
            $nd_booking_booking_form_date_from = sanitize_text_field($_POST['nd_booking_checkout_form_date_from']);
            $nd_booking_booking_form_date_to = sanitize_text_field($_POST['nd_booking_checkout_form_date_top']);
            $nd_booking_booking_form_guests = sanitize_text_field($_POST['nd_booking_checkout_form_guests']);
            $nd_booking_booking_form_final_price = sanitize_text_field($_POST['nd_booking_checkout_form_final_price']);
            $nd_booking_checkout_form_post_id = sanitize_text_field($_POST['nd_booking_checkout_form_post_id']);
            $nd_booking_checkout_form_post_title = sanitize_text_field($_POST['nd_booking_checkout_form_post_title']);
            $nd_booking_booking_form_name = sanitize_text_field($_POST['nd_booking_checkout_form_name']);
            $nd_booking_booking_form_surname = sanitize_text_field($_POST['nd_booking_checkout_form_surname']);
            $nd_booking_booking_form_email = sanitize_email($_POST['nd_booking_checkout_form_email']);
            $nd_booking_booking_form_phone = sanitize_text_field($_POST['nd_booking_checkout_form_phone']);
            $nd_booking_booking_form_address = sanitize_text_field($_POST['nd_booking_checkout_form_address']);
            $nd_booking_booking_form_city = sanitize_text_field($_POST['nd_booking_checkout_form_city']);
            $nd_booking_booking_form_country = sanitize_text_field($_POST['nd_booking_checkout_form_country']);
            $nd_booking_booking_form_zip = sanitize_text_field($_POST['nd_booking_checkout_form_zip']);
            $nd_booking_booking_form_requests = sanitize_text_field($_POST['nd_booking_checkout_form_requets']);
            $nd_booking_booking_form_arrival = sanitize_text_field($_POST['nd_booking_checkout_form_arrival']);
            $nd_booking_booking_form_coupon = sanitize_text_field($_POST['nd_booking_checkout_form_coupon']);
            $nd_booking_booking_form_term = sanitize_text_field($_POST['nd_booking_checkout_form_term']);
            $nd_booking_booking_form_services = sanitize_text_field($_POST['nd_booking_booking_form_services']);
            $nd_booking_booking_form_action_type = sanitize_text_field($_POST['nd_booking_booking_form_action_type']);
              $nd_booking_booking_form_payment_status = sanitize_text_field($_POST['nd_booking_booking_form_payment_status']);
              $nd_booking_guest_id_number = sanitize_text_field($_POST['nd_booking_checkout_form_guest_id_number']);
              $nd_booking_guest_id_type   = sanitize_text_field($_POST['nd_booking_checkout_form_guest_id_type']);
              $front_token = sanitize_text_field($_POST['nd_booking_checkout_form_guest_id_front']);
              $back_token  = sanitize_text_field($_POST['nd_booking_checkout_form_guest_id_back']);
              $front_upload = nd_booking_get_upload_data_from_token( $front_token );
              $back_upload  = nd_booking_get_upload_data_from_token( $back_token );
              $nd_booking_guest_id_front = $front_upload['url'];
              $nd_booking_guest_id_back  = $back_upload['url'];
              $nd_booking_guest_id_front_id = $front_upload['id'];
              $nd_booking_guest_id_back_id  = $back_upload['id'];

            //ids
            $nd_booking_checkout_form_post_id = sanitize_text_field($_POST['nd_booking_checkout_form_post_id']);
            $nd_booking_ids_array = explode('-', $nd_booking_checkout_form_post_id ); 
            $nd_booking_checkout_form_post_id = $nd_booking_ids_array[0];
            $nd_booking_id_room = $nd_booking_ids_array[1];


            $nd_booking_stripe_token = sanitize_text_field($_POST['stripeToken']);

            //call the api stripe only if we are not in dev mode
            if ( get_option('nd_booking_plugin_dev_mode') == 1 ){

                $nd_booking_paypal_tx = rand(100000000,999999999);   

            }else{

                //stripe data
                $nd_booking_amount = $nd_booking_booking_form_final_price*100;
                $nd_booking_currency = get_option('nd_booking_stripe_currency');
                $nd_booking_description = $nd_booking_checkout_form_post_title.' - '.$nd_booking_booking_form_name.' '.$nd_booking_booking_form_surname.' - '.$nd_booking_booking_form_date_from.' '.$nd_booking_booking_form_date_to;
                $nd_booking_source = $nd_booking_stripe_token;
                $nd_booking_stripe_secret_key = get_option('nd_booking_stripe_secret_key');
                $nd_booking_url = 'https://api.stripe.com/v1/charges';


                //prepare the request
                $nd_booking_response = wp_remote_post(

                    $nd_booking_url,

                    array(

                        'method' => 'POST',
                        'timeout' => 45,
                        'redirection' => 5,
                        'httpversion' => '1.0',
                        'blocking' => true,
                        'headers' => array(
                            'Authorization' => 'Bearer '.$nd_booking_stripe_secret_key
                        ),
                        'body' => array(
                            'amount' => $nd_booking_amount,
                            'currency' => $nd_booking_currency,
                            'description' => $nd_booking_description,
                            'source' => $nd_booking_source,
                            'metadata[date_from]' => $nd_booking_booking_form_date_from,
                            'metadata[date_to]' => $nd_booking_booking_form_date_to,
                            'metadata[guests]' => $nd_booking_booking_form_guests,
                            'metadata[name]' => $nd_booking_booking_form_name.' '.$nd_booking_booking_form_surname,
                            'metadata[email]' => $nd_booking_booking_form_email,
                            'metadata[phone]' => $nd_booking_booking_form_phone,
                            'metadata[address]' => $nd_booking_booking_form_address.' '.$nd_booking_booking_form_city.' '.$nd_booking_booking_form_country.' '.$nd_booking_booking_form_zip,
                            'metadata[requests]' => $nd_booking_booking_form_requests
                        ),
                        'cookies' => array()

                    )
                );


                if ( is_wp_error( $nd_booking_response ) ) {
                    error_log( 'nd_booking: Stripe request error: ' . $nd_booking_response->get_error_message() );
                    return '<p>'. esc_html__( 'There was a problem processing your payment. Please try again later.', 'nd-booking' ) .'</p>';
                }

                // START check the response
                $nd_booking_http_response_code = wp_remote_retrieve_response_code( $nd_booking_response );

                if ( 200 !== $nd_booking_http_response_code ) {
                    error_log( 'nd_booking: Stripe request failed with code '. $nd_booking_http_response_code .' and body: '. wp_remote_retrieve_body( $nd_booking_response ) );
                    return '<p>'. esc_html__( 'Unable to process payment at this time. Please contact support.', 'nd-booking' ) .'</p>';
                }

                $nd_booking_response_body = wp_remote_retrieve_body( $nd_booking_response );
                $nd_booking_stripe_data   = json_decode( $nd_booking_response_body );

                if ( ! is_object( $nd_booking_stripe_data ) ) {
                    error_log( 'nd_booking: Invalid Stripe response: '. $nd_booking_response_body );
                    return '<p>'. esc_html__( 'Unexpected response from payment gateway. Please contact support.', 'nd-booking' ) .'</p>';
                }

                if ( empty( $nd_booking_stripe_data->paid ) ) {
                    error_log( 'nd_booking: Stripe charge not paid. Response: '. $nd_booking_response_body );
                    return '<p>'. esc_html__( 'Payment was not completed. Please try again.', 'nd-booking' ) .'</p>';
                }

                $nd_booking_booking_form_payment_status = 'Completed';
                // store the payment id for later use
                $nd_booking_booking_form_payment_id     = $nd_booking_stripe_data->id;

                //transaction TX id
                $nd_booking_paypal_tx = $nd_booking_stripe_data->id;

                //get current date
                $nd_booking_date = date('H:m:s F j Y');

                //get currency
                $nd_booking_booking_form_currency = nd_booking_get_currency();

                $nd_booking_paypal_error = 0;
                //END check the response

            }
            //end call





        //START PAYPAL
        }else{

            

            //recover datas from plugin settings
            $nd_booking_paypal_email = get_option('nd_booking_paypal_email');
            $nd_booking_paypal_currency = get_option('nd_booking_paypal_currency');
            $nd_booking_paypal_token = get_option('nd_booking_paypal_token');

            $nd_booking_paypal_developer = get_option('nd_booking_paypal_developer');
            if ( $nd_booking_paypal_developer == 1) {
              $nd_booking_paypal_action_1 = 'https://www.sandbox.paypal.com/cgi-bin';
              $nd_booking_paypal_action_2 = 'https://www.sandbox.paypal.com/cgi-bin/webscr'; 
            }
            else{  
              $nd_booking_paypal_action_1 = 'https://www.paypal.com/cgi-bin';
              $nd_booking_paypal_action_2 = 'https://www.paypal.com/cgi-bin/webscr';
            }

            //transaction TX id
            $nd_booking_paypal_tx = sanitize_text_field($_GET['tx']);
            $nd_booking_paypal_url = $nd_booking_paypal_action_2;



            //prepare the request
            $nd_booking_paypal_response = wp_remote_post( 

                $nd_booking_paypal_url, 

                array(
                
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => array( 
                        'cmd' => '_notify-synch',
                        'tx' => $nd_booking_paypal_tx,
                        'at' => $nd_booking_paypal_token
                    ),
                    'cookies' => array()
                
                )
            );

            $nd_booking_http_paypal_response_code = wp_remote_retrieve_response_code( $nd_booking_paypal_response );

            //START if is 200
            if ( $nd_booking_http_paypal_response_code == 200 ) {

                $nd_booking_paypal_response_body = wp_remote_retrieve_body( $nd_booking_paypal_response );

                //START if is success
                if ( strpos($nd_booking_paypal_response_body, 'SUCCESS') === 0 ) {

                    $nd_booking_paypal_response = substr($nd_booking_paypal_response_body, 7);
                    $nd_booking_paypal_response = urldecode($nd_booking_paypal_response);
                    preg_match_all('/^([^=\s]++)=(.*+)/m', $nd_booking_paypal_response, $m, PREG_PATTERN_ORDER);
                    $nd_booking_paypal_response = array_combine($m[1], $m[2]);


                    if(isset($nd_booking_paypal_response['charset']) AND strtoupper($nd_booking_paypal_response['charset']) !== 'UTF-8')
                    {
                      foreach($nd_booking_paypal_response as $key => &$value)
                      {
                        $value = mb_convert_encoding($value, 'UTF-8', $nd_booking_paypal_response['charset']);
                      }
                      $nd_booking_paypal_response['charset_original'] = $nd_booking_paypal_response['charset'];
                      $nd_booking_paypal_response['charset'] = 'UTF-8';
                    }

                    ksort($nd_booking_paypal_response);

                    //get value
                    $nd_booking_date = $nd_booking_paypal_response['payment_date'];
                    $nd_booking_booking_form_final_price = $nd_booking_paypal_response['mc_gross'];
                    
                    //ids
                    $nd_booking_checkout_form_post_id = $nd_booking_paypal_response['item_number'];
                    $nd_booking_ids_array = explode('-', $nd_booking_checkout_form_post_id ); 
                    $nd_booking_checkout_form_post_id = $nd_booking_ids_array[0];
                    $nd_booking_id_room = $nd_booking_ids_array[1];

                    $nd_booking_checkout_form_post_title = get_the_title($nd_booking_checkout_form_post_id);
                    
                    //user info
                    $nd_booking_booking_form_name = $nd_booking_paypal_response['first_name'];
                    $nd_booking_booking_form_surname = $nd_booking_paypal_response['last_name'];
                    $nd_booking_booking_form_email = $nd_booking_paypal_response['payer_email'];
                    $nd_booking_booking_form_address = $nd_booking_paypal_response['address_street'];
                    $nd_booking_booking_form_city = $nd_booking_paypal_response['address_city'];
                    $nd_booking_booking_form_country = $nd_booking_paypal_response['address_country'];
                    $nd_booking_booking_form_zip = $nd_booking_paypal_response['address_zip'];

                    //transiction details
                    $nd_booking_booking_form_currency = $nd_booking_paypal_response['mc_currency'];
                    $nd_booking_booking_form_action_type = 'paypal';
                    $nd_booking_booking_form_payment_status = $nd_booking_paypal_response['payment_status'];

                    //null
                    $nd_booking_booking_form_term = '';
                    $nd_booking_paypal_error = 0;

                    //START extract custom filed
                    $nd_booking_custom_field_array = explode('[ndbcpm]', $nd_booking_paypal_response['custom']);
                    $nd_booking_booking_form_date_from = $nd_booking_custom_field_array[0];
                    $nd_booking_booking_form_date_to = $nd_booking_custom_field_array[1];
                    $nd_booking_booking_form_guests = $nd_booking_custom_field_array[2];
                    $nd_booking_booking_form_phone = $nd_booking_custom_field_array[3];
                    $nd_booking_booking_form_arrival = $nd_booking_custom_field_array[4];
                    $nd_booking_booking_form_services = $nd_booking_custom_field_array[5];
                    $nd_booking_booking_form_requests = $nd_booking_custom_field_array[6];
                    $nd_booking_booking_form_coupon = $nd_booking_custom_field_array[7];

                }else{
                    
                    $nd_booking_paypal_error = 1;

                }
                //END if is success


            }else
            {
                //$error_message = $nd_booking_paypal_response->get_error_message();
                $nd_booking_paypal_error = 1;
            }
            //END if is 200



        }
        //END BUILT VARIABLES DEPENDING ON PAYMENT METHODS





        //START extra services
        $nd_booking_booking_form_extra_services = '';

        $nd_booking_additional_services_array = explode(',', $nd_booking_booking_form_services );
        for ($nd_booking_additional_services_array_i = 0; $nd_booking_additional_services_array_i < count($nd_booking_additional_services_array)-1; $nd_booking_additional_services_array_i++) {
            
            $nd_booking_service_id = $nd_booking_additional_services_array[$nd_booking_additional_services_array_i];

            //metabox
            $nd_booking_meta_box_cpt_2_price = get_post_meta( $nd_booking_service_id, 'nd_booking_meta_box_cpt_2_price', true );
            $nd_booking_meta_box_cpt_2_price_type_1 = get_post_meta( $nd_booking_service_id, 'nd_booking_meta_box_cpt_2_price_type_1', true );
            if ( $nd_booking_meta_box_cpt_2_price_type_1 == '' ) { $nd_booking_meta_box_cpt_2_price_type_1 = 'nd_booking_price_type_person'; }
            $nd_booking_meta_box_cpt_2_price_type_2 = get_post_meta( $nd_booking_service_id, 'nd_booking_meta_box_cpt_2_price_type_2', true );
            if ( $nd_booking_meta_box_cpt_2_price_type_2 == '' ) { $nd_booking_meta_box_cpt_2_price_type_2 = 'nd_booking_price_type_day'; }

            //operator
            if ( $nd_booking_meta_box_cpt_2_price_type_1 == 'nd_booking_price_type_person' ) {
                $nd_booking_operator_1 = $nd_booking_booking_form_guests;
            }else{
                $nd_booking_operator_1 = 1; 
            }
            if ( $nd_booking_meta_box_cpt_2_price_type_2 == 'nd_booking_price_type_day' ) {
                $nd_booking_operator_2 = nd_booking_get_number_night($nd_booking_booking_form_date_from,$nd_booking_booking_form_date_to);
            }else{
                $nd_booking_operator_2 = 1; 
            }
            
            $nd_booking_additional_service_total_price = $nd_booking_meta_box_cpt_2_price*$nd_booking_operator_1*$nd_booking_operator_2;

            $nd_booking_booking_form_extra_services .= $nd_booking_service_id.'['.$nd_booking_additional_service_total_price.'],';

        }
        //END extra services

        
        //translations action type
        if ( $nd_booking_booking_form_action_type == 'bank_transfer' ) {
            $nd_booking_booking_form_action_type_lang = __('Bank Transfer','nd-booking');
        }elseif ( $nd_booking_booking_form_action_type == 'payment_on_arrive' ) {
            $nd_booking_booking_form_action_type_lang = __('Payment on arrive','nd-booking');
        }elseif ( $nd_booking_booking_form_action_type == 'booking_request' ) {
            $nd_booking_booking_form_action_type_lang = __('Booking Request','nd-booking');
        }elseif ( $nd_booking_booking_form_action_type == 'stripe' ) {
            $nd_booking_booking_form_action_type_lang = __('Stripe','nd-booking');
        }else{
            $nd_booking_booking_form_action_type_lang = __('Paypal','nd-booking');   
        }

        include realpath(dirname( __FILE__ ).'/include/thankyou/nd_booking_thankyou_left_content.php'); 
        include realpath(dirname( __FILE__ ).'/include/thankyou/nd_booking_thankyou_right_content.php'); 
        
        $nd_booking_shortcode_result .= '

        <div class="nd_booking_section">
        

            <div class="nd_booking_float_left nd_booking_width_33_percentage nd_booking_width_100_percentage_responsive nd_booking_padding_0_responsive nd_booking_padding_right_15 nd_booking_box_sizing_border_box">
                
                '.$nd_booking_shortcode_left_content.'

            </div>

            <div class="nd_booking_float_left nd_booking_width_66_percentage nd_booking_width_100_percentage_responsive nd_booking_padding_0_responsive nd_booking_padding_left_15 nd_booking_box_sizing_border_box">
                
                '.$nd_booking_shortcode_right_content.'

            </div>

        </div>
        ';


        //START check if user is logged
        if ( is_user_logged_in() == 1 ) {
          $nd_booking_current_user = wp_get_current_user();
          $nd_booking_current_user_id = $nd_booking_current_user->ID;
        }else{
          $nd_booking_current_user_id = 0; 
        }
        //END check if user is logged


        $nd_booking_booking_id = nd_booking_add_booking_in_db(
  
          $nd_booking_id_room,
          get_the_title($nd_booking_id_room),
          $nd_booking_date,
          $nd_booking_booking_form_date_from,
          $nd_booking_booking_form_date_to,
          $nd_booking_booking_form_guests,
          $nd_booking_booking_form_final_price,
          $nd_booking_booking_form_extra_services,
          $nd_booking_current_user_id,
          $nd_booking_booking_form_name,
          $nd_booking_booking_form_surname,
          $nd_booking_booking_form_email,
          $nd_booking_booking_form_phone,
          $nd_booking_booking_form_address.' '.$nd_booking_booking_form_zip,
          $nd_booking_booking_form_city,
          $nd_booking_booking_form_country,
          $nd_booking_booking_form_requests,
          $nd_booking_booking_form_arrival,
          $nd_booking_booking_form_coupon,
          $nd_booking_booking_form_payment_status,
          $nd_booking_booking_form_currency,
          $nd_booking_paypal_tx,
          $nd_booking_booking_form_action_type,
          $nd_booking_guest_id_front,
          $nd_booking_guest_id_back

        );

        update_post_meta( $nd_booking_booking_id, 'guest_id_front', esc_url_raw( $nd_booking_guest_id_front ) );
        update_post_meta( $nd_booking_booking_id, 'guest_id_back', esc_url_raw( $nd_booking_guest_id_back ) );
        update_post_meta( $nd_booking_booking_id, 'guest_id_front_id', absint( $nd_booking_guest_id_front_id ) );
        update_post_meta( $nd_booking_booking_id, 'guest_id_back_id', absint( $nd_booking_guest_id_back_id ) );
        update_post_meta( $nd_booking_booking_id, 'guest_id_number', sanitize_text_field( $nd_booking_guest_id_number ) );
        update_post_meta( $nd_booking_booking_id, 'guest_id_type', sanitize_text_field( $nd_booking_guest_id_type ) );
        update_post_meta( $nd_booking_booking_id, 'nd_booking_tax_lodging', $nd_booking_tax_lodging );
        update_post_meta( $nd_booking_booking_id, 'nd_booking_tax_gst', $nd_booking_tax_gst );
        update_post_meta( $nd_booking_booking_id, 'nd_booking_tax_qst', $nd_booking_tax_qst );

        if (
            $nd_booking_booking_form_action_type === 'stripe' &&
            $nd_booking_booking_form_payment_status === 'Completed'
        ) {
            $payload = [
                'guest_email'   => $nd_booking_booking_form_email,
                'room_type'     => $nd_booking_checkout_form_post_title,
                'check_in_date' => $nd_booking_booking_form_date_from,
                'check_out_date'=> $nd_booking_booking_form_date_to,
                'booking_id'    => $nd_booking_booking_id,
                'first_name'    => $nd_booking_booking_form_name,
                'last_name'     => $nd_booking_booking_form_surname,
            ];

            do_action( 'nd_booking_stripe_payment_complete', $payload );
        }

        if (function_exists('add_booking_to_google_calendar')) {
            $summary = sprintf(
                'Booking for %s %s',
                $nd_booking_booking_form_name,
                $nd_booking_booking_form_surname
            );

            add_booking_to_google_calendar(
                $summary,
                $nd_booking_booking_form_date_from,
                $nd_booking_booking_form_date_to
            );
        }
        if (function_exists('create_keychain_in_butterflymx')) {
            $args = [
                'name'        => $nd_booking_booking_form_name,
                'surname'     => $nd_booking_booking_form_surname,
                'email'       => $nd_booking_booking_form_email,
                'start'       => $nd_booking_booking_form_date_from,
                'end'         => $nd_booking_booking_form_date_to,
                'room_id'     => $nd_booking_id_room,
                // add other fields as needed
            ];
            create_keychain_in_butterflymx($args);
        }

        if (function_exists('trigger_amelia_booking_webhook')) {
            $args = [
                'name'        => $nd_booking_booking_form_name,
                'surname'     => $nd_booking_booking_form_surname,
                'email'       => $nd_booking_booking_form_email,
                'phone'       => $nd_booking_booking_form_phone,
                'start'       => $nd_booking_booking_form_date_from,
                'end'         => $nd_booking_booking_form_date_to,
                'room_id'     => $nd_booking_id_room,
                'payment_id'  => $nd_booking_booking_form_payment_id, // if you store Stripe id
                'loft_number' => $nd_booking_id_room,
                'key_code'    => $generated_key_code // if available
            ];
            trigger_amelia_booking_webhook($args);
        }


    //END EASY PAYMENT
    }else{
    



        $nd_booking_shortcode_result .= '

            <div class="nd_booking_section">
            
                <div class="nd_booking_float_left nd_booking_width_100_percentage nd_booking_box_sizing_border_box">
                    <p>'.__('Please select a room to make a reservation','nd-booking').'</p>
                    <div class="nd_booking_section nd_booking_height_20"></div>
                    <a href="'.nd_booking_search_page().'" class="nd_booking_bg_yellow nd_booking_padding_15_30_important nd_options_second_font_important nd_booking_border_radius_0_important nd_options_color_white nd_booking_cursor_pointer nd_booking_display_inline_block nd_booking_font_size_11 nd_booking_font_weight_bold nd_booking_letter_spacing_2">'.__('RETURN TO SEARCH PAGE','nd-booking').'</a>
                </div>

            </div>
        
        '; 

    }


    return $nd_booking_shortcode_result;
		


}
add_shortcode('nd_booking_checkout', 'nd_booking_shortcode_checkout');
//END nd_booking_checkout

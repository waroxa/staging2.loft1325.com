<?php
wp_enqueue_script( 'jquery-ui-tabs' );

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

$nd_booking_payment_heading = $nd_booking_is_english ? 'Payment details' : 'Détails de paiement';
$nd_booking_payment_status = $nd_booking_is_english ? 'Pending payment' : 'Paiement en attente';
$nd_booking_payment_cta = $nd_booking_is_english ? 'Confirm reservation' : 'Confirmer la réservation';
$nd_booking_free_message = $nd_booking_is_english
    ? 'Your coupon covers the full stay. No payment is required to confirm the reservation.'
    : 'Votre coupon couvre le séjour complet. Aucun paiement n’est requis pour confirmer la réservation.';

$nd_booking_total_due = floatval( $nd_booking_booking_form_final_price );
$nd_booking_is_free_payment = ( $nd_booking_total_due <= 0.01 );
$nd_booking_action_type = $nd_booking_is_free_payment ? 'free' : 'stripe';
$nd_booking_payment_status_value = $nd_booking_is_free_payment ? 'Completed' : $nd_booking_payment_status;

ob_start();
?>
    <div class="section loft-section-payment">
        <h3><span class="section-icon" aria-hidden="true">💳</span> <?php echo esc_html( $nd_booking_payment_heading ); ?></h3>
        <div class="section-body">
            <div class="loft-payment-info"><?php echo do_shortcode( get_option( 'nd_booking_stripe_checkout' ) ); ?></div>
            <div class="loft-payment-card">
                <?php if ( ! $nd_booking_is_free_payment ) : ?>
                    <script src="https://js.stripe.com/v3/"></script>
                <?php else : ?>
                    <p class="loft-payment-free-message"><?php echo esc_html( $nd_booking_free_message ); ?></p>
                <?php endif; ?>
                <form action="<?php echo esc_url( nd_booking_checkout_page() ); ?>" method="post" id="payment-form" class="loft-payment-form">
                    <?php if ( ! $nd_booking_is_free_payment ) : ?>
                        <div class="form-row">
                            <div id="card-element"></div>
                            <div class="loft-card-errors" id="card-errors" role="alert"></div>
                        </div>
                    <?php endif; ?>
                    <input type="hidden" id="nd_booking_form_checkout_arrive" name="nd_booking_form_checkout_arrive" value="2">
                    <input type="hidden" id="nd_booking_checkout_form_date_from" name="nd_booking_checkout_form_date_from" value="<?php echo esc_attr( $nd_booking_booking_form_date_from ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_date_top" name="nd_booking_checkout_form_date_top" value="<?php echo esc_attr( $nd_booking_booking_form_date_to ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_guests" name="nd_booking_checkout_form_guests" value="<?php echo esc_attr( $nd_booking_booking_form_guests ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_final_price" name="nd_booking_checkout_form_final_price" value="<?php echo esc_attr( nd_booking_format_decimal( $nd_booking_booking_form_final_price ) ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_base_price" name="nd_booking_checkout_form_base_price" value="<?php echo esc_attr( nd_booking_format_decimal( $nd_booking_tax_breakdown['base'] ) ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_post_id" name="nd_booking_checkout_form_post_id" value="<?php echo esc_attr( $nd_booking_booking_form_post_id . '-' . $nd_booking_id_room ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_post_title" name="nd_booking_checkout_form_post_title" value="<?php echo esc_attr( $nd_booking_booking_form_post_title ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_name" name="nd_booking_checkout_form_name" value="<?php echo esc_attr( $nd_booking_booking_form_name ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_surname" name="nd_booking_checkout_form_surname" value="<?php echo esc_attr( $nd_booking_booking_form_surname ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_email" name="nd_booking_checkout_form_email" value="<?php echo esc_attr( $nd_booking_booking_form_email ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_phone" name="nd_booking_checkout_form_phone" value="<?php echo esc_attr( $nd_booking_booking_form_phone ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_address" name="nd_booking_checkout_form_address" value="<?php echo esc_attr( $nd_booking_booking_form_address ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_city" name="nd_booking_checkout_form_city" value="<?php echo esc_attr( $nd_booking_booking_form_city ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_country" name="nd_booking_checkout_form_country" value="<?php echo esc_attr( $nd_booking_booking_form_country ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_zip" name="nd_booking_checkout_form_zip" value="<?php echo esc_attr( $nd_booking_booking_form_zip ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_requets" name="nd_booking_checkout_form_requets" value="<?php echo esc_attr( $nd_booking_booking_form_requests ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_arrival" name="nd_booking_checkout_form_arrival" value="<?php echo esc_attr( $nd_booking_booking_form_arrival ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_coupon" name="nd_booking_checkout_form_coupon" value="<?php echo esc_attr( $nd_booking_booking_form_coupon ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_guest_id_front" name="nd_booking_checkout_form_guest_id_front" value="<?php echo esc_attr( $nd_booking_guest_id_front_token ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_guest_id_back" name="nd_booking_checkout_form_guest_id_back" value="<?php echo esc_attr( $nd_booking_guest_id_back_token ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_guest_id_number" name="nd_booking_checkout_form_guest_id_number" value="<?php echo esc_attr( $nd_booking_guest_id_number ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_guest_id_type" name="nd_booking_checkout_form_guest_id_type" value="<?php echo esc_attr( $nd_booking_guest_id_type ); ?>">
                    <input type="hidden" id="nd_booking_checkout_form_term" name="nd_booking_checkout_form_term" value="<?php echo esc_attr( $nd_booking_booking_form_term ); ?>">
                    <input type="hidden" id="nd_booking_booking_form_services" name="nd_booking_booking_form_services" value="<?php echo esc_attr( $nd_booking_booking_form_services ); ?>">
                    <input type="hidden" id="nd_booking_booking_form_action_type" name="nd_booking_booking_form_action_type" value="<?php echo esc_attr( $nd_booking_action_type ); ?>">
                    <input type="hidden" id="nd_booking_booking_form_payment_status" name="nd_booking_booking_form_payment_status" value="<?php echo esc_attr( $nd_booking_payment_status_value ); ?>">
                    <button type="submit" class="button-primary"><?php echo esc_html( $nd_booking_payment_cta ); ?></button>
                </form>
                <?php if ( ! $nd_booking_is_free_payment ) : ?>
                    <script type="text/javascript">
                        (function($) {
                            var stripe = Stripe("<?php echo esc_js( get_option( 'nd_booking_stripe_public_key' ) ); ?>");
                            var elements = stripe.elements();
                            var style = {
                                base: {
                                    color: "#1f2933",
                                    lineHeight: "24px",
                                    fontFamily: "Roboto, sans-serif",
                                    fontSmoothing: "antialiased",
                                    fontSize: "16px",
                                    "::placeholder": { color: "#9AA5B1" }
                                },
                                invalid: {
                                    color: "#EF4444",
                                    iconColor: "#EF4444"
                                }
                            };
                            var card = elements.create("card", { style: style, hidePostalCode: true });
                            card.mount("#card-element");
                            card.addEventListener("change", function(event) {
                                var displayError = document.getElementById("card-errors");
                                if (event.error) {
                                    displayError.textContent = event.error.message;
                                } else {
                                    displayError.textContent = "";
                                }
                            });
                            var form = document.getElementById("payment-form");
                            form.addEventListener("submit", function(event) {
                                event.preventDefault();
                                stripe.createToken(card).then(function(result) {
                                    if (result.error) {
                                        var errorElement = document.getElementById("card-errors");
                                        errorElement.textContent = result.error.message;
                                    } else {
                                        stripeTokenHandler(result.token);
                                    }
                                });
                            });
                            function stripeTokenHandler(token) {
                                var form = document.getElementById("payment-form");
                                var hiddenInput = document.createElement("input");
                                hiddenInput.setAttribute("type", "hidden");
                                hiddenInput.setAttribute("name", "stripeToken");
                                hiddenInput.setAttribute("value", token.id);
                                form.appendChild(hiddenInput);
                                form.submit();
                            }
                        })(jQuery);
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php
$nd_booking_shortcode_right_content .= ob_get_clean();

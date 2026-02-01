<?php
$nd_booking_checkout_tax_breakdown = isset( $nd_booking_tax_breakdown ) && is_array( $nd_booking_tax_breakdown )
    ? $nd_booking_tax_breakdown
    : nd_booking_calculate_tax_breakdown_from_total( $nd_booking_booking_form_final_price );

$nd_booking_booking_original_price = isset( $nd_booking_booking_original_price )
    ? $nd_booking_booking_original_price
    : $nd_booking_booking_form_final_price;

$nd_booking_checkout_currency              = nd_booking_get_currency();
$nd_booking_checkout_subtotal_formatted    = nd_booking_format_decimal( $nd_booking_checkout_tax_breakdown['base'] );
$nd_booking_checkout_total_formatted       = nd_booking_format_decimal( $nd_booking_checkout_tax_breakdown['total'] );
$nd_booking_checkout_tax_total_formatted   = nd_booking_format_decimal( $nd_booking_checkout_tax_breakdown['total_tax'] );
$nd_booking_checkout_nights                = absint( nd_booking_get_number_night( $nd_booking_booking_form_date_from, $nd_booking_booking_form_date_to ) );
$nd_booking_checkout_nightly_rate          = 0;

if ( $nd_booking_checkout_nights > 0 ) {
    $nd_booking_checkout_nightly_rate = floatval( $nd_booking_checkout_tax_breakdown['base'] ) / $nd_booking_checkout_nights;
}

$nd_booking_checkout_nightly_rate_formatted = nd_booking_format_decimal( $nd_booking_checkout_nightly_rate );
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

$nd_booking_checkout_nights_label = $nd_booking_is_english
    ? _n( 'night', 'nights', $nd_booking_checkout_nights, 'nd-booking' )
    : _n( 'nuit', 'nuits', $nd_booking_checkout_nights, 'nd-booking' );

$nd_booking_checkout_known_tax_labels = array(
    'lodging' => $nd_booking_is_english ? __( 'Lodging tax', 'nd-booking' ) : __( 'Taxe d’hébergement', 'nd-booking' ),
    'gst'     => $nd_booking_is_english ? __( 'GST', 'nd-booking' ) : __( 'TPS', 'nd-booking' ),
    'qst'     => $nd_booking_is_english ? __( 'QST', 'nd-booking' ) : __( 'TVQ', 'nd-booking' ),
);

$nd_booking_checkout_tax_lines  = '<ul class="loft-price-breakdown">';
$nd_booking_checkout_tax_lines .= '<li class="breakdown-row" data-tax-key="nightly_rate" data-nights="' . esc_attr( $nd_booking_checkout_nights ) . '">';
$nd_booking_checkout_tax_lines .= '<span class="label">' . esc_html( $nd_booking_is_english ? __( 'Nightly rate', 'nd-booking' ) : __( 'Tarif par nuit', 'nd-booking' ) ) . '</span>';
$nd_booking_checkout_tax_lines .= '<span class="value">' . esc_html( $nd_booking_checkout_nightly_rate_formatted ) . ' <span class="currency">' . esc_html( $nd_booking_checkout_currency ) . '</span> &times; ' . esc_html( $nd_booking_checkout_nights ) . ' ' . esc_html( $nd_booking_checkout_nights_label ) . '</span>';
$nd_booking_checkout_tax_lines .= '</li>';
$nd_booking_checkout_tax_lines .= '<li class="breakdown-row" data-tax-key="subtotal">';
$nd_booking_checkout_tax_lines .= '<span class="label">' . esc_html( $nd_booking_is_english ? __( 'Subtotal', 'nd-booking' ) : __( 'Sous-total', 'nd-booking' ) ) . '</span>';
$nd_booking_checkout_tax_lines .= '<span class="value">' . esc_html( $nd_booking_checkout_subtotal_formatted ) . ' <span class="currency">' . esc_html( $nd_booking_checkout_currency ) . '</span></span>';
$nd_booking_checkout_tax_lines .= '</li>';

foreach ( $nd_booking_checkout_known_tax_labels as $nd_booking_tax_key => $nd_booking_tax_label ) {
    $nd_booking_line_style          = '';
    $nd_booking_tax_amount_formatted = nd_booking_format_decimal( 0 );
    $nd_booking_display_label       = $nd_booking_tax_label;

    if ( isset( $nd_booking_checkout_tax_breakdown['taxes'][ $nd_booking_tax_key ] ) ) {
        $nd_booking_tax_rate             = nd_booking_format_percentage( $nd_booking_checkout_tax_breakdown['taxes'][ $nd_booking_tax_key ]['rate'] );
        $nd_booking_tax_amount_formatted = nd_booking_format_decimal( $nd_booking_checkout_tax_breakdown['taxes'][ $nd_booking_tax_key ]['amount'] );
        $nd_booking_display_label        = sprintf( __( '%1$s (%2$s%%)', 'nd-booking' ), $nd_booking_checkout_tax_breakdown['taxes'][ $nd_booking_tax_key ]['label'], $nd_booking_tax_rate );
    } else {
        $nd_booking_line_style = ' class="breakdown-row breakdown-row--hidden"';
    }

    $nd_booking_checkout_tax_lines .= '<li' . $nd_booking_line_style . ' data-tax-key="' . esc_attr( $nd_booking_tax_key ) . '">';
    $nd_booking_checkout_tax_lines .= '<span class="label">' . esc_html( $nd_booking_display_label ) . '</span>';
    $nd_booking_checkout_tax_lines .= '<span class="value">' . esc_html( $nd_booking_tax_amount_formatted ) . ' <span class="currency">' . esc_html( $nd_booking_checkout_currency ) . '</span></span>';
    $nd_booking_checkout_tax_lines .= '</li>';
}

$nd_booking_checkout_tax_lines .= '<li class="breakdown-row breakdown-row--total" data-tax-key="total_tax">';
$nd_booking_checkout_tax_lines .= '<span class="label">' . esc_html( $nd_booking_is_english ? __( 'Total taxes', 'nd-booking' ) : __( 'Total des taxes', 'nd-booking' ) ) . '</span>';
$nd_booking_checkout_tax_lines .= '<span class="value">' . esc_html( $nd_booking_checkout_tax_total_formatted ) . ' <span class="currency">' . esc_html( $nd_booking_checkout_currency ) . '</span></span>';
$nd_booking_checkout_tax_lines .= '</li>';
$nd_booking_checkout_tax_lines .= '<li class="breakdown-row breakdown-row--grand" data-tax-key="grand_total">';
$nd_booking_checkout_tax_lines .= '<span class="label">' . esc_html( $nd_booking_is_english ? __( 'Grand total', 'nd-booking' ) : __( 'Total général', 'nd-booking' ) ) . '</span>';
$nd_booking_checkout_tax_lines .= '<span class="value">' . esc_html( $nd_booking_checkout_total_formatted ) . ' <span class="currency">' . esc_html( $nd_booking_checkout_currency ) . '</span></span>';
$nd_booking_checkout_tax_lines .= '</li>';
$nd_booking_checkout_tax_lines .= '</ul>';

$nd_booking_contact_fields = array(
    array(
        'label' => $nd_booking_is_english ? __( 'First name', 'nd-booking' ) : __( 'Prénom', 'nd-booking' ),
        'value' => $nd_booking_booking_form_name,
    ),
    array(
        'label' => $nd_booking_is_english ? __( 'Last name', 'nd-booking' ) : __( 'Nom', 'nd-booking' ),
        'value' => $nd_booking_booking_form_surname,
    ),
    array(
        'label' => $nd_booking_is_english ? __( 'Email', 'nd-booking' ) : __( 'Courriel', 'nd-booking' ),
        'value' => $nd_booking_booking_form_email,
    ),
    array(
        'label' => $nd_booking_is_english ? __( 'Phone', 'nd-booking' ) : __( 'Téléphone', 'nd-booking' ),
        'value' => $nd_booking_booking_form_phone,
    ),
);

$nd_booking_address_fields = array(
    array(
        'label' => $nd_booking_is_english ? __( 'Address', 'nd-booking' ) : __( 'Adresse', 'nd-booking' ),
        'value' => $nd_booking_booking_form_address,
    ),
    array(
        'label' => $nd_booking_is_english ? __( 'City', 'nd-booking' ) : __( 'Ville', 'nd-booking' ),
        'value' => $nd_booking_booking_form_city,
    ),
    array(
        'label' => $nd_booking_is_english ? __( 'Country', 'nd-booking' ) : __( 'Pays', 'nd-booking' ),
        'value' => $nd_booking_booking_form_country,
    ),
    array(
        'label' => $nd_booking_is_english ? __( 'Postal code', 'nd-booking' ) : __( 'Code postal', 'nd-booking' ),
        'value' => $nd_booking_booking_form_zip,
    ),
);

$nd_booking_requests_value = trim( (string) $nd_booking_booking_form_requests );
$nd_booking_requests_markup = $nd_booking_requests_value !== ''
    ? '<p>' . nl2br( esc_html( $nd_booking_requests_value ) ) . '</p>'
    : '<p class="loft-empty">' . esc_html( $nd_booking_is_english ? __( 'No special requests.', 'nd-booking' ) : __( 'Aucune demande particulière.', 'nd-booking' ) ) . '</p>';

$nd_booking_arrival_value = trim( (string) $nd_booking_booking_form_arrival );
$nd_booking_arrival_markup = $nd_booking_arrival_value !== ''
    ? esc_html( $nd_booking_arrival_value )
    : esc_html( $nd_booking_is_english ? __( 'Not specified', 'nd-booking' ) : __( 'Non précisé', 'nd-booking' ) );

$nd_booking_services_markup = '';
if ( '' === $nd_booking_booking_form_services ) {
    $nd_booking_services_markup = '<p class="loft-empty">' . esc_html( $nd_booking_is_english ? __( 'No additional services selected.', 'nd-booking' ) : __( 'Aucun service additionnel sélectionné.', 'nd-booking' ) ) . '</p>';
} else {
    $nd_booking_services_markup .= '<ul class="loft-service-list">';
    $nd_booking_services_array = explode( ',', $nd_booking_booking_form_services );
    foreach ( $nd_booking_services_array as $nd_booking_services_array_value ) {
        $nd_booking_service_id = absint( $nd_booking_services_array_value );
        if ( ! $nd_booking_service_id ) {
            continue;
        }
        $nd_booking_service_name = get_the_title( $nd_booking_service_id );
        if ( '' === $nd_booking_service_name ) {
            continue;
        }
        $nd_booking_services_markup .= '<li>' . esc_html( $nd_booking_service_name ) . '</li>';
    }
    $nd_booking_services_markup .= '</ul>';
    if ( '<ul class="loft-service-list"></ul>' === $nd_booking_services_markup ) {
        $nd_booking_services_markup = '<p class="loft-empty">' . esc_html( $nd_booking_is_english ? __( 'No additional services selected.', 'nd-booking' ) : __( 'Aucun service additionnel sélectionné.', 'nd-booking' ) ) . '</p>';
    }
}

$nd_booking_coupon_section_markup = '';
$nd_booking_coupon_class = nd_booking_get_coupon_enable_class();
if ( '' === $nd_booking_coupon_class ) {
    $nd_booking_coupon_section_markup .= '<div class="loft-info-item">';
    $nd_booking_coupon_section_markup .= '<label>' . esc_html( $nd_booking_is_english ? __( 'Promo code', 'nd-booking' ) : __( 'Code promotionnel', 'nd-booking' ) ) . '</label>';
    if ( $nd_booking_booking_original_price != $nd_booking_booking_form_final_price ) {
        $nd_booking_coupon_section_markup .= '<p>' . esc_html( $nd_booking_booking_form_coupon ) . ' · ' . esc_html( $nd_booking_is_english ? __( 'Discount applied', 'nd-booking' ) : __( 'Rabais appliqué', 'nd-booking' ) ) . '</p>';
    } else {
        $nd_booking_coupon_section_markup .= '<p class="loft-empty">' . esc_html( $nd_booking_is_english ? __( 'No coupon applied.', 'nd-booking' ) : __( 'Aucun coupon appliqué.', 'nd-booking' ) ) . '</p>';
    }
    $nd_booking_coupon_section_markup .= '</div>';
}

$nd_booking_shortcode_right_content  = '<div class="loft-booking-form">';
$nd_booking_shortcode_right_content .= '<div class="loft-progress-indicator">' . esc_html( $nd_booking_is_english ? __( 'Step 2 of 3', 'nd-booking' ) : __( 'Étape 2 sur 3', 'nd-booking' ) ) . '</div>';

$nd_booking_shortcode_right_content .= '<div class="section loft-section-contact">';
$nd_booking_shortcode_right_content .= '<h3><span class="section-icon" aria-hidden="true">🧍</span> ' . esc_html( $nd_booking_is_english ? __( 'Your details', 'nd-booking' ) : __( 'Vos informations', 'nd-booking' ) ) . '</h3>';
$nd_booking_shortcode_right_content .= '<div class="section-body">';
$nd_booking_shortcode_right_content .= '<div class="loft-info-grid">';
foreach ( $nd_booking_contact_fields as $nd_booking_field ) {
    if ( '' === $nd_booking_field['value'] ) {
        continue;
    }
    $nd_booking_shortcode_right_content .= '<div class="loft-info-item">';
    $nd_booking_shortcode_right_content .= '<label>' . esc_html( $nd_booking_field['label'] ) . '</label>';
    $nd_booking_shortcode_right_content .= '<p>' . esc_html( $nd_booking_field['value'] ) . '</p>';
    $nd_booking_shortcode_right_content .= '</div>';
}
$nd_booking_shortcode_right_content .= '</div>';
$nd_booking_shortcode_right_content .= '</div>';
$nd_booking_shortcode_right_content .= '</div>';

$nd_booking_shortcode_right_content .= '<div class="section loft-section-address">';
$nd_booking_shortcode_right_content .= '<h3><span class="section-icon" aria-hidden="true">🏠</span> ' . esc_html( $nd_booking_is_english ? __( 'Billing address', 'nd-booking' ) : __( 'Adresse de facturation', 'nd-booking' ) ) . '</h3>';
$nd_booking_shortcode_right_content .= '<div class="section-body">';
$nd_booking_shortcode_right_content .= '<div class="loft-info-grid">';
foreach ( $nd_booking_address_fields as $nd_booking_field ) {
    if ( '' === $nd_booking_field['value'] ) {
        continue;
    }
    $nd_booking_shortcode_right_content .= '<div class="loft-info-item">';
    $nd_booking_shortcode_right_content .= '<label>' . esc_html( $nd_booking_field['label'] ) . '</label>';
    $nd_booking_shortcode_right_content .= '<p>' . esc_html( $nd_booking_field['value'] ) . '</p>';
    $nd_booking_shortcode_right_content .= '</div>';
}
$nd_booking_shortcode_right_content .= '</div>';
$nd_booking_shortcode_right_content .= '</div>';
$nd_booking_shortcode_right_content .= '</div>';

$nd_booking_conditions_markup  = '<div class="section loft-section-conditions">';
$nd_booking_conditions_markup .= '<h3><span class="section-icon" aria-hidden="true">📋</span> ' . esc_html( $nd_booking_is_english ? __( 'Reservation details', 'nd-booking' ) : __( 'Conditions de réservation', 'nd-booking' ) ) . '</h3>';
$nd_booking_conditions_markup .= '<div class="section-body">';
$nd_booking_conditions_markup .= '<div class="loft-info-item">';
$nd_booking_conditions_markup .= '<label>' . esc_html( $nd_booking_is_english ? __( 'Arrival', 'nd-booking' ) : __( 'Arrivée', 'nd-booking' ) ) . '</label>';
$nd_booking_conditions_markup .= '<p>' . $nd_booking_arrival_markup . '</p>';
$nd_booking_conditions_markup .= '</div>';
$nd_booking_conditions_markup .= '<div class="loft-info-item">';
$nd_booking_conditions_markup .= '<label>' . esc_html( $nd_booking_is_english ? __( 'Special requests', 'nd-booking' ) : __( 'Demandes particulières', 'nd-booking' ) ) . '</label>';
$nd_booking_conditions_markup .= $nd_booking_requests_markup;
$nd_booking_conditions_markup .= '</div>';
$nd_booking_conditions_markup .= '<div class="loft-info-item">';
$nd_booking_conditions_markup .= '<label>' . esc_html( $nd_booking_is_english ? __( 'Additional services', 'nd-booking' ) : __( 'Services additionnels', 'nd-booking' ) ) . '</label>';
$nd_booking_conditions_markup .= $nd_booking_services_markup;
$nd_booking_conditions_markup .= '</div>';
if ( '' !== $nd_booking_coupon_section_markup ) {
$nd_booking_conditions_markup .= $nd_booking_coupon_section_markup;
}
$nd_booking_conditions_markup .= '<div class="loft-price-summary">';
$nd_booking_conditions_markup .= '<h4>' . esc_html( $nd_booking_is_english ? __( 'Price summary', 'nd-booking' ) : __( 'Sommaire des prix', 'nd-booking' ) ) . '</h4>';
$nd_booking_conditions_markup .= $nd_booking_checkout_tax_lines;
$nd_booking_conditions_markup .= '</div>';
$nd_booking_conditions_markup .= '</div>';
$nd_booking_conditions_markup .= '</div>';

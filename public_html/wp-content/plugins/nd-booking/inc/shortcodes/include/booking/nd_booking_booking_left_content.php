<?php


//START price
$nd_booking_loft_rule = nd_booking_find_loft_pricing_rule( $nd_booking_form_booking_id );

if ( null !== $nd_booking_loft_rule ) {
    $nd_booking_loft_price          = nd_booking_calculate_loft_pricing( $nd_booking_loft_rule, $nd_booking_date_from, $nd_booking_date_to, $nd_booking_form_booking_guests );
    $nd_booking_trip_price          = $nd_booking_loft_price['total'];
    $nd_booking_trip_price_for_person = ( get_option( 'nd_booking_price_guests' ) == 1 && $nd_booking_form_booking_guests > 0 )
        ? $nd_booking_trip_price / $nd_booking_form_booking_guests
        : $nd_booking_trip_price;
} else {
    $nd_booking_trip_price_for_person = 0;
    $nd_booking_index = 1;
    $nd_booking_date_cicle = $nd_booking_date_from;
    while ($nd_booking_index <= nd_booking_get_number_night($nd_booking_date_from,$nd_booking_date_to)) {

        $nd_booking_trip_price_for_person = $nd_booking_trip_price_for_person + nd_booking_get_final_price($nd_booking_form_booking_id,$nd_booking_date_cicle);

        $nd_booking_date_cicle = date('Y/m/d', strtotime($nd_booking_date_cicle.' + 1 days'));

        $nd_booking_index++;
    }

    $nd_booking_price_guests_enable = get_option('nd_booking_price_guests');
    if ( $nd_booking_price_guests_enable == 1 ) {
      $nd_booking_trip_price = $nd_booking_trip_price_for_person*$nd_booking_form_booking_guests;
    }else{
      $nd_booking_trip_price = $nd_booking_trip_price_for_person;
    }
}
//END price


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

$nd_booking_breakdown_title = $nd_booking_is_english ? 'Price breakdown' : 'Détail du prix';
$nd_booking_breakdown_subtitle = $nd_booking_is_english ? 'As shown on your invoice' : 'Comme sur la facture finale';
$nd_booking_sidebar_heading = $nd_booking_is_english ? 'Your reservation' : 'Votre réservation';
$nd_booking_label_checkin = $nd_booking_is_english ? 'Check-in' : 'Arrivée';
$nd_booking_label_checkout = $nd_booking_is_english ? 'Check-out' : 'Départ';
$nd_booking_label_guests = $nd_booking_is_english ? 'Guests' : 'Invités';
$nd_booking_label_nights = $nd_booking_is_english ? 'Nights' : 'Nuits';
$nd_booking_label_total = $nd_booking_is_english ? 'Total' : 'Total';

$nd_booking_tax_breakdown = nd_booking_calculate_tax_breakdown( $nd_booking_trip_price );
$nd_booking_currency = nd_booking_get_currency();
$nd_booking_initial_total_formatted = nd_booking_format_decimal( $nd_booking_tax_breakdown['total'] );
$nd_booking_initial_subtotal_formatted = nd_booking_format_decimal( $nd_booking_tax_breakdown['base'] );
$nd_booking_initial_tax_total_formatted = nd_booking_format_decimal( $nd_booking_tax_breakdown['total_tax'] );

$nd_booking_known_tax_labels = array(
    'lodging' => __( 'Lodging Tax', 'nd-booking' ),
    'gst'     => __( 'GST', 'nd-booking' ),
    'qst'     => __( 'QST', 'nd-booking' ),
);

$nd_booking_tax_lines = '<div class="nd_booking_section nd_booking_margin_top_20 nd_booking_tax_breakdown loft1325-booking-breakdown">';
$nd_booking_tax_lines .= '<div class="loft1325-booking-breakdown-header"><p class="nd_booking_margin_0 nd_booking_font_size_14"><strong>'.$nd_booking_breakdown_title.'</strong></p><p class="nd_booking_margin_0 nd_booking_font_size_12">'.$nd_booking_breakdown_subtitle.'</p></div>';
$nd_booking_tax_lines .= '<div class="loft1325-booking-breakdown-row" data-tax-key="subtotal"><span class="nd_booking_tax_label">'.__( 'Subtotal', 'nd-booking' ).'</span> <span><span class="nd_booking_tax_amount">'.$nd_booking_initial_subtotal_formatted.'</span> <span class="nd_booking_tax_currency">'.$nd_booking_currency.'</span></span></div>';

foreach ( $nd_booking_known_tax_labels as $nd_booking_tax_key => $nd_booking_tax_label ) {
    $nd_booking_line_style = '';
    if ( isset( $nd_booking_tax_breakdown['taxes'][ $nd_booking_tax_key ] ) ) {
        $nd_booking_tax_rate = nd_booking_format_percentage( $nd_booking_tax_breakdown['taxes'][ $nd_booking_tax_key ]['rate'] );
        $nd_booking_tax_amount_formatted = nd_booking_format_decimal( $nd_booking_tax_breakdown['taxes'][ $nd_booking_tax_key ]['amount'] );
        $nd_booking_display_label = sprintf( __( '%1$s (%2$s%%)', 'nd-booking' ), $nd_booking_tax_breakdown['taxes'][ $nd_booking_tax_key ]['label'], $nd_booking_tax_rate );
    } else {
        $nd_booking_tax_amount_formatted = nd_booking_format_decimal( 0 );
        $nd_booking_tax_rate = nd_booking_format_percentage( 0 );
        $nd_booking_display_label = sprintf( __( '%1$s (%2$s%%)', 'nd-booking' ), $nd_booking_tax_label, $nd_booking_tax_rate );
        $nd_booking_line_style = ' style="display:none;"';
    }

    $nd_booking_tax_lines .= '<div class="loft1325-booking-breakdown-row" data-tax-key="'.$nd_booking_tax_key.'"'.$nd_booking_line_style.'><span class="nd_booking_tax_label">'.$nd_booking_display_label.'</span> <span><span class="nd_booking_tax_amount">'.$nd_booking_tax_amount_formatted.'</span> <span class="nd_booking_tax_currency">'.$nd_booking_currency.'</span></span></div>';
}

$nd_booking_tax_lines .= '<div class="loft1325-booking-breakdown-row" data-tax-key="total_tax"><span class="nd_booking_tax_label"><strong>'.__( 'Total Tax', 'nd-booking' ).'</strong></span> <span><strong class="nd_booking_tax_amount">'.$nd_booking_initial_tax_total_formatted.'</strong> <strong class="nd_booking_tax_currency">'.$nd_booking_currency.'</strong></span></div>';
$nd_booking_tax_lines .= '<div class="loft1325-booking-breakdown-row loft1325-booking-breakdown-row--total" data-tax-key="grand_total"><span class="nd_booking_tax_label"><strong>'.__( 'Grand Total', 'nd-booking' ).'</strong></span> <span><strong class="nd_booking_tax_amount">'.$nd_booking_initial_total_formatted.'</strong> <strong class="nd_booking_tax_currency">'.$nd_booking_currency.'</strong></span></div>';
$nd_booking_tax_lines .= '</div>';

$nd_booking_shortcode_left_content = '';


//image
$nd_booking_image_src = nd_booking_get_post_img_src($nd_booking_form_booking_id);
if ( $nd_booking_image_src != '' ) { 
    
    $nd_booking_image = '<img class="nd_booking_section" src="'.$nd_booking_image_src.'" alt="'.esc_attr( get_the_title( $nd_booking_form_booking_id ) ).'">';

}else{ 
    $nd_booking_image = '';
}


//date
$nd_booking_new_date_from = new DateTime($nd_booking_date_from);
$nd_booking_new_date_from_format_d = date_format($nd_booking_new_date_from, 'd');
$nd_booking_new_date_from_format_M = date_format($nd_booking_new_date_from, 'M');
$nd_booking_new_date_from_format_M = date_i18n('M',strtotime($nd_booking_date_from));
$nd_booking_new_date_from_format_l = date_format($nd_booking_new_date_from, 'l');
$nd_booking_new_date_from_format_l = date_i18n('l',strtotime($nd_booking_date_from));
$nd_booking_new_date_from_format_Y = date_format($nd_booking_new_date_from, 'Y');
$nd_booking_new_date_to = new DateTime($nd_booking_date_to);
$nd_booking_new_date_to_format_d = date_format($nd_booking_new_date_to, 'd');
$nd_booking_new_date_to_format_M = date_format($nd_booking_new_date_to, 'M');
$nd_booking_new_date_to_format_M = date_i18n('M',strtotime($nd_booking_date_to));
$nd_booking_new_date_to_format_l = date_format($nd_booking_new_date_to, 'l');
$nd_booking_new_date_to_format_l = date_i18n('l',strtotime($nd_booking_date_to));
$nd_booking_new_date_to_format_Y = date_format($nd_booking_new_date_to, 'Y');


$nd_booking_reviews_shortcode = do_shortcode( '[trustindex no-registration=google]' );

$nd_booking_shortcode_left_content .= '
<style>
  .loft1325-booking-sidebar {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    font-family: "Inter", "Helvetica Neue", Arial, sans-serif;
    color: #0f172a;
  }
  .loft1325-booking-sidebar .loft1325-booking-hero {
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid #e2e8f0;
  }
  .loft1325-booking-sidebar .loft1325-booking-hero img {
    width: 100%;
    display: block;
    height: 220px;
    object-fit: cover;
  }
  .loft1325-booking-sidebar .loft1325-booking-hero-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 8px;
    padding: 18px;
    text-align: center;
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.35) 0%, rgba(15, 23, 42, 0.65) 100%);
    color: #ffffff;
  }
  .loft1325-booking-sidebar .loft1325-booking-title {
    font-size: 13px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    font-weight: 600;
    margin: 0;
    text-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
  }
  .loft1325-booking-sidebar .loft1325-booking-stars {
    margin-top: 2px;
    font-size: 14px;
    letter-spacing: 2px;
    color: #f8d86a;
    text-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
  }
  .loft1325-booking-sidebar .loft1325-booking-body {
    padding: 22px;
    background: #0f172a;
    color: #ffffff;
  }
  .loft1325-booking-sidebar .loft1325-booking-heading {
    text-align: center;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0 0 18px;
    color: #e2e8f0;
  }
  .loft1325-booking-sidebar .loft1325-booking-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
  }
  .loft1325-booking-sidebar .loft1325-booking-stat {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 16px;
    padding: 16px;
    text-align: center;
    box-shadow: none;
  }
  .loft1325-booking-sidebar .loft1325-booking-stat h6 {
    margin: 0;
    font-size: 11px;
    letter-spacing: 0.24em;
    text-transform: uppercase;
    color: #cbd5f5;
  }
  .loft1325-booking-sidebar .loft1325-booking-stat .stat-number {
    font-size: 34px;
    font-weight: 600;
    margin: 10px 0 6px;
    text-shadow: none;
    color: #ffffff;
  }
  .loft1325-booking-sidebar .loft1325-booking-stat .stat-subtitle {
    font-size: 12px;
    color: #e2e8f0;
  }
  .loft1325-booking-sidebar .loft1325-booking-total {
    margin-top: 22px;
    padding: 18px;
    border-radius: 16px;
    background: #0b1220;
    border: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
  }
  .loft1325-booking-sidebar .loft1325-booking-total .amount {
    font-size: 36px;
    font-weight: 600;
    margin: 0;
  }
  .loft1325-booking-sidebar .loft1325-booking-total .label {
    font-size: 12px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #cbd5f5;
  }
  .loft1325-booking-sidebar .nd_booking_tax_breakdown {
    background: #ffffff;
    color: #0f172a;
    padding: 18px 22px 22px;
    border-top: 1px solid #e2e8f0;
  }
  .loft1325-booking-sidebar .loft1325-booking-reviews {
    padding: 18px 22px 24px;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
  }
  .loft1325-booking-sidebar .loft1325-booking-breakdown-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e5e7eb;
  }
  .loft1325-booking-sidebar .loft1325-booking-breakdown-row:last-child {
    border-bottom: none;
  }
  .nd_booking_booking_alert_login_register {
    display: none !important;
  }
  @media (max-width: 767px) {
    .loft1325-booking-sidebar .loft1325-booking-body {
      padding: 18px;
    }
    .loft1325-booking-sidebar .loft1325-booking-grid {
      gap: 12px;
    }
    .loft1325-booking-sidebar .loft1325-booking-hero img {
      height: 200px;
    }
  }
</style>

<div class="nd_booking_section nd_booking_box_sizing_border_box loft1325-booking-sidebar">
  <div class="loft1325-booking-hero">
    '.$nd_booking_image.'
    <div class="loft1325-booking-hero-overlay">
      <p class="loft1325-booking-title">'.esc_html( get_the_title( $nd_booking_form_booking_id ) ).'</p>
      <div class="loft1325-booking-stars" aria-hidden="true">★★★★★</div>
    </div>
  </div>

  <div class="loft1325-booking-body">
    <p class="loft1325-booking-heading">'.$nd_booking_sidebar_heading.'</p>
    <div class="loft1325-booking-grid">
      <div class="loft1325-booking-stat">
        <h6>'.$nd_booking_label_checkin.'</h6>
        <div class="stat-number">'.$nd_booking_new_date_from_format_d.'</div>
        <div class="stat-subtitle"><em>'.$nd_booking_new_date_from_format_M.', '.$nd_booking_new_date_from_format_Y.'</em></div>
        <div class="stat-subtitle">'.$nd_booking_new_date_from_format_l.'</div>
      </div>
      <div class="loft1325-booking-stat">
        <h6>'.$nd_booking_label_checkout.'</h6>
        <div class="stat-number">'.$nd_booking_new_date_to_format_d.'</div>
        <div class="stat-subtitle"><em>'.$nd_booking_new_date_to_format_M.', '.$nd_booking_new_date_to_format_Y.'</em></div>
        <div class="stat-subtitle">'.$nd_booking_new_date_to_format_l.'</div>
      </div>
      <div class="loft1325-booking-stat">
        <h6>'.$nd_booking_label_guests.'</h6>
        <div class="stat-number">'.$nd_booking_form_booking_guests.'</div>
        <div class="stat-subtitle">'.$nd_booking_label_guests.'</div>
      </div>
      <div class="loft1325-booking-stat">
        <h6>'.$nd_booking_label_nights.'</h6>
        <div class="stat-number">'.nd_booking_get_number_night($nd_booking_date_from,$nd_booking_date_to).'</div>
        <div class="stat-subtitle">'.$nd_booking_label_nights.'</div>
      </div>
    </div>

    <div class="loft1325-booking-total">
      <div class="amount">'.$nd_booking_initial_total_formatted.' '.$nd_booking_currency.'</div>
      <div class="label">'.$nd_booking_label_total.'</div>
    </div>
  </div>

  '.$nd_booking_tax_lines.'
  <div class="loft1325-booking-reviews">
    '.$nd_booking_reviews_shortcode.'
  </div>
</div>
';

<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wcc-admin-wrap">
  <h1 class="wcc-admin-title">
    <span class="dashicons dashicons-admin-settings"></span>
    <?php esc_html_e( 'Certificate Settings', 'wordcamp-certificate' ); ?>
  </h1>

  <form method="post" class="wcc-settings-form">
    <?php wp_nonce_field( 'wcc_settings' ); ?>

    <table class="form-table">
      <tr>
        <th scope="row">
          <label for="wcc_event_name"><?php esc_html_e( 'Event Name', 'wordcamp-certificate' ); ?></label>
        </th>
        <td>
          <input type="text" id="wcc_event_name" name="wcc_event_name" class="regular-text"
            value="<?php echo esc_attr( get_option( 'wcc_event_name', 'WordCamp' ) ); ?>" />
          <p class="description"><?php esc_html_e( 'e.g. WordCamp Manila 2025', 'wordcamp-certificate' ); ?></p>
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="wcc_event_date"><?php esc_html_e( 'Event Date', 'wordcamp-certificate' ); ?></label>
        </th>
        <td>
          <input type="text" id="wcc_event_date" name="wcc_event_date" class="regular-text"
            value="<?php echo esc_attr( get_option( 'wcc_event_date', '' ) ); ?>"
            placeholder="<?php esc_attr_e( 'e.g. July 12–13, 2025', 'wordcamp-certificate' ); ?>" />
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="wcc_event_location"><?php esc_html_e( 'Event Location', 'wordcamp-certificate' ); ?></label>
        </th>
        <td>
          <input type="text" id="wcc_event_location" name="wcc_event_location" class="regular-text"
            value="<?php echo esc_attr( get_option( 'wcc_event_location', '' ) ); ?>"
            placeholder="<?php esc_attr_e( 'e.g. Manila, Philippines', 'wordcamp-certificate' ); ?>" />
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="wcc_organizer_name"><?php esc_html_e( 'Organizer Name', 'wordcamp-certificate' ); ?></label>
        </th>
        <td>
          <input type="text" id="wcc_organizer_name" name="wcc_organizer_name" class="regular-text"
            value="<?php echo esc_attr( get_option( 'wcc_organizer_name', get_bloginfo( 'name' ) ) ); ?>" />
          <p class="description"><?php esc_html_e( 'Appears on the signature line of the certificate.', 'wordcamp-certificate' ); ?></p>
        </td>
      </tr>
      <tr>
        <th scope="row">
          <label for="wcc_event_logo"><?php esc_html_e( 'Event Logo', 'wordcamp-certificate' ); ?></label>
        </th>
        <td>
          <?php
          $wcc_logo_id  = (int) get_option( 'wcc_event_logo_id', 0 );
          $wcc_logo_url = $wcc_logo_id ? wp_get_attachment_image_url( $wcc_logo_id, 'medium' ) : '';
          ?>
          <div class="wcc-logo-uploader">
            <div class="wcc-logo-preview<?php echo $wcc_logo_url ? '' : ' wcc-logo-preview--empty'; ?>">
              <img src="<?php echo esc_url( $wcc_logo_url ); ?>" alt="" style="<?php echo $wcc_logo_url ? '' : 'display:none;'; ?>" />
              <span class="wcc-logo-placeholder" style="<?php echo $wcc_logo_url ? 'display:none;' : ''; ?>">
                <span class="dashicons dashicons-format-image"></span>
              </span>
            </div>
            <div class="wcc-logo-buttons">
              <button type="button" class="button wcc-logo-select"><?php esc_html_e( 'Select Logo', 'wordcamp-certificate' ); ?></button>
              <button type="button" class="button wcc-logo-remove" style="<?php echo $wcc_logo_url ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'wordcamp-certificate' ); ?></button>
            </div>
            <input type="hidden" id="wcc_event_logo_id" name="wcc_event_logo_id" value="<?php echo esc_attr( $wcc_logo_id ); ?>" />
          </div>
          <p class="description"><?php esc_html_e( 'Replaces the default WordPress logo badge on the certificate. Recommended: square image, at least 200×200px.', 'wordcamp-certificate' ); ?></p>
        </td>
      </tr>
    </table>

    <div class="wcc-shortcode-box">
      <h3><?php esc_html_e( '📋 How to Use', 'wordcamp-certificate' ); ?></h3>
      <p><?php esc_html_e( 'Add this shortcode to any page or post:', 'wordcamp-certificate' ); ?></p>
      <code class="wcc-shortcode-code">[wordcamp_certificate]</code>
      <p><?php esc_html_e( 'Or with custom overrides:', 'wordcamp-certificate' ); ?></p>
      <code class="wcc-shortcode-code">[wordcamp_certificate event_name="WordCamp Manila 2025" event_date="July 12, 2025"]</code>
    </div>

    <p class="submit">
      <input type="submit" name="wcc_save_settings" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'wordcamp-certificate' ); ?>" />
    </p>
  </form>
</div>
<script>
jQuery(function ($) {
  var frame;
  $('.wcc-logo-select').on('click', function (e) {
    e.preventDefault();
    if (frame) { frame.open(); return; }
    frame = wp.media({
      title: '<?php echo esc_js( __( 'Select Event Logo', 'wordcamp-certificate' ) ); ?>',
      button: { text: '<?php echo esc_js( __( 'Use this logo', 'wordcamp-certificate' ) ); ?>' },
      library: { type: 'image' },
      multiple: false,
    });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
      $('#wcc_event_logo_id').val(attachment.id);
      $('.wcc-logo-preview').removeClass('wcc-logo-preview--empty');
      $('.wcc-logo-preview img').attr('src', url).show();
      $('.wcc-logo-placeholder').hide();
      $('.wcc-logo-remove').show();
    });
    frame.open();
  });
  $('.wcc-logo-remove').on('click', function (e) {
    e.preventDefault();
    $('#wcc_event_logo_id').val('');
    $('.wcc-logo-preview').addClass('wcc-logo-preview--empty');
    $('.wcc-logo-preview img').hide();
    $('.wcc-logo-placeholder').show();
    $(this).hide();
  });
});
</script>

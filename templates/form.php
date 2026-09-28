<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wcc-form-wrap" id="wcc-form-wrap">
  <div class="wcc-form-card">

    <!-- Icon + heading -->
    <div class="wcc-form-header">
      <div class="wcc-form-icon">🏆</div>
      <h2 class="wcc-form-title"><?php esc_html_e( 'Get Your Certificate', 'wordcamp-certificate' ); ?></h2>
      <p class="wcc-form-subtitle">
        <?php printf(
          /* translators: %s: event name */
          esc_html__( 'Enter your details below to receive your official %s Certificate of Attendance.', 'wordcamp-certificate' ),
          '<strong>' . esc_html( $atts['event_name'] ) . '</strong>'
        ); ?>
      </p>
    </div>

    <!-- Form -->
    <form id="wcc-form" class="wcc-form" novalidate>
      <?php wp_nonce_field( 'wcc_generate_nonce', 'wcc_nonce_field' ); ?>

      <div class="wcc-field">
        <label for="wcc-full-name" class="wcc-label">
          <?php esc_html_e( 'Full Name', 'wordcamp-certificate' ); ?>
          <span class="wcc-required" aria-hidden="true">*</span>
        </label>
        <input
          type="text"
          id="wcc-full-name"
          name="full_name"
          class="wcc-input"
          placeholder="<?php esc_attr_e( 'e.g. Jane Doe', 'wordcamp-certificate' ); ?>"
          required
          autocomplete="name"
        />
        <span class="wcc-field-error" id="wcc-name-error" role="alert"></span>
      </div>

      <div class="wcc-field">
        <label for="wcc-email" class="wcc-label">
          <?php esc_html_e( 'Email Address', 'wordcamp-certificate' ); ?>
          <span class="wcc-required" aria-hidden="true">*</span>
        </label>
        <input
          type="email"
          id="wcc-email"
          name="email"
          class="wcc-input"
          placeholder="<?php esc_attr_e( 'e.g. jane@example.com', 'wordcamp-certificate' ); ?>"
          required
          autocomplete="email"
        />
        <span class="wcc-field-error" id="wcc-email-error" role="alert"></span>
      </div>

      <div class="wcc-form-error" id="wcc-form-error" role="alert" style="display:none;"></div>

      <button type="submit" class="wcc-submit" id="wcc-submit">
        <span class="wcc-submit-text"><?php esc_html_e( 'Generate My Certificate', 'wordcamp-certificate' ); ?></span>
        <span class="wcc-submit-loading" style="display:none;">
          <span class="wcc-spinner"></span>
          <?php esc_html_e( 'Generating…', 'wordcamp-certificate' ); ?>
        </span>
      </button>
    </form>

    <!-- Success state -->
    <div id="wcc-success" class="wcc-success" style="display:none;">
      <div class="wcc-success-icon">🎉</div>
      <h3 class="wcc-success-title"><?php esc_html_e( 'Your Certificate is Ready!', 'wordcamp-certificate' ); ?></h3>
      <p class="wcc-success-msg"><?php esc_html_e( 'Congratulations! Click the button below to view and download your certificate.', 'wordcamp-certificate' ); ?></p>
      <a href="#" id="wcc-cert-link" class="wcc-cert-btn" target="_blank" rel="noopener">
        <?php esc_html_e( '🏅 View Certificate', 'wordcamp-certificate' ); ?>
      </a>
      <button class="wcc-another-btn" id="wcc-another"><?php esc_html_e( 'Generate another', 'wordcamp-certificate' ); ?></button>
    </div>

  </div>
</div>

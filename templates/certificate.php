<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo esc_html( $event_name ); ?> – Certificate of Attendance</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="<?php echo esc_url( wcc_get_google_fonts_url() ); ?>" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url( WCC_PLUGIN_URL . 'assets/cert-canvas.css' ); ?>?ver=<?php echo esc_attr( WCC_VERSION ); ?>">
<style>
<?php
$heading_family = $fonts[ $design['heading_font'] ]['family'] ?? "'Playfair Display', serif";
$body_family    = $fonts[ $design['body_font'] ]['family'] ?? "'Inter', sans-serif";
?>
  * { box-sizing: border-box; }
  body { margin: 0; overflow-x: hidden; }

  html, body {
    width: 100%; height: 100%;
    background: #e8eff4;
    display: flex; align-items: center; justify-content: center;
    min-height: 100vh;
  }

  .wcc-cert-canvas {
    --wc-blue:   <?php echo esc_html( $design['primary_color'] ); ?>;
    --wc-dark:   <?php echo esc_html( $design['dark_color'] ); ?>;
    --wc-gold:   <?php echo esc_html( $design['gold_color'] ); ?>;
    --wc-light:  <?php echo esc_html( $design['light_color'] ); ?>;
    --wc-border: <?php echo esc_html( $design['border_color'] ); ?>;
    --wc-heading-font: <?php echo $heading_family; ?>;
    --wc-body-font: <?php echo $body_family; ?>;
  }

  .cert-actions {
    text-align: center;
    margin-top: 24px;
    display: flex;
    gap: 12px;
    justify-content: center;
  }
  .cert-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all 0.2s;
  }
  .cert-btn-primary { background: var(--wc-blue); color: #fff; }
  .cert-btn-primary:hover { background: var(--wc-dark); color: #fff; }
  .cert-btn-secondary { background: #fff; color: var(--wc-dark); border: 1px solid var(--wc-border); }
  .cert-btn-secondary:hover { background: var(--wc-light); }

  @media print {
    html, body { background: #fff; display: block; overflow-x: visible; }
    .cert-actions { display: none; }
    .cert { border-color: #000; }
    .wcc-cert-canvas .wp-block-columns { flex-wrap: nowrap; }
  }
  @media screen and (max-width: 480px) {
    html, body { align-items: flex-start; padding-top: 12px; }
    .cert-actions { flex-direction: column; align-items: stretch; gap: 10px; }
    .cert-btn { justify-content: center; width: 100%; }

    /* Critical responsive overrides are inlined here (rather than only in the
       cacheable stylesheet) so they always reach the page even if cert-canvas.css
       is served from a stale cache. !important beats any inline style set by the
       Certificate Field block's typography controls. */
    .wcc-cert-canvas .cert-wrap { padding: 6px 4px !important; }
    .wcc-cert-canvas .cert { padding: 24px 16px !important; }
    .wcc-cert-canvas .cert-inner { padding: 16px 12px !important; }
    .wcc-cert-canvas .cert::before,
    .wcc-cert-canvas .cert::after { width: 32px !important; height: 32px !important; }
    .wcc-cert-canvas .cert-inner::before,
    .wcc-cert-canvas .cert-inner::after { width: 24px !important; height: 24px !important; }
    .wcc-cert-canvas .wcc-cert-body { overflow-wrap: break-word; word-wrap: break-word; }
    .wcc-cert-canvas .wcc-cert-body > * { max-width: 100% !important; }

    .wcc-cert-canvas .cert-event-name { font-size: 17px !important; letter-spacing: 0.5px; }
    .wcc-cert-canvas .cert-title      { font-size: 22px !important; }
    .wcc-cert-canvas .cert-name       { font-size: 26px !important; }
    .wcc-cert-canvas .cert-desc       { font-size: 13px !important; line-height: 1.6; max-width: 100% !important; }
    .wcc-cert-canvas .cert-subtitle   { font-size: 9px !important; letter-spacing: 2px; }
    .wcc-cert-canvas .cert-presented  { font-size: 10px !important; }

    .wcc-cert-canvas .cert-footer { flex-direction: column; align-items: center; gap: 16px; }
    .wcc-cert-canvas .cert-sig,
    .wcc-cert-canvas .cert-seal  { width: 100% !important; }
    .wcc-cert-canvas .cert-detail-item { font-size: 11px !important; }
  }
</style>
</head>
<body>
<div class="wcc-cert-canvas">
  <div class="cert-wrap">
    <div class="cert" id="certificate">
      <div class="cert-inner">

        <?php if ( $design['show_logo'] ) : ?>
        <div class="cert-header">
          <div class="wc-logo">
            <?php if ( $logo_url ) : ?>
              <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $event_name ); ?>" />
            <?php else : ?>
              <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12.158 12.786l-2.698 7.84c.806.237 1.657.365 2.54.365a9.294 9.294 0 0 0 3.848-.83 1.02 1.02 0 0 1-.08-.155l-3.61-7.22zM3.008 7.44L7.38 19.8a9.338 9.338 0 0 0 5.77-12.48 9.338 9.338 0 0 0-10.142.12zm16.06 9.29a9.312 9.312 0 0 0 1.562-5.23c0-3.28-1.69-6.16-4.252-7.84l3.576 12.432a9.27 9.27 0 0 0 .114.638zM12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z"/></svg>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="wcc-cert-body">
          <?php echo $body_html; // phpcs:ignore -- pre-sanitized design HTML with escaped field substitutions. ?>
        </div>

      </div>
    </div>

    <div class="cert-actions">
      <button class="cert-btn cert-btn-primary" onclick="window.print()">
        🖨️ Print / Save as PDF
      </button>
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cert-btn cert-btn-secondary">
        ← Back to Site
      </a>
    </div>
  </div>
</div>
</body>
</html>

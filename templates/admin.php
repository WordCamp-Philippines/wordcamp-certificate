<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wcc-admin-wrap">
  <h1 class="wcc-admin-title">
    <span class="dashicons dashicons-awards"></span>
    <?php esc_html_e( 'WordCamp Certificates Issued', 'wordcamp-certificate' ); ?>
  </h1>
  <p class="wcc-admin-desc">
    <?php printf(
      esc_html__( 'Total certificates issued: %s', 'wordcamp-certificate' ),
      '<strong>' . count( $certs ) . '</strong>'
    ); ?>
  </p>

  <?php if ( empty( $certs ) ) : ?>
    <div class="wcc-empty-state">
      <span class="dashicons dashicons-awards wcc-empty-icon"></span>
      <p><?php esc_html_e( 'No certificates have been generated yet.', 'wordcamp-certificate' ); ?></p>
      <p><?php esc_html_e( 'Add the [wordcamp_certificate] shortcode to any page to get started.', 'wordcamp-certificate' ); ?></p>
    </div>
  <?php else : ?>
    <table class="widefat striped wcc-table">
      <thead>
        <tr>
          <th><?php esc_html_e( '#', 'wordcamp-certificate' ); ?></th>
          <th><?php esc_html_e( 'Full Name', 'wordcamp-certificate' ); ?></th>
          <th><?php esc_html_e( 'Email', 'wordcamp-certificate' ); ?></th>
          <th><?php esc_html_e( 'Date Issued', 'wordcamp-certificate' ); ?></th>
          <th><?php esc_html_e( 'Certificate', 'wordcamp-certificate' ); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $certs as $i => $c ) :
          $cert_url = add_query_arg( [ 'wcc_cert' => '1', 'token' => $c->cert_token ], home_url( '/' ) );
        ?>
        <tr>
          <td><?php echo intval( $i + 1 ); ?></td>
          <td><strong><?php echo esc_html( $c->full_name ); ?></strong></td>
          <td><?php echo esc_html( $c->email ); ?></td>
          <td><?php echo esc_html( date_i18n( 'M j, Y g:i a', strtotime( $c->issued_at ) ) ); ?></td>
          <td>
            <a href="<?php echo esc_url( $cert_url ); ?>" target="_blank" class="button button-small">
              <?php esc_html_e( 'View Certificate', 'wordcamp-certificate' ); ?>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

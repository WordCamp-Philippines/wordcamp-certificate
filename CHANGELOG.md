# Changelog

All notable changes to **WordCamp Certificate Generator** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Planned
- Email the certificate link to the attendee after generation
- CSV export of all issued certificates from the admin panel
- Custom logo upload field in the Settings page
- Multi-event support (one plugin, multiple WordCamp events)
- QR code on the certificate linking back to the verification URL
- WooCommerce integration — gate certificate generation behind a ticket purchase
- `wcc_before_certificate_insert` and `wcc_after_certificate_insert` action hooks
- `wcc_certificate_data` and `wcc_form_fields` filter hooks

---

## [1.0.0] — 2025-07-03

### Added
- Initial release of WordCamp Certificate Generator
- Attendee-facing form with **Full Name** and **Email Address** fields
- AJAX-powered form submission with nonce verification and inline field validation
- Auto-generation of a unique, token-based certificate URL per attendee
- Duplicate prevention — the same email always returns the same certificate
- Standalone certificate page rendered outside the WordPress theme loop
- Beautiful certificate design using Playfair Display + Inter (Google Fonts)
  - Gold corner ornaments and inner border
  - Event name, date, and location details pill
  - Organizer signature line and verification seal
  - Unique short certificate ID (first 8 characters of the token)
- **Print / Save as PDF** button with clean print stylesheet
- **Back to Site** navigation button on the certificate page
- WordPress admin menu: **WC Certificates**
  - Certificates log — lists all issued certificates with a direct view link
  - Settings page — configure event name, date, location, and organizer name
- `[wordcamp_certificate]` shortcode with optional `event_name` and `event_date` attribute overrides
- Gutenberg block: **WordCamp Certificate Form**
  - Editor-side preview with Inspector Controls for event name and date
  - Server-side render callback
- `register_activation_hook` creates the `wp_wordcamp_certificates` database table via `dbDelta()`
- Full input sanitization: `sanitize_text_field`, `sanitize_email`, `$wpdb->prepare()`
- Tokens generated with `wp_generate_password( 32, false )`
- Mobile-responsive form and certificate layouts
- `prefers-reduced-motion` media query support

---

[Unreleased]: https://github.com/YOUR_USERNAME/wordcamp-certificate/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/YOUR_USERNAME/wordcamp-certificate/releases/tag/v1.0.0

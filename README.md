# WordCamp Certificate Generator

> A WordPress plugin that auto-generates a Certificate of Attendance for WordCamp attendees. Attendees simply enter their full name and email address to instantly receive a beautifully designed, printable certificate.

![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-21759b?style=flat-square&logo=wordpress)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4?style=flat-square&logo=php)
![License](https://img.shields.io/badge/License-GPL--2.0--or--later-green?style=flat-square)
![Version](https://img.shields.io/badge/Version-1.0.0-blue?style=flat-square)

---

## 📸 Screenshots

| Attendee Form | Certificate Output |
|---|---|
| Attendees enter their name and email | A print-ready certificate with their name |

---

## ✨ Features

- **Instant certificate generation** — attendees fill in their name and email, click submit, and get their certificate immediately
- **Beautiful certificate design** — Playfair Display typography, gold corner ornaments, event seal, and signature lines
- **Print / Save as PDF** — one-click browser print with a clean print stylesheet (no server-side PDF library required)
- **Duplicate prevention** — the same email address always returns the same certificate
- **Unique certificate ID** — every certificate has a short token-based ID for verification
- **Admin certificates log** — view all issued certificates with a direct link to each one
- **Settings panel** — configure event name, date, location, and organizer name from WP Admin
- **Shortcode support** — `[wordcamp_certificate]` works on any page or post
- **Gutenberg block** — drag-and-drop "WordCamp Certificate Form" block with Inspector Controls
- **Fully sanitized & secure** — nonce-verified AJAX, `sanitize_text_field`, `sanitize_email`, and `wpdb->prepare` throughout

---

## 📋 Requirements

| Requirement | Minimum Version |
|---|---|
| WordPress | 5.8 |
| PHP | 7.4 |
| Browser | Any modern browser (Chrome, Firefox, Safari, Edge) |

> **Note:** The plugin uses `dbDelta()` for table creation and is compatible with both MySQL and SQLite (via the WordPress SQLite Database integration).

---

## 🚀 Installation

### Option A — Manual Upload

1. Download or clone this repository.
2. Copy the `wordcamp-certificate` folder into your site's `wp-content/plugins/` directory.
3. In WP Admin, go to **Plugins → Installed Plugins**.
4. Find **WordCamp Certificate Generator** and click **Activate**.

### Option B — Via WP-CLI

```bash
# From your WordPress root
wp plugin install /path/to/wordcamp-certificate --activate
```

### Option C — Clone directly into plugins folder

```bash
cd wp-content/plugins
git clone https://github.com/YOUR_USERNAME/wordcamp-certificate.git
wp plugin activate wordcamp-certificate
```

---

## ⚙️ Configuration

After activation, configure the plugin under **WP Admin → WC Certificates → Settings**:

| Setting | Description | Example |
|---|---|---|
| **Event Name** | The full name of your WordCamp event | `WordCamp Manila 2025` |
| **Event Date** | Date or date range of the event | `July 12–13, 2025` |
| **Event Location** | City and country (optional) | `Manila, Philippines` |
| **Organizer Name** | Appears on the signature line | `WordCamp Manila Team` |

---

## 🖊️ Usage

### Shortcode

Add this shortcode to any page or post:

```
[wordcamp_certificate]
```

You can also override event details inline:

```
[wordcamp_certificate event_name="WordCamp Manila 2025" event_date="July 12, 2025"]
```

### Gutenberg Block

1. Open the Block Editor on any page or post.
2. Search for **"WordCamp Certificate Form"** in the block inserter.
3. Add the block — it renders a live preview in the editor.
4. Use the **Inspector Controls** (right sidebar) to override the event name and date per-page.

### Direct URL

Once a certificate is generated, it lives at a permanent URL:

```
https://yoursite.com/?wcc_cert=1&token=UNIQUE_TOKEN
```

This URL can be bookmarked, shared, or re-printed at any time.

---

## 🗂️ File Structure

```
wordcamp-certificate/
├── wordcamp-certificate.php   # Main plugin file — hooks, AJAX, shortcode, block registration
├── assets/
│   ├── frontend.css           # Attendee form styles
│   ├── frontend.js            # AJAX form submission, inline validation, success state
│   ├── admin.css              # WP Admin styles
│   └── block.js               # Gutenberg block (editor-side React/wp.element)
├── templates/
│   ├── form.php               # Attendee-facing input form
│   ├── certificate.php        # Standalone certificate page (no WP theme wrapper)
│   ├── admin.php              # Admin certificates list table
│   └── settings.php           # Admin settings form
└── README.md
```

---

## 🗄️ Database

On activation the plugin creates one custom table:

```sql
CREATE TABLE wp_wordcamp_certificates (
  id           BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name    VARCHAR(255) NOT NULL,
  email        VARCHAR(255) NOT NULL,
  issued_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  cert_token   VARCHAR(64) NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY   cert_token (cert_token),
  KEY          email (email)
);
```

The table is created with `dbDelta()` so it is safe to re-activate without data loss.

---

## 🔒 Security

- All AJAX requests are verified with `wp_create_nonce` / `check_ajax_referer`
- User input is sanitized with `sanitize_text_field` and `sanitize_email` before processing or storage
- All database queries use `$wpdb->prepare()` — no raw interpolated SQL
- Certificate tokens are generated with `wp_generate_password( 32, false )` (cryptographically random)
- The certificate template runs outside the WordPress theme loop but does **not** expose any credentials

---

## 🎨 Customization

### Colors

CSS custom properties are defined at the top of `assets/frontend.css` and inside the `<style>` block of `templates/certificate.php`. Override them in your theme's stylesheet:

```css
/* In your theme's style.css */
.wcc-form-card { --wcc-blue: #your-color; }
```

| Variable | Default | Used For |
|---|---|---|
| `--wcc-blue` | `#21759b` | Primary buttons, headings, accents |
| `--wcc-dark` | `#0a2a40` | Dark text, gradient |
| `--wcc-gold` | `#c9a84c` | Corner ornaments, dividers |
| `--wcc-light` | `#f0f7fb` | Form header background tint |
| `--wcc-border` | `#d4e8f3` | Input and card borders |

### Fonts

The certificate uses **Playfair Display** (Google Fonts) for headings and **Inter** for body text. To use custom fonts, edit the `<link>` tag at the top of `templates/certificate.php`.

### Certificate Logo

The WordPress "W" SVG logo in `templates/certificate.php` can be swapped for your own event logo by replacing the `<svg>` inside `.wc-logo`.

### Certificate Design block & Field typography

The **Certificate Design** block renders the full certificate as a drag-and-drop canvas in the Block Editor. Save the page to push the layout site-wide.

Each **Certificate Field** token (attendee name, event date, etc.) has its own **Typography** panel in the inspector:

| Control | Effect on the certificate |
|---|---|
| Font Family | Overrides the field's font; "Inherit global design font" keeps the design block's global heading/body font |
| Font Size | Sets the size in pixels; leave at `0` to keep the default class-based size |
| Font Weight | Light (300) through Bold (700); "Inherit" keeps the design's default |
| Italic | Toggles italic styling |
| Case | Uppercase, lowercase, or capitalize; "Default" leaves text as-is |
| Letter Spacing | Adds tracking in pixels |
| Color | Sets the token's text color |

Untouched controls inherit the current design, so existing certificates keep their default look until a control is changed.

---

## 🪝 Hooks & Filters

The plugin is built to be extendable. Future versions will expose the following hooks (contributions welcome):

| Hook | Type | Description |
|---|---|---|
| `wcc_before_certificate_insert` | action | Fires before a new certificate record is saved |
| `wcc_after_certificate_insert` | action | Fires after a new certificate record is saved |
| `wcc_certificate_data` | filter | Filter the data array before it is passed to the certificate template |
| `wcc_form_fields` | filter | Add or remove fields from the attendee form |

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-new-feature`
3. Commit your changes: `git commit -m 'Add some feature'`
4. Push to the branch: `git push origin feature/my-new-feature`
5. Open a Pull Request

### Ideas for Contributions

- [ ] Email the certificate link to the attendee after generation
- [ ] CSV export of all issued certificates from the admin panel
- [ ] Custom logo upload field in Settings
- [ ] Multi-event support (one plugin installation, multiple WordCamp events)
- [ ] QR code on the certificate linking back to the verification URL
- [ ] WooCommerce integration — gate certificate generation behind a ticket purchase

---

## 📄 License

This plugin is licensed under the **GNU General Public License v2.0 or later**.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) for the full license text.

---

## 👏 Credits

- Built with [WordPress](https://wordpress.org) and ❤️ for the WordCamp community
- Certificate typography: [Playfair Display](https://fonts.google.com/specimen/Playfair+Display) & [Inter](https://fonts.google.com/specimen/Inter) via Google Fonts
- WordPress logo SVG from [WordPress Brand Resources](https://wordpress.org/about/logos/)

---

## 📬 Support

Found a bug or have a question? [Open an issue](https://github.com/YOUR_USERNAME/wordcamp-certificate/issues) on GitHub.

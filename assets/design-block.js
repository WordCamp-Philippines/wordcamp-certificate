/* WordCamp Certificate – "Certificate Design" block.
   A real drag-and-drop Gutenberg canvas: colors/fonts/logo live in the
   sidebar, but the certificate wording and layout are ordinary blocks
   (heading, paragraph, group, separator, Certificate Field tokens) that can
   be reordered, edited inline, deleted, and have new blocks inserted among
   them, exactly like any other part of the editor. */
(function (blocks, element, blockEditor, components, i18n) {
  var el = element.createElement;
  var useBlockProps = blockEditor.useBlockProps;
  var InnerBlocks = blockEditor.InnerBlocks;
  var InspectorControls = blockEditor.InspectorControls;
  var PanelBody = components.PanelBody;
  var PanelColorSettings = blockEditor.PanelColorSettings;
  var SelectControl = components.SelectControl;
  var ToggleControl = components.ToggleControl;
  var __ = i18n.__;

  var FONT_CHOICES = [
    { label: 'Playfair Display', value: 'playfair' },
    { label: 'Merriweather', value: 'merriweather' },
    { label: 'Georgia (system)', value: 'georgia' },
    { label: 'Inter', value: 'inter' },
    { label: 'Roboto', value: 'roboto' },
    { label: 'Arial (system)', value: 'arial' },
  ];

  // Starting layout inserted the first time this block is added to a page.
  // Every item here is a normal, movable/removable/insertable block.
  var TEMPLATE = [
    ['wordcamp-certificate/field', { field: 'event_name' }],
    ['core/separator', { className: 'cert-divider-block' }],
    ['core/paragraph', { content: __('This is to certify that', 'wordcamp-certificate'), className: 'cert-subtitle' }],
    ['core/heading', { level: 1, content: __('Certificate of Attendance', 'wordcamp-certificate'), className: 'cert-title' }],
    ['core/paragraph', { content: __('proudly presented to', 'wordcamp-certificate'), className: 'cert-presented' }],
    ['wordcamp-certificate/field', { field: 'full_name' }],
    ['core/paragraph', { content: __('has successfully attended and participated in {event_name}, a community-driven WordPress conference dedicated to sharing knowledge, fostering collaboration, and celebrating the open web.', 'wordcamp-certificate'), className: 'cert-desc' }],
    ['core/group', { className: 'cert-details', layout: { type: 'flex', flexWrap: 'wrap' } }, [
      ['wordcamp-certificate/field', { field: 'event_date' }],
      ['wordcamp-certificate/field', { field: 'event_location' }],
    ]],
    ['core/separator', { className: 'cert-divider-block' }],
    ['core/group', { className: 'cert-footer', layout: { type: 'flex', justifyContent: 'space-between', flexWrap: 'wrap' } }, [
      ['core/group', { className: 'cert-sig', layout: { type: 'flex', orientation: 'vertical' } }, [
        ['core/paragraph', { content: __('Organizer', 'wordcamp-certificate'), className: 'cert-sig-label' }],
        ['wordcamp-certificate/field', { field: 'organizer_name' }],
      ]],
      ['core/group', { className: 'cert-seal', layout: { type: 'flex', orientation: 'vertical' } }, [
        ['core/paragraph', { content: __('Certificate ID', 'wordcamp-certificate'), className: 'cert-sig-label' }],
        ['wordcamp-certificate/field', { field: 'cert_id' }],
      ]],
      ['core/group', { className: 'cert-sig', layout: { type: 'flex', orientation: 'vertical' } }, [
        ['core/paragraph', { content: __('Date Issued', 'wordcamp-certificate'), className: 'cert-sig-label' }],
        ['wordcamp-certificate/field', { field: 'date_issued' }],
      ]],
    ]],
  ];

  var ALLOWED_BLOCKS = [
    'core/heading', 'core/paragraph', 'core/group', 'core/columns', 'core/column',
    'core/separator', 'core/spacer', 'core/buttons', 'core/button', 'core/image',
    'wordcamp-certificate/field',
  ];

  blocks.registerBlockType('wordcamp-certificate/design', {
    title: __('Certificate Design', 'wordcamp-certificate'),
    icon: 'art',
    category: 'wordcamp-certificate',
    description: __('Drag, drop, and edit the certificate layout directly. Colors, fonts, and the logo are in the sidebar. Saving this page updates the live certificate design site-wide.', 'wordcamp-certificate'),

    edit: function (props) {
      var attrs = props.attributes;
      var setAttributes = props.setAttributes;
      var blockProps = useBlockProps({ className: 'wcc-cert-canvas' });

      function setColor(key) {
        return function (value) {
          var patch = {};
          patch[key] = value || '';
          setAttributes(patch);
        };
      }

      var style = {
        '--wc-blue': attrs.primaryColor,
        '--wc-dark': attrs.darkColor,
        '--wc-gold': attrs.goldColor,
        '--wc-light': attrs.lightColor,
        '--wc-border': attrs.borderColor,
      };

      return el(
        'div', blockProps,
        el(InspectorControls, {},
          el(PanelColorSettings, {
            title: __('Colors', 'wordcamp-certificate'),
            initialOpen: true,
            colorSettings: [
              { value: attrs.primaryColor, onChange: setColor('primaryColor'), label: __('Primary (accents, name)', 'wordcamp-certificate') },
              { value: attrs.darkColor, onChange: setColor('darkColor'), label: __('Dark (headings, text)', 'wordcamp-certificate') },
              { value: attrs.goldColor, onChange: setColor('goldColor'), label: __('Gold (ornaments)', 'wordcamp-certificate') },
              { value: attrs.lightColor, onChange: setColor('lightColor'), label: __('Light (detail pill background)', 'wordcamp-certificate') },
              { value: attrs.borderColor, onChange: setColor('borderColor'), label: __('Border', 'wordcamp-certificate') },
            ],
          }),
          el(PanelBody, { title: __('Typography', 'wordcamp-certificate'), initialOpen: false },
            el(SelectControl, {
              label: __('Heading Font', 'wordcamp-certificate'),
              value: attrs.headingFont,
              options: FONT_CHOICES,
              onChange: function (v) { setAttributes({ headingFont: v }); },
            }),
            el(SelectControl, {
              label: __('Body Font', 'wordcamp-certificate'),
              value: attrs.bodyFont,
              options: FONT_CHOICES,
              onChange: function (v) { setAttributes({ bodyFont: v }); },
            })
          ),
          el(PanelBody, { title: __('Logo', 'wordcamp-certificate'), initialOpen: false },
            el(ToggleControl, {
              label: __('Show event logo', 'wordcamp-certificate'),
              checked: !!attrs.showLogo,
              onChange: function (v) { setAttributes({ showLogo: v }); },
            }),
            el('p', { style: { color: '#6b7280', fontSize: '12px' } },
              __('The logo image itself is set on the Settings page.', 'wordcamp-certificate'))
          )
        ),
        el('div', { className: 'wcc-design-block-notice' },
          __('Certificate Design — drag blocks to reorder, click to edit text, or use the block inserter (+) to add new elements. Saving this page updates the live certificate for every attendee.', 'wordcamp-certificate')
        ),
        el('div', { className: 'cert-wrap' },
          el('div', { className: 'cert', style: style },
            el('div', { className: 'cert-inner' },
              attrs.showLogo && el('div', { className: 'cert-header' },
                el('div', { className: 'wc-logo' },
                  el('svg', { viewBox: '0 0 24 24', xmlns: 'http://www.w3.org/2000/svg' },
                    el('path', { d: 'M12.158 12.786l-2.698 7.84c.806.237 1.657.365 2.54.365a9.294 9.294 0 0 0 3.848-.83 1.02 1.02 0 0 1-.08-.155l-3.61-7.22zM3.008 7.44L7.38 19.8a9.338 9.338 0 0 0 5.77-12.48 9.338 9.338 0 0 0-10.142.12zm16.06 9.29a9.312 9.312 0 0 0 1.562-5.23c0-3.28-1.69-6.16-4.252-7.84l3.576 12.432a9.27 9.27 0 0 0 .114.638zM12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z' })
                  )
                )
              ),
              el('div', { className: 'wcc-cert-body' },
                el(InnerBlocks, {
                  template: TEMPLATE,
                  allowedBlocks: ALLOWED_BLOCKS,
                  templateLock: false,
                })
              )
            )
          )
        )
      );
    },

    save: function () {
      // InnerBlocks content is saved via save.Content below.
      var blockProps = blockEditor.useBlockProps.save({ className: 'wcc-cert-canvas' });
      return el('div', blockProps,
        el('div', { className: 'cert-wrap' },
          el('div', { className: 'cert' },
            el('div', { className: 'cert-inner' },
              el('div', { className: 'wcc-cert-body' },
                el(InnerBlocks.Content, null)
              )
            )
          )
        )
      );
    },
  });
}(
  window.wp.blocks,
  window.wp.element,
  window.wp.blockEditor,
  window.wp.components,
  window.wp.i18n
));

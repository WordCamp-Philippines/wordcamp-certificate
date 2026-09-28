/* WordCamp Certificate – "Certificate Field" block: a movable, insertable
   placeholder for a piece of per-attendee or per-event data (name, date,
   seal ID, etc.). Shows sample data in the editor; the real value is
   substituted server-side when a certificate is actually issued. */
(function (blocks, element, blockEditor, components, i18n) {
  var el = element.createElement;
  var useBlockProps = blockEditor.useBlockProps;
  var InspectorControls = blockEditor.InspectorControls;
  var PanelBody = components.PanelBody;
  var SelectControl = components.SelectControl;
  var RangeControl = components.RangeControl;
  var ToggleControl = components.ToggleControl;
  var TextControl = components.TextControl;
  var __ = i18n.__;

  // ColorControl is only available since WP 6.6; fall back to a hex text
  // input on older installs so the block never crashes.
  var ColorField = blockEditor.ColorControl
    ? function (props) {
        return el(blockEditor.ColorControl, {
          label: props.label,
          value: props.value,
          onChange: props.onChange,
        });
      }
    : function (props) {
        return el(TextControl, {
          label: props.label,
          value: props.value || '',
          onChange: props.onChange,
        });
      };

  var FIELDS = {
    full_name:      { label: __('Attendee Name', 'wordcamp-certificate'), sample: 'Jane Doe', className: 'cert-name' },
    event_name:     { label: __('Event Name', 'wordcamp-certificate'), sample: 'WordCamp', className: 'cert-event-name' },
    event_date:     { label: __('Event Date', 'wordcamp-certificate'), sample: 'March 12, 2026', className: 'cert-detail-item', icon: '📅' },
    event_location: { label: __('Event Location', 'wordcamp-certificate'), sample: 'Manila, Philippines', className: 'cert-detail-item', icon: '📍' },
    organizer_name: { label: __('Organizer Name', 'wordcamp-certificate'), sample: 'Event Organizer', className: 'cert-sig-name' },
    cert_id:        { label: __('Certificate ID', 'wordcamp-certificate'), sample: 'ABC12345', className: 'cert-id' },
    date_issued:    { label: __('Date Issued', 'wordcamp-certificate'), sample: 'March 12, 2026', className: 'cert-sig-name' },
  };

  // Keys match design-block.js FONT_CHOICES and PHP wcc_get_font_choices();
  // values are the CSS font-family stack applied via inline style.
  var FONT_FAMILIES = {
    playfair:    "'Playfair Display', serif",
    merriweather: "'Merriweather', serif",
    georgia:     'Georgia, serif',
    inter:       "'Inter', sans-serif",
    roboto:      "'Roboto', sans-serif",
    arial:       'Arial, sans-serif',
  };

  var FONT_OPTIONS = [
    { label: __('Inherit global design font', 'wordcamp-certificate'), value: '' },
    { label: 'Playfair Display', value: 'playfair' },
    { label: 'Merriweather', value: 'merriweather' },
    { label: 'Georgia (system)', value: 'georgia' },
    { label: 'Inter', value: 'inter' },
    { label: 'Roboto', value: 'roboto' },
    { label: 'Arial (system)', value: 'arial' },
  ];

  var WEIGHT_OPTIONS = [
    { label: __('Inherit', 'wordcamp-certificate'), value: '' },
    { label: 'Light (300)', value: '300' },
    { label: 'Regular (400)', value: '400' },
    { label: 'Medium (500)', value: '500' },
    { label: 'Semi-bold (600)', value: '600' },
    { label: 'Bold (700)', value: '700' },
  ];

  var TRANSFORM_OPTIONS = [
    { label: __('Default', 'wordcamp-certificate'), value: 'none' },
    { label: __('Uppercase', 'wordcamp-certificate'), value: 'uppercase' },
    { label: __('Lowercase', 'wordcamp-certificate'), value: 'lowercase' },
    { label: __('Capitalize', 'wordcamp-certificate'), value: 'capitalize' },
  ];

  // Build the inline style object for the token's typography. Empty values
  // are omitted so untouched fields inherit the global design fonts/colors
  // and the class-based sizing in cert-canvas.css.
  function typoStyle(attrs) {
    var style = {};
    if (attrs.fontFamily && FONT_FAMILIES[attrs.fontFamily]) {
      style.fontFamily = FONT_FAMILIES[attrs.fontFamily];
    }
    if (attrs.fontSize) {
      style.fontSize = attrs.fontSize + 'px';
    }
    if (attrs.fontWeight) {
      style.fontWeight = attrs.fontWeight;
    }
    if (attrs.fontStyle === 'italic') {
      style.fontStyle = 'italic';
    }
    if (attrs.textTransform && attrs.textTransform !== 'none') {
      style.textTransform = attrs.textTransform;
    }
    if (attrs.letterSpacing) {
      style.letterSpacing = attrs.letterSpacing + 'px';
    }
    if (attrs.color) {
      style.color = attrs.color;
    }
    return style;
  }

  blocks.registerBlockType('wordcamp-certificate/field', {
    title: __('Certificate Field', 'wordcamp-certificate'),
    icon: 'id-alt',
    category: 'wordcamp-certificate',
    description: __('A dynamic value filled in automatically (attendee name, event date, certificate ID, etc.).', 'wordcamp-certificate'),
    attributes: {
      field: { type: 'string', default: 'full_name' },
      fontFamily: { type: 'string', default: '' },
      fontSize: { type: 'number', default: 0 },
      fontWeight: { type: 'string', default: '' },
      fontStyle: { type: 'string', default: '' },
      textTransform: { type: 'string', default: 'none' },
      letterSpacing: { type: 'number', default: 0 },
      color: { type: 'string', default: '' },
    },

    edit: function (props) {
      var field = FIELDS[props.attributes.field] || FIELDS.full_name;
      var blockProps = useBlockProps({ className: 'wcc-cert-field ' + field.className });
      var style = typoStyle(props.attributes);

      function setAttr(key) {
        return function (value) {
          var patch = {};
          patch[key] = value;
          props.setAttributes(patch);
        };
      }

      return el(
        element.Fragment,
        {},
        el(InspectorControls, {},
          el(PanelBody, { title: __('Field', 'wordcamp-certificate') },
            el(SelectControl, {
              label: __('Show', 'wordcamp-certificate'),
              value: props.attributes.field,
              options: Object.keys(FIELDS).map(function (key) {
                return { label: FIELDS[key].label, value: key };
              }),
              onChange: setAttr('field'),
            })
          ),
          el(PanelBody, { title: __('Typography', 'wordcamp-certificate') },
            el(SelectControl, {
              label: __('Font Family', 'wordcamp-certificate'),
              value: props.attributes.fontFamily,
              options: FONT_OPTIONS,
              onChange: setAttr('fontFamily'),
            }),
            el(RangeControl, {
              label: __('Font Size', 'wordcamp-certificate'),
              value: props.attributes.fontSize || 0,
              min: 8,
              max: 90,
              onChange: setAttr('fontSize'),
            }),
            el(SelectControl, {
              label: __('Font Weight', 'wordcamp-certificate'),
              value: props.attributes.fontWeight,
              options: WEIGHT_OPTIONS,
              onChange: setAttr('fontWeight'),
            }),
            el(ToggleControl, {
              label: __('Italic', 'wordcamp-certificate'),
              checked: props.attributes.fontStyle === 'italic',
              onChange: function (v) { setAttr('fontStyle')(v ? 'italic' : ''); },
            }),
            el(SelectControl, {
              label: __('Case', 'wordcamp-certificate'),
              value: props.attributes.textTransform,
              options: TRANSFORM_OPTIONS,
              onChange: setAttr('textTransform'),
            }),
            el(RangeControl, {
              label: __('Letter Spacing', 'wordcamp-certificate'),
              value: props.attributes.letterSpacing || 0,
              min: 0,
              max: 10,
              onChange: setAttr('letterSpacing'),
            }),
            el(ColorField, {
              label: __('Color', 'wordcamp-certificate'),
              value: props.attributes.color || undefined,
              onChange: setAttr('color'),
            })
          )
        ),
        el('span', blockProps,
          el('span', { className: 'wcc-cert-field-tag' }, field.label),
          el('span', { className: 'wcc-cert-field-sample', style: style }, (field.icon ? field.icon + ' ' : '') + field.sample)
        )
      );
    },

    save: function (props) {
      var field = FIELDS[props.attributes.field] || FIELDS.full_name;
      var blockProps = useBlockProps.save({
        className: 'wcc-cert-field ' + field.className,
        'data-wcc-field': props.attributes.field,
        style: typoStyle(props.attributes),
      });
      return el('span', blockProps, (field.icon ? field.icon + ' ' : '') + field.sample);
    },
  });
}(
  window.wp.blocks,
  window.wp.element,
  window.wp.blockEditor,
  window.wp.components,
  window.wp.i18n
));
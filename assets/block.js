/* WordCamp Certificate Generator – Gutenberg Block */
(function (blocks, element, blockEditor, components) {
  var el          = element.createElement;
  var useBlockProps = blockEditor.useBlockProps;
  var TextControl = components.TextControl;
  var PanelBody   = components.PanelBody;
  var InspectorControls = blockEditor.InspectorControls;

  blocks.registerBlockType('wordcamp-certificate/form', {
    title: 'WordCamp Certificate Form',
    icon:  'awards',
    category: 'widgets',
    description: 'Displays the WordCamp Certificate of Attendance form for attendees.',
    attributes: {
      eventName: { type: 'string', default: '' },
      eventDate: { type: 'string', default: '' },
    },

    edit: function (props) {
      var attrs = props.attributes;
      var blockProps = useBlockProps({ className: 'wcc-block-editor-preview' });

      return el(
        'div', blockProps,
        el(InspectorControls, {},
          el(PanelBody, { title: 'Certificate Settings', initialOpen: true },
            el(TextControl, {
              label:    'Event Name',
              value:    attrs.eventName,
              onChange: function (v) { props.setAttributes({ eventName: v }); },
              placeholder: 'e.g. WordCamp Manila 2025',
            }),
            el(TextControl, {
              label:    'Event Date',
              value:    attrs.eventDate,
              onChange: function (v) { props.setAttributes({ eventDate: v }); },
              placeholder: 'e.g. July 12, 2025',
            })
          )
        ),
        el('div', {
          style: {
            background: 'linear-gradient(135deg,#0a2a40,#21759b)',
            borderRadius: '12px',
            padding: '32px',
            textAlign: 'center',
            color: '#fff',
          }
        },
          el('div', { style: { fontSize: '40px', marginBottom: '12px' } }, '🏆'),
          el('p', { style: { fontSize: '18px', fontWeight: 700, margin: '0 0 8px' } }, 'WordCamp Certificate Form'),
          el('p', { style: { fontSize: '13px', opacity: .8, margin: 0 } },
            'Attendees will enter their name and email to get their certificate.'
          ),
          attrs.eventName && el('p', {
            style: { fontSize: '13px', marginTop: '10px', opacity: .9, margin: '10px 0 0' }
          }, '📅 ' + attrs.eventName + (attrs.eventDate ? ' · ' + attrs.eventDate : ''))
        )
      );
    },

    save: function () {
      // Rendered server-side
      return null;
    },
  });
}(
  window.wp.blocks,
  window.wp.element,
  window.wp.blockEditor,
  window.wp.components
));

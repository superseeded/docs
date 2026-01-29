/**
 * SuperSeeded Upload Gutenberg Block
 */
(function(blocks, element, blockEditor, components, i18n) {
    'use strict';

    var el = element.createElement;
    var useBlockProps = blockEditor.useBlockProps;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;
    var SelectControl = components.SelectControl;
    var __ = i18n.__;

    // Register the block
    blocks.registerBlockType('superseeded/upload', {
        title: __('SuperSeeded Upload', 'superseeded-upload'),
        description: __('Add a file upload widget for SuperSeeded data enrichment', 'superseeded-upload'),
        icon: 'upload',
        category: 'widgets',
        keywords: [
            __('upload', 'superseeded-upload'),
            __('file', 'superseeded-upload'),
            __('superseeded', 'superseeded-upload'),
            __('csv', 'superseeded-upload'),
            __('excel', 'superseeded-upload')
        ],
        attributes: {
            merchantId: {
                type: 'string',
                default: ''
            },
            theme: {
                type: 'string',
                default: ''
            }
        },
        supports: {
            html: false,
            className: true
        },

        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var blockProps = useBlockProps({
                className: 'superseeded-upload-block-editor'
            });

            return el(
                element.Fragment,
                null,
                // Inspector Controls (sidebar)
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        {
                            title: __('Settings', 'superseeded-upload'),
                            initialOpen: true
                        },
                        el(TextControl, {
                            label: __('Merchant ID', 'superseeded-upload'),
                            help: __('Override the default merchant ID (optional)', 'superseeded-upload'),
                            value: attributes.merchantId,
                            onChange: function(value) {
                                setAttributes({ merchantId: value });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Theme', 'superseeded-upload'),
                            help: __('Override the default theme (optional)', 'superseeded-upload'),
                            value: attributes.theme,
                            options: [
                                { label: __('Default (from settings)', 'superseeded-upload'), value: '' },
                                { label: __('Light', 'superseeded-upload'), value: 'light' },
                                { label: __('Dark', 'superseeded-upload'), value: 'dark' }
                            ],
                            onChange: function(value) {
                                setAttributes({ theme: value });
                            }
                        })
                    )
                ),
                // Block Preview
                el(
                    'div',
                    blockProps,
                    el(
                        'div',
                        {
                            className: 'superseeded-upload-preview',
                            style: {
                                border: '2px dashed #ccc',
                                borderRadius: '8px',
                                padding: '40px 20px',
                                textAlign: 'center',
                                backgroundColor: attributes.theme === 'dark' ? '#1a1a1a' : '#fafafa',
                                color: attributes.theme === 'dark' ? '#fff' : '#333'
                            }
                        },
                        el(
                            'div',
                            { style: { marginBottom: '10px' } },
                            el('span', {
                                className: 'dashicons dashicons-upload',
                                style: { fontSize: '48px', width: '48px', height: '48px' }
                            })
                        ),
                        el(
                            'p',
                            { style: { margin: '10px 0 5px', fontWeight: 'bold' } },
                            __('SuperSeeded Upload Widget', 'superseeded-upload')
                        ),
                        el(
                            'p',
                            { style: { margin: '5px 0', fontSize: '12px', color: '#666' } },
                            __('Drag & drop or click to upload files', 'superseeded-upload')
                        ),
                        attributes.merchantId && el(
                            'p',
                            { style: { margin: '10px 0 0', fontSize: '11px', color: '#999' } },
                            __('Merchant ID:', 'superseeded-upload') + ' ' + attributes.merchantId
                        )
                    )
                )
            );
        },

        save: function() {
            // Server-side rendering
            return null;
        }
    });

})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n
);

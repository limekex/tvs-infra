import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, ToggleControl } from '@wordpress/components';
import { createElement, Fragment } from '@wordpress/element';

registerBlockType( 'tvs/route-card', {
    title: __( 'Route Card', 'tvs-virtual-sports' ),
    attributes: { routeId: { type:'number' }, showMeta:{type:'boolean', default:true}, showCTA:{type:'boolean', default:true}, ctaLabel:{ type:'string', default:'View route' }, metaPlacement: { type:'string', default: 'bottom' } },
    edit( { attributes, setAttributes } ) {
        const props = useBlockProps();
        return createElement( Fragment, {},
            createElement( InspectorControls, {},
                createElement( PanelBody, { title: __('Settings', 'tvs-virtual-sports'), initialOpen: true },
                    createElement( TextControl, {
                        label: __('Route ID', 'tvs-virtual-sports'),
                        type: 'number',
                        value: attributes.routeId || '',
                        onChange: (v)=> setAttributes({ routeId: v ? parseInt(v,10) : undefined })
                    }),
                    createElement( ToggleControl, {
                        label: __('Show meta', 'tvs-virtual-sports'),
                        checked: !!attributes.showMeta,
                        onChange: (v)=> setAttributes({ showMeta: !!v })
                    }),
                    createElement( ToggleControl, {
                        label: __('Show CTA button', 'tvs-virtual-sports'),
                        checked: !!attributes.showCTA,
                        onChange: (v)=> setAttributes({ showCTA: !!v })
                    }),
                    !!attributes.showCTA && createElement( TextControl, {
                        label: __('CTA label', 'tvs-virtual-sports'),
                        value: attributes.ctaLabel || '',
                        placeholder: __('View route', 'tvs-virtual-sports'),
                        onChange: (v)=> setAttributes({ ctaLabel: v })
                    }),
                    createElement( SelectControl, {
                        label: __('Stats placement', 'tvs-virtual-sports'),
                        value: attributes.metaPlacement || 'bottom',
                        options: [
                            { label: __('Bottom', 'tvs-virtual-sports'), value: 'bottom' },
                            { label: __('Left', 'tvs-virtual-sports'), value: 'left' },
                            { label: __('Right', 'tvs-virtual-sports'), value: 'right' }
                        ],
                        onChange: (v)=> setAttributes({ metaPlacement: v })
                    })
                )
            ),
            createElement('div', props, __('Route Card (server-rendered on frontend)', 'tvs-virtual-sports'))
        );
    },
    save() { return null; }
} );

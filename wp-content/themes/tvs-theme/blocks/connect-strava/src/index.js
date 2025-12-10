import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { createElement } from '@wordpress/element';
import { SiStrava } from 'react-icons/si';

registerBlockType( 'tvs/connect-strava', {
    title: __( 'Connect Strava', 'tvs-virtual-sports' ),
    edit() {
        const props = useBlockProps({ className: 'tvs-connect-strava-editor' });
        return createElement('div', props,
            createElement('p', {},
                createElement(SiStrava, { style: { color: '#fc4c02', verticalAlign: 'text-bottom', marginRight: '8px' } }),
                __('Connect with Strava (frontend will render server-side)', 'tvs-virtual-sports')
            )
        );
    },
    save() { return null; }
} );

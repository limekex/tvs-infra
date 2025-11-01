import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

registerBlockType( 'tvs/routes-grid', {
    title: __( 'Routes Grid', 'norway-virtual-sport' ),
    edit() {
        return ( 'div', {}, 'Routes Grid placeholder — server-side rendering recommended' );
    },
    save() {
        return null; // server-rendered
    }
} );

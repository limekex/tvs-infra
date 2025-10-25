import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

registerBlockType( 'tvs/route-card', {
    title: __( 'Route Card', 'norway-virtual-sport' ),
    edit() {
        return ( 'div', {}, 'Route Card placeholder' );
    },
    save() { return null; }
} );

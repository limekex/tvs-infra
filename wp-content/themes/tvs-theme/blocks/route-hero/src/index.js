import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

registerBlockType( 'tvs/route-hero', {
    title: __( 'Route Hero', 'norway-virtual-sport' ),
    edit() { return ( 'div', {}, 'Route Hero placeholder' ); },
    save() { return null; }
} );

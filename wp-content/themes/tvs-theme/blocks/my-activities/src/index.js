import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

registerBlockType( 'tvs/my-activities', {
    title: __( 'My Activities', 'norway-virtual-sport' ),
    edit() { return ( 'div', {}, 'My Activities placeholder (requires login)' ); },
    save() { return null; }
} );

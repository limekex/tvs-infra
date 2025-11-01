import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

registerBlockType( 'tvs/connect-strava', {
    title: __( 'Connect Strava', 'norway-virtual-sport' ),
    edit() { return ( 'div', {}, 'Connect Strava placeholder' ); },
    save() { return null; }
} );

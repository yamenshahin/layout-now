/**
 * REST client for the layout-now/v1 routes. Nonce and root URL are handled by core's api-fetch middleware.
 */
import apiFetch from '@wordpress/api-fetch';

const NS = window.layoutNowAdmin?.namespace || 'layout-now/v1';
const route = ( path ) => `/${ NS }${ path }`;

const TAXONOMY_BASES = { category: 'categories', post_tag: 'tags' };

export const api = {
	schema: () => apiFetch( { path: route( '/schema' ) } ),
	list: () => apiFetch( { path: route( '/layouts' ) } ),
	get: ( id ) => apiFetch( { path: route( `/layouts/${ id }` ) } ),
	create: ( data ) => apiFetch( { path: route( '/layouts' ), method: 'POST', data } ),
	update: ( id, data ) => apiFetch( { path: route( `/layouts/${ id }` ), method: 'PUT', data } ),
	remove: ( id ) => apiFetch( { path: route( `/layouts/${ id }` ), method: 'DELETE' } ),
	duplicate: ( id ) => apiFetch( { path: route( `/layouts/${ id }/duplicate` ), method: 'POST' } ),
	preview: ( config, signal ) =>
		apiFetch( { path: route( '/preview' ), method: 'POST', data: { config }, signal } ),
	terms: ( taxonomy ) =>
		apiFetch( {
			path: `/wp/v2/${ TAXONOMY_BASES[ taxonomy ] || taxonomy }?per_page=100&orderby=count&order=desc&hide_empty=false&_fields=id,name,count`,
		} ),
};

/**
 * Copies text to the clipboard, with a fallback for non-secure (http) admin URLs.
 *
 * @param {string} text Text to copy.
 * @return {Promise<void>} Resolves once copied.
 */
export async function copyText( text ) {
	if ( window.navigator.clipboard && window.isSecureContext ) {
		return window.navigator.clipboard.writeText( text );
	}
	const area = document.createElement( 'textarea' );
	area.value = text;
	area.setAttribute( 'readonly', '' );
	area.style.position = 'fixed';
	area.style.opacity = '0';
	document.body.appendChild( area );
	area.select();
	document.execCommand( 'copy' );
	area.remove();
}

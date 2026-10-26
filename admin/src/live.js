/**
 * Client-side mirror of Renderer::attributes() so "live" style controls update the preview
 * instantly without a server round trip. Keep in sync with src/Render/Renderer.php.
 */

export function findSkin( schema, id ) {
	return schema.skins.find( ( skin ) => skin.id === id ) || schema.skins[ 0 ];
}

export function contrastColor( hex ) {
	let value = hex.replace( '#', '' );
	if ( value.length === 3 ) {
		value = value
			.split( '' )
			.map( ( c ) => c + c )
			.join( '' );
	}
	const [ r, g, b ] = [ 0, 2, 4 ].map( ( i ) => {
		const c = parseInt( value.slice( i, i + 2 ), 16 ) / 255;
		return c <= 0.03928 ? c / 12.92 : Math.pow( ( c + 0.055 ) / 1.055, 2.4 );
	} );
	return 0.2126 * r + 0.7152 * g + 0.0722 * b > 0.179 ? '#111111' : '#ffffff';
}

export function rootAttributes( style, skin ) {
	const resolve = ( key ) =>
		style[ key ] === 'auto' && skin.defaults?.[ key ] ? skin.defaults[ key ] : style[ key ];

	const vars = [];
	if ( style.accent ) {
		vars.push( `--ln-accent:${ style.accent }`, `--ln-on-accent:${ contrastColor( style.accent ) }` );
	}
	if ( style.max_width > 0 ) {
		vars.push( `--ln-max:${ parseInt( style.max_width, 10 ) }px` );
	}

	return {
		'data-skin': skin.id,
		'data-card': skin.card,
		'data-radius': resolve( 'radius' ),
		'data-spacing': resolve( 'spacing' ),
		'data-hover': resolve( 'hover' ),
		'data-ratio': style.ratio,
		'data-title': style.title_size,
		'data-font': style.font,
		'data-mode': skin.modes ? style.mode : null,
		style: vars.length ? vars.join( ';' ) : null,
	};
}

export function applyAttributes( element, attributes ) {
	Object.entries( attributes ).forEach( ( [ name, value ] ) => {
		if ( value === null || value === undefined || value === '' ) {
			element.removeAttribute( name );
		} else if ( element.getAttribute( name ) !== String( value ) ) {
			element.setAttribute( name, value );
		}
	} );
}

/**
 * Keys of style fields the preview can apply without re-rendering.
 *
 * @param {Object} schema Admin schema.
 * @return {string[]} Field keys.
 */
export function liveStyleKeys( schema ) {
	return Object.keys( schema.style ).filter( ( key ) => schema.style[ key ].live );
}

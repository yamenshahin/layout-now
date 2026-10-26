/**
 * Tiny query-string router so screens are linkable and survive a refresh without page reloads.
 *
 * Routes: (none) = list, view=new = layout picker, view=edit&id=12 = editor, view=edit&layout=bento = new layout editor.
 */
import { useEffect, useState } from '@wordpress/element';

const PARAMS = [ 'view', 'id', 'layout' ];
const EVENT = 'lnow:navigate';

let guard = null;

/**
 * Registers a function asked before in-app navigation; returning false cancels it.
 *
 * @param {Function|null} fn Guard.
 */
export function setNavigationGuard( fn ) {
	guard = fn;
}

function read() {
	const params = new URLSearchParams( window.location.search );
	const id = parseInt( params.get( 'id' ), 10 );
	return {
		view: params.get( 'view' ) || 'list',
		id: Number.isNaN( id ) ? null : id,
		layout: params.get( 'layout' ),
	};
}

/**
 * Navigates within the app.
 *
 * @param {Object}  params         Route params.
 * @param {Object}  options        Options.
 * @param {boolean} options.replace Replace the history entry instead of pushing.
 * @param {boolean} options.force   Skip the navigation guard.
 */
export function navigate( params = {}, { replace = false, force = false } = {} ) {
	if ( ! force && guard && ! guard() ) {
		return;
	}
	const url = new URL( window.location.href );
	PARAMS.forEach( ( key ) => url.searchParams.delete( key ) );
	Object.entries( params ).forEach( ( [ key, value ] ) => {
		if ( value !== null && value !== undefined ) {
			url.searchParams.set( key, value );
		}
	} );
	window.history[ replace ? 'replaceState' : 'pushState' ]( {}, '', url );
	window.dispatchEvent( new Event( EVENT ) );
}

export function useRoute() {
	const [ route, setRoute ] = useState( read );

	useEffect( () => {
		const update = () => setRoute( read() );
		window.addEventListener( 'popstate', update );
		window.addEventListener( EVENT, update );
		return () => {
			window.removeEventListener( 'popstate', update );
			window.removeEventListener( EVENT, update );
		};
	}, [] );

	return route;
}

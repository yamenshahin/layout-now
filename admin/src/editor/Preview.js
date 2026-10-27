/**
 * Isolated live preview. Real frontend markup from the REST preview endpoint is rendered inside a
 * same-origin srcdoc iframe that loads only plugin CSS, so wp-admin styles can't leak in.
 *
 * Structural changes are debounced and re-rendered on the server; "live" style changes are
 * applied directly to the root element's attributes for instant feedback.
 */
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { api } from '../api';
import { applyAttributes, findSkin, liveStyleKeys, rootAttributes } from '../live';

const DEVICE_WIDTHS = { desktop: null, tablet: 834, mobile: 390 };

const escapeAttr = ( value ) => String( value ).replace( /&/g, '&amp;' ).replace( /"/g, '&quot;' );

function buildDocument( schema ) {
	const styles = [
		window.layoutNowAdmin?.baseStyle,
		...schema.layouts.map( ( layout ) => layout.style ),
		...schema.skins.map( ( skin ) => skin.style ),
	]
		.filter( Boolean )
		.map( ( href ) => `<link rel="stylesheet" href="${ escapeAttr( href ) }">` )
		.join( '' );

	// Layout scripts self-register on window.LayoutNow; the preview re-runs them after each render.
	const scripts = schema.layouts
		.map( ( layout ) => layout.script )
		.filter( Boolean )
		.map( ( src ) => `<script src="${ escapeAttr( src ) }"></script>` )
		.join( '' );

	// Page backgrounds come from the admin's --a-preview-* tokens (app.scss), the single source of truth.
	const tokens = window.getComputedStyle( document.querySelector( '.lnow-app' ) || document.documentElement );
	const light = tokens.getPropertyValue( '--a-preview-light' ).trim() || 'white';
	const dark = tokens.getPropertyValue( '--a-preview-dark' ).trim() || 'black';

	return `<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">${ styles }<style>
		html,body{margin:0;min-height:100%}
		body{padding:40px 32px;box-sizing:border-box;background:${ light };font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;transition:background-color .25s}
		body.is-dark{background:${ dark }}
		@media (max-width:600px){body{padding:20px 16px}}
	</style></head><body><div id="lnow-preview"></div>${ scripts }</body></html>`;
}

export default function Preview( { schema, config, device, background } ) {
	const frame = useRef( null );
	const lastHtml = useRef( null );
	const [ ready, setReady ] = useState( false );
	const [ html, setHtml ] = useState( '' );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ] = useState( null );

	const srcDoc = useMemo( () => buildDocument( schema ), [ schema ] );
	const liveKeys = useMemo( () => liveStyleKeys( schema ), [ schema ] );
	const skin = findSkin( schema, config.style.skin );

	// Everything that requires new markup from the server.
	const structuralKey = useMemo( () => {
		const style = { ...config.style };
		liveKeys.forEach( ( key ) => delete style[ key ] );
		return JSON.stringify( {
			layout: config.layout,
			layoutOptions: config.layout_options[ config.layout ],
			source: config.source,
			sourceOptions: config.source_options[ config.source ],
			style,
		} );
	}, [ config, liveKeys ] );

	useEffect( () => {
		const controller = new window.AbortController();
		setLoading( true );

		const timer = window.setTimeout( () => {
			api.preview( config, controller.signal )
				.then( ( response ) => {
					setHtml( response.html );
					setError( null );
				} )
				.catch( ( err ) => {
					if ( err?.name !== 'AbortError' ) {
						setError( err?.message || __( 'Preview failed to load.', 'layout-now' ) );
					}
				} )
				.finally( () => {
					if ( ! controller.signal.aborted ) {
						setLoading( false );
					}
				} );
		}, 250 );

		return () => {
			window.clearTimeout( timer );
			controller.abort();
		};
		// Only structural changes trigger a server render; config is read at that moment.
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ structuralKey ] );

	// Push markup and live attributes into the iframe.
	useEffect( () => {
		const doc = frame.current?.contentDocument;
		const mount = ready && doc ? doc.getElementById( 'lnow-preview' ) : null;
		if ( ! mount ) {
			return;
		}
		if ( lastHtml.current !== html ) {
			// Let the layout scripts release observers on the markup being replaced.
			frame.current.contentWindow.LayoutNow?.destroy?.( mount );
			mount.innerHTML = html;
			lastHtml.current = html;
			frame.current.contentWindow.LayoutNow?.init?.( mount );
		}
		const root = mount.querySelector( '.lnow' );
		if ( root ) {
			applyAttributes( root, rootAttributes( config.style, skin ) );
		}
		doc.body.classList.toggle( 'is-dark', background === 'dark' );
	}, [ ready, html, config.style, skin, background ] );

	const onLoad = () => {
		const doc = frame.current?.contentDocument;
		if ( doc ) {
			// Cards link to real posts; keep the preview in place.
			doc.addEventListener( 'click', ( event ) => {
				if ( event.target.closest( 'a' ) ) {
					event.preventDefault();
				}
			} );
		}
		lastHtml.current = null;
		setReady( true );
	};

	const width = DEVICE_WIDTHS[ device ];

	return (
		<div className={ `lnow-canvas is-${ device }${ background === 'dark' ? ' is-dark' : '' }` }>
			<div className={ `lnow-canvas__progress${ loading ? ' is-active' : '' }` } />
			{ error && <div className="lnow-canvas__error">{ error }</div> }
			<div className="lnow-canvas__frame" style={ width ? { width: `${ width }px` } : undefined }>
				<iframe
					ref={ frame }
					title={ __( 'Layout preview', 'layout-now' ) }
					srcDoc={ srcDoc }
					onLoad={ onLoad }
				/>
			</div>
		</div>
	);
}

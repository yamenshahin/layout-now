/**
 * Layout editor: top bar, settings sidebar and live preview canvas.
 */
import { useCallback, useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Spinner } from '@wordpress/components';
import { api, copyText } from '../api';
import { navigate, setNavigationGuard } from '../router';
import { useToast } from '../toasts';
import Preview from '../editor/Preview';
import { ContentPanel, LayoutPanel, StylePanel } from '../editor/panels';
import { findSkin } from '../live';
import { ArrowLeft, Copy, Desktop, Mobile, Moon, Sun, Tablet } from '../components/icons';

const TABS = [
	{ id: 'layout', label: __( 'Layout', 'layout-now' ) },
	{ id: 'content', label: __( 'Content', 'layout-now' ) },
	{ id: 'style', label: __( 'Style', 'layout-now' ) },
];

const DEVICES = [
	{ id: 'desktop', label: __( 'Desktop', 'layout-now' ), Icon: Desktop },
	{ id: 'tablet', label: __( 'Tablet', 'layout-now' ), Icon: Tablet },
	{ id: 'mobile', label: __( 'Mobile', 'layout-now' ), Icon: Mobile },
];

/**
 * Fresh config for a new layout, using schema defaults.
 *
 * @param {Object} schema   Admin schema.
 * @param {string} layoutId Chosen layout.
 * @return {Object} Config.
 */
function newConfig( schema, layoutId ) {
	const config = JSON.parse( JSON.stringify( schema.defaults ) );
	const layout = schema.layouts.find( ( item ) => item.id === layoutId ) || schema.layouts[ 0 ];
	config.layout = layout.id;
	config.layout_options = { ...config.layout_options, [ layout.id ]: { ...layout.defaults } };
	return config;
}

export default function Editor( { schema, route } ) {
	const toast = useToast();
	const [ id, setId ] = useState( route.id );
	const [ title, setTitle ] = useState( '' );
	const [ config, setConfig ] = useState( () => ( route.id ? null : newConfig( schema, route.layout ) ) );
	const [ saved, setSaved ] = useState( () => ( route.id ? null : '' ) );
	const [ saving, setSaving ] = useState( false );
	const [ tab, setTab ] = useState( 'style' );
	const [ device, setDevice ] = useState( 'desktop' );
	const [ background, setBackground ] = useState( null );

	// Load an existing layout once.
	useEffect( () => {
		if ( ! route.id ) {
			return;
		}
		api.get( route.id )
			.then( ( layout ) => {
				setTitle( layout.title );
				setConfig( layout.config );
				setSaved( JSON.stringify( { title: layout.title, config: layout.config } ) );
			} )
			.catch( () => {
				toast( __( 'That layout could not be found.', 'layout-now' ), 'error' );
				navigate( {}, { replace: true, force: true } );
			} );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	const snapshot = useMemo( () => JSON.stringify( { title, config } ), [ title, config ] );
	const dirty = config !== null && snapshot !== saved;

	// Guard in-app navigation and tab closing while there are unsaved changes.
	useEffect( () => {
		setNavigationGuard( () =>
			// eslint-disable-next-line no-alert
			! dirty || window.confirm( __( 'You have unsaved changes. Leave without saving?', 'layout-now' ) )
		);
		const beforeUnload = ( event ) => {
			if ( dirty ) {
				event.preventDefault();
				event.returnValue = '';
			}
		};
		window.addEventListener( 'beforeunload', beforeUnload );
		return () => {
			setNavigationGuard( null );
			window.removeEventListener( 'beforeunload', beforeUnload );
		};
	}, [ dirty ] );

	const save = useCallback( async () => {
		if ( ! config || saving ) {
			return;
		}
		setSaving( true );
		try {
			const data = { title: title.trim() || __( 'Untitled layout', 'layout-now' ), config };
			const layout = id ? await api.update( id, data ) : await api.create( data );
			setTitle( layout.title );
			setConfig( layout.config );
			setSaved( JSON.stringify( { title: layout.title, config: layout.config } ) );
			if ( ! id ) {
				setId( layout.id );
				navigate( { view: 'edit', id: layout.id }, { replace: true, force: true } );
			}
			toast( __( 'Layout saved', 'layout-now' ) );
		} catch ( error ) {
			toast( error?.message || __( 'Saving failed.', 'layout-now' ), 'error' );
		} finally {
			setSaving( false );
		}
	}, [ config, id, saving, title, toast ] );

	// Ctrl/Cmd + S.
	useEffect( () => {
		const onKey = ( event ) => {
			if ( ( event.metaKey || event.ctrlKey ) && event.key.toLowerCase() === 's' ) {
				event.preventDefault();
				save();
			}
		};
		window.addEventListener( 'keydown', onKey );
		return () => window.removeEventListener( 'keydown', onKey );
	}, [ save ] );

	if ( ! config ) {
		return (
			<div className="lnow-loading">
				<Spinner />
			</div>
		);
	}

	const update = ( fn ) => setConfig( ( current ) => fn( JSON.parse( JSON.stringify( current ) ) ) );

	const setStyle = ( key, value ) =>
		update( ( next ) => {
			next.style[ key ] = value;
			return next;
		} );

	const applyPreset = ( values ) =>
		update( ( next ) => {
			next.style = { ...next.style, ...values };
			return next;
		} );

	const setLayout = ( layoutId ) =>
		update( ( next ) => {
			const layout = schema.layouts.find( ( item ) => item.id === layoutId );
			next.layout = layoutId;
			next.layout_options[ layoutId ] = next.layout_options[ layoutId ] || { ...layout.defaults };
			return next;
		} );

	const setLayoutOption = ( key, value ) =>
		update( ( next ) => {
			next.layout_options[ next.layout ][ key ] = value;
			return next;
		} );

	const setSource = ( sourceId ) =>
		update( ( next ) => {
			const source = schema.sources.find( ( item ) => item.id === sourceId );
			next.source = sourceId;
			next.source_options[ sourceId ] = next.source_options[ sourceId ] || { ...source.defaults };
			return next;
		} );

	const setSourceOption = ( key, value ) =>
		update( ( next ) => {
			next.source_options[ next.source ][ key ] = value;
			return next;
		} );

	// Preview background follows the skin until the user picks one.
	const skin = findSkin( schema, config.style.skin );
	const autoBackground = ( ! skin.modes && skin.card === 'stacked' ) || ( skin.modes && config.style.mode === 'dark' ) ? 'dark' : 'light';
	const canvasBackground = background || autoBackground;

	const shortcode = id ? `[layout_now id="${ id }"]` : '';

	let status = __( 'Saved', 'layout-now' );
	if ( saving ) {
		status = __( 'Saving…', 'layout-now' );
	} else if ( ! id ) {
		status = __( 'Not saved yet', 'layout-now' );
	} else if ( dirty ) {
		status = __( 'Unsaved changes', 'layout-now' );
	}

	return (
		<div className="lnow-editor">
			<header className="lnow-topbar">
				<div className="lnow-topbar__start">
					<button type="button" className="lnow-icon-button" onClick={ () => navigate( {} ) } aria-label={ __( 'All layouts', 'layout-now' ) } title={ __( 'All layouts', 'layout-now' ) }>
						<ArrowLeft />
					</button>
					<input
						className="lnow-title-input"
						value={ title }
						placeholder={ __( 'Untitled layout', 'layout-now' ) }
						aria-label={ __( 'Layout name', 'layout-now' ) }
						onChange={ ( event ) => setTitle( event.target.value ) }
					/>
				</div>

				<div className="lnow-topbar__center" role="group" aria-label={ __( 'Preview size', 'layout-now' ) }>
					{ DEVICES.map( ( { id: deviceId, label, Icon } ) => (
						<button
							key={ deviceId }
							type="button"
							className={ `lnow-icon-button${ device === deviceId ? ' is-active' : '' }` }
							aria-pressed={ device === deviceId }
							aria-label={ label }
							title={ label }
							onClick={ () => setDevice( deviceId ) }
						>
							<Icon />
						</button>
					) ) }
					<span className="lnow-topbar__divider" />
					<button
						type="button"
						className="lnow-icon-button"
						aria-label={ canvasBackground === 'dark' ? __( 'Light preview background', 'layout-now' ) : __( 'Dark preview background', 'layout-now' ) }
						title={ __( 'Preview background', 'layout-now' ) }
						onClick={ () => setBackground( canvasBackground === 'dark' ? 'light' : 'dark' ) }
					>
						{ canvasBackground === 'dark' ? <Sun /> : <Moon /> }
					</button>
				</div>

				<div className="lnow-topbar__end">
					<span className={ `lnow-status${ dirty ? ' is-dirty' : '' }` }>{ status }</span>
					{ shortcode && (
						<button
							type="button"
							className="lnow-button is-ghost lnow-shortcode-button"
							onClick={ () =>
								copyText( shortcode ).then( () => toast( __( 'Shortcode copied', 'layout-now' ) ) )
							}
							title={ __( 'Copy shortcode', 'layout-now' ) }
						>
							<Copy size={ 16 } />
							<code>{ shortcode }</code>
						</button>
					) }
					<button type="button" className="lnow-button is-primary" onClick={ save } disabled={ saving || ( id && ! dirty ) }>
						{ saving ? __( 'Saving…', 'layout-now' ) : __( 'Save', 'layout-now' ) }
					</button>
				</div>
			</header>

			<div className="lnow-editor__body">
				<aside className="lnow-sidebar">
					<div className="lnow-tabs" role="tablist">
						{ TABS.map( ( item ) => (
							<button
								key={ item.id }
								type="button"
								role="tab"
								id={ `lnow-tab-${ item.id }` }
								aria-selected={ tab === item.id }
								aria-controls="lnow-tabpanel"
								className={ tab === item.id ? 'is-active' : '' }
								onClick={ () => setTab( item.id ) }
							>
								{ item.label }
							</button>
						) ) }
					</div>
					<div className="lnow-sidebar__panel" id="lnow-tabpanel" role="tabpanel" aria-labelledby={ `lnow-tab-${ tab }` }>
						{ tab === 'layout' && (
							<LayoutPanel schema={ schema } config={ config } onLayout={ setLayout } onOption={ setLayoutOption } />
						) }
						{ tab === 'content' && (
							<ContentPanel schema={ schema } config={ config } onOption={ setSourceOption } onSource={ setSource } />
						) }
						{ tab === 'style' && (
							<StylePanel schema={ schema } config={ config } onStyle={ setStyle } onPreset={ applyPreset } />
						) }
					</div>
				</aside>

				<Preview schema={ schema } config={ config } device={ device } background={ canvasBackground } />
			</div>
		</div>
	);
}

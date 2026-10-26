/**
 * All saved layouts.
 */
import { useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Button, Dropdown, MenuGroup, MenuItem, Modal, Spinner } from '@wordpress/components';
import { api, copyText } from '../api';
import { navigate } from '../router';
import { useToast } from '../toasts';
import { findSkin } from '../live';
import LayoutGlyph from '../components/LayoutGlyph';
import { Copy, Dots, Plus, Sparkle } from '../components/icons';

const relative = new Intl.RelativeTimeFormat( undefined, { numeric: 'auto' } );

function timeAgo( iso ) {
	const seconds = ( new Date( iso ).getTime() - Date.now() ) / 1000;
	const steps = [
		[ 60, 'second' ],
		[ 60, 'minute' ],
		[ 24, 'hour' ],
		[ 30, 'day' ],
		[ 12, 'month' ],
		[ Infinity, 'year' ],
	];
	let value = seconds;
	for ( const [ size, unit ] of steps ) {
		if ( Math.abs( value ) < size ) {
			return relative.format( Math.round( value ), unit );
		}
		value /= size;
	}
	return '';
}

export default function LayoutsList( { schema } ) {
	const toast = useToast();
	const [ layouts, setLayouts ] = useState( null );
	const [ confirming, setConfirming ] = useState( null );

	const load = () =>
		api.list()
			.then( setLayouts )
			.catch( () => {
				setLayouts( [] );
				toast( __( 'Layouts could not be loaded.', 'layout-now' ), 'error' );
			} );

	useEffect( () => {
		load();
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	const duplicate = ( layout ) =>
		api.duplicate( layout.id ).then( () => {
			toast( __( 'Layout duplicated', 'layout-now' ) );
			load();
		} );

	const remove = ( layout ) =>
		api.remove( layout.id ).then( () => {
			setConfirming( null );
			toast( __( 'Layout deleted', 'layout-now' ) );
			setLayouts( ( list ) => list.filter( ( item ) => item.id !== layout.id ) );
		} );

	const layoutLabel = ( id ) => schema.layouts.find( ( item ) => item.id === id )?.label || id;

	return (
		<div className="lnow-list">
			<div className="lnow-list__header">
				<div>
					<h1>{ __( 'Layout Now', 'layout-now' ) }</h1>
					<p>{ __( 'Modern grids and sliders for your posts. Embed any layout with its shortcode.', 'layout-now' ) }</p>
				</div>
				{ layouts?.length > 0 && (
					<button type="button" className="lnow-button is-primary" onClick={ () => navigate( { view: 'new' } ) }>
						<Plus size={ 18 } />
						{ __( 'New layout', 'layout-now' ) }
					</button>
				) }
			</div>

			{ layouts === null && (
				<div className="lnow-loading">
					<Spinner />
				</div>
			) }

			{ layouts?.length === 0 && (
				<div className="lnow-empty">
					<div className="lnow-empty__art">
						<LayoutGlyph layout="bento" animated />
					</div>
					<h2>{ __( 'Create your first layout', 'layout-now' ) }</h2>
					<p>{ __( 'Pick a layout, choose a skin, and drop the shortcode anywhere. It takes about a minute.', 'layout-now' ) }</p>
					<button type="button" className="lnow-button is-primary is-large" onClick={ () => navigate( { view: 'new' } ) }>
						<Sparkle size={ 18 } />
						{ __( 'Create a layout', 'layout-now' ) }
					</button>
				</div>
			) }

			{ layouts?.length > 0 && (
				<div className="lnow-cards">
					{ layouts.map( ( layout ) => {
						const skin = findSkin( schema, layout.config.style.skin );
						const swatch = { ...skin.swatch, accent: layout.config.style.accent || skin.swatch.accent };
						const open = () => navigate( { view: 'edit', id: layout.id } );

						return (
							<article key={ layout.id } className="lnow-card-item">
								<button type="button" className="lnow-card-item__thumb" onClick={ open } aria-label={ sprintf( /* translators: %s: layout name. */ __( 'Edit %s', 'layout-now' ), layout.title ) }>
									<LayoutGlyph layout={ layout.config.layout } swatch={ swatch } />
								</button>
								<div className="lnow-card-item__body">
									<h2>
										<button type="button" onClick={ open }>
											{ layout.title }
										</button>
									</h2>
									<p className="lnow-card-item__meta">
										{ layoutLabel( layout.config.layout ) } · { skin.label } · { timeAgo( layout.modified ) }
									</p>
									<div className="lnow-card-item__footer">
										<button
											type="button"
											className="lnow-chip"
											onClick={ () => copyText( layout.shortcode ).then( () => toast( __( 'Shortcode copied', 'layout-now' ) ) ) }
											title={ __( 'Copy shortcode', 'layout-now' ) }
										>
											<code>{ layout.shortcode }</code>
											<Copy size={ 14 } />
										</button>
										<Dropdown
											popoverProps={ { placement: 'bottom-end' } }
											renderToggle={ ( { isOpen, onToggle } ) => (
												<button type="button" className="lnow-icon-button" onClick={ onToggle } aria-expanded={ isOpen } aria-label={ __( 'More actions', 'layout-now' ) }>
													<Dots />
												</button>
											) }
											renderContent={ ( { onClose } ) => (
												<MenuGroup>
													<MenuItem onClick={ () => { onClose(); open(); } }>{ __( 'Edit', 'layout-now' ) }</MenuItem>
													<MenuItem onClick={ () => { onClose(); duplicate( layout ); } }>{ __( 'Duplicate', 'layout-now' ) }</MenuItem>
													<MenuItem isDestructive onClick={ () => { onClose(); setConfirming( layout ); } }>
														{ __( 'Delete', 'layout-now' ) }
													</MenuItem>
												</MenuGroup>
											) }
										/>
									</div>
								</div>
							</article>
						);
					} ) }
				</div>
			) }

			{ confirming && (
				<Modal title={ __( 'Delete layout?', 'layout-now' ) } onRequestClose={ () => setConfirming( null ) } size="small">
					<p>
						{ sprintf(
							/* translators: %s: layout name. */
							__( '"%s" will be removed permanently. Pages using its shortcode will show nothing in its place.', 'layout-now' ),
							confirming.title
						) }
					</p>
					<div className="lnow-modal-actions">
						<Button variant="tertiary" onClick={ () => setConfirming( null ) }>
							{ __( 'Cancel', 'layout-now' ) }
						</Button>
						<Button variant="primary" isDestructive onClick={ () => remove( confirming ) }>
							{ __( 'Delete', 'layout-now' ) }
						</Button>
					</div>
				</Modal>
			) }
		</div>
	);
}

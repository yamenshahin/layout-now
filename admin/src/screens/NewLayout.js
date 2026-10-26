/**
 * Layout picker shown when creating a layout.
 */
import { __ } from '@wordpress/i18n';
import { navigate } from '../router';
import LayoutGlyph from '../components/LayoutGlyph';
import { ArrowLeft } from '../components/icons';

const DESCRIPTIONS = {
	bento: __( 'A large hero card with smaller tiles packed around it. Great for featured stories.', 'layout-now' ),
	carousel: __( 'A swipeable row where the next card peeks in from the edge.', 'layout-now' ),
	marquee: __( 'A continuous, auto-scrolling stream of cards that pauses on hover.', 'layout-now' ),
	'filter-grid': __( 'A clean grid with category pills that filter cards instantly.', 'layout-now' ),
};

export default function NewLayout( { schema } ) {
	return (
		<div className="lnow-new">
			<header className="lnow-new__header">
				<button type="button" className="lnow-icon-button" onClick={ () => navigate( {} ) } aria-label={ __( 'All layouts', 'layout-now' ) }>
					<ArrowLeft />
				</button>
				<div>
					<h1>{ __( 'Choose a layout', 'layout-now' ) }</h1>
					<p>{ __( 'You can switch layouts later. Your content and style settings carry over.', 'layout-now' ) }</p>
				</div>
			</header>
			<div className="lnow-new__grid">
				{ schema.layouts.map( ( layout ) => (
					<button
						key={ layout.id }
						type="button"
						className="lnow-new__tile"
						onClick={ () => navigate( { view: 'edit', layout: layout.id } ) }
					>
						<LayoutGlyph layout={ layout.id } animated />
						<span className="lnow-new__name">{ layout.label }</span>
						{ DESCRIPTIONS[ layout.id ] && <span className="lnow-new__desc">{ DESCRIPTIONS[ layout.id ] }</span> }
					</button>
				) ) }
			</div>
		</div>
	);
}

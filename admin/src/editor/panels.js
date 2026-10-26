/**
 * Editor sidebar panels: Layout, Content and Style.
 */
import { __, sprintf } from '@wordpress/i18n';
import { AccentPicker, Field, Row, Section, Segmented } from '../components/controls';
import LayoutGlyph from '../components/LayoutGlyph';
import { findSkin } from '../live';

export function LayoutPanel( { schema, config, onLayout, onOption } ) {
	const layout = schema.layouts.find( ( item ) => item.id === config.layout );
	const options = config.layout_options[ config.layout ] || layout.defaults;
	const swatch = findSkin( schema, config.style.skin ).swatch;

	return (
		<>
			<Section title={ __( 'Layout type', 'layout-now' ) }>
				<div className="lnow-layout-choices">
					{ schema.layouts.map( ( item ) => (
						<button
							key={ item.id }
							type="button"
							className={ item.id === config.layout ? 'is-active' : '' }
							aria-pressed={ item.id === config.layout }
							onClick={ () => onLayout( item.id ) }
						>
							<LayoutGlyph layout={ item.id } swatch={ swatch } />
							<span>{ item.label }</span>
						</button>
					) ) }
				</div>
			</Section>
			{ Object.keys( layout.fields ).length > 0 && (
				<Section title={ sprintf( /* translators: %s: layout name. */ __( '%s options', 'layout-now' ), layout.label ) }>
					{ Object.entries( layout.fields ).map( ( [ key, field ] ) => (
						<Field
							key={ key }
							name={ key }
							field={ field }
							value={ options[ key ] }
							onChange={ ( value ) => onOption( key, value ) }
						/>
					) ) }
				</Section>
			) }
		</>
	);
}

const CONTENT_GROUPS = [
	{ title: __( 'Posts', 'layout-now' ), keys: [ 'count', 'offset', 'orderby', 'order' ] },
	{ title: __( 'Filter', 'layout-now' ), keys: [ 'categories', 'tags', 'exclude_categories' ] },
	{ title: __( 'Options', 'layout-now' ), keys: [ 'exclude_current', 'sticky_first', 'require_image' ] },
];

export function ContentPanel( { schema, config, onOption, onSource } ) {
	const source = schema.sources.find( ( item ) => item.id === config.source );
	const layout = schema.layouts.find( ( item ) => item.id === config.layout );
	const options = config.source_options[ config.source ] || source.defaults;

	const grouped = new Set( CONTENT_GROUPS.flatMap( ( group ) => group.keys ) );
	const groups = [
		...CONTENT_GROUPS,
		// Fields added by other sources or filters land in a generic group.
		{ title: __( 'More', 'layout-now' ), keys: Object.keys( source.fields ).filter( ( key ) => ! grouped.has( key ) ) },
	];

	const renderField = ( key ) => {
		const field = source.fields[ key ];
		if ( ! field ) {
			return null;
		}
		if ( key === 'count' && layout.fixed_count ) {
			return (
				<Row key={ key } label={ field.label }>
					<p className="lnow-note">
						{ __( 'Set by the layout pattern so the grid always fills completely.', 'layout-now' ) }
					</p>
				</Row>
			);
		}
		if ( key === 'order' && options.orderby === 'rand' ) {
			return null;
		}
		return (
			<Field
				key={ key }
				name={ key }
				field={ field }
				value={ options[ key ] }
				onChange={ ( value ) => onOption( key, value ) }
			/>
		);
	};

	return (
		<>
			{ schema.sources.length > 1 && (
				<Section title={ __( 'Source', 'layout-now' ) }>
					<Segmented
						label={ __( 'Source', 'layout-now' ) }
						value={ config.source }
						options={ schema.sources.map( ( item ) => ( { value: item.id, label: item.label } ) ) }
						onChange={ onSource }
					/>
				</Section>
			) }
			{ groups
				.filter( ( group ) => group.keys.some( ( key ) => source.fields[ key ] ) )
				.map( ( group ) => (
					<Section key={ group.title } title={ group.title }>
						{ group.keys.map( renderField ) }
					</Section>
				) ) }
		</>
	);
}

const CARD_TOGGLES = [ 'show_image', 'show_badge', 'show_excerpt', 'show_date', 'show_author', 'show_more' ];
const LOOK_FIELDS = [ 'align', 'radius', 'spacing', 'hover', 'ratio', 'title_size', 'font' ];

export function StylePanel( { schema, config, onStyle, onPreset } ) {
	const style = config.style;
	const fields = schema.style;
	const skin = findSkin( schema, style.skin );
	const layout = schema.layouts.find( ( item ) => item.id === config.layout );
	const ignored = new Set( layout.ignored_style || [] );

	const field = ( key ) =>
		fields[ key ] && ! ignored.has( key ) ? (
			<Field
				key={ key }
				name={ key }
				field={ fields[ key ] }
				value={ style[ key ] }
				onChange={ ( value ) => onStyle( key, value ) }
			/>
		) : null;

	const activePreset = schema.presets.find( ( preset ) =>
		Object.entries( preset.values ).every( ( [ key, value ] ) => style[ key ] === value )
	);

	return (
		<>
			<Section title={ __( 'Presets', 'layout-now' ) }>
				<div className="lnow-presets">
					{ schema.presets.map( ( preset ) => {
						const presetSkin = findSkin( schema, preset.values.skin );
						return (
							<button
								key={ preset.id }
								type="button"
								className={ activePreset?.id === preset.id ? 'is-active' : '' }
								aria-pressed={ activePreset?.id === preset.id }
								onClick={ () => onPreset( preset.values ) }
							>
								<span
									className="lnow-presets__dot"
									style={ {
										'--a': preset.values.accent || presetSkin.swatch.accent,
										'--b': presetSkin.swatch.bg,
									} }
								/>
								{ preset.label }
							</button>
						);
					} ) }
				</div>
			</Section>

			<Section title={ __( 'Skin', 'layout-now' ) }>
				<div className="lnow-skins">
					{ schema.skins.map( ( item ) => (
						<button
							key={ item.id }
							type="button"
							className={ item.id === style.skin ? 'is-active' : '' }
							aria-pressed={ item.id === style.skin }
							onClick={ () => onStyle( 'skin', item.id ) }
						>
							<SkinThumb skin={ item } />
							<span>{ item.label }</span>
						</button>
					) ) }
				</div>
				<Row label={ fields.accent.label }>
					<AccentPicker value={ style.accent } skinAccent={ skin.swatch.accent } onChange={ ( value ) => onStyle( 'accent', value ) } />
				</Row>
				{ skin.modes && field( 'mode' ) }
			</Section>

			<Section title={ __( 'Look', 'layout-now' ) }>{ LOOK_FIELDS.map( field ) }</Section>

			<Section title={ __( 'Card content', 'layout-now' ) }>
				<div className="lnow-toggles">{ CARD_TOGGLES.map( field ) }</div>
				{ style.show_excerpt && field( 'excerpt_length' ) }
			</Section>

			<Section title={ __( 'Advanced', 'layout-now' ) } collapsible defaultOpen={ false }>
				{ Object.keys( fields )
					.filter(
						( key ) =>
							! [ 'skin', 'accent', 'mode', 'excerpt_length', ...LOOK_FIELDS, ...CARD_TOGGLES ].includes( key )
					)
					.map( field ) }
			</Section>
		</>
	);
}

function SkinThumb( { skin } ) {
	const { bg, surface, text, accent } = skin.swatch;
	const overlay = skin.card === 'overlay';

	return (
		<svg className="lnow-skin-thumb" viewBox="0 0 120 72" aria-hidden="true" focusable="false">
			<rect width="120" height="72" fill={ bg } />
			<g>
				<rect x="16" y="10" width="88" height="54" rx="6" fill={ surface } />
				{ overlay ? (
					<>
						<rect x="16" y="10" width="88" height="54" rx="6" fill={ text } opacity="0.08" />
						<rect x="24" y="44" width="16" height="5" rx="2.5" fill={ accent } />
						<rect x="24" y="53" width="56" height="5" rx="2.5" fill={ text } />
					</>
				) : (
					<>
						<rect x="16" y="10" width="88" height="28" rx="6" fill={ accent } opacity="0.22" />
						<rect x="24" y="44" width="16" height="4" rx="2" fill={ accent } />
						<rect x="24" y="52" width="58" height="5" rx="2.5" fill={ text } opacity="0.85" />
					</>
				) }
			</g>
		</svg>
	);
}

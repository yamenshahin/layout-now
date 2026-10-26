/**
 * Form controls. Schema fields (see src/Settings/Schema.php) render through <Field>.
 */
import { useEffect, useId, useMemo, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { ColorPicker, Popover } from '@wordpress/components';
import { api } from '../api';
import { ChevronDown } from './icons';

export function Section( { title, children, collapsible = false, defaultOpen = true } ) {
	const [ open, setOpen ] = useState( defaultOpen );

	return (
		<section className={ `lnow-section${ open ? ' is-open' : '' }` }>
			{ title &&
				( collapsible ? (
					<button
						type="button"
						className="lnow-section__title is-toggle"
						aria-expanded={ open }
						onClick={ () => setOpen( ! open ) }
					>
						{ title }
						<ChevronDown size={ 16 } />
					</button>
				) : (
					<h3 className="lnow-section__title">{ title }</h3>
				) ) }
			{ open && <div className="lnow-section__body">{ children }</div> }
		</section>
	);
}

export function Row( { label, htmlFor, children, hint } ) {
	return (
		<div className="lnow-row">
			{ label && (
				<label className="lnow-row__label" htmlFor={ htmlFor }>
					{ label }
				</label>
			) }
			{ children }
			{ hint && <p className="lnow-row__hint">{ hint }</p> }
		</div>
	);
}

export function Segmented( { label, value, options, onChange } ) {
	return (
		<div className="lnow-segmented" role="radiogroup" aria-label={ label }>
			{ options.map( ( option ) => (
				<button
					key={ option.value }
					type="button"
					role="radio"
					aria-checked={ value === option.value }
					title={ option.title || option.label }
					className={ value === option.value ? 'is-active' : '' }
					onClick={ () => onChange( option.value ) }
				>
					{ option.label }
				</button>
			) ) }
		</div>
	);
}

export function Switch( { label, checked, onChange } ) {
	const id = useId();
	return (
		<div className="lnow-switch">
			<label htmlFor={ id }>{ label }</label>
			<button
				id={ id }
				type="button"
				role="switch"
				aria-checked={ checked }
				className={ checked ? 'is-on' : '' }
				onClick={ () => onChange( ! checked ) }
			>
				<span />
			</button>
		</div>
	);
}

export function Range( { id, value, min, max, step = 1, onChange, format = String } ) {
	return (
		<div className="lnow-range">
			<input
				id={ id }
				type="range"
				min={ min }
				max={ max }
				step={ step }
				value={ value }
				onChange={ ( event ) => onChange( parseInt( event.target.value, 10 ) ) }
				style={ { '--lnow-fill': `${ ( ( value - min ) / ( max - min ) ) * 100 }%` } }
			/>
			<output htmlFor={ id }>{ format( value ) }</output>
		</div>
	);
}

const PALETTE = [ '#4f46e5', '#0284c7', '#059669', '#d97706', '#e11d48', '#7c3aed', '#18181b' ];

export function AccentPicker( { value, onChange, skinAccent } ) {
	const [ anchor, setAnchor ] = useState( null );
	const [ open, setOpen ] = useState( false );
	const custom = value && ! PALETTE.includes( value );

	return (
		<div className="lnow-swatches">
			<button
				type="button"
				className={ `lnow-swatch is-auto${ ! value ? ' is-active' : '' }` }
				style={ { '--swatch': skinAccent } }
				onClick={ () => onChange( '' ) }
				title={ __( 'Skin default', 'layout-now' ) }
				aria-label={ __( 'Skin default accent', 'layout-now' ) }
				aria-pressed={ ! value }
			/>
			{ PALETTE.map( ( color ) => (
				<button
					key={ color }
					type="button"
					className={ `lnow-swatch${ value === color ? ' is-active' : '' }` }
					style={ { '--swatch': color } }
					onClick={ () => onChange( color ) }
					aria-label={ color }
					aria-pressed={ value === color }
				/>
			) ) }
			<button
				ref={ setAnchor }
				type="button"
				className={ `lnow-swatch is-custom${ custom ? ' is-active' : '' }` }
				style={ custom ? { '--swatch': value } : undefined }
				onClick={ () => setOpen( ! open ) }
				aria-label={ __( 'Custom color', 'layout-now' ) }
				aria-expanded={ open }
			/>
			{ open && (
				<Popover anchor={ anchor } onClose={ () => setOpen( false ) } placement="bottom-end">
					<div className="lnow-color-popover">
						<ColorPicker
							color={ value || skinAccent }
							onChange={ ( color ) => onChange( color ) }
							enableAlpha={ false }
						/>
					</div>
				</Popover>
			) }
		</div>
	);
}

export function TermPills( { taxonomy, value, onChange } ) {
	const [ terms, setTerms ] = useState( null );
	const [ search, setSearch ] = useState( '' );

	useEffect( () => {
		let active = true;
		api.terms( taxonomy )
			.then( ( result ) => active && setTerms( result ) )
			.catch( () => active && setTerms( [] ) );
		return () => {
			active = false;
		};
	}, [ taxonomy ] );

	const visible = useMemo( () => {
		if ( ! terms ) {
			return [];
		}
		const needle = search.trim().toLowerCase();
		return needle ? terms.filter( ( term ) => term.name.toLowerCase().includes( needle ) ) : terms;
	}, [ terms, search ] );

	if ( ! terms ) {
		return <div className="lnow-pills is-loading" />;
	}
	if ( ! terms.length ) {
		return <p className="lnow-row__hint">{ __( 'Nothing to choose from yet.', 'layout-now' ) }</p>;
	}

	const toggle = ( id ) =>
		onChange( value.includes( id ) ? value.filter( ( v ) => v !== id ) : [ ...value, id ] );

	return (
		<>
			{ terms.length > 12 && (
				<input
					type="search"
					className="lnow-input lnow-pills__search"
					placeholder={ __( 'Search…', 'layout-now' ) }
					value={ search }
					onChange={ ( event ) => setSearch( event.target.value ) }
				/>
			) }
			<div className="lnow-pills">
				{ visible.map( ( term ) => (
					<button
						key={ term.id }
						type="button"
						className={ value.includes( term.id ) ? 'is-active' : '' }
						aria-pressed={ value.includes( term.id ) }
						onClick={ () => toggle( term.id ) }
					>
						{ decodeEntities( term.name ) }
						<span className="lnow-pills__count">{ term.count }</span>
					</button>
				) ) }
			</div>
		</>
	);
}

function decodeEntities( text ) {
	const el = document.createElement( 'textarea' );
	el.innerHTML = text;
	return el.value;
}

/**
 * Short label for a select choice; "Skin default" becomes "Auto" in segmented controls.
 *
 * @param {string} key   Choice key.
 * @param {string} label Choice label.
 * @return {string} Label.
 */
const shortLabel = ( key, label ) => ( key === 'auto' ? __( 'Auto', 'layout-now' ) : label );

/**
 * Renders any schema field.
 *
 * @param {Object}   props          Props.
 * @param {string}   props.name     Field key.
 * @param {Object}   props.field    Field definition.
 * @param {*}        props.value    Current value.
 * @param {Function} props.onChange Change handler.
 */
export function Field( { name, field, value, onChange } ) {
	const id = `lnow-field-${ name }`;

	switch ( field.type ) {
		case 'toggle':
			return <Switch label={ field.label } checked={ !! value } onChange={ onChange } />;

		case 'select': {
			const choices = Object.entries( field.choices );
			const options = choices.map( ( [ key, label ] ) => ( {
				value: key,
				label: shortLabel( key, label ),
				title: label,
			} ) );
			const compact = choices.length <= 5 && options.every( ( option ) => option.label.length <= 9 );

			return (
				<Row label={ field.label } htmlFor={ id }>
					{ compact ? (
						<Segmented label={ field.label } value={ value } options={ options } onChange={ onChange } />
					) : (
						<select
							id={ id }
							className="lnow-input"
							value={ value }
							onChange={ ( event ) => onChange( event.target.value ) }
						>
							{ choices.map( ( [ key, label ] ) => (
								<option key={ key } value={ key }>
									{ label }
								</option>
							) ) }
						</select>
					) }
				</Row>
			);
		}

		case 'number':
			return (
				<Row label={ field.label } htmlFor={ id }>
					<Range
						id={ id }
						value={ value }
						min={ field.min ?? 0 }
						max={ field.max ?? 100 }
						step={ field.step ?? 1 }
						onChange={ onChange }
						format={
							name === 'max_width'
								? ( v ) => ( v ? sprintf( /* translators: %d: pixels. */ __( '%dpx', 'layout-now' ), v ) : __( 'Full', 'layout-now' ) )
								: String
						}
					/>
				</Row>
			);

		case 'terms':
			return (
				<Row label={ field.label }>
					<TermPills taxonomy={ field.taxonomy } value={ value || [] } onChange={ onChange } />
				</Row>
			);

		default:
			return (
				<Row label={ field.label } htmlFor={ id }>
					<input
						id={ id }
						type="text"
						className="lnow-input"
						value={ value }
						onChange={ ( event ) => onChange( event.target.value ) }
					/>
				</Row>
			);
	}
}

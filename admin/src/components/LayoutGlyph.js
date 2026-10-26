/**
 * Schematic thumbnails for layouts, tinted with a skin swatch.
 */

const SHAPES = {
	bento: [
		[ 0, 0, 2, 2 ],
		[ 2, 0, 1, 1 ],
		[ 3, 0, 1, 1 ],
		[ 2, 1, 1, 1 ],
		[ 3, 1, 1, 1 ],
	],
	carousel: [
		[ 0, 0, 1.5, 2 ],
		[ 1.65, 0, 1.5, 2 ],
		[ 3.3, 0, 0.7, 2 ],
	],
	marquee: [
		[ -0.4, 0.4, 1.1, 1.2 ],
		[ 0.85, 0.4, 1.1, 1.2 ],
		[ 2.1, 0.4, 1.1, 1.2 ],
		[ 3.35, 0.4, 1.1, 1.2 ],
	],
	'filter-grid': [
		[ 0, 0.55, 1.3, 0.7 ],
		[ 1.35, 0.55, 1.3, 0.7 ],
		[ 2.7, 0.55, 1.3, 0.7 ],
		[ 0, 1.3, 1.3, 0.7 ],
		[ 1.35, 1.3, 1.3, 0.7 ],
		[ 2.7, 1.3, 1.3, 0.7 ],
	],
};

const DEFAULT_SWATCH = { bg: '#f4f4f5', surface: '#ffffff', text: '#18181b', accent: '#4f46e5' };

export default function LayoutGlyph( { layout, swatch = DEFAULT_SWATCH, animated = false } ) {
	const shapes = SHAPES[ layout ] || SHAPES.bento;
	const unit = 40;
	const gap = 4;

	return (
		<svg
			className={ `lnow-glyph is-${ layout }${ animated ? ' is-animated' : '' }` }
			viewBox="-8 -8 176 96"
			preserveAspectRatio="xMidYMid meet"
			aria-hidden="true"
			focusable="false"
			style={ { background: swatch.bg } }
		>
			{ layout === 'filter-grid' &&
				[ 0, 1, 2 ].map( ( i ) => (
					<rect
						key={ `pill-${ i }` }
						x={ i * 30 }
						y={ 0 }
						width={ 26 }
						height={ 10 }
						rx={ 5 }
						fill={ i === 0 ? swatch.accent : swatch.surface }
						stroke={ swatch.text }
						strokeOpacity={ i === 0 ? 0 : 0.14 }
					/>
				) ) }
			<g className="lnow-glyph__track">
				{ shapes.map( ( [ x, y, w, h ], i ) => (
					<g key={ i } className="lnow-glyph__tile" style={ { '--i': i } }>
						<rect
							x={ x * unit }
							y={ y * unit }
							width={ w * unit - gap }
							height={ h * unit - gap }
							rx={ 5 }
							fill={ swatch.surface }
							stroke={ swatch.text }
							strokeOpacity={ 0.14 }
						/>
						<rect
							x={ x * unit + 5 }
							y={ y * unit + h * unit - gap - 13 }
							width={ Math.max( 8, w * unit * 0.45 ) }
							height={ 4 }
							rx={ 2 }
							fill={ i === 0 ? swatch.accent : swatch.text }
							opacity={ i === 0 ? 0.9 : 0.35 }
						/>
					</g>
				) ) }
			</g>
		</svg>
	);
}

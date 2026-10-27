/**
 * Layout Now — Instant-filter grid.
 *
 * Filters cards by term without a reload and animates them into place with the Web Animations
 * API (FLIP): cards that stay glide to their new position, cards that appear fade and scale in.
 * Scoped to the grid, so it never interferes with the theme or other scripts.
 */
( function () {
	'use strict';

	var LN = ( window.LayoutNow = window.LayoutNow || {} );
	LN.inits = LN.inits || {};
	LN.init =
		LN.init ||
		function ( root ) {
			Object.keys( LN.inits ).forEach( function ( key ) {
				LN.inits[ key ]( root || document );
			} );
		};
	// Releases observers and window listeners before markup is discarded (the admin preview re-renders).
	LN.destroy =
		LN.destroy ||
		function ( root ) {
			Array.prototype.forEach.call( ( root || document ).querySelectorAll( '.is-ready' ), function ( el ) {
				if ( typeof el.lnowDestroy === 'function' ) {
					el.lnowDestroy();
				}
			} );
		};

	var reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
	var EASE = 'cubic-bezier(0.2, 0.7, 0.2, 1)';

	function matches( item, term ) {
		return term === '*' || ( ' ' + item.getAttribute( 'data-terms' ) + ' ' ).indexOf( ' ' + term + ' ' ) !== -1;
	}

	function setup( filter ) {
		var pills = Array.prototype.slice.call( filter.querySelectorAll( '.lnow-filter__pill' ) );
		var items = Array.prototype.slice.call( filter.querySelectorAll( '.lnow-filter__item' ) );
		var status = filter.querySelector( '.lnow-sr' );
		var template = filter.getAttribute( 'data-status' ) || '%d';

		function apply( term ) {
			var animate = ! reducedMotion.matches && typeof Element.prototype.animate === 'function';
			var before = new Map();

			if ( animate ) {
				items.forEach( function ( item ) {
					if ( ! item.hidden ) {
						before.set( item, item.getBoundingClientRect() );
					}
				} );
			}

			var shown = 0;
			items.forEach( function ( item ) {
				item.hidden = ! matches( item, term );
				if ( ! item.hidden ) {
					shown++;
				}
			} );

			pills.forEach( function ( pill ) {
				pill.setAttribute( 'aria-pressed', pill.getAttribute( 'data-term' ) === term ? 'true' : 'false' );
			} );

			if ( status ) {
				status.textContent = template.replace( '%d', shown );
			}

			if ( ! animate ) {
				return;
			}

			var entering = 0;
			items.forEach( function ( item ) {
				if ( item.hidden ) {
					return;
				}
				var first = before.get( item );
				if ( first ) {
					var last = item.getBoundingClientRect();
					var dx = first.left - last.left;
					var dy = first.top - last.top;
					if ( dx || dy ) {
						item.animate(
							[ { transform: 'translate(' + dx + 'px, ' + dy + 'px)' }, { transform: 'none' } ],
							{ duration: 480, easing: EASE }
						);
					}
				} else {
					item.animate(
						[
							{ opacity: 0, transform: 'scale(0.94) translateY(8px)' },
							{ opacity: 1, transform: 'none' },
						],
						{ duration: 420, easing: EASE, delay: 40 * entering++, fill: 'backwards' }
					);
				}
			} );
		}

		pills.forEach( function ( pill ) {
			pill.addEventListener( 'click', function () {
				if ( pill.getAttribute( 'aria-pressed' ) !== 'true' ) {
					apply( pill.getAttribute( 'data-term' ) );
				}
			} );
		} );

		filter.classList.add( 'is-ready' );
	}

	LN.inits.filterGrid = function ( root ) {
		Array.prototype.forEach.call( root.querySelectorAll( '.lnow-filter:not(.is-ready)' ), setup );
	};

	LN.inits.filterGrid( document );
} )();

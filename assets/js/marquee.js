/**
 * Layout Now — Infinite marquee.
 *
 * The motion is pure CSS. This adds the pause/play button (WCAG 2.2.2) and pauses marquees
 * that are scrolled out of view.
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

	var observer =
		'IntersectionObserver' in window
			? new window.IntersectionObserver( function ( entries ) {
					entries.forEach( function ( entry ) {
						entry.target.classList.toggle( 'is-offscreen', ! entry.isIntersecting );
					} );
			  } )
			: null;

	function setup( marquee ) {
		var toggle = marquee.querySelector( '.lnow-marquee__toggle' );

		if ( toggle ) {
			var label = toggle.querySelector( 'span' );
			toggle.addEventListener( 'click', function () {
				// The visible label is the state ("Pause" / "Play"), so no aria-pressed as well.
				var paused = marquee.classList.toggle( 'is-paused' );
				if ( label ) {
					label.textContent = paused ? toggle.dataset.labelPlay : toggle.dataset.labelPause;
				}
			} );
		}

		if ( observer ) {
			observer.observe( marquee );
		}
		marquee.lnowDestroy = function () {
			if ( observer ) {
				observer.unobserve( marquee );
			}
		};

		marquee.classList.add( 'is-ready' );
	}

	LN.inits.marquee = function ( root ) {
		Array.prototype.forEach.call( root.querySelectorAll( '.lnow-marquee:not(.is-ready)' ), setup );
	};

	LN.inits.marquee( document );
} )();

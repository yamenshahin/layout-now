/**
 * Layout Now — Edge-peek carousel.
 *
 * Native scroll-snap handles swiping, touch and keyboard scrolling; this only wires up the
 * arrow buttons and dots. Without JavaScript the carousel still scrolls.
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

	var reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	function setup( carousel ) {
		var track = carousel.querySelector( '.lnow-carousel__track' );
		if ( ! track ) {
			return;
		}
		var slides = Array.prototype.slice.call( track.children );
		var prev = carousel.querySelector( '[data-lnow-prev]' );
		var next = carousel.querySelector( '[data-lnow-next]' );
		var dots = Array.prototype.slice.call( carousel.querySelectorAll( '.lnow-carousel__dot' ) );
		var rtl = window.getComputedStyle( track ).direction === 'rtl';
		var queued = false;

		function step() {
			var first = slides[ 0 ];
			var gap = parseFloat( window.getComputedStyle( track ).columnGap ) || 0;
			return first ? first.getBoundingClientRect().width + gap : 1;
		}

		function maxScroll() {
			return Math.max( 0, track.scrollWidth - track.clientWidth );
		}

		function position() {
			return Math.abs( track.scrollLeft );
		}

		function current() {
			return Math.round( position() / step() );
		}

		function go( index ) {
			var left = Math.min( index * step(), maxScroll() );
			track.scrollTo( {
				left: rtl ? -left : left,
				behavior: reducedMotion.matches ? 'auto' : 'smooth',
			} );
		}

		function update() {
			queued = false;
			var max = maxScroll();
			var pos = position();
			// Snap positions that can actually be reached; trailing slides share the last one.
			var stops = Math.max( 1, Math.round( max / step() ) + 1 );
			var active = pos >= max - 2 ? stops - 1 : Math.min( current(), stops - 1 );

			carousel.classList.toggle( 'is-static', max < 2 );
			if ( prev ) {
				prev.disabled = pos < 2;
			}
			if ( next ) {
				next.disabled = pos > max - 2;
			}
			dots.forEach( function ( dot, i ) {
				dot.hidden = i >= stops;
				dot.setAttribute( 'aria-current', i === active ? 'true' : 'false' );
			} );
		}

		function schedule() {
			if ( ! queued ) {
				queued = true;
				window.requestAnimationFrame( update );
			}
		}

		if ( prev ) {
			prev.addEventListener( 'click', function () {
				go( Math.max( 0, current() - 1 ) );
			} );
		}
		if ( next ) {
			next.addEventListener( 'click', function () {
				go( current() + 1 );
			} );
		}
		dots.forEach( function ( dot, i ) {
			dot.addEventListener( 'click', function () {
				go( i );
			} );
		} );

		track.addEventListener( 'scroll', schedule, { passive: true } );
		if ( 'ResizeObserver' in window ) {
			new window.ResizeObserver( schedule ).observe( track );
		} else {
			window.addEventListener( 'resize', schedule );
		}

		carousel.classList.add( 'is-ready' );
		update();
	}

	LN.inits.carousel = function ( root ) {
		Array.prototype.forEach.call( root.querySelectorAll( '.lnow-carousel:not(.is-ready)' ), setup );
	};

	LN.inits.carousel( document );
} )();

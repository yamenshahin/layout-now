<?php
/**
 * Infinite marquee template.
 *
 * Each track holds two identical copies so a -50% translate loops seamlessly. Only the first
 * occurrence of each post is exposed to assistive tech and keyboard focus; repeats, the second
 * copy and the second row are aria-hidden and inert.
 *
 * @package LayoutNow
 *
 * @var \LayoutNow\Sources\Item[] $items
 * @var array                     $data  { rows: Item[][], repeat: int, duration: float }
 * @var \LayoutNow\Render\View    $view
 */

defined( 'ABSPATH' ) || exit;

$layout_now_card_args = array(
	'size'  => 'medium_large',
	'sizes' => '360px',
);
?>
<div class="lnow-marquee" data-direction="<?php echo esc_attr( $view->option( 'direction' ) ); ?>" data-size="<?php echo esc_attr( $view->option( 'size' ) ); ?>" data-fade="<?php echo esc_attr( $view->option( 'fade' ) ); ?>" style="<?php echo esc_attr( '--ln-duration:' . $data['duration'] . 's' ); ?>" role="region" aria-label="<?php echo esc_attr( $view->label() ); ?>">
	<?php foreach ( $data['rows'] as $layout_now_row => $layout_now_row_items ) : ?>
		<div class="lnow-marquee__row<?php echo $layout_now_row ? ' lnow-marquee__row--alt' : ''; ?>"<?php echo $layout_now_row ? ' aria-hidden="true" inert' : ''; ?>>
			<div class="lnow-marquee__track">
				<?php
				for ( $layout_now_copy = 0; $layout_now_copy < 2; $layout_now_copy++ ) {
					for ( $layout_now_rep = 0; $layout_now_rep < $data['repeat']; $layout_now_rep++ ) {
						foreach ( $layout_now_row_items as $layout_now_item ) {
							$layout_now_clone = $layout_now_copy || $layout_now_rep;
							echo $layout_now_clone && ! $layout_now_row ? '<div class="lnow-marquee__item" aria-hidden="true" inert>' : '<div class="lnow-marquee__item">';
							$view->card( $layout_now_item, $layout_now_card_args );
							echo '</div>';
						}
					}
				}
				?>
			</div>
		</div>
	<?php endforeach; ?>
	<?php if ( $view->option( 'pause_button' ) ) : ?>
		<div class="lnow-marquee__controls">
			<button type="button" class="lnow-control lnow-marquee__toggle" data-label-pause="<?php esc_attr_e( 'Pause', 'layout-now' ); ?>" data-label-play="<?php esc_attr_e( 'Play', 'layout-now' ); ?>">
				<svg class="lnow-marquee__icon-pause" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
				<svg class="lnow-marquee__icon-play" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.5-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5z"/></svg>
				<span><?php esc_html_e( 'Pause', 'layout-now' ); ?></span>
			</button>
		</div>
	<?php endif; ?>
</div>

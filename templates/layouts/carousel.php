<?php
/**
 * Edge-peek carousel template.
 *
 * Works without JavaScript (native swipe/scroll); the controls appear once carousel.js is ready.
 *
 * @package LayoutNow
 *
 * @var \LayoutNow\Sources\Item[] $items
 * @var array                     $data
 * @var \LayoutNow\Render\View    $view
 */

defined( 'ABSPATH' ) || exit;

$layout_now_total = count( $items );
$layout_now_nav   = $view->option( 'nav', 'both' );
?>
<div class="lnow-carousel" data-size="<?php echo esc_attr( $view->option( 'size' ) ); ?>" data-peek="<?php echo esc_attr( $view->option( 'peek' ) ); ?>" data-nav="<?php echo esc_attr( $layout_now_nav ); ?>" role="region" aria-roledescription="<?php esc_attr_e( 'carousel', 'layout-now' ); ?>" aria-label="<?php echo esc_attr( $view->label() ); ?>">
	<div class="lnow-carousel__track" tabindex="0">
		<?php foreach ( array_values( $items ) as $layout_now_index => $layout_now_item ) : ?>
			<?php
			/* translators: 1: slide number, 2: total slides. */
			$layout_now_slide_label = sprintf( __( '%1$d of %2$d', 'layout-now' ), $layout_now_index + 1, $layout_now_total );
			?>
			<div class="lnow-carousel__slide" role="group" aria-roledescription="<?php esc_attr_e( 'slide', 'layout-now' ); ?>" aria-label="<?php echo esc_attr( $layout_now_slide_label ); ?>">
				<?php
				$view->card(
					$layout_now_item,
					array(
						'size'  => 'medium_large',
						'sizes' => '(max-width: 600px) 80vw, 420px',
					)
				);
				?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php if ( 'none' !== $layout_now_nav ) : ?>
		<div class="lnow-carousel__controls">
			<?php if ( 'arrows' !== $layout_now_nav ) : ?>
				<div class="lnow-carousel__dots">
					<?php for ( $layout_now_i = 1; $layout_now_i <= $layout_now_total; $layout_now_i++ ) : ?>
						<?php /* translators: %d: slide number. */ ?>
						<button type="button" class="lnow-carousel__dot" aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'layout-now' ), $layout_now_i ) ); ?>"></button>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
			<?php if ( 'dots' !== $layout_now_nav ) : ?>
				<div class="lnow-carousel__arrows">
					<button type="button" class="lnow-control lnow-carousel__arrow" data-lnow-prev aria-label="<?php esc_attr_e( 'Previous', 'layout-now' ); ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M15 18l-6-6 6-6"/></svg>
					</button>
					<button type="button" class="lnow-control lnow-carousel__arrow" data-lnow-next aria-label="<?php esc_attr_e( 'Next', 'layout-now' ); ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 18l6-6-6-6"/></svg>
					</button>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>

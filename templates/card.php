<?php
/**
 * Card template, shared by every layout.
 *
 * The title link is stretched over the whole card, so the card is one tab stop and one link
 * for screen readers; the "Read more" label is decorative.
 *
 * @package LayoutNow
 *
 * @var \LayoutNow\Sources\Item $item
 * @var array                   $args
 * @var \LayoutNow\Render\View  $view
 */

defined( 'ABSPATH' ) || exit;

$layout_now_term    = $view->show( 'badge' ) ? $item->primary_term() : null;
$layout_now_excerpt = $view->show( 'excerpt' ) ? $view->excerpt( $item ) : '';
$layout_now_meta    = $view->show( 'date' ) && $item->timestamp || $view->show( 'author' ) && '' !== $item->author;
?>
<article class="lnow-card <?php echo esc_attr( $args['class'] ); ?>">
	<?php if ( $view->show( 'image' ) ) : ?>
		<div class="lnow-card__media">
			<?php if ( $item->has_image() ) : ?>
				<?php echo $view->image( $item, $args['size'], $args['sizes'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built by wp_get_attachment_image() or escaped in View::image(). ?>
			<?php else : ?>
				<span class="lnow-card__placeholder" aria-hidden="true"><?php echo esc_html( $view->initial( $item ) ); ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="lnow-card__body">
		<?php if ( $layout_now_term ) : ?>
			<span class="lnow-card__badge"><?php echo esc_html( $layout_now_term['name'] ); ?></span>
		<?php endif; ?>
		<<?php echo tag_escape( $view->heading_tag() ); ?> class="lnow-card__title"><a href="<?php echo esc_url( $item->url ); ?>"><?php echo esc_html( $item->title ); ?></a></<?php echo tag_escape( $view->heading_tag() ); ?>>
		<?php if ( '' !== $layout_now_excerpt ) : ?>
			<p class="lnow-card__excerpt"><?php echo esc_html( $layout_now_excerpt ); ?></p>
		<?php endif; ?>
		<?php if ( $layout_now_meta || $view->show( 'more' ) ) : ?>
			<div class="lnow-card__foot">
				<?php if ( $layout_now_meta ) : ?>
					<div class="lnow-card__meta">
						<?php if ( $view->show( 'date' ) && $item->timestamp ) : ?>
							<time datetime="<?php echo esc_attr( wp_date( DATE_W3C, $item->timestamp ) ); ?>"><?php echo esc_html( wp_date( get_option( 'date_format' ), $item->timestamp ) ); ?></time>
						<?php endif; ?>
						<?php if ( $view->show( 'author' ) && '' !== $item->author ) : ?>
							<span><?php echo esc_html( $item->author ); ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ( $view->show( 'more' ) ) : ?>
					<span class="lnow-card__more" aria-hidden="true"><?php esc_html_e( 'Read more', 'layout-now' ); ?><span class="lnow-card__arrow">→</span></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</article>

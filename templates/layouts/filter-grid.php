<?php
/**
 * Instant-filter grid template.
 *
 * @package LayoutNow
 *
 * @var \LayoutNow\Sources\Item[] $items
 * @var array                     $data  { terms: array[], item_ids: string[][] } Pills, and term IDs per item index.
 * @var \LayoutNow\Render\View    $view
 */

defined( 'ABSPATH' ) || exit;

$layout_now_show_all = (bool) $view->option( 'show_all' );
$layout_now_counts   = (bool) $view->option( 'show_counts' );
// Without an "All" pill the first term starts selected.
$layout_now_initial = $layout_now_show_all || ! $data['terms'] ? '*' : $data['terms'][0]['id'];
?>
<?php /* translators: %d: number of posts currently shown. */ ?>
<div class="lnow-filter" data-size="<?php echo esc_attr( $view->option( 'size' ) ); ?>" data-pills="<?php echo esc_attr( $view->option( 'pill_style' ) ); ?>" data-status="<?php echo esc_attr__( 'Posts shown: %d', 'layout-now' ); ?>">
	<?php if ( $data['terms'] ) : ?>
		<div class="lnow-filter__pills" role="group" aria-label="<?php esc_attr_e( 'Filter posts', 'layout-now' ); ?>">
			<?php if ( $layout_now_show_all ) : ?>
				<button type="button" class="lnow-filter__pill" data-term="*" aria-pressed="true">
					<?php esc_html_e( 'All', 'layout-now' ); ?>
					<?php if ( $layout_now_counts ) : ?>
						<span class="lnow-filter__count"><?php echo esc_html( number_format_i18n( count( $items ) ) ); ?></span>
					<?php endif; ?>
				</button>
			<?php endif; ?>
			<?php foreach ( $data['terms'] as $layout_now_term ) : ?>
				<button type="button" class="lnow-filter__pill" data-term="<?php echo esc_attr( $layout_now_term['id'] ); ?>" aria-pressed="<?php echo $layout_now_initial === $layout_now_term['id'] ? 'true' : 'false'; ?>">
					<?php echo esc_html( $layout_now_term['name'] ); ?>
					<?php if ( $layout_now_counts ) : ?>
						<span class="lnow-filter__count"><?php echo esc_html( number_format_i18n( $layout_now_term['count'] ) ); ?></span>
					<?php endif; ?>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<div class="lnow-filter__grid">
		<?php foreach ( $items as $layout_now_index => $layout_now_item ) : ?>
			<?php $layout_now_ids = $data['item_ids'][ $layout_now_index ]; ?>
			<div class="lnow-filter__item" data-terms="<?php echo esc_attr( implode( ' ', $layout_now_ids ) ); ?>"<?php echo '*' !== $layout_now_initial && ! in_array( $layout_now_initial, $layout_now_ids, true ) ? ' hidden' : ''; ?>>
				<?php
				$view->card(
					$layout_now_item,
					array(
						'size'  => 'medium_large',
						'sizes' => '(max-width: 600px) 100vw, 420px',
					)
				);
				?>
			</div>
		<?php endforeach; ?>
	</div>
	<p class="lnow-sr" aria-live="polite"></p>
</div>

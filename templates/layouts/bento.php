<?php
/**
 * Bento grid template.
 *
 * @package LayoutNow
 *
 * @var \LayoutNow\Sources\Item[] $items
 * @var array                     $data  { pattern: string, tiles: string[] } Effective pattern and tile size per item index.
 * @var \LayoutNow\Render\View    $view
 */

defined( 'ABSPATH' ) || exit;

$layout_now_sizes = array(
	'full'     => array( 'full', '(max-width: 1400px) 100vw, 1400px' ),
	'hero'     => array( 'large', '(max-width: 600px) 100vw, 720px' ),
	'hero-alt' => array( 'large', '(max-width: 600px) 100vw, 720px' ),
	'wide'     => array( 'large', '(max-width: 600px) 100vw, 720px' ),
	'sm'       => array( 'medium_large', '(max-width: 480px) 100vw, 360px' ),
);
?>
<div class="lnow-bento" data-pattern="<?php echo esc_attr( $data['pattern'] ); ?>" data-height="<?php echo esc_attr( $view->option( 'height' ) ); ?>">
	<?php
	foreach ( $items as $layout_now_index => $layout_now_item ) {
		$layout_now_tile = $data['tiles'][ $layout_now_index ];
		$view->card(
			$layout_now_item,
			array(
				'class' => 'lnow-tile lnow-tile--' . $layout_now_tile,
				'size'  => $layout_now_sizes[ $layout_now_tile ][0],
				'sizes' => $layout_now_sizes[ $layout_now_tile ][1],
			)
		);
	}
	?>
</div>

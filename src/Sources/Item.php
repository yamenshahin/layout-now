<?php
/**
 * Normalized content item shared by every source and layout.
 *
 * @package LayoutNow
 */

namespace LayoutNow\Sources;

defined( 'ABSPATH' ) || exit;

/**
 * Plain value object. Text fields are raw (unescaped); templates escape on output.
 */
final class Item {

	/**
	 * Source-specific ID.
	 *
	 * @var int|string
	 */
	public $id = 0;

	/**
	 * Title.
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Permalink.
	 *
	 * @var string
	 */
	public $url = '';

	/**
	 * Plain-text excerpt (untrimmed; layouts trim to the configured length).
	 *
	 * @var string
	 */
	public $excerpt = '';

	/**
	 * Attachment ID of the image, 0 if none or external.
	 *
	 * @var int
	 */
	public $image_id = 0;

	/**
	 * External image URL, used when there is no attachment.
	 *
	 * @var string
	 */
	public $image_url = '';

	/**
	 * Alt text for external images.
	 *
	 * @var string
	 */
	public $image_alt = '';

	/**
	 * Publish time as a Unix timestamp, 0 if unknown.
	 *
	 * @var int
	 */
	public $timestamp = 0;

	/**
	 * Author display name.
	 *
	 * @var string
	 */
	public $author = '';

	/**
	 * Terms as arrays with id, name, slug and taxonomy keys. The first entry is the primary term.
	 *
	 * @var array[]
	 */
	public $terms = array();

	/**
	 * Constructor.
	 *
	 * @param array $data Property values.
	 */
	public function __construct( array $data = array() ) {
		$this->id        = isset( $data['id'] ) ? $data['id'] : 0;
		$this->title     = isset( $data['title'] ) ? (string) $data['title'] : '';
		$this->url       = isset( $data['url'] ) ? (string) $data['url'] : '';
		$this->excerpt   = isset( $data['excerpt'] ) ? (string) $data['excerpt'] : '';
		$this->image_id  = isset( $data['image_id'] ) ? (int) $data['image_id'] : 0;
		$this->image_url = isset( $data['image_url'] ) ? (string) $data['image_url'] : '';
		$this->image_alt = isset( $data['image_alt'] ) ? (string) $data['image_alt'] : '';
		$this->timestamp = isset( $data['timestamp'] ) ? (int) $data['timestamp'] : 0;
		$this->author    = isset( $data['author'] ) ? (string) $data['author'] : '';
		$this->terms     = isset( $data['terms'] ) && is_array( $data['terms'] ) ? array_values( $data['terms'] ) : array();
	}

	/**
	 * Rebuilds an item from cached data.
	 *
	 * @param array $data Data from to_array().
	 * @return Item
	 */
	public static function from_array( array $data ) {
		return new self( $data );
	}

	/**
	 * Serializable representation.
	 *
	 * @return array
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * Whether the item has any image.
	 *
	 * @return bool
	 */
	public function has_image() {
		return $this->image_id > 0 || '' !== $this->image_url;
	}

	/**
	 * Primary term, used for badges.
	 *
	 * @return array|null
	 */
	public function primary_term() {
		return isset( $this->terms[0] ) ? $this->terms[0] : null;
	}
}

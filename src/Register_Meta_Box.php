<?php

class WPSL_Register_Meta_Box extends WPSL\MetaBox\Abstract_Meta_Box {

	const INPUT_NAME = 'wpslcf';

	private $public_fields;

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10 );
		add_action( 'save_post', array( $this, 'save_post_meta' ), 10 );
	}

	/**
	 * Save post meta
	 *
	 * @param int $post_id
	 */
	public function save_post_meta( $post_id ) {
		// Check meta nonce
		if ( !isset( $_POST['wpslcf_nonce'] ) OR !wp_verify_nonce( $_POST['wpslcf_nonce'], 'save_postmeta' ) ) {
			return $post_id;
		}

		if( isset( $_POST['wpslcf'] ) ) {
			foreach( $_POST['wpslcf'] as $key => $value ) {
				update_post_meta( $post_id, sanitize_key( $key ), $value );
			}
		}
	}

	public function add_meta_boxes() {
		global $post, $current_screen;

		if( !isset( $current_screen->post_type ) ) return;
		$post_type = $current_screen->post_type;
		$meta_boxes = apply_filters( 'wpsl_meta_boxes', array() );

		foreach( $meta_boxes as $meta_box ) {
			$meta_post_type = (array) $meta_box['post_types'];

			if( isset( $meta_box['public'] ) )
				$this->public_fields[ $meta_box['post_types'] ] = $meta_box['fields'];

			if( in_array( $post_type, $meta_post_type ) ) {
				$this->register_meta_box( $meta_box, $post_type );
			}
		}

	}

	public function register_meta_box( $args, $post_type ) {
		global $post;
		$meta_box_id = isset( $args['id'] ) ? $args['id'] : sanitize_key( $args['title'] );
		$title = isset( $args['title'] ) ? $args['title'] : __('Meta', 'wpsl-crm' );
		$context = isset( $args['context'] ) ? $args['context'] : 'normal';
		$priority = isset( $args['priority'] ) ? $args['priority'] : 'high';
		$fields = isset( $args['fields'] ) ? $args['fields'] : array();
		$columns = isset( $args['columns'] ) ? $args['columns'] : 2;

		add_meta_box(
			$meta_box_id,
			$title,
			array( $this, 'callback' ),
			$post_type,
			$context,
			$priority,
			array( 'fields' => $fields, 'columns' => $columns )
		);
	}

	/**
	 * @param object $post Post object
	 * @param array  $data Data
	 */
	public function callback( $post, $data ) {
		$fields = isset( $data['args']['fields'] ) ? $data['args']['fields'] : array();
		wp_nonce_field( 'save_postmeta', 'wpslcf_nonce' );
		$output = '<div class="wp-clearfix cols-'. $data['args']['columns'] .'">';
		foreach( $fields as $input ) {
			// Typ pola to nazwa metody
			$field = new WPSL\MetaBox\Form_Inputs( $input, $post->ID );
			// Sprawdzam czy istnieje metoda w obiekcie, jeśli tak wywołuje ją
			if( method_exists( $field, $input['type'] ) ) {
				if( $input['type'] == 'custom_html' ) {
					$output .= call_user_func( array( $field, $input['type'] ), $post->ID, $this->public_fields );
				} else {
					$output .= $field->before();
					$output .= call_user_func( array( $field, $input['type'] ), $post->ID );
					$output .= $field->description();
					$output .= $field->after();
				}

			}
		}
		$output .= '</div>';
		echo $output;
	}

	private function public_fields() {

	}
}

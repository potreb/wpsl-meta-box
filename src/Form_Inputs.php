<?php

namespace WPSL\MetaBox;

class Form_Inputs {

	protected $args;

	public function defaults() {
		return array(
			'id'        => '',
			'name'      => null,
			'descr'     => null,
			'std'       => '',
			'type'      => 'text',
			'required'  => null,
			'clone'     => null,
			'private'   => null,
			'callback'  => null,
			'class'     => '',
			'sanitize_callback' => '',
			'options'   => array(),
		);
	}

	public function __construct( $args, $post_id ) {

		$this->args = wp_parse_args( $args, $this->defaults() );

		if( is_array( $this->args['class'] ) ) {
			$this->args['class'] = implode( ' ', $this->args['class'] );
		}

		// Define input name to proper way to save data
		$this->iname = 'name="wpslcf['. $this->args['id'] .']"';
		if( $this->args['type'] == 'checkbox' ) {
			$this->iname = 'name="wpslcf['. $this->args['id'] .'][]"';
		}

		$this->iname_id = 'name="wpslcf['. $this->args['id'] .'_id]"';

		// Change name of field is cloned
		$this->clone = $this->args['clone'];
		if( $this->args['clone'] ) {
			$this->iname = 'name="wpslcf['. $this->args['id'] .'][]"';
		}

		// Define atribute required
		$this->required = $this->args['required'] ? 'required="required"' : '';

		// Get option value
		$this->meta_value_id = get_post_meta( $post_id, $this->args['id'] .'_id', true );
		$value = get_post_meta( $post_id, $this->args['id'], true );
		if( !$value ) {
			$value = $this->args['std'];
		}

		if( $this->args['type'] == 'editor' ) {
			$this->value = $value;
		} else {
			if( is_array( $value ) ) {
				$this->value = array_map( 'sanitize_text_field', $value );
			} else {
				$this->value = sanitize_text_field( $value );
			}
		}

	}


	public function before() {
		$asteriks = '';
		if( $this->required ) {
			$asteriks = " *";
		}
		$output = '<div class="wpslcrm-field">' . PHP_EOL;
		if( $this->args['name'] ) {
			$output .= '<div class="wpslcrm-label"><label>'. $this->args['name'] . $asteriks .'</label></div>'. PHP_EOL;
		}
		$output .= '<div class="wpslcrm-input" '. $this->args['class'] .'>'. PHP_EOL;
		return $output;
	}

	public function after() {
		$output = PHP_EOL . '</div></div>'. PHP_EOL;
		return $output;
	}

	public function description() {
		if( !empty( $this->args['descr'] ) ) {
			return '<p class="description">'. $this->args['descr'] .'</p>';
		}
	}

	public function text() {
		return '<input class="wpslt-input-text" type="text" value="'. $this->value .'" '. $this->required .' '. $this->iname .' />';
	}

	public function textarea() {
		return '<textarea class="wpslt-input-textarea" '. $this->iname .' rows="4">'. $this->value .'</textarea>';
	}

	public function image_select() {
		$output = '';

		foreach( $this->args['options'] as $id => $img_src ) {
			$checked = checked( $this->value, $id, false );
			$class = ( $id == $this->value ) ? 'image-selected' : '';
			$output .= '<label class="image-select '. $class .'">';
			$output .= '<input '. $this->iname .' type="radio" '. $checked .' value="'. $id .'" style="display:none;">';
			$output .= '<span><img src="'. $img_src .'" alt="" width="100" /></span>';
			$output .= '</label>';
		}
		return $output;
	}

	public function select() {
		$output = '<select class="wpslt-input-select" '. $this->required .' '. $this->iname .' />';
		$output .= '<option value="">---</option>';
		foreach( $this->args['options'] as $option_value => $option_label ) {
			$selected = selected( (string) $this->value, $option_value, false );
			$output .= '<option '. $selected .' value="'. $option_value .'">'. $option_label .'</option>';
		}
		$output .= '</select>';
		return $output;
	}

	public function radio() {
		$output = '';
		foreach( $this->args['options'] as $option_value => $option_label ) {
			$output .= '<div><label>';
			$checked = wpslh::checked( (string) $option_value, $this->value );
			$output .= '<input type="radio" value="'. $option_value .'" '. $checked .' '. $this->required .' '. $this->iname .' />';
			$output .= $option_label .'</label></div>';
		}
		return $output;
	}

	public function checkbox() {
		$output = '';
		foreach( $this->args['options'] as $option_value => $option_label ) {
			$output .= '<div><label>';
			$checked = wpslh::checked( (string) $option_value, $this->value );
			$output .= '<input type="checkbox" value="'. $option_value .'" '. $checked .' '. $this->required .' '. $this->iname .' />';
			$output .= $option_label .'</label></div>';
		}
		return $output;
	}

	public function date() {
		return '<input class="wpslt-input-date" type="date" value="'. $this->value .'" '. $this->iname .' '. $this->required .'/>';
	}

	public function tel() {
		return '<input class="wpslt-input-tel" type="tel" value="'. $this->value .'" '. $this->iname .' '. $this->required .'/>';
	}

	public function url() {
		return '<input class="wpslt-input-url" type="url" value="'. $this->value .'" '. $this->iname .' '. $this->required .'/>';
	}

	public function number() {
		return '<input class="wpslt-input-number" type="number" value="'. $this->value .'" '. $this->iname .' '. $this->required .'/>';
	}

	public function email() {
		return '<input class="wpslt-input-email" type="email" value="'. $this->value .'" '. $this->iname .' '. $this->required .'/>';
	}

	public function editor() {
		ob_start();
		wp_editor(
			$this->value,
			$this->args['id'],
			array(
				'textarea_name' => 'wpslcf['. $this->args['id'] .']',
				'teeny' => true,
				/*'tinymce' => array(
					'toolbar1' => 'bold, italic, underline',
					'toolbar2' => false
				)*/
			)
		);
		$editor = ob_get_contents();
		ob_end_clean();
		return $editor;
	}

	public function post( $post_id ) {
		// Define field type and post type (only for post type)
		$this->field_type = isset( $this->args['field_type'] ) ? $this->args['field_type'] : 'select_advanced';
		$this->post_type = isset( $this->args['post_type'] ) ? $this->args['post_type'] : null;

		$multiple = '';
		if( $this->clone ) {
			$multiple = 'multiple="multiple"';
		}

		if( $this->field_type == 'select_advanced' ) {
			$output = '<select '. $multiple .' class="wpslcrm-change" '. $this->required .' '. $this->iname .'>';
			$output .= '<option value="">---</option>';

			$args = array(
				'posts_per_page'    => -1,
				'post_type'         => $this->post_type,
				'post_status'       => 'publish',
				'suppress_filters'  => true,
				'order'             => 'ASC',
				'orderby'           => 'title'
			);
			$posts_array = get_posts( $args );
			foreach( $posts_array as $post ) {

				if( !is_array( $this->value ) ) {
					$selected = selected( $this->value, $post->ID, false );
				} else {
					$selected = ( in_array( $post->ID, $this->value ) ? 'selected="selected"' : '' );
				}

				$output .= '<option '. $selected .' value="'. $post->ID .'">'. $post->post_title .'</option>';
			}
			unset( $posts_array, $post );
			$output .= '</select><input value="'. $this->meta_value_id .'" type="hidden" '. $this->iname_id .' />';
			return $output;
		}
	}

	public function user() {
		$blogusers = get_users(
			array(
				'roles' => array ( 'administrator', 'crm_user' ),
				'fields' => array( 'ID', 'display_name' )
			)
		);

		$output = '<select class="wpslcrm-change" '. $this->required .' '. $this->iname .' />';
		$output .= '<option value="">---</option>';
		foreach ( $blogusers as $user ) {
			$selected = selected( $this->value, $user->ID, false );
			$output .= '<option '. $selected .' value="'. $user->ID .'">'. $user->display_name .'</option>';
		}
		$output .= '</select>';
		return $output;
	}

	public function custom_html( $post_id, $fields ) {
		if( $this->args['callback'] ) {
			if( is_callable( $this->args['callback'] ) )
				return call_user_func( array( $this->args['callback'][0], $this->args['callback'][1] ), $post_id, $fields );
		}

	}



}
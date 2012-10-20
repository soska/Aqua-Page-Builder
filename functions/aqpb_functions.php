<?php
/**
 * Aqua Page Builder functions
 *
 * This holds the external functions which can be used by the theme
 *
 * Requires the AQ_Page_Builder class
**/

if(class_exists('AQ_Page_Builder')) {
	
	/** 
	 * Core functions
	*******************/
	 
	/* Register a block */
	function aq_register_block($block_class) {
		global $aq_registered_blocks;
		$aq_registered_blocks[strtolower($block_class)] = new $block_class;
	}
	
	/** Un-register a block **/
	function aq_unregister_block($block_class) {
		global $aq_registered_blocks;
		foreach($aq_registered_blocks as $block) {
			if($block->id_base == $block_class) unset($aq_registered_blocks[$block_class]);
		}
	}
	
	/** Get list of all blocks **/
	function aq_get_blocks($template_id) {
		global $aq_page_builder;
		$blocks = $aq_page_builder->get_blocks($template_id);
		
		return $blocks;
	}
	
	/** Display the template (for front-end usage only) **/
	function aq_display_template( $atts, $content = null) {
		
		global $aq_page_builder;
		$defaults = array('id' => 0);
		extract( shortcode_atts( $defaults, $atts ) );
		
		//capture template output into string
		ob_start();
			$aq_page_builder->display_template($id);
			$template = ob_get_contents();
		ob_end_clean();
		
		$template = $template ? $template : '';
		return $template;
		
	}
		//add the [template] shortcode
		global $shortcode_tags;
		if ( !array_key_exists( 'template', $shortcode_tags ) ) {
			add_shortcode( 'template', 'aq_display_template' );
		} else {
			add_action('admin_notices', create_function('', "echo '<div id=\"message\" class=\"error\"><p><strong>Aqua Page Builder notice: </strong>'. __('The \"[template]\" shortcode already exists, possibly added by the theme or other plugins. Please consult with the theme author to consult with this issue', 'framework') .'</p></div>';"));
		}
		
	/** 
	 * Form Field Helper functions
	 *
	 * Provides some default fields for use in the blocks
	 *
	 * @todo build this into a separate class instead!
	********************************************************/
	
	/* Input field - Options: $size = min, small, full */
	function aq_field_input($field_id, $block_id, $input, $size = 'full', $type = 'text') {
		$output = '<input type="'.$type.'" id="'. $block_id .'_'.$field_id.'" class="input-'.$size.'" value="'.$input.'" name="aq_blocks['.$block_id.']['.$field_id.']">';
		
		return $output;
	}
	
	/* Textarea field */
	function aq_field_textarea($field_id, $block_id, $text, $size = 'full') {
		$output = '<textarea id="'. $block_id .'_'.$field_id.'" class="textarea-'.$size.'" name="aq_blocks['.$block_id.']['.$field_id.']" rows="5">'.$text.'</textarea>';
		
		return $output;
	}
	
	
	/* Select field */
	function aq_field_select($field_id, $block_id, $options, $selected) {
		$options = is_array($options) ? $options : array();
		$output = '<select id="'. $block_id .'_'.$field_id.'" name="aq_blocks['.$block_id.']['.$field_id.']">';
		foreach($options as $key=>$value) {
			$output .= '<option value="'.$key.'" '.selected( $selected, $key, false ).'>'.htmlspecialchars($value).'</option>';
		}
		$output .= '</select>';
		
		return $output;
	}
	
	/* Multiselect field */
	function aq_field_multiselect($field_id, $block_id, $options, $selected_keys = array()) {
		$output = '<select id="'. $block_id .'_'.$field_id.'" multiple="multiple" class="select of-input" name="aq_blocks['.$block_id.']['.$field_id.'][]">';
		foreach ($options as $key => $option) {
			$selected = '';
			if(is_array($selected_keys) && in_array($key, $selected_keys)) $selected = 'selected="selected"';			
			$output .= '<option id="'. $block_id .'_'.$field_id.'_'. $key .'" value="'.$key.'" '. $selected .' />'.$option.'</option>';
		}
		$output .= '</select>';
		
		return $output;
	}
	
	/* Color picker field */
	function aq_field_color_picker($field_id, $block_id, $color) {
		$output = '<div class="aqpb-color-picker">';
			$output .= '<input type="text" id="'. $block_id .'_'.$field_id.'" class="input-color-picker" value="'. $color .'" name="aq_blocks['.$block_id.']['.$field_id.']" />';
			$output .= '<div class="cw-color-picker" rel="'. $block_id .'_'.$field_id.'"></div>';
		$output .= '</div>';
		
		return $output;
	}
	
	/* Single Checkbox */
	function aq_field_checkbox($field_id, $block_id, $check) {
		$output = '<input type="hidden" name="aq_blocks['.$block_id.']['.$field_id.']" value="0" />';
		$output .= '<input type="checkbox" id="'. $block_id .'_'.$field_id.'" class="input-checkbox" name="aq_blocks['.$block_id.']['.$field_id.']" '. checked( 1, $check, false ) .' value="1"/>';
		
		return $output;
	}
	
	/* Multi Checkbox */
	function aq_field_multicheck($field_id, $block_id, $fields = array()) {
	
	}
	
	/* Media Uploader */
	function aq_field_upload($field_id, $block_id, $media, $media_type = 'img') {
		$output = '<input type="text" id="'. $block_id .'_'.$field_id.'" class="input-full input-upload" value="'.$media.'" name="aq_blocks['.$block_id.']['.$field_id.']">';
		$output .= '<a href="#" class="aq_upload_button button" rel="'.$media_type.'">Upload</a><p></p>';
		
		return $output;
	}
	
	/** 
	 * Misc Helper Functions
	**************************/
	
	/** Get column width
	 * @parameters - $size (column size), $grid (grid size e.g 940), $margin
	 */
	function aq_get_column_width($size, $grid = 940, $margin = 20) {
		
		$columns = range(1,12);
		$widths = array();
		foreach($columns as $column) {
			$width = (( $grid + $margin ) / 12 * $column) - $margin;
			$width = round($width);
			$widths[$column] = $width;
		}
		
		$column_id = absint(preg_replace("/[^0-9]/", '', $size));
		$column_width = $widths[$column_id];
		return $column_width;
	}
	
}
?>
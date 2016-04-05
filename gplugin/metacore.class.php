<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

// FIXME: DEPREATED
class gPluginMetaCore extends gPluginClassCore
{

	// FIXME: MUST DEPREATED
	function admin_field( $field, $fields, $post, $ltr = FALSE )
	{
		self::__dep();

		global $gEditorial;

		if ( in_array( $field, $fields ) && $gEditorial->meta->user_can( 'view', $field )  ) {
			?><p class="">
				<input style="width:99%;" class="geditorial-meta-prompt" type="text" autocomplete="off"
				name="geditorial-meta-<?php echo $field; ?>" id="geditorial-meta-<?php echo $field; ?>" <?php if ( $ltr ) echo 'dir="ltr"'; ?>
				<?php if ( ! $gEditorial->meta->user_can( 'edit', $field ) ) echo 'readonly="readonly"'; ?>
				value="<?php echo esc_attr( $gEditorial->meta->get_postmeta( $post->ID, $field ) ); ?>"
				title="<?php echo esc_attr( $gEditorial->meta->get_string( $field, $post->post_type ) ); ?>"
				placeholder="<?php echo esc_attr( $gEditorial->meta->get_string( $field, $post->post_type ) ); ?>" />
			</p> <?php
		}
	}

	public static function is_active()
	{
		self::__dep();

		global $gEditorial;

		if ( is_object( $gEditorial ) )
			return TRUE;

		return FALSE;

		// TODO : find a sane method!!
	}

	public static function get_the_term( $taxonomy, $b = '', $a = '', $f = FALSE, $args = array() )
	{
		self::__dep();
		
		global $post;

		if ( ! self::is_active() )
			return;

		$args = wp_parse_args( $args, array(
			'id'    => $post->ID,
			'echo'  => TRUE,
			'def'   => '',
			'sep'   => ', ',
			'class' => 'term',
		) );

		$html = '';
		$terms = get_the_terms( $args['id'], $taxonomy );

		if ( $terms && ! is_wp_error( $terms ) ) {

			foreach ( $terms as $term ) {
				$desc = get_term_field( 'description', $term->term_id, $term->taxonomy );
				//$desc = is_wp_error( $desc ) ? '' : wpautop( $desc, FALSE );
				$desc = is_wp_error( $desc ) ? '' : strip_tags( $desc );
				$html .= '<a href="'.get_term_link( $term, $taxonomy ).'" title="'.esc_attr( $desc ).'" class="'.$args['class'].'" >'.$term->name.'</a>'.$args['sep'];
			}
			if ( ! empty( $html ) ) {
				if ( isset( $args['echo'] ) && $args['echo'] ) {
					echo $b.rtrim( $html, $args['sep'] ).$a;
					return;
				}
				return $b.rtrim( $html, $args['sep'] ).$a;
			}
		}

		if ( isset( $args['def'] ) && $args['def'] ) {
			if ( isset( $args['echo'] ) && $args['echo'] ) {
				echo $args['def'];
				return;
			}
			return $args['def'];
		}
	}
}

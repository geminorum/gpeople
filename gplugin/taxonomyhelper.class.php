<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginTaxonomyHelper' ) ) { class gPluginTaxonomyHelper extends gPluginClassCore
{

	// Originally from : Custom Field Taxonomies : https://github.com/scribu/wp-custom-field-taxonomies
	public static function getMetaRows( $meta_key, $limit = FALSE, $offset = 0 )
	{
		global $wpdb;

		if ( $limit )
			$query = $wpdb->prepare( "
				SELECT post_id, GROUP_CONCAT( meta_value ) as meta
				FROM $wpdb->postmeta
				WHERE meta_key = %s
				GROUP BY post_id
				LIMIT %d
				OFFSET %d
			", $meta_key, $limit, $offset );
		else
			$query = $wpdb->prepare( "
				SELECT post_id, GROUP_CONCAT( meta_value ) as meta
				FROM $wpdb->postmeta
				WHERE meta_key = %s
				GROUP BY post_id
			", $meta_key );

		return $wpdb->get_results( $query );
	}

	// Originally from : Custom Field Taxonomies : https://github.com/scribu/wp-custom-field-taxonomies
	// here because we used this to convert meta into terms
	public static function getMetaKeys( $table = 'postmeta' )
	{
		global $wpdb;

		$from = $wpdb->{$table};

		return $wpdb->get_col( "
			SELECT meta_key
			FROM $from
			GROUP BY meta_key
			HAVING meta_key NOT LIKE '\_%'
			ORDER BY meta_key ASC
		" );
	}

	// Originally from : Custom Field Taxonomies : https://github.com/scribu/wp-custom-field-taxonomies
	// here because we used this to convert meta into terms
	public static function deleteMetaKeys( $meta_key, $limit = FALSE, $table = 'postmeta' )
	{
		global $wpdb;

		$from = $wpdb->{$table};

		if ( $limit )
			$query = $wpdb->prepare( "DELETE FROM $from WHERE meta_key = %s LIMIT %d", $meta_key, $limit );
		else
			$query = $wpdb->prepare( "DELETE FROM $from WHERE meta_key = %s", $meta_key );

		return $wpdb->query( $query );
	}

	// USED WHEN: admin edit table
	public static function get_admin_terms_edit( $post_id, $post_type, $taxonomy, $glue = ', ', $empty = '&#8212;' )
	{
		$taxonomy_object = get_taxonomy( $taxonomy );

		if ( $terms = get_the_terms( $post_id, $taxonomy ) ) {
			$out = array();
			foreach ( $terms as $t ) {
				$posts_in_term_qv = array();
				if ( 'post' != $post_type )
					$posts_in_term_qv['post_type'] = $post_type;
				if ( $taxonomy_object->query_var ) {
					$posts_in_term_qv[ $taxonomy_object->query_var ] = $t->slug;
				} else {
					$posts_in_term_qv['taxonomy'] = $taxonomy;
					$posts_in_term_qv['term'] = $t->slug;
				}

				$out[] = sprintf( '<a href="%s">%s</a>',
					esc_url( add_query_arg( $posts_in_term_qv, 'edit.php' ) ),
					esc_html( sanitize_term_field( 'name', $t->name, $t->term_id, $taxonomy, 'display' ) )
				);
			}
			return join( $glue, $out );
		} else {
			return $empty;
		}
	}

	public static function update_count_callback( $terms, $taxonomy )
	{
		global $wpdb;
		foreach ( (array) $terms as $term ) {
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term ) );
			do_action( 'edit_term_taxonomy', $term, $taxonomy );
			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
			do_action( 'edited_term_taxonomy', $term, $taxonomy );
		}
	}

	public static function insert_default_terms( $taxonomy, $defaults )
	{
		if ( ! taxonomy_exists( $taxonomy ) )
			return FALSE;

		foreach ( $defaults as $term_slug => $term_name )
			if ( ! term_exists( $term_slug, $taxonomy ) )
				wp_insert_term( $term_name, $taxonomy, array( 'slug' => $term_slug ) );

		return TRUE;
	}

	// FIXME: DEPRECATED
	public static function prepare_terms( $taxonomy, $extra = array(), $terms = NULL, $key = 'term_id', $object = TRUE )
	{
		self::__dep( 'gPluginTaxonomyHelper::prepareTerms()');
		return self::prepareTerms( $taxonomy, $extra, $terms, $key, $object );
	}

	public static function prepareTerms( $taxonomy, $extra = array(), $terms = NULL, $key = 'term_id', $object = TRUE )
	{
		$new_terms = array();

		if ( is_null( $terms ) ) {
			$terms = get_terms( $taxonomy, array_merge( array(
				'hide_empty' => FALSE,
				'orderby'    => 'name',
				'order'      => 'ASC'
			), $extra ) );
		}

		if ( is_wp_error( $terms ) || FALSE === $terms )
			return $new_terms;

		foreach ( $terms as $term ) {

			$new = array(
				'name'        => $term->name,
				'description' => $term->description,
				'excerpt'     => $term->description,
				'link'        => get_term_link( $term, $taxonomy ),
				'count'       => $term->count,
				'parent'      => $term->parent,
				'slug'        => $term->slug,
				'id'          => $term->term_id,
			);

			$new_terms[$term->{$key}] = $object ? (object) $new : $new;
		}

		return $new_terms;
	}

	public static function theTerm( $taxonomy, $post_ID, $object = FALSE )
	{
		$terms = get_the_terms( $post_ID, $taxonomy );

		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( $object ) {
					return $term;
				} else {
					return $term->term_id;
				}
			}
		}

		return '0';
	}

	public static function getTerms( $taxonomy = 'category', $post = FALSE, $object = FALSE, $key = 'term_id', $extra = array() )
	{
		if ( FALSE !== $post )
			$terms = get_the_terms( $post, $taxonomy );

		else
			$terms = get_terms( array_merge( array(
				'taxonomy'               => $taxonomy,
				'hide_empty'             => FALSE,
				'orderby'                => 'name',
				'order'                  => 'ASC',
				'update_term_meta_cache' => FALSE,
			), $extra ) );

		if ( ! $terms || is_wp_error( $terms ) )
			return array();

		$list = wp_list_pluck( $terms, $key );

		return $object ? array_combine( $list, $terms ) : $list;
	}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
/// NOT USED YET ---------------------------------------------------------------

	// https://gist.github.com/danielbachhuber/2922627
	// Check if another blog has a given taxonomy
	/**
	 * Check if another blog on the network has a taxonomy registered
	 * We check by seeing if there's a term in that taxonomy, so this
	 * approach will only work if that's the case.
	 *
	 * I didn't actually test this... you obviously should :)
	 *
	 * @see https://twitter.com/trepmal/status/212766835504984065
	 */
	function dbx_blog_has_taxonomy( $_blog_id, $taxonomy = 'author' )
	{
		// This isn't cached but you'd want to cache it heavily as you
		// don't want to run switch_to_blog() on every pageload
		switch_to_blog( $_blog_id );
		global $wpdb;
		$query = $wpdb->prepare( "SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy=%s LIMIT 1", $taxonomy );
		$result = (bool)$wpdb->get_var( $query );
		restore_current_blog();
		return $result;
	}

	// dep!
	// based on WP get_term_by()
	// returns array of matched terms
	function search_term_by( $field, $value, $taxonomy, $output = OBJECT, $filter = 'raw' )
	{
		global $wpdb;

		if ( ! taxonomy_exists( $taxonomy ) )
			return FALSE;

		if ( 'slug' == $field ) {
			$field = 't.slug';
			$value = sanitize_title( $value );
			if ( empty($value) )
				return FALSE;
		} else if ( 'name' == $field ) {
			// Assume already escaped
			$value = wp_unslash( $value );
			$field = 't.name';
		} else {
			$term = get_term( (int) $value, $taxonomy, $output, $filter );
			if ( is_wp_error( $term ) )
				$term = FALSE;
			return $term;
		}

		$term = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND $field LIKE %%s% LIMIT 1", $taxonomy, $value ) );
		if ( !$term )
			return FALSE;

		wp_cache_add($term->term_id, $term, $taxonomy);

		$term = apply_filters( 'get_term', $term, $taxonomy);
		$term = apply_filters( "get_$taxonomy", $term, $taxonomy);
		$term = sanitize_term( $term, $taxonomy, $filter);

		if ( $output == OBJECT ) {
			return $term;
		} else if ( $output == ARRAY_A ) {
			return get_object_vars($term);
		} else if ( $output == ARRAY_N ) {
			return array_values(get_object_vars($term));
		} else {
			return $term;
		}
	}
} }

<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

/**
* Class for logging events and errors
* Originally based on https://github.com/pippinsplugins/WP-Logging
* http://pippinsplugins.com/wp-logging/
*/

// ALSO SEE : http://wordpress.org/plugins/wordpress-logging-service/

if ( ! class_exists( 'gPluginLoggerCore' ) ) { class gPluginLoggerCore extends gPluginClassCore
{

	public function setup_globals( $constants = array(), $args = array() )
	{
		$this->args = gPluginUtils::recursiveParseArgs( $args, array(
			'domain'      => 'gplugin',
			'title'       => 'gPlugin',
			'logger_args' => array(
				'name'        => 'Logs',
				'post_type'   => 'gplugin_log',
				'taxonomy'    => 'gplugin_log_type',
				'meta_prefix' => '_gplugin_log_',
				'hook_prefix' => 'gplugin_log_',
				'types'       => array( 'error', 'event' ),
			),
		) );

		$this->constants = gPluginUtils::recursiveParseArgs( $constants, array(
			'plugin_dir' => GPLUGIN_DIR,
			'plugin_url' => GPLUGIN_URL,
		) );
	}

	public function setup_action()
	{
		 add_action( 'init', array( $this, 'register' ) );
	}

	public function register()
	{
		register_post_type( $this->args['logger_args']['post_type'],
			apply_filters( $this->args['logger_args']['hook_prefix'].'post_type_args', array(
				'labels'          => array( 'name' => $this->args['logger_args']['name'] ),
				'public'          => FALSE,
				'query_var'       => FALSE,
				'rewrite'         => FALSE,
				'capability_type' => 'post',
				'supports'        => array( 'title', 'editor' ),
				'can_export'      => FALSE,
				// 'show_ui'         => TRUE,
		) ) );

		register_taxonomy( $this->args['logger_args']['taxonomy'], $this->args['logger_args']['post_type'] );

		foreach ( $this->types() as $type )
			if ( ! term_exists( $type, $this->args['logger_args']['taxonomy'] ) )
				wp_insert_term( $type, $this->args['logger_args']['taxonomy'] );

	}

	// Sets up the default log types and allows for new ones to be created
	private function types()
	{
		return apply_filters( $this->args['logger_args']['hook_prefix'].'types', $this->args['logger_args']['types'] );
	}

	// Checks to see if the specified type is in the registered list of types
	private function valid_type( $type )
	{
		return in_array( $type, $this->types() );
	}

	public static function add( $title = '', $message = '', $parent = 0, $type = NULL )
	{
		return $this->insert( array(
			'post_title'   => $title,
			'post_content' => $message,
			'post_parent'  => $parent,
			'log_type'     => $type
		) );
	}

	public static function insert( $log_data = array(), $log_meta = array() )
	{
		$defaults = array(
			'post_type'    => $this->args['logger_args']['post_type'],
			'post_status'  => 'publish',
			'post_parent'  => 0,
			'post_content' => '',
			'log_type'     => FALSE
		);

		$args = wp_parse_args( $log_data, $defaults );
		do_action( $this->args['logger_args']['hook_prefix'].'pre_insert' );
		$log_id = wp_insert_post( $args );

		// set the log type, if any
		if ( $log_data['log_type'] && $this->valid_type( $log_data['log_type'] ) )
			wp_set_object_terms( $log_id, $log_data['log_type'], $this->args['logger_args']['taxonomy'], FALSE );

		// set log meta, if any
		if ( $log_id && ! empty( $log_meta ) )
			foreach ( (array) $log_meta as $key => $meta )
				update_post_meta( $log_id, $this->args['logger_args']['meta_prefix'].sanitize_key( $key ), $meta );

		do_action( $this->args['logger_args']['hook_prefix'].'insert', $log_id );
		return $log_id;

	}

	// Update and existing log item
	public static function update( $log_data = array(), $log_meta = array() )
	{
		do_action( $this->args['logger_args']['hook_prefix'].'pre_update', $log_id );

		$defaults = array(
			'post_type'   => $this->args['logger_args']['post_type'],
			'post_status' => 'publish',
			'post_parent' => 0
		);

		$args = wp_parse_args( $log_data, $defaults );

		// store the log entry
		$log_id = wp_update_post( $args );

		if ( $log_id && ! empty( $log_meta ) )
			foreach ( (array) $log_meta as $key => $meta )
				if ( ! empty( $meta ) )
					update_post_meta( $log_id, $this->args['logger_args']['meta_prefix'].sanitize_key( $key ), $meta );

		do_action( $this->args['logger_args']['hook_prefix'].'update', $log_id );
	}


	// Easily retrieves log items for a particular object ID
	public static function logs( $object_id = 0, $type = NULL, $paged = NULL )
	{
		return $this->connected( array(
			'post_parent' => $object_id,
			'paged'       => $paged,
			'log_type'    => $type,
		) );
	}


	// Retrieve all connected logs
	// Used for retrieving logs related to particular items, such as a specific purchase.
	function connected( $args = array() )
	{
		$defaults = array(
			'post_parent'    => 0,
			'post_type'      => $this->args['logger_args']['post_type'],
			'posts_per_page' => 10,
			'post_status'    => 'publish',
			'paged'          => get_query_var( 'paged' ),
			'log_type'       => FALSE
		);

		$query_args = wp_parse_args( $args, $defaults );

		if ( $query_args['log_type'] && $this->valid_type( $query_args['log_type'] ) ) {
			$query_args['tax_query'] = array( array(
				'taxonomy' => $this->args['logger_args']['taxonomy'],
				'field'    => 'slug',
				'terms'    => $query_args['log_type']
			) );
		}

		$logs = get_posts( $query_args );

		if ( $logs )
			return $logs;
		return FALSE; // no logs found
	}

	// Retrieves number of log entries connected to particular object ID
	function count( $object_id = 0, $type = NULL, $meta_query = NULL )
	{
		$query_args = array(
			'post_parent'    => $object_id,
			'post_type'      => $this->args['logger_args']['post_type'],
			'posts_per_page' => -1,
			'post_status'    => 'publish'
		);

		if ( ! empty( $type ) && $this->valid_type( $type ) ) {
			$query_args['tax_query'] = array( array(
				'taxonomy' => $this->args['logger_args']['taxonomy'],
				'field'    => 'slug',
				'terms'    => $type
			) );
		}

		if ( ! empty( $meta_query ) )
			$query_args['meta_query'] = $meta_query;

		$logs = new WP_Query( $query_args );
		return (int) $logs->post_count;
	}

} }

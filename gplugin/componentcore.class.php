<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

// Base class for WordPress plugins that automagically hooks things for you.
// https://gist.github.com/chrisguitarguy/3845798

if ( ! class_exists( 'gPluginComponentCore' ) ) { class gPluginComponentCore extends gPluginClassCore
{

	protected $priority_init           = 10;
	protected $priority_plugins_loaded = 10;
	protected $priority_admin_init     = 10;

	public function setup_globals( $constants = array(), $args = array() )
	{
		$this->args = gPluginUtils::recursiveParseArgs( $args, array(
			'title'        => 'gPlugin',
			'domain'       => 'gplugin',
			'network'      => FALSE,
			'component'    => 'default',
			'options'      => array(),
			'option_group' => FALSE,
		) );

		// FIXME: DROP THIS
		if ( isset( $this->_plugins_loaded ) ) {
			self::__dep( 'var $_plugins_loaded' );
			$this->priority_plugins_loaded = $this->_plugins_loaded;
		}

		// FIXME: DROP THIS
		if ( isset( $this->_init_priority ) ) {
			self::__dep( 'var $_init_priority' );
			$this->priority_init = $this->_init_priority;
		}

		// FIXME: DROP THIS
		if ( isset( $this->_admin_init_priority ) ) {
			self::__dep( 'var $_admin_init_priority' );
			$this->priority_admin_init = $this->_admin_init_priority;
		}

		if ( FALSE === $this->args['option_group'] )
			$this->inject( 'args', array( 'option_group' => $this->args['domain'] ) );

		$this->current_blog = get_current_blog_id();

		$constants = apply_filters( $this->args['domain'].'_constants', $constants );

		$this->constants = gPluginUtils::recursiveParseArgs( $constants, array(
			'plugin_dir'          => GPLUGIN_DIR,
			'plugin_url'          => GPLUGIN_URL,
			'class_filters'       => 'gPluginFiltersCore',
			'meta_key'            => '_'.$this->args['domain'],
			'theme_templates_dir' => 'gplugin_templates',
		) );

		$this->options = $this->init_options();

		$this->setup_settings();

		// bail if the plugin is in network mode
		if ( $this->args['network'] )
			return;

		if ( isset( $this->constants['class_filters'] ) )
			gPluginFactory::get( $this->constants['class_filters'], $constants, $args );
	}

	public function setup_settings()
	{
		if ( isset( $this->constants['class_'.$this->args['component'].'_settings'] ) )
			$this->settings = gPluginFactory::get(
				$this->constants['class_'.$this->args['component'].'_settings'],
				$this->constants,
				$this->getFilters( $this->args['component'].'_settings_args' )
			);
	}

	public function setup_actions()
	{
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), $this->priority_plugins_loaded );
		add_action( 'init', array( $this, 'init' ), $this->priority_init );
		add_action( 'admin_init', array( $this, 'admin_init' ), $this->priority_admin_init );
	}

	public function init()
	{
		// bail if the plugin is in network mode
		if ( $this->args['network'] )
			return;

		// gPlugin Local:
		// if ( ! is_textdomain_loaded( GPLUGIN_TEXTDOMAIN ) )
		// 	load_plugin_textdomain( GPLUGIN_TEXTDOMAIN, FALSE, 'gplugin/languages' );

		// Parent Plugin Local:
		if ( ! is_textdomain_loaded( $this->args['domain'] ) )
			$this->load_textdomain();

		// init here to help filtering the templates
		if ( isset( $this->constants['class_mustache'] ) )
			call_user_func( array( $this->constants['class_mustache'], 'init' ) );
	}

	public function plugins_loaded() {}
	public function admin_init() {}
	public function load_textdomain() {}

	public function is_admin() { return gPluginWPHelper::is_admin(); } // for ajax calls
	public function getConstants() { return $this->constants; }
	public function getArgs() { return $this->args; }
	public function getOptions() { return $this->options; }

	// UNFINISHED!
	public function textdomain()
	{
		// try to get locale
		$locale = apply_filters( 'bp_invitation_load_textdomain_get_locale', get_locale() );

		// if we found a locale, try to load .mo file
		if ( !empty( $locale ) ) {
			// default .mo file path
			$mofile_default = sprintf( '%s/languages/%s-%s.mo', $this->plugin_dir, 'bp-invitation', $locale );
			// final filtered file path
			$mofile = apply_filters( 'bp_invitation_textdomain_mofile', $mofile_default );
			// make sure file exists, and load it
			if ( file_exists( $mofile ) ) {
				load_textdomain( 'bp-invitation', $mofile );
			}
		}
	}

	public function getFilters( $context, $fallback = array() )
	{
		if ( isset( $this->constants['class_filters'] )
			&& class_exists( $this->constants['class_filters'] ) ) {
				$filtred = gPluginFactory::get( $this->constants['class_filters'] );
				return $filtred->get( $context, $fallback );
		}
		return $fallback;
	}

	// CAUTION : must not use for the first time calling the class
	public function getOption( $name, $default = FALSE )
	{
		//$class= get_class();
		//$the_class = $class::getInstance();
		//$options = $the_class::getOptions();
		//return ( isset( $this->options[$name] ) ? $this->options[$name] : $default ) ;

		if ( isset( $this->options[$name] ) )
			return $this->options[$name];
		return $default;
	}

	public function get_option( $name, $default = FALSE )
	{
		return ( isset( $this->options[$name] ) ? $this->options[$name] : $default ) ;
	}

	public function update_option( $name, $value )
	{
		$this->options[$name] = $value;
		$options = get_option( $this->args['option_group'], FALSE );
		if ( $options === FALSE ) $options = array();
		$options[$name] = $value;
		return update_option( $this->args['option_group'], $options );
	}

	public function delete_option( $name )
	{
		$options = get_option( $this->args['option_group'] );
		if ( $options === FALSE ) $options = array();
		unset( $this->options[$name] );
		unset( $options[$name] );
		return update_option( $this->args['option_group'], $options );
	}

	public function init_options()
	{
		$defaults = $this->getFilters( $this->args['option_group'].'_defaults' );
		//gPeopleComponentCore::dump($defaults); die();

		$options = get_option( $this->args['option_group'], FALSE );
		if ( $options === FALSE ) {
			$options = $defaults;
		} else {
			foreach ( $defaults as $key => $value )
				if ( ! isset( $options[$key] ) )
					$options[$key] = $value;
		}
		return $options;
	}

	public function get_postmeta( $post_id, $field = FALSE, $default = '', $key = NULL )
	{
		return $this->get_meta( 'post', $post_id, $field, $default, $key );
	}

	public function get_termmeta( $term_id, $field = FALSE, $default = '', $key = NULL )
	{
		return $this->get_meta( 'term', $term_id, $field, $default, $key );
	}

	public function sanitize_meta_key( $key, $from = 'post' )
	{
		if ( is_null( $key ) ) {

			if ( isset( $this->constants[$from.'_meta_key'] ) )
				$key = $this->constants[$from.'_meta_key'];

			else if ( isset( $this->constants[$this->args['component'].'_meta_key'] ) )
				$key = $this->constants[$this->args['component'].'_meta_key'];

			else
				$key = $this->constants['meta_key'];
		}

		return $key;
	}

	public function get_meta( $from, $id, $field = FALSE, $default = '', $key = NULL )
	{
		$key = $this->sanitize_meta_key( $key, $from );

		switch ( $from ) {
			case 'user' :

				$meta = get_user_meta( $id, $key, FALSE );

			break;
			case 'term' :

				// TODO: DROP: gPluginTermMeta
				if ( function_exists( 'get_term_meta' ) )
					$meta = get_term_meta( $id, $key, TRUE );
				else
					$meta = gPluginTermMeta::get_term_meta( $id, $key, TRUE );

			break;
			case 'post' :
			default :

				$meta = get_metadata( 'post', $id, $key, TRUE );
		}

		if ( empty( $meta ) )
			return $default;

		if ( FALSE === $field )
			return $meta;

		if ( isset( $meta[$field] ) )
			return $meta[$field];

		return $default;
	}

	public function update_postmeta( $post_id, $value, $field = FALSE, $key = NULL )
	{
		return $this->update_meta( 'post', $post_id, $value, $field, $key );
	}

	public function update_meta( $to, $id, $value, $field = FALSE, $key = NULL )
	{
		$key = $this->sanitize_meta_key( $key, $to );

		if ( FALSE === $field ) {
			$meta = $value;
		} else {
			$meta = $this->get_meta( $to, $id, FALSE, array(), $key );
			$meta[$field] = $value;
		}

		switch ( $to ) {
			case 'user' :

				if ( FALSE === $value || ( is_array( $value ) && ! count( $value ) ) )
					delete_user_meta( $id, $key );
				else
					update_user_meta( $id, $key, $meta );

			break;
			case 'term' :

				// TODO: DROP: gPluginTermMeta
				if ( FALSE === $value || ( is_array( $value ) && ! count( $value ) ) ) {
					if ( function_exists( 'delete_term_meta' ) )
						delete_term_meta( $id, $key );
					else
						gPluginTermMeta::delete_term_meta( $id, $key );
				} else {
					if ( function_exists( 'update_term_meta' ) )
						update_term_meta( $id, $key, $meta );
					else
						gPluginTermMeta::update_term_meta( $id, $key, $meta );
				}

			break;
			case 'post' :
			default :

				if ( FALSE === $value || ( is_array( $value ) && ! count( $value ) ) )
					delete_post_meta( $id, $key );
				else
					update_post_meta( $id, $key, $meta );
		}

		wp_cache_flush();
		return $id;
	}

	public function get_template_part( $slug, $name = NULL, $load = TRUE )
	{
		do_action( 'get_template_part_'.$slug, $slug, $name ); // standard WP action

		$templates = array();
		if ( isset( $name ) )
			$templates[] = $slug.'-'.$name.'.php';
		$templates[] = $slug.'.php';

		$templates = apply_filters( $this->args['domain'].'_get_template_part', $templates, $slug, $name );
		return $this->locate_template( $templates, $load, FALSE );
	}

	// TODO : add our own load_template()
	public function locate_template( $template_names, $load = FALSE, $require_once = TRUE )
	{
		$located = FALSE;
		$dir = '/'.$this->constants['theme_templates_dir'].'/';

		foreach ( (array) $template_names as $template_name ) {

			if ( empty( $template_name ) )
				continue;

			$name = gPluginUtils::untrail( $template_name );

			if ( file_exists( get_stylesheet_directory().$dir.$name ) )
				$located = get_stylesheet_directory().$dir.$name;

			else if ( file_exists( get_template_directory().$dir.$name ) )
				$located = get_template_directory().$dir.$name;

			else if ( file_exists( $this->constants['plugin_dir'].'/templates/'.$name ) )
				$located = $this->constants['plugin_dir'].'/templates/'.$name;

			if ( $located )
				break;
		}

		if ( $load && ! empty( $located ) )
			load_template( $located, $require_once );

		return $located;
	}
} }

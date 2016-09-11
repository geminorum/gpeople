<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginNetworkCore' ) ) { class gPluginNetworkCore extends gPluginClassCore
{

	protected $asset_styles = FALSE;
	protected $asset_config = FALSE; // set NULL to enable
	protected $asset_object = 'gPlugin';
	protected $asset_args   = array();

	public function setup_globals( $constants = array(), $args = array() )
	{
		$this->args = gPluginUtils::recursiveParseArgs( $args, array(
			'title'   => 'gPlugin',
			'domain'  => 'gplugin',
			'network' => TRUE,
			'options' => array(),
		) );

		$this->constants    = apply_filters( $this->args['domain'].'_network_constants', $constants );
		$this->current_blog = get_current_blog_id();
		// $this->blog_map     = get_site_option( $this->args['domain'].'_blog_map', array() ); // FIXME

		$this->root   = FALSE;
		$this->remote = FALSE;

		if ( isset( $this->constants['class_filters'] ) )
			gPluginFactory::get( $this->constants['class_filters'], $constants, $args );
	}

	public function setup_actions()
	{
		$this->setup_constants();
		$this->setup_settings();

		if ( method_exists( $this, 'load_textdomain' ) )
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		add_action( 'init', array( $this, 'init' ) );

		if ( is_network_admin() ) {

			// bail if extended class not ready to have a network settings page
			if ( method_exists( $this, 'network_settings_save' ) )
				add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ) );

			if ( FALSE !== $this->asset_config )
				add_action( 'admin_print_footer_scripts', array( $this, 'footer_asset_config' ), 99 );

		} else {

			if ( is_admin() ) {

				if ( method_exists( $this, 'admin_init' ) )
					add_action( 'admin_init', array( $this, 'admin_init' ) );

				if ( FALSE !== $this->asset_config )
					add_action( 'admin_print_footer_scripts', array( $this, 'footer_asset_config' ), 99 );

			} else {

				if ( FALSE !== $this->asset_config )
					add_action( 'wp_footer' , array( $this, 'footer_asset_config'  ), 99 );
			}

			$this->setup_network();
		}

		$this->setup_modules();
	}

	protected function setup_constants()
	{
		$domain = strtoupper( $this->args['domain'] );

		// JUST IN CASE
		defined( $domain.'_TEXTDOMAIN' ) or define( $domain.'_TEXTDOMAIN', $this->args['domain'] );
		defined( $domain.'_ENABLE_MULTIROOTBLOG' ) or define( $domain.'_ENABLE_MULTIROOTBLOG', FALSE );

		if ( ! defined( $domain.'_ROOT_BLOG' ) ) {

			// root blog is the main site on this network
			if ( is_multisite() && ! constant( $domain.'_ENABLE_MULTIROOTBLOG' ) ) {

				$current_site = get_current_site();

				// root blogs for multi-network
				if ( defined( $domain.'_SITE_ROOT_BLOG_'.$current_site->id ) ) {
					$root_blog_id = constant( $domain.'_SITE_ROOT_BLOG_'.$current_site->id );

				} else {
					$root_blog_id = $current_site->blog_id;
				}

			// root blog is every site on this network
			} else if ( is_multisite() && constant( $domain.'_ENABLE_MULTIROOTBLOG' ) ) {
				$root_blog_id = $this->current_blog;

			// root blog is the only blog on this network
			} else if ( ! is_multisite() ) {
				$root_blog_id = 1;
			}

			define( $domain.'_ROOT_BLOG', $root_blog_id );
		}
	}

	public function setup_settings()
	{
		if ( isset( $this->constants['class_network_settings'] ) ) {
			$this->settings = gPluginFactory::get(
				$this->constants['class_network_settings'],
				$this->constants,
				$this->getFilters( 'network_settings_args' )
			);

			if ( method_exists( $this, 'settings_args_late' ) )
				add_filter( 'gplugin_settings_args_'.strtolower( $this->constants['class_network_settings'] ), array( $this, 'settings_args_late' ) );
		}

		if ( isset( $this->constants['class_component_settings'] ) )
			$this->components = gPluginFactory::get(
				$this->constants['class_component_settings'],
				$this->constants,
				$this->getFilters( 'component_settings_args' )
			);

		if ( isset( $this->constants['class_module_settings'] ) )
			$this->modules = gPluginFactory::get(
				$this->constants['class_module_settings'],
				$this->constants,
				$this->getFilters( 'module_settings_args' )
			);
	}

	public function init()
	{
		// gPlugin Locale:
		// if ( ! is_textdomain_loaded( GPLUGIN_TEXTDOMAIN ) )
		// 	load_plugin_textdomain( GPLUGIN_TEXTDOMAIN, FALSE, 'gplugin/languages' );

		// Parent Plugin Locale:
		// if ( ! is_textdomain_loaded( $this->args['domain'] ) )
		// 	$this->load_textdomain();

		// init here to help filtering the templates
		if ( isset( $this->constants['class_mustache'] ) )
			call_user_func( array( $this->constants['class_mustache'], 'init' ) );
	}

	// FIXME: DEPRECATE THIS
	public function plugins_loaded()
	{
		self::__log( 'UNNECESSARY: '.get_class( $this ).'::plugins_loaded' );

		// $this->load_textdomain();
	}

	// public function load_textdomain() {}
	// public function admin_init() {}
	public function setup_network() {}
	public function setup_modules() {}

	public function network_admin_menu()
	{
		$titles = $this->getFilters( 'network_settings_titles', array() );

		$hook = add_submenu_page( 'settings.php',
			( isset( $titles['title'] ) ? $titles['title'] : $this->args['title'] ),
			( isset( $titles['menu'] ) ? $titles['menu'] : $this->args['title'] ),
			'manage_network_options',
			$this->args['domain'],
			array( $this, 'network_settings' )
		);

		add_action( 'load-'.$hook, array( $this, 'network_settings_save' ) );
	}

	public function network_settings()
	{
		$uri = 'settings.php?page='.$this->args['domain'];
		$sub = isset( $_GET['sub'] ) ? trim( $_GET['sub'] ) : 'general';

		$messages = $this->getFilters( 'network_settings_messages', array() );
		$subs     = $this->getFilters( 'network_settings_subs', array() );
		$titles   = $this->getFilters( 'network_settings_titles', array() );

		echo '<div class="wrap -settings-wrap">';
			printf( '<h1>%s</h1>', ( isset( $titles['title'] ) ? $titles['title'] : $this->args['title'] ) );

			gPluginFormHelper::headerNav( $uri, $sub, $subs );

			if ( ! empty( $_GET['message'] ) ) {

				if ( empty( $messages[$_REQUEST['message']] ) )
					gPluginHTML::notice( $_REQUEST['message'], 'notice-error' );
				else
					echo $messages[$_REQUEST['message']];

				$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'message' ), $_SERVER['REQUEST_URI'] );
			}

			if ( file_exists( $this->constants['plugin_dir'].'admin/network.'.$sub.'.php' ) )
				require_once( $this->constants['plugin_dir'].'admin/network.'.$sub.'.php' );
			else
				do_action( $this->args['domain'].'_network_settings_sub_'.$sub, $uri, $sub );

		echo '<div class="clear"></div></div>';
	}

	// called by extended class as default settings page html
	public function network_settings_html( $uri, $sub )
	{
		echo '<form method="post" action="">';
			settings_fields( $this->args['domain'].'_'.$sub );
			do_settings_sections( $this->args['domain'].'_'.$sub );
			submit_button();
		echo '</form>';
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

	public function enqueue_asset_config( $args = array(), $scope = NULL )
	{
		$this->asset_config = TRUE;

		if ( count( $args ) ) {
			if ( is_null( $scope ) )
				$temp = array_merge( $this->asset_args, $args );
			else
				$temp = array_merge( $this->asset_args, array( $scope => $args ) );
			$this->asset_args = $temp;
		}
	}

	// front & admin
	public function footer_asset_config()
	{
		if ( ! $this->asset_config )
			return;

		$args = $this->asset_args;
		$args['api'] = defined( 'GNETWORK_AJAX_ENDPOINT' ) && GNETWORK_AJAX_ENDPOINT ? GNETWORK_AJAX_ENDPOINT : admin_url( 'admin-ajax.php' );

	?><script type="text/javascript">
/* <![CDATA[ */
	var <?php echo $this->asset_object; ?> = <?php echo wp_json_encode( $args ); ?>;

	<?php if ( gPluginWPHelper::isDev() ) echo 'console.log('.$this->asset_object.');'; ?>

/* ]]> */
</script><?php
	}

	// TODO: extend by child to use network option
	public function get_site_user_id( $fallback = TRUE )
	{
		if ( defined( 'GNETWORK_SITE_USER_ID' ) && constant( 'GNETWORK_SITE_USER_ID' ) )
			return GNETWORK_SITE_USER_ID;

		if ( function_exists( 'gtheme_get_option' ) ) {
			$gtheme_user = gtheme_get_option( 'default_user', 0 );
			if ( $gtheme_user )
				return $gtheme_user;
		}

		if ( $fallback )
			return get_current_user_id();

		return 0;
	}

	// FIXME: DEPRECATED
	public function get_option( $name, $default = FALSE )
	{
		self::__dep();

		$options = get_site_option( $this->args['domain'], FALSE );

		if ( $options === FALSE )
			$options = array();

		if ( !isset( $options[$name] ) )
			$options[$name] = $default;

		return $options[$name];
	}

	// FIXME: DEPRECATED
	public function update_option( $name, $value )
	{
		self::__dep();

		$options = get_site_option( $this->args['domain'], FALSE );

		if ( $options === FALSE )
			$options = array();

		$options[$name] = $value;

		return update_site_option( $this->args['domain'], $options );
	}

	// FIXME: DEPRECATED
	public function delete_option( $name )
	{
		self::__dep();

		$options = get_site_option( $this->args['domain'], FALSE );

		if ( $options === FALSE )
			$options = array();

		unset( $options[$name] );

		return update_option( $this->args['domain'], $options );
	}
} }

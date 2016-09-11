<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gPluginAdminCore extends gPluginClassCore
{

	protected $component = 'admin'; // root / remote

	protected $priority_init       = 10;
	protected $priority_admin_init = 10;

	public function setup_actions()
	{
		if ( method_exists( $this, 'init' ) )
			add_action( 'init', array( $this, 'init' ), $this->priority_init );

		if ( is_admin() ) {

			if ( method_exists( $this, 'admin_init' ) )
				add_action( 'admin_init', array( $this, 'admin_init' ), $this->priority_admin_init );

			if ( method_exists( $this, 'current_screen' ) )
				add_action( 'current_screen', array( $this, 'current_screen' ) );

			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			if ( method_exists( $this, 'admin_footer' ) )
				add_action( 'admin_footer', array( $this, 'admin_footer' ) );

			// no need for network
			if ( ! $this->args['network'] )
				add_action( 'plugin_action_links_'.$this->args['domain'].'/'.$this->args['domain'].'.php', array( $this, 'settings_link' ), 10, 4 );
		}
	}

	public function admin_menu()
	{
		// bail if extended class not ready to have an admin settings page
		if ( ! method_exists( $this, 'admin_settings_load' ) )
			return;

		$titles = $this->getFilters( $this->component.'_settings_titles', array() );

		$hook = add_submenu_page( 'options-general.php',
			( isset( $titles['title'] ) ? $titles['title'] : $this->args['title'] ),
			( isset( $titles['menu'] ) ? $titles['menu'] : $this->args['title'] ),
			'manage_options',
			$this->args['domain'],
			array( $this, 'admin_settings' )
		);

		add_action( 'load-'.$hook, array( $this, 'admin_settings_load' ) );
		add_action( 'admin_print_styles', array( $this, 'admin_print_styles_settings' ) ); // TODO: use this as helper on child's admin_settings_load()
	}

	public function admin_settings()
	{
		$uri = 'options-general.php?page='.$this->args['domain'];
		$sub = isset( $_GET['sub'] ) ? trim( $_GET['sub'] ) : 'general';

		$subs     = $this->getFilters( $this->component.'_settings_subs', array() );
		$messages = $this->getFilters( $this->component.'_settings_messages', array() );
		$titles   = $this->getFilters( $this->component.'_settings_titles', array() );

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

			$file = $this->constants['plugin_dir'].'admin/'.$this->component.'.admin.'.$sub.'.php';
			if ( file_exists( $file ) )
				require_once( $file );
			else
				do_action( $this->args['domain'].'_'.$this->component.'_settings_sub_'.$sub, $uri, $sub );

		echo '<div class="clear"></div></div>';
	}

	// called by extended class as default settings page html
	public function admin_settings_html( $uri, $sub )
	{
		echo '<form method="post" action="">';
			settings_fields( $this->args['domain'].'_'.$this->component.'_'.$sub );
			do_settings_sections( $this->args['domain'].'_'.$this->component.'_'.$sub );
			submit_button();
		echo '</form>';
	}

	// FIXME: DEPRECATED
	// usually overrides by the child class
	public function admin_print_styles()
	{
		self::__log( 'UNNECESSARY: '.get_class( $this ).'::admin_print_styles' );

		// $this->print_admin_edit_styles();
	}

	public function linkStyleSheet( $post_type, $base = 'post' )
	{
		gPluginHTML::linkStyleSheet(
			$this->constants['plugin_url'].
			'assets/css/'.
			$this->component.'.'.
			'admin'.'.'.
			$post_type.'.'.
			$base.'.css',
		$this->constants['plugin_ver'] );
	}

	public function enqueue_style( $post_type, $base = 'post' )
	{
		wp_enqueue_style(
			$this->args['domain'].'-'.$post_type.'-'.$base,
			$this->constants['plugin_url'].
			'assets/css/'.
			$this->component.'.'.
			'admin'.'.'.
			$post_type.'.'.
			$base.'.css',
			array(),
			$this->constants['plugin_ver'],
			'screen' );
	}

	// the caller must check cpt first
	public function print_admin_edit_styles( $post_type = 'post', $screen = NULL )
	{
		self::__dep( '$this->linkStyleSheet()' );

		if ( is_null( $screen ) )
			$screen = get_current_screen();

		if ( in_array( $screen->base, array( 'edit', 'post' ) ) )
			$this->linkStyleSheet( $post_type, $screen->base );
	}

	// for settings page only
	public function admin_print_styles_settings()
	{
		if ( strpos( $_SERVER['REQUEST_URI'], 'page='.$this->args['domain'] ) )
			gPluginHTML::linkStyleSheet( $this->constants['plugin_url'].'assets/css/'.$this->component.'.admin.settings.css', $this->constants['plugin_ver'] );
	}

	public function settings_link( $links )
	{
		array_unshift( $links, '<a href="options-general.php?page='.$this->args['domain'].'">'.__( 'Settings' ).'</a>' );
		return $links;
	}

	// wrapper
	public static function post_type()
	{
		return gPluginWPHelper::getCurrentPostType();
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
}

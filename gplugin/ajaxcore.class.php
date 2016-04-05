<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginAjaxCore' ) ) { class gPluginAjaxCore extends gPluginClassCore
{

	protected $ajax_action = 'gplugin_ajax';
	protected $ajax_nonce  = 'gplugin-ajax';

	public function setup_globals( $constants = array(), $args = array() )
	{
		$this->current_blog = get_current_blog_id();
		parent::setup_globals( $constants, $args );

		// FIXME: DROP THIS
		if ( isset( $this->_ajax_action ) ) {
			self::__dep( 'var $_ajax_action' );
			$this->ajax_action = $this->_ajax_action;
		}

		// FIXME: DROP THIS
		if ( isset( $this->_ajax_nonce ) ) {
			self::__dep( 'var $_ajax_nonce' );
			$this->ajax_nonce = $this->_ajax_nonce;
		}
	}

	public function setup_actions()
	{
		add_action( 'wp_ajax_'.$this->ajax_action, array( $this, 'ajax' ) );
	}

	public function ajax()
	{
		if ( wp_verify_nonce( $_REQUEST['_ajax_nonce'], $this->ajax_nonce ) ) {

			$post = stripslashes_deep( $_POST );
			$sub  = isset( $post['sub'] ) ? $post['sub'] : 'nosub';

			// if ( ! method_exists( $this, 'sub_'.$sub ) ) {
			// 	if ( ! count( $fallback ) )
			//		self::__log( 'GROUP FILTER NOT EXISTS: '.get_class( $this ).'::'.$group );
			// 	return $fallback;
			// }

			if ( method_exists( $this, 'sub_'.$sub ) )
				$results = call_user_func_array ( array( $this, 'sub_'.$sub ), array( $post ) );

			//	if FALSE /error / if not success

		} else {
			self::sendError( __( 'Cheatin&#8217; uh?' ) );
		}

		die();
	}

	protected function sub_nosub( $post )
	{
		self::sendError( __( 'dont know what to do?' ) );

		return FALSE;
	}

	protected static function sendError( $message )
	{
		wp_send_json_error( gPluginWPHelper::notice( $message, 'error', FALSE ) );
	}
} }

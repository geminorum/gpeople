<?php defined( 'ABSPATH' ) || die( header( 'HTTP/1.0 403 Forbidden' ) );

class gPeopleNetwork extends gPluginNetworkCore
{

	protected $asset_object = 'gPeopleNetwork';

	public function setup_network()
	{
		$modules = array();

		if ( constant( 'GPEOPLE_ROOT_BLOG' ) == get_current_blog_id()
			&& ( $this->components->get( 'gpeople_root' )
				|| ! constant( 'GPEOPLE_ENABLE_MULTIROOTBLOG' ) ) ) {

			$this->root = gPluginFactory::get( 'gPeopleRootComponent',
				$this->constants,
				array_merge( $this->args, array(
					'option_group' => 'gpeople_root_options',
					'component'    => 'root',
			) ) );

			$modules['root_admin'] = 'gPeopleRootAdmin';
			$modules['importer']   = 'gPeopleImporter';
			// $modules['root_ajax']  = 'gPeopleRootAjax'; // not yet for root
		}

		if ( $this->components->get( 'gpeople_remote' ) ) {

			$this->remote = gPluginFactory::get( 'gPeopleRemoteComponent',
				$this->constants,
				array_merge( $this->args, array(
					'option_group' => 'gpeople_remote_options',
					'component'    => 'remote',
			) ) );

			$modules['remote_admin'] = 'gPeopleRemoteAdmin';
			$modules['importer']     = 'gPeopleImporter';
			$modules['profile']      = 'gPeopleProfile';
			$modules['picture']      = 'gPeoplePicture';
			$modules['user']         = 'gPeopleUser';
			$modules['relation']     = 'gPeopleRelation';
			$modules['people']       = 'gPeoplePeople';
			$modules['remote_ajax']  = 'gPeopleRemoteAjax';
		}

		foreach ( $modules as $module => $class ) {

			$this->{$module} = gPluginFactory::get( $class, $this->constants, $this->args );

			if ( FALSE === $this->{$module} )
				unset( $this->{$module} );
		}

		if ( is_admin() )
			$this->asset_config = NULL; // enabling!

		// add_action( 'bp_include', array( $this, 'bp_include' ) );
	}

	public function bp_include()
	{
		$this->buddypress = gPluginFactory::get( 'gPeopleBuddyPress', $this->constants, $this->args );
	}

	public function load_textdomain()
	{
		load_plugin_textdomain( GPEOPLE_TEXTDOMAIN, FALSE, 'gpeople/languages' );
	}

	public function colorbox()
	{
		wp_enqueue_style(
			'jquery-colorbox',
			$this->constants['plugin_url'].'assets/css/admin.colorbox.css',
			array(),
			'1.6.3',
			'screen' );

		wp_enqueue_script(
			'jquery-colorbox',
			$this->constants['plugin_url'].'assets/packages/jquery-colorbox/jquery.colorbox-min.js',
			array( 'jquery'),
			'1.6.3',
			TRUE );
	}
}

class gPeopleNetworkSettings extends gPluginSettingsCore { }
class gPeopleComponentSettings extends gPluginSettingsCore { }
class gPeopleRemoteSettings extends gPluginSettingsCore { }

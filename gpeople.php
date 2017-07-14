<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

/*
Plugin Name: gPeople
Plugin URI: http://geminorum.ir/wordpress/gpeople
Description: People, the way involved in your site. Depends on <a href="http://geminorum.ir/wordpress/gplugin/">gPlugin</a>
Version: 3.4.0
License: GPLv3+
Author: geminorum
Author URI: http://geminorum.ir
Network: true
TextDomain: gpeople
DomainPath: /languages
RepoGitHub: geminorum/gpeople
GitHub Plugin URI: https://github.com/geminorum/gpeople
GitHub Branch: master
Requires WP: 4.5
Requires PHP: 5.3
*/

define( 'GPEOPLE_VERSION', '3.4.0' );
define( 'GPEOPLE_VERSION_DB', '0.1' );
define( 'GPEOPLE_VERSION_GPLUGIN', 39 );
define( 'GPEOPLE_DIR', plugin_dir_path( __FILE__ ) );
define( 'GPEOPLE_URL', plugin_dir_url( __FILE__ ) );
define( 'GPEOPLE_FILE', basename( GPEOPLE_DIR ).'/'.basename( __FILE__ ) );

if ( file_exists( WP_PLUGIN_DIR.'/gpeople-custom.php' ) )
	require( WP_PLUGIN_DIR.'/gpeople-custom.php' );

defined( 'GPEOPLE_TEXTDOMAIN' ) or define( 'GPEOPLE_TEXTDOMAIN', 'gpeople' );
defined( 'GPEOPLE_ENABLE_MULTIROOTBLOG' ) or define( 'GPEOPLE_ENABLE_MULTIROOTBLOG', FALSE );
defined( 'GPEOPLE_ROOT_BLOG_REMOTE' ) or define( 'GPEOPLE_ROOT_BLOG_REMOTE', FALSE );
defined( 'GPEOPLE_PEOPLE_TAXONOMY' ) or define( 'GPEOPLE_PEOPLE_TAXONOMY', 'people' );

function gpeople_init( $gplugin_version = NULL ){

	global $gPeopleNetwork;

	if ( ! $gplugin_version || ! version_compare( $gplugin_version, GPEOPLE_VERSION_GPLUGIN, '>=' ) )
		return;

	if ( ! class_exists( 'WP_List_Table' ) )
		require_once( ABSPATH.'wp-admin/includes/class-wp-list-table.php' );

	$includes = array(
		'network',
		'filtered',
		'mustache',

		'root',
		'rootadmin',

		'remote',
		'remoteadmin',
		'remoteajax',
		'remotemeta',
		'remotetemplate',
		'remotemetatable',

		'people',
		'profile',
		'picture',
		'user',
		'relation',
		'buddypress',
		'importer',
	);

	foreach ( $includes as $file )
		if ( file_exists( GPEOPLE_DIR.'includes/'.$file.'.class.php' ) )
			require_once( GPEOPLE_DIR.'includes/'.$file.'.class.php' );

	$args = array(
		'title'   => __( 'gPeople', GPEOPLE_TEXTDOMAIN ),
		'domain'  => 'gpeople',
		'network' => TRUE,
	);

	$constants = array(
		'plugin_dir' => GPEOPLE_DIR,
		'plugin_url' => GPEOPLE_URL,
		'plugin_ver' => GPEOPLE_VERSION,
		'plugin_vdb' => GPEOPLE_VERSION_DB,

		'class_filters'            => 'gPeopleFiltered',
		'class_mustache'           => 'gPeopleMustache',
		'class_component_settings' => 'gPeopleComponentSettings',
		'class_remote_settings'    => 'gPeopleRemoteSettings',

		'theme_templates_dir' => 'gpeople_templates',

		'meta_key'        => '_gpeople',
		'term_meta_key'   => '_gpeople',
		'root_meta_key'   => '_gpeople_root',
		'remote_meta_key' => '_gpeople_remote',

		'profile_cpt'              => 'profile',
		'profile_archives'         => 'profiles',
		'profile_group_tax'        => 'profile_group',
		'profile_group_tax_slug'   => 'profiles/group',
		'profile_nationality_tax'  => 'profile_nationality',
		'profile_nationality_slug' => 'profiles/nationality',
		'profile_meta_key'         => 'profiles',
		'root_connection_type'     => 'profile_to_profile',
		'people_tax'               => GPEOPLE_PEOPLE_TAXONOMY,
		'people_slug'              => 'people',
		'affiliation_tax'          => 'affiliation',
		'affiliation_slug'         => 'affiliation',
		'rel_people_tax'           => 'rel_people',
		'rel_post_tax'             => 'rel_post',
		'rel_post_tax_pre'         => '_gp_',
		'activation_flag'          => 'gpeople_activation_flag',
		'remote_connection_type'   => 'post_to_people',
		'user_profile_map'         => 'gpeople_profile_map',
		'user_term_map'            => 'gpeople_term_map',

		'metakey_people_firstname' => 'firstname',
		'metakey_people_lastname'  => 'lastname',
		'metakey_people_altname'   => 'altname',
	);

	if ( class_exists( 'gPluginFactory' ) )
		$gPeopleNetwork = gPluginFactory::get( 'gPeopleNetwork', $constants, $args );
}

require( GPEOPLE_DIR.'gplugin/load.php' );
gplugin_init( 'gpeople_init' );

<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

defined( 'GPLUGIN_SESSION_NETWORKWIDE' ) or define( 'GPLUGIN_SESSION_NETWORKWIDE', TRUE );
defined( 'GPLUGIN_SESSION_CRON_ROUTINE' ) or define( 'GPLUGIN_SESSION_CRON_ROUTINE', 'twicedaily' ); // 'hourly'

// based on WP Session Manager 1.1.2 by Eric Mann
// http://jumping-duck.com/wordpress/plugins
// http://wordpress.org/plugins/wp-session-manager/
// http://jumping-duck.com/wordpress/plugins/wp-session-manager/

class gPluginSessionHelper
{

	public static function setup_actions()
	{
		global $gPluginSessionLoaded;

		if ( empty ( $gPluginSessionLoaded ) ) {

			add_action( 'wp', array( __CLASS__, 'register_garbage_collection' ) );
			add_action( 'plugins_loaded', array( __CLASS__, 'start' ) );
			add_action( 'shutdown', array( __CLASS__, 'write_close' ) );
			add_action( 'gplugin_session_garbage_collection', array( __CLASS__, 'cleanup' ) );

			$gPluginSessionLoaded = TRUE;
		}
	}

	/**
	 * Return the current cache expire setting.
	 *
	 * @return int
	 */
	public static function cache_expire()
	{
		$gPluginSession = gPluginSession::get_instance();
		return $gPluginSession->cache_expiration();
	}

	/**
	 * Alias of write_close()
	 */
	public static function commit()
	{
		self::write_close();
	}

	/**
	 * Load a JSON-encoded string into the current session.
	 *
	 * @param string $data
	 */
	public static function decode( $data )
	{
		$gPluginSession = gPluginSession::get_instance();
		return $gPluginSession->json_in( $data );
	}

	/**
	 * Encode the current session's data as a JSON string.
	 *
	 * @return string
	 */
	public static function encode()
	{
		$gPluginSession = gPluginSession::get_instance();
		return $gPluginSession->json_out();
	}

	/**
	 * Regenerate the session ID.
	 *
	 * @param bool $delete_old_session
	 *
	 * @return bool
	 */
	public static function regenerate_id( $delete_old_session = FALSE )
	{
		$gPluginSession = gPluginSession::get_instance();
		$gPluginSession->regenerate_id( $delete_old_session );
		return TRUE;
	}

	/**
	 * Start new or resume existing session.
	 *
	 * Resumes an existing session based on a value sent by the _wp_session cookie.
	 *
	 * @return bool
	 */
	public static function start()
	{
		$gPluginSession = gPluginSession::get_instance();
		do_action( 'wp_session_start' ); // back comp
		return $gPluginSession->session_started();
	}


	/**
	 * Return the current session status.
	 *
	 * @return int
	 */
	public static function status()
	{
		$gPluginSession = gPluginSession::get_instance();
		if ( $gPluginSession->session_started() )
			return PHP_SESSION_ACTIVE;
		return PHP_SESSION_NONE;
	}

	/**
	 * Unset all session variables.
	 */
	public static function reset()
	{
		$gPluginSession = gPluginSession::get_instance();
		$gPluginSession->reset();
	}

	/**
	 * Write session data and end session
	 */
	public static function write_close()
	{
		$gPluginSession = gPluginSession::get_instance();
		$gPluginSession->write_data();
		do_action( 'wp_session_commit' ); // back comp
	}

	/**
	 * Clean up expired sessions by removing data and their expiration entries from
	 * the WordPress options table.
	 *
	 * This method should never be called directly and should instead be triggered as part
	 * of a scheduled task or cron job.
	 */
	public static function cleanup()
	{
		global $wpdb;

		if ( defined( 'WP_SETUP_CONFIG' ) )
			return;

		if ( ! wp_installing() ) {
			if ( GPLUGIN_SESSION_NETWORKWIDE )
				$expiration_keys = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->sitemeta WHERE meta_key LIKE '_gp_session_expires_%'" );
			else
				$expiration_keys = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '_gp_session_expires_%'" );

			$now = time();
			$expired_sessions = array();
			if ( GPLUGIN_SESSION_NETWORKWIDE ) {
				foreach ( $expiration_keys as $expiration ) {
					// If the session has expired
					if ( $now > intval( $expiration->meta_value ) ) {
						// Get the session ID by parsing the option_name
						$session_id = substr( $expiration->meta_key, 20 );

						$expired_sessions[] = $expiration->meta_key;
						$expired_sessions[] = "_gp_session_$session_id";
					}
				}
			} else {
				foreach ( $expiration_keys as $expiration ) {
					// If the session has expired
					if ( $now > intval( $expiration->option_value ) ) {
						// Get the session ID by parsing the option_name
						$session_id = substr( $expiration->option_name, 20 );

						$expired_sessions[] = $expiration->option_name;
						$expired_sessions[] = "_gp_session_$session_id";
					}
				}
			}
			// Delete all expired sessions in a single query
			if ( ! empty( $expired_sessions ) ) {
				$option_names = implode( "','", $expired_sessions );
				if ( GPLUGIN_SESSION_NETWORKWIDE )
					$wpdb->query( "DELETE FROM $wpdb->sitemeta WHERE meta_key IN ('$option_names')" );
				else
					$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name IN ('$option_names')" );
			}
		}

		// Allow other plugins to hook in to the garbage collection process.
		do_action( 'wp_session_cleanup' );

	}

	// Register the garbage collector as a twice daily event.
	public static function register_garbage_collection()
	{
		if ( ! wp_next_scheduled( 'gplugin_session_garbage_collection' ) )
			wp_schedule_event( time(), GPLUGIN_SESSION_CRON_ROUTINE, 'gplugin_session_garbage_collection' );
	}
}

// Multidimensional ArrayAccess : Allows ArrayAccess-like functionality with multidimensional arrays.  Fully supports both sets and unsets.
// based on WP Session Manager 1.1.2 by Eric Mann
// http://jumping-duck.com/wordpress/plugins
// http://wordpress.org/plugins/wp-session-manager/
class gPluginRecursiveArrayAccess implements ArrayAccess {
	/**
	 * Internal data collection.
	 *
	 * @var array
	 */
	protected $container = array();

	/**
	 * Flag whether or not the internal collection has been changed.
	 *
	 * @var bool
	 */
	protected $dirty = FALSE;

	/**
	 * Default object constructor.
	 *
	 * @param array $data
	 */
	protected function __construct( $data = array() ) {
		foreach ( $data as $key => $value ) {
			$this[ $key ] = $value;
		}
	}

	/**
	 * Allow deep copies of objects
	 */
	public function __clone() {
		foreach ( $this->container as $key => $value ) {
			if ( $value instanceof self ) {
				$this[ $key ] = clone $value;
			}
		}
	}

	/**
	 * Output the data container as a multidimensional array.
	 *
	 * @return array
	 */
	public function toArray() {
		$data = $this->container;
		foreach ( $data as $key => $value ) {
			if ( $value instanceof self ) {
				$data[ $key ] = $value->toArray();
			}
		}
		return $data;
	}

	/*****************************************************************/
	/*                   ArrayAccess Implementation                  */
	/*****************************************************************/

	/**
	 * Whether a offset exists
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset An offset to check for.
	 *
	 * @return boolean TRUE on success or FALSE on failure.
	 */
	public function offsetExists( $offset ) {
		return isset( $this->container[ $offset ]) ;
	}

	/**
	 * Offset to retrieve
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset The offset to retrieve.
	 *
	 * @return mixed Can return all value types.
	 */
	public function offsetGet( $offset ) {
		return isset( $this->container[ $offset ] ) ? $this->container[ $offset ] : NULL;
	}

	/**
	 * Offset to set
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value  The value to set.
	 *
	 * @return void
	 */
	public function offsetSet( $offset, $data ) {
		if ( is_array( $data ) ) {
			$data = new self( $data );
		}
		if ( $offset === NULL ) { // don't forget this!
			$this->container[] = $data;
		} else {
			$this->container[ $offset ] = $data;
		}

		$this->dirty = TRUE;
	}

	/**
	 * Offset to unset
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset The offset to unset.
	 *
	 * @return void
	 */
	public function offsetUnset( $offset ) {
		unset( $this->container[ $offset ] );

		$this->dirty = TRUE;
	}
}

// Standardizes WordPress session data using database-backed options for storage. for storing user session information.
// based on WP Session Manager 1.1.2 by Eric Mann
// http://jumping-duck.com/wordpress/plugins
// http://wordpress.org/plugins/wp-session-manager/
final class gPluginSession extends gPluginRecursiveArrayAccess implements Iterator, Countable {
	/**
	 * ID of the current session.
	 *
	 * @var string
	 */
	private $session_id;

	/**
	 * Unix timestamp when session expires.
	 *
	 * @var int
	 */
	private $expires;

	/**
	 * Unix timestamp indicating when the expiration time needs to be reset.
	 *
	 * @var int
	 */
	private $exp_variant;

	/**
	 * Singleton instance.
	 *
	 * @var bool|WP_Session
	 */
	private static $instance = FALSE;

	/**
	 * Retrieve the current session instance.
	 *
	 * @param bool $session_id Session ID from which to populate data.
	 *
	 * @return bool|WP_Session
	 */
	public static function get_instance()
	{
		if ( ! self::$instance )
			self::$instance = new self();
		return self::$instance;
	}

	/**
	 * Default constructor.
	 * Will rebuild the session collection from the given session ID if it exists. Otherwise, will
	 * create a new session with that ID.
	 *
	 * @param $session_id
	 * @uses apply_filters Calls `wp_session_expiration` to determine how long until sessions expire.
	 */
	protected function __construct()
	{

		if ( ! defined( 'GPLUGIN_SESSION_COOKIE' ) )
			define( 'GPLUGIN_SESSION_COOKIE', '_gp_session' );

		if ( isset( $_COOKIE[GPLUGIN_SESSION_COOKIE] ) ) {
			$cookie = stripslashes( $_COOKIE[GPLUGIN_SESSION_COOKIE] );
			$cookie_crumbs = explode( '||', $cookie );

			$this->session_id = $cookie_crumbs[0];
			$this->expires = $cookie_crumbs[1];
			$this->exp_variant = $cookie_crumbs[2];

			// Update the session expiration if we're past the variant time
			if ( time() > $this->exp_variant ) {
				$this->set_expiration();
				if ( GPLUGIN_SESSION_NETWORKWIDE )
					update_site_option( "_gp_session_expires_{$this->session_id}", $this->expires );
				else
					update_option( "_gp_session_expires_{$this->session_id}", $this->expires );
			}
		} else {
			$this->session_id = $this->generate_id();
			$this->set_expiration();
		}

		$this->read_data();
		$this->set_cookie();
	}

	/**
	 * Set both the expiration time and the expiration variant.
	 *
	 * If the current time is below the variant, we don't update the session's expiration time. If it's
	 * greater than the variant, then we update the expiration time in the database.  This prevents
	 * writing to the database on every page load for active sessions and only updates the expiration
	 * time if we're nearing when the session actually expires.
	 *
	 * By default, the expiration time is set to 30 minutes.
	 * By default, the expiration variant is set to 24 minutes.
	 *
	 * As a result, the session expiration time - at a maximum - will only be written to the database once
	 * every 24 minutes.  After 30 minutes, the session will have been expired. No cookie will be sent by
	 * the browser, and the old session will be queued for deletion by the garbage collector.
	 *
	 * @uses apply_filters Calls `wp_session_expiration_variant` to get the max update window for session data.
	 * @uses apply_filters Calls `wp_session_expiration` to get the standard expiration time for sessions.
	 */
	private function set_expiration()
	{
		//$this->exp_variant = time() + intval( apply_filters( 'wp_session_expiration_variant', 24 * 60 ) );
		//$this->expires = time() + intval( apply_filters( 'wp_session_expiration', 30 * 60 ) );

		$this->exp_variant = time() + (int) apply_filters( 'wp_session_expiration_variant', 24 * 60 );
		$this->expires = time() + (int) apply_filters( 'wp_session_expiration', 30 * 60 );

	}

	/**
	 * Set the session cookie
	 */
	protected function set_cookie() {
		// setcookie( WP_SESSION_COOKIE, $this->session_id . '||' . $this->expires . '||' . $this->exp_variant , $this->expires, COOKIEPATH, COOKIE_DOMAIN );
		setcookie( GPLUGIN_SESSION_COOKIE,
			$this->session_id.'||'.$this->expires.'||'.$this->exp_variant,
			$this->expires,
			( GPLUGIN_SESSION_NETWORKWIDE ? SITECOOKIEPATH : COOKIEPATH ),
			COOKIE_DOMAIN
		);
	}

	/**
	 * Generate a cryptographically strong unique ID for the session token.
	 *
	 * @return string
	 */
	private function generate_id()
	{
		require_once( ABSPATH.'wp-includes/class-phpass.php' );
		$hasher = new PasswordHash( 8, FALSE );

		return md5( $hasher->get_random_bytes( 32 ) );
	}

	/**
	 * Read data from a transient for the current session.
	 *
	 * Automatically resets the expiration time for the session transient to some time in the future.
	 *
	 * @return array
	 */
	private function read_data()
	{
		if ( GPLUGIN_SESSION_NETWORKWIDE )
			$this->container = get_site_option( "_gp_session_{$this->session_id}", array() );
		else
			$this->container = get_option( "_gp_session_{$this->session_id}", array() );

		return $this->container;
	}

	/**
	 * Write the data from the current session to the data storage system.
	 */
	public function write_data() {

		$option_key = "_gp_session_{$this->session_id}";

		// Only write the collection to the DB if it's changed.
		if ( $this->dirty ) {
			if ( GPLUGIN_SESSION_NETWORKWIDE ) {
				if ( FALSE === get_site_option( $option_key ) ) {
					add_site_option( "_gp_session_{$this->session_id}", $this->container  );
					add_site_option( "_gp_session_expires_{$this->session_id}", $this->expires );
				} else {
					update_site_option( "_gp_session_{$this->session_id}", $this->container );
				}
			} else {
				if ( FALSE === get_option( $option_key ) ) {
					add_option( "_gp_session_{$this->session_id}", $this->container, '', 'no' );
					add_option( "_gp_session_expires_{$this->session_id}", $this->expires, '', 'no' );
				} else {
					update_option( "_gp_session_{$this->session_id}", $this->container );
				}
			}
		}
	}

	/**
	 * Output the current container contents as a JSON-encoded string.
	 *
	 * @return string
	 */
	public function json_out()
	{
		return wp_json_encode( $this->container );
	}

	/**
	 * Decodes a JSON string and, if the object is an array, overwrites the session container with its contents.
	 *
	 * @param string $data
	 *
	 * @return bool
	 */
	public function json_in( $data )
	{
		$array = json_decode( $data );

		if ( is_array( $array ) ) {
			$this->container = $array;
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Regenerate the current session's ID.
	 *
	 * @param bool $delete_old Flag whether or not to delete the old session data from the server.
	 */
	public function regenerate_id( $delete_old = FALSE )
	{
		if ( $delete_old ) {
			if ( GPLUGIN_SESSION_NETWORKWIDE )
				delete_site_option( "_gp_session_{$this->session_id}" );
			else
				delete_option( "_gp_session_{$this->session_id}" );
		}

		$this->session_id = $this->generate_id();
		$this->set_cookie();
	}

	// mine
	public function get_id()
	{
		return $this->session_id;
	}


	/**
	 * Check if a session has been initialized.
	 *
	 * @return bool
	 */
	public function session_started()
	{
		return !!self::$instance;
	}

	/**
	 * Return the read-only cache expiration value.
	 *
	 * @return int
	 */
	public function cache_expiration()
	{
		return $this->expires;
	}

	/**
	 * Flushes all session variables.
	 */
	public function reset()
	{
		$this->container = array();
	}

	/*****************************************************************/
	/*                     Iterator Implementation                   */
	/*****************************************************************/

	/**
	 * Current position of the array.
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 *
	 * @return mixed
	 */
	public function current()
	{
		return current( $this->container );
	}

	/**
	 * Key of the current element.
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 *
	 * @return mixed
	 */
	public function key()
	{
		return key( $this->container );
	}

	/**
	 * Move the internal point of the container array to the next item
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 *
	 * @return void
	 */
	public function next()
	{
		next( $this->container );
	}

	/**
	 * Rewind the internal point of the container array.
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 *
	 * @return void
	 */
	public function rewind()
	{
		reset( $this->container );
	}

	/**
	 * Is the current key valid?
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 *
	 * @return bool
	 */
	public function valid()
	{
		return $this->offsetExists( $this->key() );
	}

	/*****************************************************************/
	/*                    Countable Implementation                   */
	/*****************************************************************/

	/**
	 * Get the count of elements in the container array.
	 *
	 * @link http://php.net/manual/en/countable.count.php
	 *
	 * @return int
	 */
	public function count()
	{
		return count( $this->container );
	}
}

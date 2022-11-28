<?php

namespace Vrts\Core\Utilities;

/**
 * Async Request.
 *
 * @see https://github.com/deliciousbrains/wp-background-processing
 */
abstract class Async_Request {
	/**
	 * Prefix.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Action.
	 *
	 * @var string
	 */
	protected $action = 'async_request';

	/**
	 * Identifier.
	 *
	 * @var mixed
	 */
	protected $identifier;

	/**
	 * Data.
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Initiate new async request.
	 */
	public function __construct() {
		// if the prefix hasn't been set explicity use the theme identifier.
		if ( is_null( $this->prefix ) ) {
			$this->prefix = vrts()->get_plugin_identifier();
		}

		// Uses unique prefix per blog so each blog has separate queue.
		$this->prefix = $this->prefix . '_' . get_current_blog_id();
		$this->identifier = $this->prefix . '_' . $this->action;

		add_action( 'wp_ajax_' . $this->identifier, [ $this, 'maybe_handle' ] );
		add_action( 'wp_ajax_nopriv_' . $this->identifier, [ $this, 'maybe_handle' ] );
	}

	/**
	 * Set data used during the request.
	 *
	 * @param array $data Data.
	 *
	 * @return $this
	 */
	public function data( $data ) {
		$this->data = $data;

		return $this;
	}

	/**
	 * Dispatch the async request.
	 *
	 * @return array|WP_Error
	 */
	public function dispatch() {
		$url = add_query_arg( $this->get_query_args(), $this->get_query_url() );
		$args = $this->get_post_args();

		return wp_remote_post( esc_url_raw( $url ), $args );
	}

	/**
	 * Get query args.
	 *
	 * @return array
	 */
	protected function get_query_args() {
		if ( property_exists( $this, 'query_args' ) ) {
			return $this->query_args;
		}

		$args = [
			'action' => $this->identifier,
			'nonce' => wp_create_nonce( $this->identifier ),
		];

		/**
		 * Filters the post arguments used during an async request.
		 *
		 * @param array $url
		 */
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- Prefix is theme identifier and action name.
		return apply_filters( $this->identifier . '_query_args', $args );
	}

	/**
	 * Get query URL.
	 *
	 * @return string
	 */
	protected function get_query_url() {
		if ( property_exists( $this, 'query_url' ) ) {
			return $this->query_url;
		}

		$url = admin_url( 'admin-ajax.php' );

		/**
		 * Filters the post arguments used during an async request.
		 *
		 * @param string $url
		 */
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- Prefix is theme identifier and action name.
		return apply_filters( $this->identifier . '_query_url', $url );
	}

	/**
	 * Get post args.
	 *
	 * @return array
	 */
	protected function get_post_args() {
		if ( property_exists( $this, 'post_args' ) ) {
			return $this->post_args;
		}

		$args = [
			'timeout' => 0.01,
			'blocking' => false,
			'body' => $this->data,
			'cookies' => $_COOKIE,
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Using default wp hook.
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
		];

		/**
		 * Filters the post arguments used during an async request.
		 *
		 * @param array $args
		 */
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- Prefix is theme identifier and action name.
		return apply_filters( $this->identifier . '_post_args', $args );
	}

	/**
	 * Maybe handle.
	 *
	 * Check for correct nonce and pass to handler.
	 */
	public function maybe_handle() {
		// Don't lock up other requests while processing.
		session_write_close();

		check_ajax_referer( $this->identifier, 'nonce' );

		$this->handle();

		wp_die();
	}

	/**
	 * Handle.
	 *
	 * Override this method to perform any actions required
	 * during the async request.
	 */
	abstract protected function handle();
}

<?php
namespace CI_Lib;
/**
 * Scripts and Styles Class
 */
class Assets {

	private static function asset_version( $relative_path ) {
		$path = CI_PATH . $relative_path;
		return file_exists( $path ) ? filemtime( $path ) : CI_VERSION;
	}

	function __construct() {

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'register' ), 5 );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'register' ), 5 );
		}
	}

	/**
	 * Register our app scripts and styles
	 *
	 * @return void
	 */
	public function register() {
		$this->register_scripts( $this->get_scripts() );
		$this->register_styles( $this->get_styles() );
	}

	/**
	 * Register scripts
	 *
	 * @param  array $scripts
	 *
	 * @return void
	 */
	private function register_scripts( $scripts ) {
		foreach ( $scripts as $handle => $script ) {
			if ( isset( $script['path'] ) && ! file_exists( CI_PATH . $script['path'] ) ) {
				continue;
			}

			$deps          = isset( $script['deps'] ) ? $script['deps'] : false;
			$in_footer     = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
			$version       = isset( $script['version'] ) ? $script['version'] : CI_VERSION;
			$inline_script = isset( $script['inline'] ) ? $script['inline'] : false;

			wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );

			if ( $inline_script ) {
				wp_add_inline_script( $handle, $inline_script, 'before' );
			}
		}
	}

	/**
	 * Register styles
	 *
	 * @param  array $styles
	 *
	 * @return void
	 */
	public function register_styles( $styles ) {
		foreach ( $styles as $handle => $style ) {
			if ( isset( $style['path'] ) && ! file_exists( CI_PATH . $style['path'] ) ) {
				continue;
			}

			$deps = isset( $style['deps'] ) ? $style['deps'] : false;

			wp_register_style( $handle, $style['src'], $deps, CI_VERSION );
		}
	}

	/**
	 * Get all registered scripts
	 *
	 * @return array
	 */
	public static function get_scripts() {
		$scripts = array(
			'ci-image-url-js' => array(
				'path'      => '/assets/js/ci-image-url.js',
				'src'       => CI_ASSETS . '/js/ci-image-url.js',
				'version'   => self::asset_version( '/assets/js/ci-image-url.js' ),
				'in_footer' => true,
			),
			'ci-front-js' => array(
				'path'      => '/assets/js/script.js',
				'src'       => CI_ASSETS . '/js/script.js',
				'version'   => self::asset_version( '/assets/js/script.js' ),
				'deps'      => array( 'ci-image-url-js' ),
				'in_footer' => true,
			),
			'ci-search-js' => array(
				'path'      => '/assets/js/search.js',
				'src'       => CI_ASSETS . '/js/search.js',
				'version'   => self::asset_version( '/assets/js/search.js' ),
				'in_footer' => true,
			),
			'ci-register-js' => array(
				'path'      => '/assets/js/register.js',
				'src'       => CI_ASSETS . '/js/register.js',
				'version'   => self::asset_version( '/assets/js/register.js' ),
				'in_footer' => true,
			),
			'ci-login-js' => array(
				'path'      => '/assets/js/login.js',
				'src'       => CI_ASSETS . '/js/login.js',
				'version'   => self::asset_version( '/assets/js/login.js' ),
				'in_footer' => true,
			),
			'ci-bootstrap-js' => array(
				'path'      => '/assets/js/bootstrap.bundle.min.js',
				'src'       => CI_ASSETS . '/js/bootstrap.bundle.min.js',
				'version'   => self::asset_version( '/assets/js/bootstrap.bundle.min.js' ),
				'in_footer' => false,
			),
			'ci-swal-js' => array(
				'path'      => '/assets/js/swal.min.js',
				'src'       => CI_ASSETS . '/js/swal.min.js',
				'version'   => self::asset_version( '/assets/js/swal.min.js' ),
				'in_footer' => true,
			),
			'ci-home-js' => array(
				'path'      => '/assets/js/home.js',
				'src'       => CI_ASSETS . '/js/home.js',
				'version'   => self::asset_version( '/assets/js/home.js' ),
				'deps'      => array( 'ci-image-url-js' ),
				'in_footer' => true,
			),
		);

		return $scripts;
	}

		/**
	 * Get registered styles
	 *
	 * @return array
	 */
	public function get_styles() {

		$styles = array(
			'ci-style-css' => array(
				'path'    => '/assets/css/style.css',
				'src'     => CI_ASSETS . '/css/style.css',
				'version' => self::asset_version( '/assets/css/style.css' ),
			),
			'ci-bootstrap-css' => array(
				'path'    => '/assets/css/bootstrap.min.css',
				'src'     => CI_ASSETS . '/css/bootstrap.min.css',
				'version' => self::asset_version( '/assets/css/bootstrap.min.css' ),
			),
		);

		return $styles;
	}

}

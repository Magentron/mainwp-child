<?php

namespace MainWP\Child;

// phpcs:disable WordPress.WP.AlternativeFunctions --  to use external code, third party credit.

class MainWP_Child_Misc {

	protected static $instance = null;

	/**
	 * Method get_class_name()
	 *
	 * Get Class Name.
	 *
	 * @return object
	 */
	public static function get_class_name() {
		return __CLASS__;
	}

	public function __construct() {
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_site_icon() {
		$information = array();
		$url         = $this->get_favicon( true );
		if ( ! empty( $url ) ) {
			$information['faviIconUrl'] = $url;
		}
		MainWP_Helper::write( $information );
	}

	public function get_favicon( $parse_page = false ) {

		$favi_url = '';
		$favi     = '';
		$site_url = get_option( 'siteurl' );
		if ( substr( $site_url, - 1 ) != '/' ) {
			$site_url .= '/';
		}

		if ( function_exists( '\get_site_icon_url' ) && \has_site_icon() ) {
			$favi     = \get_site_icon_url();
			$favi_url = $favi;
		}

		if ( empty( $favi ) ) {
			if ( file_exists( ABSPATH . 'favicon.ico' ) ) {
				$favi = 'favicon.ico';
			} elseif ( file_exists( ABSPATH . 'favicon.png' ) ) {
				$favi = 'favicon.png';
			}

			if ( ! empty( $favi ) ) {
				$favi_url = $site_url . $favi;
			}
		}

		if ( $parse_page ) {
			// try to parse page.
			if ( empty( $favi_url ) ) {
				$favi_url = $this->try_to_parse_favicon( $site_url );
			}

			if ( ! empty( $favi_url ) ) {
				return $favi_url;
			} else {
				return false;
			}
		} else {
			return $favi_url;
		}
	}

	private function try_to_parse_favicon( $site_url ) {
		$request = wp_remote_get( $site_url, array( 'timeout' => 50 ) );
		$favi    = '';
		if ( is_array( $request ) && isset( $request['body'] ) ) {
			$preg_str1 = '/(<link\s+(?:[^\>]*)(?:rel="shortcut\s+icon"\s*)(?:[^>]*)?href="([^"]+)"(?:[^>]*)?>)/is';
			$preg_str2 = '/(<link\s+(?:[^\>]*)(?:rel="(?:shortcut\s+)?icon"\s*)(?:[^>]*)?href="([^"]+)"(?:[^>]*)?>)/is';

			if ( preg_match( $preg_str1, $request['body'], $matches ) ) {
				$favi = $matches[2];
			} elseif ( preg_match( $preg_str2, $request['body'], $matches ) ) {
				$favi = $matches[2];
			}
		}
		$favi_url = '';
		if ( ! empty( $favi ) ) {
			if ( false === strpos( $favi, 'http' ) ) {
				if ( 0 === strpos( $favi, '//' ) ) {
					if ( 0 === strpos( $site_url, 'https' ) ) {
						$favi_url = 'https:' . $favi;
					} else {
						$favi_url = 'http:' . $favi;
					}
				} else {
					$favi_url = $site_url . $favi;
				}
			} else {
				$favi_url = $favi;
			}
		}
		return $favi_url;
	}

	public function get_security_stats() {
		$information = array();

		$information['listing']             = ( ! MainWP_Security::prevent_listing_ok() ? 'N' : 'Y' );
		$information['wp_version']          = ( ! MainWP_Security::remove_wp_version_ok() ? 'N' : 'Y' );
		$information['rsd']                 = ( ! MainWP_Security::remove_rsd_ok() ? 'N' : 'Y' );
		$information['wlw']                 = ( ! MainWP_Security::remove_wlw_ok() ? 'N' : 'Y' );
		$information['db_reporting']        = ( ! MainWP_Security::remove_database_reporting_ok() ? 'N' : 'Y' );
		$information['php_reporting']       = ( ! MainWP_Security::remove_php_reporting_ok() ? 'N' : 'Y' );
		$information['versions']            = ( ! MainWP_Security::remove_scripts_version_ok() || ! MainWP_Security::remove_styles_version_ok() || ! MainWP_Security::remove_generator_version_ok() ? 'N' : 'Y' );
		$information['registered_versions'] = ( MainWP_Security::remove_registered_versions_ok() ? 'Y' : 'N' );
		$information['admin']               = ( MainWP_Security::admin_user_ok() ? 'Y' : 'N' );
		$information['readme']              = ( MainWP_Security::remove_readme_ok() ? 'Y' : 'N' );

		MainWP_Helper::write( $information );
	}


	public function do_security_fix() {
		$sync = false;
		if ( 'all' === $_POST['feature'] ) {
			$sync = true;
		}

		$information = array();
		$security    = get_option( 'mainwp_security' );
		if ( ! is_array( $security ) ) {
			$security = array();
		}

		if ( 'all' === $_POST['feature'] || 'listing' === $_POST['feature'] ) {
			MainWP_Security::prevent_listing();
			$information['listing'] = ( ! MainWP_Security::prevent_listing_ok() ? 'N' : 'Y' );
		}

		if ( 'all' === $_POST['feature'] || 'wp_version' === $_POST['feature'] ) {
			$security['wp_version'] = true;
			MainWP_Security::remove_wp_version( true );
			$information['wp_version'] = ( ! MainWP_Security::remove_wp_version_ok() ? 'N' : 'Y' );
		}

		if ( 'all' === $_POST['feature'] || 'rsd' === $_POST['feature'] ) {
			$security['rsd'] = true;
			MainWP_Security::remove_rsd( true );
			$information['rsd'] = ( ! MainWP_Security::remove_rsd_ok() ? 'N' : 'Y' );
		}

		if ( 'all' === $_POST['feature'] || 'wlw' === $_POST['feature'] ) {
			$security['wlw'] = true;
			MainWP_Security::remove_wlw( true );
			$information['wlw'] = ( ! MainWP_Security::remove_wlw_ok() ? 'N' : 'Y' );
		}

		if ( 'all' === $_POST['feature'] || 'db_reporting' === $_POST['feature'] ) {
			MainWP_Security::remove_database_reporting();
			$information['db_reporting'] = ( ! MainWP_Security::remove_database_reporting_ok() ? 'N' : 'Y' );
		}

		if ( 'all' === $_POST['feature'] || 'php_reporting' === $_POST['feature'] ) {
			$security['php_reporting'] = true;
			MainWP_Security::remove_php_reporting( true );
			$information['php_reporting'] = ( ! MainWP_Security::remove_php_reporting_ok() ? 'N' : 'Y' );
		}

		if ( 'all' === $_POST['feature'] || 'versions' === $_POST['feature'] ) {
			$security['scripts_version']   = true;
			$security['styles_version']    = true;
			$security['generator_version'] = true;
			MainWP_Security::remove_generator_version( true );
			$information['versions'] = 'Y';
		}

		if ( 'all' === $_POST['feature'] || 'registered_versions' === $_POST['feature'] ) {
			$security['registered_versions']    = true;
			$information['registered_versions'] = 'Y';
		}

		if ( 'all' === $_POST['feature'] || 'admin' === $_POST['feature'] ) {
			$information['admin'] = ( ! MainWP_Security::admin_user_ok() ? 'N' : 'Y' );
		}

		if ( 'all' === $_POST['feature'] || 'readme' === $_POST['feature'] ) {
			$security['readme'] = true;
			MainWP_Security::remove_readme( true );
			$information['readme'] = ( MainWP_Security::remove_readme_ok() ? 'Y' : 'N' );
		}

		MainWP_Helper::update_option( 'mainwp_security', $security, 'yes' );

		if ( $sync ) {
			$information['sync'] = MainWP_Child_Stats::get_instance()->get_site_stats( array(), false );
		}
		MainWP_Helper::write( $information );
	}

	public function do_security_un_fix() {
		$information = array();

		$sync = false;
		if ( 'all' === $_POST['feature'] ) {
			$sync = true;
		}

		$security = get_option( 'mainwp_security' );

		if ( 'all' === $_POST['feature'] || 'wp_version' === $_POST['feature'] ) {
			$security['wp_version']    = false;
			$information['wp_version'] = 'N';
		}

		if ( 'all' === $_POST['feature'] || 'rsd' === $_POST['feature'] ) {
			$security['rsd']    = false;
			$information['rsd'] = 'N';
		}

		if ( 'all' === $_POST['feature'] || 'wlw' === $_POST['feature'] ) {
			$security['wlw']    = false;
			$information['wlw'] = 'N';
		}

		if ( 'all' === $_POST['feature'] || 'php_reporting' === $_POST['feature'] ) {
			$security['php_reporting']    = false;
			$information['php_reporting'] = 'N';
		}

		if ( 'all' === $_POST['feature'] || 'versions' === $_POST['feature'] ) {
			$security['scripts_version']   = false;
			$security['styles_version']    = false;
			$security['generator_version'] = false;
			$information['versions']       = 'N';
		}

		if ( 'all' === $_POST['feature'] || 'registered_versions' === $_POST['feature'] ) {
			$security['registered_versions']    = false;
			$information['registered_versions'] = 'N';
		}
		if ( 'all' === $_POST['feature'] || 'readme' === $_POST['feature'] ) {
			$security['readme']    = false;
			$information['readme'] = MainWP_Security::remove_readme_ok();
		}

		MainWP_Helper::update_option( 'mainwp_security', $security, 'yes' );

		if ( $sync ) {
			$information['sync'] = MainWP_Child_Stats::get_instance()->get_site_stats( array(), false );
		}

		MainWP_Helper::write( $information );
	}

	public function settings_tools() {
		if ( isset( $_POST['action'] ) ) {
			switch ( $_POST['action'] ) {
				case 'force_destroy_sessions':
					if ( 0 === get_current_user_id() ) {
						MainWP_Helper::write( array( 'error' => __( 'Cannot get user_id', 'mainwp-child' ) ) );
					}

					wp_destroy_all_sessions();

					$sessions = wp_get_all_sessions();

					if ( empty( $sessions ) ) {
						MainWP_Helper::write( array( 'success' => 1 ) );
					} else {
						MainWP_Helper::write( array( 'error' => __( 'Cannot destroy sessions', 'mainwp-child' ) ) );
					}
					break;

				default:
					MainWP_Helper::write( array( 'error' => __( 'Invalid action', 'mainwp-child' ) ) );
			}
		} else {
			MainWP_Helper::write( array( 'error' => __( 'Missing action', 'mainwp-child' ) ) );
		}
	}

	public function uploader_action() {
		$file_url    = base64_decode( $_POST['url'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- base64_encode function is used for http encode compatible..
		$path        = $_POST['path'];
		$filename    = $_POST['filename'];
		$information = array();

		if ( empty( $file_url ) || empty( $path ) ) {
			MainWP_Helper::write( $information );

			return;
		}

		if ( strpos( $path, 'wp-content' ) === 0 ) {
			$path = basename( WP_CONTENT_DIR ) . substr( $path, 10 );
		} elseif ( strpos( $path, 'wp-includes' ) === 0 ) {
			$path = WPINC . substr( $path, 11 );
		}

		if ( '/' === $path ) {
			$dir = ABSPATH;
		} else {
			$path = str_replace( ' ', '-', $path );
			$path = str_replace( '.', '-', $path );
			$dir  = ABSPATH . $path;
		}

		if ( ! file_exists( $dir ) ) {
			if ( false === mkdir( $dir, 0777, true ) ) {
				$information['error'] = 'ERRORCREATEDIR';
				MainWP_Helper::write( $information );

				return;
			}
		}

		try {
			$upload = $this->uploader_upload_file( $file_url, $dir, $filename );
			if ( null !== $upload ) {
				$information['success'] = true;
			}
		} catch ( \Exception $e ) {
			$information['error'] = $e->getMessage();
		}
		MainWP_Helper::write( $information );
	}


	public function uploader_upload_file( $file_url, $path, $file_name ) {
		// to fix uploader extension rename htaccess file issue.
		if ( '.htaccess' != $file_name && '.htpasswd' != $file_name ) {
			$file_name = sanitize_file_name( $file_name );
		}

		$full_file_name = $path . DIRECTORY_SEPARATOR . $file_name;

		$response = wp_remote_get(
			$file_url,
			array(
				'timeout'  => 10 * 60 * 60,
				'stream'   => true,
				'filename' => $full_file_name,
			)
		);

		if ( is_wp_error( $response ) ) {
			unlink( $full_file_name );
			throw new \Exception( 'Error: ' . $response->get_error_message() );
		}

		if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			unlink( $full_file_name );
			throw new \Exception( 'Error 404: ' . trim( wp_remote_retrieve_response_message( $response ) ) );
		}
		if ( '.phpfile.txt' === substr( $file_name, - 12 ) ) {
			$new_file_name = substr( $file_name, 0, - 12 ) . '.php';
			$new_file_name = $path . DIRECTORY_SEPARATOR . $new_file_name;
			$moved         = rename( $full_file_name, $new_file_name );
			if ( $moved ) {
				return array( 'path' => $new_file_name );
			} else {
				unlink( $full_file_name );
				throw new \Exception( 'Error: Copy file.' );
			}
		}

		return array( 'path' => $full_file_name );
	}

	public function code_snippet() {

		$action = $_POST['action'];
		$type   = isset( $_POST['type'] ) ? $_POST['type'] : '';
		$slug   = isset( $_POST['slug'] ) ? $_POST['slug'] : '';

		$snippets = get_option( 'mainwp_ext_code_snippets' );

		if ( ! is_array( $snippets ) ) {
			$snippets = array();
		}

		if ( 'run_snippet' === $action || 'save_snippet' === $action ) {
			if ( ! isset( $_POST['code'] ) ) {
				MainWP_Helper::write( array( 'status' => 'FAIL' ) );
			}
		}

		$code = isset( $_POST['code'] ) ? stripslashes( $_POST['code'] ) : '';

		$information = array();
		if ( 'run_snippet' === $action ) {
			$information = MainWP_Utility::execute_snippet( $code );
		} elseif ( 'save_snippet' === $action ) {
			$information = $this->snippet_save_snippet( $slug, $type, $code, $snippets );
		} elseif ( 'delete_snippet' === $action ) {
			$information = $this->snippet_delete_snippet( $slug, $type, $snippets );
		}

		if ( empty( $information ) ) {
			$information = array( 'status' => 'FAIL' );
		}

		MainWP_Helper::write( $information );
	}

	private function snippet_save_snippet( $slug, $type, $code, $snippets ) {
		$return = array();
		if ( 'C' === $type ) { // save into wp-config file.
			if ( false !== $this->snippet_update_wp_config( 'save', $slug, $code ) ) {
				$return['status'] = 'SUCCESS';
			}
		} else {
			$snippets[ $slug ] = $code;
			if ( MainWP_Helper::update_option( 'mainwp_ext_code_snippets', $snippets ) ) {
				$return['status'] = 'SUCCESS';
			}
		}
		MainWP_Helper::update_option( 'mainwp_ext_snippets_enabled', true, 'yes' );
		return $return;
	}

	private function snippet_delete_snippet( $slug, $type, $snippets ) {
		$return = array();
		if ( 'C' === $type ) { // delete in wp-config file.
			if ( false !== $this->snippet_update_wp_config( 'delete', $slug ) ) {
				$return['status'] = 'SUCCESS';
			}
		} else {
			if ( isset( $snippets[ $slug ] ) ) {
				unset( $snippets[ $slug ] );
				if ( MainWP_Helper::update_option( 'mainwp_ext_code_snippets', $snippets ) ) {
					$return['status'] = 'SUCCESS';
				}
			} else {
				$return['status'] = 'SUCCESS';
			}
		}
		return $return;
	}

	public function snippet_update_wp_config( $action, $slug, $code = '' ) {

		$config_file = '';
		if ( file_exists( ABSPATH . 'wp-config.php' ) ) {
			// The config file resides in ABSPATH.
			$config_file = ABSPATH . 'wp-config.php';
		} elseif ( file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {
			// The config file resides one level above ABSPATH but is not part of another install.
			$config_file = dirname( ABSPATH ) . '/wp-config.php';
		}

		if ( ! empty( $config_file ) ) {
			$wpConfig = file_get_contents( $config_file );

			if ( 'delete' === $action ) {
				$wpConfig = preg_replace( '/' . PHP_EOL . '{1,2}\/\*\*\*snippet_' . $slug . '\*\*\*\/(.*)\/\*\*\*end_' . $slug . '\*\*\*\/' . PHP_EOL . '/is', '', $wpConfig );
			} elseif ( 'save' === $action ) {
				$wpConfig = preg_replace( '/(\$table_prefix *= *[\'"][^\'|^"]*[\'"] *;)/is', '${1}' . PHP_EOL . PHP_EOL . '/***snippet_' . $slug . '***/' . PHP_EOL . $code . PHP_EOL . '/***end_' . $slug . '***/' . PHP_EOL, $wpConfig );
			}
			file_put_contents( $config_file, $wpConfig );
			return true;
		}
		return false;
	}

}
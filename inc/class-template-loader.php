<?php
/**
 * $Desc$
 *
 * @version    $Id$
 * @package    opalmembership
 * @author     Opal  Team <info@wpopal.com >
 * @copyright  Copyright (C) 2016 wpopal.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @website  http://www.wpopal.com
 * @support  http://www.wpopal.com/support/forum.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * @class Opalmembership_Template_Loader
 *
 * @version 1.0
 */
class Opalmembership_Template_Loader {

	/**
	 * Initialize template loader
	 *
	 * @access public
	 * @return void
	 */
	public static function init() {
		add_filter( 'template_include', array( __CLASS__, 'templates' ) );
	}

	/**
	 * Default templates
	 *
	 * @access public
	 * @param $template
	 * @return string
	 * @throws Exception
	 */
	public static function templates( $template ) {
		$post_type = get_post_type();
		$custom_post_types = array( 'membership_packages'  );

		if ( in_array( $post_type, $custom_post_types ) ) {
			if ( is_archive() ) {
				if ( is_tax() ) {
					return self::locate( 'taxonomy-' . get_query_var( 'taxonomy' ) );
				}

				return self::locate( 'archive-' . $post_type );
			}

			if ( is_single() ) {
				return self::locate( 'single-' . $post_type );
			}
		}

		return $template;
	}

	/**
	 * Gets template path
	 *
	 * @access public
	 * @param $name
	 * @param $plugin_dir
	 * @return string
	 */
	public static function locate( $name, $plugin_dir = OPALMEMBERSHIP_PLUGIN_DIR ) {
		$template = '';

		// Current theme base dir
		if ( ! empty( $name ) ) {
			$template = locate_template( "{$name}.php" );
		}

		// Child theme
		if ( ! $template && ! empty( $name ) && file_exists( get_stylesheet_directory() . "/templates/{$name}.php" ) ) {
			$template = get_stylesheet_directory() . "/templates/{$name}.php";
		}

		// Original theme
		if ( ! $template && ! empty( $name ) && file_exists( get_template_directory() . "/templates/{$name}.php" ) ) {
			$template = get_template_directory() . "/templates/{$name}.php";
		}

		// Plugin
		if ( ! $template && ! empty( $name ) && file_exists( $plugin_dir . "/templates/{$name}.php" ) ) {
			$template = $plugin_dir . "/templates/{$name}.php";
		}

		// Nothing found
		if ( empty( $template ) ) {
			throw new Exception( "Template /templates/{$name}.php in plugin dir {$plugin_dir} not found." );
		}

		return $template;
	}

	/**
	 * Loads template content
	 *
	 * @param string $name
	 * @param array  $args
	 * @param string $plugin_dir
	 * @return string
	 */
	public static function get_template_part( $name, $args = array(), $plugin_dir = '' ) {
		if ( is_array( $args ) && count( $args ) > 0 ) {
			extract( $args, EXTR_SKIP );
		}

		if ( ! $plugin_dir ) {
			$plugin_dir = OPALMEMBERSHIP_PLUGIN_DIR;
		}

		$path = self::locate( $name, $plugin_dir );
		ob_start();
		include $path;
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}
}

Opalmembership_Template_Loader::init();

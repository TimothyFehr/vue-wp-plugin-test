<?php
/*
Plugin Name: VueWPPluginTest
Description: Test if a vue js application can be used as a Wordpress Plugin
Version: 0.1
Author: Timothy Fehr
Author URI: https://fehrcoding.ch
*/

// Shortcode Definition
// [vue-wp-plugin-test id="vue-wp-plugin-test"]

require_once 'libs/composer/autoload.php';

if( ! class_exists( 'VueWPPluginTest' ) ) {
	class VueWPPluginTest {

		private $shortcode_name = 'vue-wp-plugin-test';

		public function register() {
			add_shortcode( $this->shortcode_name, [$this, 'shortcode'] );
			add_action( 'wp_enqueue_scripts', [$this, 'scripts'] );
		}
		public function shortcode( $atts ) {
			$vue_atts = esc_attr( json_encode( [
				'id' => sanitize_title_with_dashes( $atts['id'], '', 'save' )
			] ) );
			return preg_replace('/{{pluginBasePath}}/', plugin_dir_url( __FILE__ ), file_get_contents(plugins_url('/index.html',__FILE__ )));
		}

		// Only enqueue scripts if we're displaying a post that contains the shortcode
		public function scripts() {
		    global $post;
		    if( has_shortcode( $post->post_content, $this->shortcode_name ) ) {
		        wp_enqueue_script( 'vue', plugin_dir_url( __FILE__ ) . 'libs/vue/vue.min.js', [], '2.6.10' );
		    }
		}
	}
	(new VueWPPluginTest())->register();
}


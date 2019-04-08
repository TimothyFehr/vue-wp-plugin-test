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

//$configuration = include('config.php');

class VueWPPluginTest {
    private $shortcode_name = 'vue-wp-plugin-test';
    private $tablename = 'helloWorldDB';

    // Put all your add_action, add_shortcode, add_filter functions in __construct()
    // For the callback name, use this: array($this,'<function name>')
    // <function name> is the name of the function within this class, so need not be globally unique
    // Some sample commonly used functions are included below
    public function __construct() {

        if (is_admin()) {
            register_activation_hook(__FILE__, array(&$this, 'activate'));
        }

        // Add Javascript and CSS for admin screens
        add_action('admin_enqueue_scripts', array($this,'enqueueAdmin'));

        // Add Javascript and CSS for front-end display
        add_action('wp_enqueue_scripts', array($this,'enqueue'));
    }

    /* ENQUEUE SCRIPTS AND STYLES */
    // This is an example of enqueuing a Javascript file and a CSS file for use on the editor
    public function enqueueAdmin() {
        // These two lines allow you to only load the files on the relevant screen, in this case, the editor for a "books" custom post type
        $screen = get_current_screen();
        if (!($screen->base == 'post' && $screen->post_type == 'books')) return;

        // Actual enqueues, note the files are in the js and css folders
        // For scripts, make sure you are including the relevant dependencies (jquery in this case)
        wp_enqueue_script('very-descriptive-name', plugins_url('js/books-post-editor.js', __FILE__), array('jquery'), '1.0', true);
        wp_enqueue_style('very-exciting-name', plugins_url('css/books-post-editor.css', __FILE__), null, '1.0');
    }

    // This is enqueuing a vue application for use on the front end display
    public function enqueue() {
        add_shortcode($this->shortcode_name, [$this, 'shortcode']);

        global $post;
        if (has_shortcode($post->post_content, $this->shortcode_name)) {
            wp_enqueue_script('vue', plugin_dir_url(__FILE__) . 'libs/vue/vue.min.js', [], '2.6.10');
        }

    }

    public function shortcode($atts)
    {
        $vue_atts = esc_attr(json_encode([
            'id' => sanitize_title_with_dashes($atts['id'], '', 'save')
        ]));
        return preg_replace('/{{pluginBasePath}}/', plugin_dir_url(__FILE__), file_get_contents(plugins_url('/index.html', __FILE__)));
    }

    public function activate() {
        global $wpdb;
        $table = $wpdb->prefix . $this->tablename;
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table ("
            ."id int AUTO_INCREMENT,"
            ."result int NOT NULL,"
            ."PRIMARY KEY id (id)"
            .") $charset;";
          require_once(ABSPATH.'wp-admin/includes/upgrade.php');
          dbDelta($sql);

        // Register the uninstall hook only on activation
        register_uninstall_hook(__FILE__, array(&$this, 'uninstall'));
    }

    public function uninstall() {;
        global $wpdb;
        $table = $wpdb->prefix . $this->tablename;
        $sql = "DROP TABLE IF EXISTS $table";
        $wpdb->query($sql);
    }
}

// If you need this available beyond our initial creation, you can create it as a global
global $vueWPPluginTest;

// Create an instance of our class to kick off the whole thing
$vueWPPluginTest = new VueWPPluginTest();




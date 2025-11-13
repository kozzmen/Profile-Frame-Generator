<?php
/**
 * Plugin Name: Profile Frame Generator
 * Plugin URI: https://velev.dev/profile-frame-generator
 * Description: Allow users to upload photos and position them behind custom PNG frames, then download or save the result.
 * Version: 1.2.0
 * Author: Stefan velev
 * Author URI: https://velev.dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: profile-frame-generator
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PFG_VERSION', '1.0.0');
define('PFG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PFG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PFG_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Profile_Frame_Generator {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->includes();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once PFG_PLUGIN_DIR . 'includes/admin.php';
        require_once PFG_PLUGIN_DIR . 'includes/frontend.php';
        
        // Initialize components
        PFG_Admin::get_instance();
        PFG_Frontend::get_instance();
    }
    
    /**
     * Load plugin textdomain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'profile-frame-generator',
            false,
            dirname(PFG_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create default options if they don't exist
        if (!get_option('pfg_frames')) {
            add_option('pfg_frames', array());
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

/**
 * Initialize the plugin
 */
function pfg_init() {
    return Profile_Frame_Generator::get_instance();
}

// Start the plugin
pfg_init();

<?php
/**
 * Frontend functionality for Profile Frame Generator
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class PFG_Frontend {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_shortcode('profile_frame', array($this, 'render_shortcode'));
        add_action('wp_ajax_pfg_save_to_library', array($this, 'ajax_save_to_library'));
        add_action('wp_ajax_nopriv_pfg_save_to_library', array($this, 'ajax_save_to_library'));
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0
        ), $atts, 'profile_frame');
        
        $frame_id = intval($atts['id']);
        
        if (!$frame_id) {
            return '<p>' . __('Please specify a frame ID.', 'profile-frame-generator') . '</p>';
        }
        
        $frames = get_option('pfg_frames', array());
        
        if (!isset($frames[$frame_id])) {
            return '<p>' . __('Frame not found.', 'profile-frame-generator') . '</p>';
        }
        
        $frame_data = $frames[$frame_id];
        
        // Enqueue assets
        $this->enqueue_assets();
        
        // Pass data to JavaScript
        wp_localize_script('pfg-frontend-js', 'pfgData', array(
            'frameUrl' => $frame_data['url'],
            'frameId' => $frame_id,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pfg_save_image'),
            'isLoggedIn' => is_user_logged_in(),
            'strings' => array(
                'choosePhoto' => __('Choose Photo', 'profile-frame-generator'),
                'dragToMove' => __('Drag to move, use slider to zoom', 'profile-frame-generator'),
                'generatePreview' => __('Generate Preview', 'profile-frame-generator'),
                'downloadImage' => __('Download Image', 'profile-frame-generator'),
                'saveToLibrary' => __('Save to Library', 'profile-frame-generator'),
                'uploading' => __('Uploading...', 'profile-frame-generator'),
                'error' => __('Error', 'profile-frame-generator'),
                'success' => __('Image saved to your library!', 'profile-frame-generator')
            )
        ));
        
        // Load template
        ob_start();
        include PFG_PLUGIN_DIR . 'templates/generator-template.php';
        return ob_get_clean();
    }
    
    /**
     * Enqueue frontend assets
     */
    private function enqueue_assets() {
        wp_enqueue_style(
            'pfg-frontend-css',
            PFG_PLUGIN_URL . 'assets/css/profile-frame-generator.css',
            array(),
            PFG_VERSION
        );
        
        wp_enqueue_script(
            'pfg-frontend-js',
            PFG_PLUGIN_URL . 'assets/js/profile-frame-generator.js',
            array('jquery'),
            PFG_VERSION,
            true
        );
    }
    
    /**
     * AJAX: Save image to library
     */
    public function ajax_save_to_library() {
        check_ajax_referer('pfg_save_image', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in', 'profile-frame-generator')));
        }
        
        if (!isset($_POST['image_data'])) {
            wp_send_json_error(array('message' => __('No image data provided', 'profile-frame-generator')));
        }
        
        $image_data = $_POST['image_data'];
        $image_data = str_replace('data:image/png;base64,', '', $image_data);
        $image_data = str_replace(' ', '+', $image_data);
        $decoded = base64_decode($image_data);
        
        if (!$decoded) {
            wp_send_json_error(array('message' => __('Invalid image data', 'profile-frame-generator')));
        }
        
        $upload_dir = wp_upload_dir();
        $filename = 'profile-frame-' . time() . '.png';
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        file_put_contents($file_path, $decoded);
        
        $file_type = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $file_type['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        $attachment_id = wp_insert_attachment($attachment, $file_path);
        
        if (!is_wp_error($attachment_id)) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
            
            wp_send_json_success(array(
                'message' => __('Image saved successfully', 'profile-frame-generator'),
                'attachment_id' => $attachment_id,
                'url' => wp_get_attachment_url($attachment_id)
            ));
        } else {
            wp_send_json_error(array('message' => __('Error saving image', 'profile-frame-generator')));
        }
    }
}

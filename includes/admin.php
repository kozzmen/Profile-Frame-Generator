<?php
/**
 * Admin functionality for Profile Frame Generator
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class PFG_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_pfg_upload_frame', array($this, 'ajax_upload_frame'));
        add_action('wp_ajax_pfg_delete_frame', array($this, 'ajax_delete_frame'));
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_options_page(
            __('Profile Frame Generator', 'profile-frame-generator'),
            __('Profile Frame Generator', 'profile-frame-generator'),
            'manage_options',
            'profile-frame-generator',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ('settings_page_profile-frame-generator' !== $hook) {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_style(
            'pfg-admin-css',
            PFG_PLUGIN_URL . 'assets/css/profile-frame-generator.css',
            array(),
            PFG_VERSION
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $frames = get_option('pfg_frames', array());
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="pfg-admin-container">
                <div class="pfg-upload-section">
                    <h2><?php _e('Upload New Frame', 'profile-frame-generator'); ?></h2>
                    <p><?php _e('Upload a PNG image with transparent areas where user photos will appear.', 'profile-frame-generator'); ?></p>
                    <button type="button" class="button button-primary pfg-upload-frame-btn">
                        <?php _e('Upload Frame', 'profile-frame-generator'); ?>
                    </button>
                </div>
                
                <div class="pfg-frames-list">
                    <h2><?php _e('Uploaded Frames', 'profile-frame-generator'); ?></h2>
                    <?php if (empty($frames)) : ?>
                        <p><?php _e('No frames uploaded yet.', 'profile-frame-generator'); ?></p>
                    <?php else : ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Preview', 'profile-frame-generator'); ?></th>
                                    <th><?php _e('Frame ID', 'profile-frame-generator'); ?></th>
                                    <th><?php _e('Shortcode', 'profile-frame-generator'); ?></th>
                                    <th><?php _e('Actions', 'profile-frame-generator'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($frames as $frame_id => $frame_data) : ?>
                                    <tr data-frame-id="<?php echo esc_attr($frame_id); ?>">
                                        <td>
                                            <img src="<?php echo esc_url($frame_data['url']); ?>" 
                                                 alt="Frame Preview" 
                                                 style="max-width: 100px; height: auto;">
                                        </td>
                                        <td><?php echo esc_html($frame_id); ?></td>
                                        <td>
                                            <code>[profile_frame id="<?php echo esc_attr($frame_id); ?>"]</code>
                                            <button type="button" class="button button-small pfg-copy-shortcode" 
                                                    data-shortcode='[profile_frame id="<?php echo esc_attr($frame_id); ?>"]'>
                                                <?php _e('Copy', 'profile-frame-generator'); ?>
                                            </button>
                                        </td>
                                        <td>
                                            <button type="button" class="button button-small pfg-delete-frame" 
                                                    data-frame-id="<?php echo esc_attr($frame_id); ?>">
                                                <?php _e('Delete', 'profile-frame-generator'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Upload frame
            var mediaUploader;
            $('.pfg-upload-frame-btn').on('click', function(e) {
                e.preventDefault();
                
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                mediaUploader = wp.media({
                    title: '<?php _e('Choose Frame Image', 'profile-frame-generator'); ?>',
                    button: {
                        text: '<?php _e('Use this image', 'profile-frame-generator'); ?>'
                    },
                    library: {
                        type: 'image/png'
                    },
                    multiple: false
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    
                    $.post(ajaxurl, {
                        action: 'pfg_upload_frame',
                        attachment_id: attachment.id,
                        attachment_url: attachment.url,
                        nonce: '<?php echo wp_create_nonce('pfg_upload_frame'); ?>'
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message || '<?php _e('Error uploading frame', 'profile-frame-generator'); ?>');
                        }
                    });
                });
                
                mediaUploader.open();
            });
            
            // Copy shortcode
            $('.pfg-copy-shortcode').on('click', function() {
                var shortcode = $(this).data('shortcode');
                navigator.clipboard.writeText(shortcode).then(function() {
                    alert('<?php _e('Shortcode copied!', 'profile-frame-generator'); ?>');
                });
            });
            
            // Delete frame
            $('.pfg-delete-frame').on('click', function() {
                if (!confirm('<?php _e('Are you sure you want to delete this frame?', 'profile-frame-generator'); ?>')) {
                    return;
                }
                
                var frameId = $(this).data('frame-id');
                var $row = $(this).closest('tr');
                
                $.post(ajaxurl, {
                    action: 'pfg_delete_frame',
                    frame_id: frameId,
                    nonce: '<?php echo wp_create_nonce('pfg_delete_frame'); ?>'
                }, function(response) {
                    if (response.success) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert(response.data.message || '<?php _e('Error deleting frame', 'profile-frame-generator'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX: Upload frame
     */
    public function ajax_upload_frame() {
        check_ajax_referer('pfg_upload_frame', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'profile-frame-generator')));
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        $attachment_url = esc_url_raw($_POST['attachment_url']);
        
        if (!$attachment_id || !$attachment_url) {
            wp_send_json_error(array('message' => __('Invalid attachment', 'profile-frame-generator')));
        }
        
        $frames = get_option('pfg_frames', array());
        $frame_id = $attachment_id;
        
        $frames[$frame_id] = array(
            'id' => $frame_id,
            'url' => $attachment_url,
            'uploaded' => current_time('mysql')
        );
        
        update_option('pfg_frames', $frames);
        
        wp_send_json_success(array(
            'message' => __('Frame uploaded successfully', 'profile-frame-generator'),
            'frame_id' => $frame_id
        ));
    }
    
    /**
     * AJAX: Delete frame
     */
    public function ajax_delete_frame() {
        check_ajax_referer('pfg_delete_frame', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'profile-frame-generator')));
        }
        
        $frame_id = intval($_POST['frame_id']);
        
        $frames = get_option('pfg_frames', array());
        
        if (isset($frames[$frame_id])) {
            unset($frames[$frame_id]);
            update_option('pfg_frames', $frames);
            wp_send_json_success(array('message' => __('Frame deleted', 'profile-frame-generator')));
        } else {
            wp_send_json_error(array('message' => __('Frame not found', 'profile-frame-generator')));
        }
    }
}

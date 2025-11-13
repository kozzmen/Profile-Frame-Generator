<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="pfg-generator-container">
    <div class="pfg-instructions">
        <h3><?php _e('Create Your Framed Profile Picture', 'profile-frame-generator'); ?></h3>
        <p><?php _e('Upload your photo, position it, and download your framed image.', 'profile-frame-generator'); ?></p>
    </div>
    
    <div class="pfg-upload-area">
        <input type="file" id="pfg-photo-upload" accept="image/jpeg,image/png,image/webp" style="display: none;">
        <button type="button" id="pfg-choose-photo" class="pfg-button pfg-button-primary">
            <?php _e('Choose Photo', 'profile-frame-generator'); ?>
        </button>
    </div>
    
    <div class="pfg-editor-area" style="display: none;">
        <div class="pfg-canvas-wrapper">
            <canvas id="pfg-canvas"></canvas>
            <img id="pfg-frame-overlay" src="" alt="Frame" style="display: none;">
        </div>
        
        <div class="pfg-controls">
            <div class="pfg-control-group">
                <label for="pfg-zoom"><?php _e('Zoom:', 'profile-frame-generator'); ?></label>
                <input type="range" id="pfg-zoom" min="10" max="200" value="100" step="1">
                <span id="pfg-zoom-value">100%</span>
            </div>
            
            <p class="pfg-help-text"><?php _e('Drag to move, use slider to zoom', 'profile-frame-generator'); ?></p>
        </div>
        
        <div class="pfg-actions">
            <button type="button" id="pfg-generate" class="pfg-button pfg-button-primary">
                <?php _e('Generate Preview', 'profile-frame-generator'); ?>
            </button>
        </div>
    </div>
    
    <div class="pfg-result-area" style="display: none;">
        <div class="pfg-preview-wrapper">
            <canvas id="pfg-result-canvas"></canvas>
        </div>
        
        <div class="pfg-download-actions">
            <button type="button" id="pfg-download" class="pfg-button pfg-button-success">
                <?php _e('Download Image', 'profile-frame-generator'); ?>
            </button>
            
            <?php if (is_user_logged_in()) : ?>
                <button type="button" id="pfg-save-library" class="pfg-button pfg-button-secondary">
                    <?php _e('Save to Library', 'profile-frame-generator'); ?>
                </button>
            <?php endif; ?>
            
            <button type="button" id="pfg-start-over" class="pfg-button pfg-button-tertiary">
                <?php _e('Start Over', 'profile-frame-generator'); ?>
            </button>
        </div>
        
        <div id="pfg-message" class="pfg-message" style="display: none;"></div>
    </div>
</div>

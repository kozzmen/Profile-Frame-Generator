# Profile Frame Generator for WordPress

A complete WordPress plugin that enables admins to upload decorative PNG frames and allows front-end users to create custom framed profile pictures through an interactive editor.

## 🎯 Features

- **Admin Frame Management**: Upload and manage PNG frames with transparent sections through the WordPress admin panel
- **Auto-Generated Shortcodes**: Each uploaded frame automatically generates a `[profile_frame id="X"]` shortcode
- **Interactive Photo Editor**: Drag, zoom, and position user photos behind frames using HTML5 Canvas
- **Touch & Mouse Support**: Fully responsive with gesture support for mobile and desktop
- **Image Generation**: Composite and download framed images as PNG/JPG
- - **Image Frame**: Works best on frames with ratio 1:1. It might requires some CSS tweaks for images with different aspect ratios
- **Media Library Integration**: Automatically save generated images to WordPress Media Library for logged-in users
- **Secure & Modern**: Built with WordPress best practices, nonce verification, and AJAX/REST API support
- **Translation Ready**: Full i18n support with `__()` and `_e()` functions

## 🚀 Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate through the WordPress admin panel
3. Navigate to Settings → Profile Frame Generator
4. Upload your PNG frames
5. Use the generated shortcode on any page or post

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Modern browser with Canvas support

## 🔧 Usage

Admins upload frames, users upload photos, position them interactively, and download their custom framed profile picture.

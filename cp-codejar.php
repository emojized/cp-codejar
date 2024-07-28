<?php
/*
 * Plugin Name:       codejar Code Editor
 * Plugin URI:        https://github.com/emojized/ace-c9-editor
 * Description:       Replacing the WP/CP Code Editor with the CodeJar Editor
 * Version:           0.1
 * Requires at least: 4.9.15
 * Requires PHP:      7.4
 * Requires CP:       2.2
 * Author:            The emojized Team
 * Author URI:        https://emojized.com
 * License:           GPL v2 and MIT
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
*/

function emojized_codejar_deregister_all_scripts() {
    // Deregister each script
    wp_deregister_script('wp-codemirror');
    wp_deregister_script('csslint');
    wp_deregister_script('esprima');
    wp_deregister_script('jshint');
    wp_deregister_script('jsonlint');
    wp_deregister_script('htmlhint');
    wp_deregister_script('htmlhint-kses');
    wp_deregister_script('code-editor');
    wp_deregister_script('wp-theme-plugin-editor');
}
add_action('admin_enqueue_scripts', 'emojized_codejar_deregister_all_scripts', 100);

function emojized_codejar_admin_script() {
    // Check if we are in the admin area
    if (is_admin()) {
        // Register and enqueue the script

        // WP 6.5 hould have wp_enqueue_script_module

        wp_enqueue_script(
            'codejar-editor', // Handle for the script
            plugins_url('codejar.min.js', __FILE__), // URL to the codejar.min.js script in the plugin directory
            array(), // No dependencies
            null, // Version number
            false // Load in footer
        );
    }
}
// Hook into the admin_enqueue_scripts action
add_action('admin_enqueue_scripts', 'emojized_codejar_admin_script');


add_filter('script_loader_tag', 'cj_type_attribute' , 10, 3);

//Define the callback function like the example given on the link above:
// https://stackoverflow.com/questions/58931144/enqueue-javascript-with-type-module
function cj_type_attribute($tag, $handle, $src) {
    // if not your script, do nothing and return original $tag
    if ( 'codejar-editor' !== $handle ) {
        return $tag;
    }
    // change the script tag by adding type="module" and return it.
    $tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
    return $tag;
}


function emojized_codejar_inline_script_for_plugin_editor() {
    // Get the current screen object
    $current_screen = get_current_screen();

    // Check if we are on the plugin-editor.php or theme-editor.php page
    if ($current_screen->base === 'plugin-editor' || $current_screen->base === 'theme-editor') {
        // Ensure codejar-editor script is enqueued
        wp_enqueue_script('codejar-editor');

        // Enqueue the inline script
        wp_add_inline_script(
            'codejar-editor', // Dependency on codejar-editor
            '
            document.addEventListener("DOMContentLoaded", function() {
                var textarea = document.querySelector("textarea#newcontent"); // Adjust selector as needed
                if (textarea) {
                    // Create a div to replace the textarea
                    var div = document.createElement("div");
                    div.id = "codejar-editor";
                    div.style.width = "100%";
                    div.style.height = "500px"; // Adjust height as needed
                    textarea.parentNode.insertBefore(div, textarea);
                    textarea.style.display = "none"; // Hide the textarea

                    // Initialize CodeJar editor on the div
                    let jar = CodeJar(document.querySelector("#codejar-editor"), highlight);

                    // Sync CodeJar editor content with the textarea
                    jar.onUpdate(function (code) {
                        textarea.value = code;
                    });
                }
            });
            ' // The actual inline script
        );
    }
}
// Hook into admin_enqueue_scripts
add_action('admin_enqueue_scripts', 'emojized_codejar_inline_script_for_plugin_editor');


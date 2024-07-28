<?php
/*
 * Plugin Name:       codejar Code Editor
 * Plugin URI:        https://github.com/emojized/ace-c9-editor
 * Description:       Replacing the WP/CP Code Editor with the CodeJar Editor
 * Version:           1.0
 * Requires at least: 4.9.15
 * Requires PHP:      7.4
 * Requires CP:       2.2
 * Author:            The emojized Team
 * Author URI:        https://emojized.com
 * License:           GPL v2 and BSD
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
        //wp_enqueue_script('codejar-editor');

        ?>
<script type="module">
  import {CodeJar} from '<?php echo plugins_url('cp-codejar/codejar.min.js');?>'
  const editor = document.querySelector('#newcontent')

  const highlight = editor => {
    editor.innerHTML = Prism.highlight(editor.textContent, Prism.languages.javascript, 'javascript')
  }

  const jar = CodeJar(editor, highlight, {
    tab: '  ',
  })

  jar.updateCode(localStorage.getItem('code'))
  jar.onUpdate(code => {
    localStorage.setItem('code', code)
  })
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>

        <?php }
}
// Hook into admin_enqueue_scripts
add_action('admin_footer', 'emojized_codejar_inline_script_for_plugin_editor');


<?php
/*
    * Plugin Name: HD Quiz
    * Description: HD Quiz allows you to easily add an unlimited amount of Quizzes to your site.
    * Plugin URI: https://harmonicdesign.ca/hd-quiz/
    * Author: Harmonic Design
    * Author URI: https://harmonicdesign.ca
    * Version: 1.8.15
	* Text Domain: hd-quiz
	* Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    die('Invalid request.');
}

if (!defined('HDQ_PLUGIN_VERSION')) {
    define('HDQ_PLUGIN_VERSION', '1.8.15');
}

// custom quiz image sizes
add_image_size('hd_qu_size2', 400, 400, true); // image-as-answer

// load in translations
function hdq_load_translations()
{
    load_plugin_textdomain('hd-quiz', false, plugin_basename(dirname(__FILE__)) . '/languages/');
}
add_action('init', 'hdq_load_translations');

/* Include the basic required files
------------------------------------------------------- */
require dirname(__FILE__) . '/includes/settings.php'; // global settings class
require dirname(__FILE__) . '/includes/post-type.php'; // custom post types
require dirname(__FILE__) . '/includes/meta.php'; // custom meta
require dirname(__FILE__) . '/includes/functions.php'; // general functions

// function to check if HD Quiz is active
function hdq_exists()
{
    return;
}

/* Add shortcode
------------------------------------------------------- */
function hdq_add_shortcode($atts)
{
    // Attributes
    extract(
        shortcode_atts(
            array(
                'quiz' => '',
            ),
            $atts
        )
    );

    // Code
    ob_start();
    include plugin_dir_path(__FILE__) . './includes/template.php';
    return ob_get_clean();
}
add_shortcode('HDquiz', 'hdq_add_shortcode');


/* Add Gutenberg block
------------------------------------------------------- */
function hdq_register_block_box()
{
    if (!function_exists('register_block_type')) {
        return; // Gutenberg is not active.
    }
    wp_register_script(
        'hdq-block-quiz',
        plugin_dir_url(__FILE__) . 'includes/js/hdq_block.js',
        array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
        HDQ_PLUGIN_VERSION
    );
    register_block_type('hdquiz/hdq-block-quiz', array(
        'style' => 'hdq-block-quiz',
        'editor_style' => 'hdq-block-quiz',
        'editor_script' => 'hdq-block-quiz',
    ));
}
add_action('init', 'hdq_register_block_box');

/* Get Quiz list
 * used for the gutenberg block
------------------------------------------------------- */
function hdq_get_quiz_list()
{
    $taxonomy = 'quiz';
    $term_args = array(
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    );
    $tax_terms = get_terms($taxonomy, $term_args);
    $quizzes = array();
    if (!empty($tax_terms) && !is_wp_error($tax_terms)) {
        foreach ($tax_terms as $tax_terms) {
            $quiz = new stdClass;
            $quiz->value = $tax_terms->term_id;
            $quiz->label = $tax_terms->name;
            array_push($quizzes, $quiz);
        }
    }
    echo json_encode($quizzes);
    die();
}
add_action('wp_ajax_hdq_get_quiz_list', 'hdq_get_quiz_list');

/* Disable Canonical redirection for paginated quizzes
------------------------------------------------------- */
function hdq_disable_redirect_canonical($redirect_url)
{
    global $post;
    if (!isset($post->post_content)) {
        return;
    }
    if (has_shortcode($post->post_content, 'HDquiz')) {
        $redirect_url = false;
    }
    return $redirect_url;
}
add_filter('redirect_canonical', 'hdq_disable_redirect_canonical');

/* Create HD Quiz Settings page
------------------------------------------------------- */
function hdq_create_settings_page()
{
    if (hdq_user_permission()) {
        function hdq_register_quizzes_page()
        {

            $addon_text = "";
            $new_addon = get_transient("hdq_new_addon");
            if ($new_addon === false) {
                hdq_check_for_updates();
            } else {
                $new_addon["isNew"] = sanitize_text_field($new_addon["isNew"]);
                if ($new_addon["isNew"] === "yes") {
                    $addon_text = ' <span class="awaiting-mod">NEW</span>';
                }
            }

            add_menu_page('HD Quiz', 'HD Quiz', 'publish_posts', 'hdq_quizzes', 'hdq_register_quizzes_page_callback', 'dashicons-clipboard', 5);

            add_submenu_page("hdq_quizzes", "HD Quiz Addons", __("Addons", "hd-quiz") . $addon_text, "delete_others_posts", "hdq_addons", "hdq_register_addons_page_callbak");
            add_submenu_page("hdq_quizzes", "HD Quiz Tools", __("Tools", "hd-quiz"), "delete_others_posts", "hdq_tools", "hdq_register_tools_page_callbak");
            add_submenu_page("hdq_quizzes", "HD Quiz About / Settings", __("About / Settings", "hd-quiz"), "delete_others_posts", 'hdq_options', 'hdq_register_settings_page_callback');

            // tools, hidden pages
            add_submenu_page("", "CSV Importer", "CSV Importer", "publish_posts", "hdq_tools_csv_importer", "hdq_register_tools_csv_importer_page_callback");
            add_submenu_page("", "Data Upgrade", "Data Upgrade", "publish_posts", "hdq_tools_data_upgrade", "hdq_register_tools__data_upgrade_page_callback");
        }
        add_action('admin_menu', 'hdq_register_quizzes_page');
    }

    $hdq_version = sanitize_text_field(get_option('HDQ_PLUGIN_VERSION'));

    if ($hdq_version != "" && $hdq_version != null && $hdq_version < "1.8") {
        update_option("hdq_remove_data_upgrade_notice", "yes");
        update_option("hdq_data_upgraded", "occured");
        hdq_update_legacy_data();
    } else {
        update_option("hdq_data_upgraded", "all good");
    }

    if (HDQ_PLUGIN_VERSION != $hdq_version) {
        update_option('HDQ_PLUGIN_VERSION', HDQ_PLUGIN_VERSION);
        delete_option("hdq_new_addon");
        delete_transient("hdq_new_addon");
        wp_clear_scheduled_hook('hdq_addon_styler_check_for_updates');
        wp_clear_scheduled_hook('hdq_check_for_updates');
    }
}
add_action('init', 'hdq_create_settings_page');

function hdq_check_for_updates()
{
    $remote = wp_remote_get("https://hdplugins.com/plugins/hd-quiz/addons_updated.txt");
    $local = intval(get_option("hdq_new_addon"));
    if (is_array($remote)) {
        $remote = intval($remote["body"]);
        update_option("hdq_new_addon", $remote);

        $transient = array(
            "date" => $remote,
            "isNew" => ""
        );

        if ($remote > $local) {
            $transient["isNew"] = "yes";
        }

        set_transient("hdq_new_addon", $transient, WEEK_IN_SECONDS); // only check every week

    } else {
        update_option("hdq_new_addon", "");
        set_transient("hdq_new_addon", array("date" => 0, "isNew" => ""), DAY_IN_SECONDS); // unable to connect. try again tomorrow
    }
}

function hddq_plugin_links($actions, $plugin_file, $plugin_data, $context)
{
    $new = array(
        'settings'    => sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=hdq_options')),
            esc_html__('Settings', 'hd-quiz')
        ),
        'help' => sprintf(
            '<a href="%s">%s</a>',
            'https://hdplugins.com/forum/hd-quiz-support/',
            esc_html__('Help', 'hd-quiz')
        )
    );
    return array_merge($new, $actions);
}
add_filter("plugin_action_links_" . plugin_basename(__FILE__), 'hddq_plugin_links', 10, 4);


function hdq_deactivation()
{
    wp_clear_scheduled_hook('hdq_check_for_updates');
}
register_deactivation_hook(__FILE__, 'hdq_deactivation');

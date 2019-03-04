<?php
global $wpdb, $wp_version, $yarpp;

/* Enforce YARPP setup: */
$yarpp->enforce();

if(!$yarpp->enabled() && !$yarpp->activate()) {
    echo '<div class="updated">'.__('The YARPP database has an error which could not be fixed.','yarpp').'</div>';
}

/* Check to see that templates are in the right place */
if (!$yarpp->diagnostic_custom_templates()) {

    $template_option = yarpp_get_option('template');
    if ($template_option !== false &&  $template_option !== 'thumbnails') yarpp_set_option('template', false);

    $template_option = yarpp_get_option('rss_template');
    if ($template_option !== false && $template_option !== 'thumbnails') yarpp_set_option('rss_template', false);
}

/* MyISAM Check */
include 'yarpp_myisam_notice.php';

/* This is not a yarpp pluging update, it is an yarpp option update */
if (isset($_POST['update_yarpp']) && check_admin_referer('update_yarpp', 'update_yarpp-nonce')) {
    $new_options = array();
    foreach ($yarpp->default_options as $option => $default) {
        if ( is_bool($default) )
            $new_options[$option] = isset($_POST[$option]);
        if ( (is_string($default) || is_int($default)) &&
             isset($_POST[$option]) && is_string($_POST[$option]) )
            $new_options[$option] = stripslashes($_POST[$option]);
    }

    if ( isset($_POST['weight']) ) {
        $new_options['weight'] = array();
        $new_options['require_tax'] = array();
        foreach ( (array) $_POST['weight'] as $key => $value) {
            if ( $value == 'consider' )
                $new_options['weight'][$key] = 1;
            if ( $value == 'consider_extra' )
                $new_options['weight'][$key] = YARPP_EXTRA_WEIGHT;
        }
        foreach ( (array) $_POST['weight']['tax'] as $tax => $value) {
            if ( $value == 'consider' )
                $new_options['weight']['tax'][$tax] = 1;
            if ( $value == 'consider_extra' )
                $new_options['weight']['tax'][$tax] = YARPP_EXTRA_WEIGHT;
            if ( $value == 'require_one' ) {
                $new_options['weight']['tax'][$tax] = 1;
                $new_options['require_tax'][$tax] = 1;
            }
            if ( $value == 'require_more' ) {
                $new_options['weight']['tax'][$tax] = 1;
                $new_options['require_tax'][$tax] = 2;
            }
        }
    }

    if ( isset( $_POST['auto_display_post_types'] ) ) {
        $new_options['auto_display_post_types'] = array_keys( $_POST['auto_display_post_types'] );
    } else {
        $new_options['auto_display_post_types'] = array();
    }

    $new_options['recent'] = isset($_POST['recent_only']) ?
        $_POST['recent_number'] . ' ' . $_POST['recent_units'] : false;

    if ( isset($_POST['exclude']) )
        $new_options['exclude'] = implode(',',array_keys($_POST['exclude']));
    else
        $new_options['exclude'] = '';

    $new_options['template'] = $_POST['use_template'] == 'custom' ? $_POST['template_file'] :
        ( $_POST['use_template'] == 'thumbnails' ? 'thumbnails' : false );
    $new_options['rss_template'] = $_POST['rss_use_template'] == 'custom' ? $_POST['rss_template_file'] :
        ( $_POST['rss_use_template'] == 'thumbnails' ? 'thumbnails' : false );

    $new_options = apply_filters( 'yarpp_settings_save', $new_options );
    yarpp_set_option($new_options);

    echo '<div class="updated fade"><p>'.__('Options saved!','yarpp').'</p></div>';
}

wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
wp_nonce_field('yarpp_display_demo', 'yarpp_display_demo-nonce', false);
wp_nonce_field('yarpp_display_exclude_terms', 'yarpp_display_exclude_terms-nonce', false);
wp_nonce_field('yarpp_optin_data', 'yarpp_optin_data-nonce', false);
wp_nonce_field('yarpp_set_display_code', 'yarpp_set_display_code-nonce', false);

if (!count($yarpp->admin->get_templates()) && $yarpp->admin->can_copy_templates()) {
    wp_nonce_field('yarpp_copy_templates', 'yarpp_copy_templates-nonce', false);
}

include(YARPP_DIR.'/includes/phtmls/yarpp_options.phtml');
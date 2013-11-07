<?php
/*
Plugin Name: Creative Commons Configurator
Plugin URI: http://www.g-loaded.eu/2006/01/14/creative-commons-configurator-wordpress-plugin/
Description: Helps you publish your content under the terms of a Creative Commons license.
Version: 1.5.0
Author: George Notaras
Author URI: http://www.g-loaded.eu/
License: Apache License v2
*/

/**
 *  Copyright 2008-2012 George Notaras <gnot@g-loaded.eu>, CodeTRAX.org
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */


/*
Creative Commons Icon Selection.
"0" : 88x31.png
"1" : somerights20.png
"2" : 80x15.png
*/
$default_button = "0";


// Store plugin directory
define('BCCL_DIR', dirname(__FILE__));

// Import modules
require_once( join( DIRECTORY_SEPARATOR, array( BCCL_DIR, 'bccl-settings.php' ) ) );
require_once( join( DIRECTORY_SEPARATOR, array( BCCL_DIR, 'bccl-admin-panel.php' ) ) );
require_once( join( DIRECTORY_SEPARATOR, array( BCCL_DIR, 'bccl-template-tags.php' ) ) );


/*
 * Translation Domain
 *
 * Translation files are searched in: wp-content/plugins
 */
load_plugin_textdomain('cc-configurator', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');


/**
 * Settings Link in the ``Installed Plugins`` page
 */
function bccl_plugin_actions( $links, $file ) {
    // if( $file == 'creative-commons-configurator-1/cc-configurator.php' && function_exists( "admin_url" ) ) {
    if( $file == plugin_basename(__FILE__) && function_exists( "admin_url" ) ) {
        $settings_link = '<a href="' . admin_url( 'options-general.php?page=cc-configurator-options' ) . '">' . __('Settings') . '</a>';
        // Add the settings link before other links
        array_unshift( $links, $settings_link );
    }
    return $links;
}
add_filter( 'plugin_action_links', 'bccl_plugin_actions', 10, 2 );






function bccl_add_placeholders($data, $what = "html") {
    if (!(trim($data))) { return ""; }
    if ($what = "html") {
        return sprintf( PHP_EOL . "<!-- Creative Commons License -->" . PHP_EOL . "%s" . PHP_EOL . "<!-- /Creative Commons License -->" . PHP_EOL , trim($data) );
    } else {
        return sprintf( PHP_EOL . "<!--" . PHP_EOL . "%s" . PHP_EOL . "-->" . PHP_EOL, trim($data) );
    }
}


function bccl_get_license_text_hyperlink() {
    /*
    Returns Full TEXT hyperlink to License <a href=...>...</a>
    */
    $cc_settings = get_option("cc_settings");
    if (!$cc_settings) { return ""; }
    $license_url = $cc_settings["license_url"];
    $license_name = $cc_settings["license_name"];
    
    $text_link_format = '<a rel="license" href="%s">%s %s %s</a>';
    return sprintf($text_link_format, $license_url, __('Creative Commons', 'cc-configurator'), trim($license_name), __('License', 'cc-configurator'));
}


function bccl_license_text_hyperlink() {
    /*
    Displays Full TEXT hyperlink to License <a href=...>...</a>
    */
    echo bccl_add_placeholders(bccl_get_license_text_hyperlink());
}


function bccl_get_license_image_hyperlink($button = "default") {
    /*
    Returns Full IMAGE hyperlink to License <a href=...><img.../></a>
    
    Creative Commons Icon Selection
    "0" : 88x31.png
    "1" : http://creativecommons.org/images/public/somerights20.png
    "2" : 80x15.png

    CSS customization via "cc-button" class.
    */
    
    global $default_button;
    
    $cc_settings = get_option("cc_settings");
    if (!$cc_settings) { return ""; }
    $license_url = $cc_settings["license_url"];
    $license_name = $cc_settings["license_name"];
    $license_button = $cc_settings["license_button"];
    
    // Available buttons
    $buttons = array(
        "0" => dirname($license_button) . "/88x31.png",
        "1" => "http://creativecommons.org/images/public/somerights20.png",
        "2" => dirname($license_button) . "/80x15.png"
        );
    
    // Modify button
    if ($button == "default") {
        if (array_key_exists($default_button, $buttons)) {
            $license_button = $buttons[$default_button];
        }
    } elseif (array_key_exists($button, $buttons)){
        $license_button = $buttons[$button];
    }
    
    // Finally check whether the WordPress site is served over the HTTPS protocol
    // so as to use https in the image source. Creative Commons makes license
    // images available over HTTPS as well.
    if (is_ssl()) {
        $license_button = str_replace('http://', 'https://', $license_button);
    }

    $image_link_format = "<a rel=\"license\" href=\"%s\"><img alt=\"%s\" src=\"%s\" class=\"cc-button\" /></a>";
    return sprintf($image_link_format, $license_url, __('Creative Commons License', 'cc-configurator'), $license_button);

}


function bccl_license_image_hyperlink($button = "default") {
    /*
    Displays Full IMAGE hyperlink to License <a href=...><img...</a>
    */
    echo bccl_add_placeholders(bccl_get_license_image_hyperlink($button));
}


function bccl_get_license_url() {
    /*
    Returns only the license URL.
    */
    $cc_settings = get_option("cc_settings");
    if (!$cc_settings) { return ""; }
    return $cc_settings["license_url"];
}

function bccl_get_license_deed_url() {
    /*
    Returns only the license deed URL.
    */
    $cc_settings = get_option("cc_settings");
    if (!$cc_settings) { return ""; }
    return $cc_settings["deed_url"];
}


function bccl_license_summary($width = "100%", $height = "600px", $css_class= "cc-frame") {
    /*
    Displays the licence summary page from creative commons in an iframe
    
    */
    printf('
        <iframe src="%s" frameborder="0" width="%s" height="%s" class="%s"></iframe>
        ', bccl_get_license_url(), $width, $height, $css_class);
}


function bccl_license_legalcode($width = "100%", $height = "600px", $css_class= "cc-frame") {
    /*
    Displays the licence summary page from creative commons in an iframe
    */
    printf('
        <iframe src="%slegalcode" frameborder="0" width="%s" height="%s" class="%s"></iframe>
        ', bccl_get_license_url(), $width, $height, $css_class);
}


function bccl_get_full_html_license($button = "default") {
    /*
    Returns the full HTML code of the license
    */    
    return bccl_get_license_image_hyperlink($button) . "<br />" . bccl_get_license_text_hyperlink();
}


function bccl_full_html_license($button = "default") {
    /*
    Displays the full HTML code of the license
    */    
    echo bccl_add_placeholders(bccl_get_full_html_license($button));
}


function bccl_get_license_block($work = "", $css_class = "", $show_button = "default", $button = "default") {
    /*
    This function should not be used in template tags.
    
    $work: The work that is licensed can be defined by the user.
    
    $show_button: (default, yes, no) - no explanation (TODO possibly define icon URL)
    
    $button: The user can se the desired button (hidden feature): "0", "1", "2"
    
    */

    $cc_block = "LICENSE BLOCK ERROR";
    $cc_settings = get_option("cc_settings");
    if (!$cc_settings) { return ""; }
    
    // Set CSS class
    if (empty($css_class)) {
        $css_class = "cc-block";
    }
    
    // License button inclusion
    $button_code = '';
    if ($show_button == "default") {
        if ($cc_settings["cc_body_img"]) {
            $button_code = bccl_get_license_image_hyperlink($button) . "<br />";
        }
    } elseif ($show_button == "yes") {
        $button_code = bccl_get_license_image_hyperlink($button) . "<br />";
    } elseif ($show_button == "no") {
        $button_code = "";
    } else {
        $button_code = "ERROR";
    }
    
    // License block pre/after text
    $pre_text = '';
    // $pre_text = 'Copyright &copy; ' . get_the_date('Y') . ' - Some Rights Reserved' . '<br />';
    $pre_text = apply_filters( 'bccl_license_block_pre', $pre_text );
    $after_text = '';
    $after_text = apply_filters( 'bccl_license_block_after', $after_text );

    // Work analysis
    if ( empty($work) ) {
        // Proceed only if the user has not defined the work.
        if ( $cc_settings["cc_extended"] ) {
            $creator = bccl_get_the_creator($cc_settings["cc_creator"]);
            $author_archive_url = get_author_posts_url( get_the_author_meta( 'ID' ) );
            $work = "<em><a href=\"" . get_permalink() . "\">" . get_the_title() . "</a></em>";
            $by = "<em><a href=\"" . $author_archive_url . "\">" . $creator . "</a></em>";
            $work = sprintf("%s %s %s", $work, __("by", 'cc-configurator'), $by);
        } else {
            $work = __('This work', 'cc-configurator');
        }
    }
    $work .= sprintf(", ".__('unless otherwise expressly stated', 'cc-configurator').", ".__('is licensed under a', 'cc-configurator')." %s.", bccl_get_license_text_hyperlink());
    
    // Additional Permissions
    if ( $cc_settings["cc_perm_url"] ) {
        $additional_perms = " ".__('Terms and conditions beyond the scope of this license may be available at', 'cc-configurator')." <a href=\"" . $cc_settings["cc_perm_url"] . "\">" . $_SERVER["HTTP_HOST"] . "</a>.";
    } else {
        $additional_perms = "";
    }
    
    // $cc_block = sprintf("<div class=\"%s\">%s%s%s</div>", $css_class, $button_code, $work, $additional_perms);
    $cc_block = sprintf("<p class=\"%s\">%s%s%s%s%s</p>", $css_class, $button_code, $pre_text, $work, $additional_perms, $after_text);
    return $cc_block;
}


function bccl_license_block($work = "", $css_class = "", $show_button = "default", $button = "default") {
    /*
    $work: The work that is licensed can be defined by the user.
    $css_class : The user can define the CSS class that will be used to
    $show_button: (default, yes, no)
    format the license block. (if empty, the default cc-block is used)
    */
    echo bccl_add_placeholders(bccl_get_license_block($work, $css_class, $show_button, $button));
}




function bccl_get_creator_pool() {
    $creator_arr = array(
        "blogname"    => __('Blog Name', 'cc-configurator'),
        "firstlast"    => __('First + Last Name', 'cc-configurator'),
        "lastfirst"    => __('Last + First Name', 'cc-configurator'),
        "nickname"    => __('Nickname', 'cc-configurator'),
        "displayedname"    => __('Displayed Name', 'cc-configurator'),
        );
    return $creator_arr;
}


function bccl_get_the_creator($who) {
    /*
    Return the creator/publisher of the licensed work according to the user-defined option (cc-creator)
    */
    $author_name = '';
    if ($who == "blogname") {
        $author_name = get_bloginfo("name");
    } elseif ($who == "firstlast") {
        $author_name = get_the_author_meta('first_name') . " " . get_the_author_meta('last_name');
    } elseif ($who == "lastfirst") {
        $author_name = get_the_author_meta('last_name') . " " . get_the_author_meta('first_name');
    } elseif ($who == "nickname") {
        $author_name = get_the_author_meta('nickname');
    } elseif ($who == "displayedname") {
        $author_name = get_the_author_meta('display_name');
    } else {
        $author_name = get_the_author_meta('display_name');
    }
    // If we do not have an author name, revert to the display name.
    if ( trim($author_name) == '' ) {
        return get_the_author();
    }
    return $author_name;
}



// Action

function bccl_add_to_header() {
    /*
    Adds a link element with "license" relation in the web page HEAD area.
    
    Also, adds style for the license block, only if the user has:
     * enabled the display of such a block
     * not disabled internal license block styling
     * if it is single-post view
    */
    $cc_settings = get_option("cc_settings");

    if ( is_singular() && ! is_front_page() ) { // The license link is not appended to static front page content.

        // If the user has enabled the inclusion of the link in the head
        if ( ! empty($cc_settings["license_url"]) && $cc_settings["cc_head"] == "1" ) {
            echo PHP_EOL . "<!-- Creative Commons License added by Creative-Commons-Configurator plugin for WordPress -->" . PHP_EOL;
            // Adds a link element with "license" relation in the web page HEAD area.
            echo "<link rel=\"license\" type=\"text/html\" href=\"" . bccl_get_license_url() . "\" />" . PHP_EOL . PHP_EOL;
        }

        // If the license block has not been enabled for this type, return and do not print our style
        if ( is_attachment() ) {
            if ( $cc_settings["cc_body_attachments"] != "1" ) {
                return;
            }
        } elseif ( is_page() ) {
            if ( $cc_settings["cc_body_pages"] != "1" ) {
                return;
            }
        } elseif ( is_single() ) {
            if ( $cc_settings["cc_body"] != "1" ) {
                return;
            }
        }

        // If the user has not deactivated our internal style, print it too
        if ( $cc_settings["cc_no_style"] != "1" ) {
            // Adds style for the license block
            $color = $cc_settings["cc_color"];
            $bgcolor = $cc_settings["cc_bgcolor"];
            $brdrcolor = $cc_settings["cc_brdr_color"];
            $bccl_default_block_style = "clear: both; width: 90%; margin: 8px auto; padding: 4px; text-align: center; border: 1px solid $brdrcolor; color: $color; background-color: $bgcolor;";
            $style = "<style type=\"text/css\"><!--" . PHP_EOL . "p.cc-block { $bccl_default_block_style }" . PHP_EOL . "--></style>" . PHP_EOL . PHP_EOL;
            echo $style;
        }
    }
}


function bccl_add_cc_ns_feed() {
    /*
    Adds the CC RSS module namespace declaration.
    */
    $cc_settings = get_option("cc_settings");
    if (!$cc_settings) { return ""; }
    if ( $cc_settings["cc_feed"] == "1" ) {
        echo "xmlns:creativeCommons=\"http://backend.userland.com/creativeCommonsRssModule\"" . PHP_EOL;
    }
}

function bccl_add_cc_element_feed() {
    /*
    Adds the CC URL to the feeds.
    */
    $cc_settings = get_option("cc_settings");
    if (!$cc_settings) { return ""; }
    if ( $cc_settings["license_url"] && $cc_settings["cc_feed"] == "1" ) {
        echo "<creativeCommons:license>" . bccl_get_license_url() . "</creativeCommons:license>" . PHP_EOL;
    }
}


function bccl_append_to_post_body($PostBody) {
    /*
    Adds the license block under the published content.
    
    The check if the user has chosen to display a block under the published
    content is performed in bccl_get_license_block(), in order not to retrieve
    the saved settings two timesor pass them between functions.
    */
    $cc_settings = get_option("cc_settings");

    if ( is_singular() && ! is_front_page() ) { // The license block is not appended to static front page content.

        if ( is_attachment() ) {
            if ( $cc_settings["cc_body_attachments"] != "1" ) {
                return $PostBody;
            }
        } elseif ( is_page() ) {
            if ( $cc_settings["cc_body_pages"] != "1" ) {
                return $PostBody;
            }
        } elseif ( is_single() ) {
            if ( $cc_settings["cc_body"] != "1" ) {
                return $PostBody;
            }
        }

        // Append the license block to the content
        $cc_block = bccl_get_license_block("", "", "default", "default");
        if ( $cc_block ) {
            $PostBody .= bccl_add_placeholders($cc_block);
        }

    }
    return $PostBody;
}

// ACTION

add_action('wp_head', 'bccl_add_to_header', 10);

add_filter('the_content', 'bccl_append_to_post_body', 250);

add_action('rdf_ns', 'bccl_add_cc_ns_feed');
add_action('rdf_header', 'bccl_add_cc_element_feed');
add_action('rdf_item', 'bccl_add_cc_element_feed');

add_action('rss2_ns', 'bccl_add_cc_ns_feed');
add_action('rss2_head', 'bccl_add_cc_element_feed');
add_action('rss2_item', 'bccl_add_cc_element_feed');

add_action('atom_ns', 'bccl_add_cc_ns_feed');
add_action('atom_head', 'bccl_add_cc_element_feed');
add_action('atom_entry', 'bccl_add_cc_element_feed');

?>
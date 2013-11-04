<?php




function bccl_add_pages() {
    add_options_page(__('License Settings', 'cc-configurator'), __('License', 'cc-configurator'), 'manage_options', 'cc-configurator-options', 'bccl_options_page');
}
add_action('admin_menu', 'bccl_add_pages');


function bccl_show_info_msg($msg) {
    echo '<div id="message" class="updated fade"><p>' . $msg . '</p></div>';
}


function bccl_options_page() {
    // Permission Check
    if ( ! current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    if (isset($_POST['info_update'])) {

        bccl_save_settings( $_POST );

    } elseif ( isset( $_POST['info_reset'] ) ) {

        bccl_reset_settings();

    } elseif ( isset( $_GET['new_license'] ) ) {

        bccl_set_new_license_settings( $_GET );

    } elseif ( isset( $_POST['license_reset'] ) ) {

        bccl_reset_license_settings();

    }

    // Try to get the options from the DB.
    $cc_settings = get_option('cc_settings');
    //var_dump($cc_settings);

    if ( ! empty( $cc_settings['license_url'] ) ) {

        bccl_set_license_options($cc_settings);

    } else {

        bccl_select_license();

    }

}


/** Enqueue scripts and styles
 *  From: http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts#Example:_Target_a_Specific_Admin_Page
 *  For a better way to add custom scripts and styles:
 *  - http://codex.wordpress.org/Function_Reference/wp_enqueue_script#Link_Scripts_Only_on_a_Plugin_Administration_Screen
 *  - http://codex.wordpress.org/Function_Reference/wp_enqueue_style#Load_stylesheet_only_on_a_plugin.27s_options_page
 */
function bccl_my_enqueue($hook) {
    //var_dump($hook);
    if ( 'settings_page_cc-configurator-options' != $hook ) {
        return;
    }
    wp_enqueue_script('thickbox');
    wp_enqueue_style('thickbox');
}
//add_action( 'admin_enqueue_scripts', 'bccl_my_enqueue' );



function bccl_select_license() {
    /*
     * License selection using the partner interface.
     * http://wiki.creativecommons.org/Partner_Interface
     */

    // Determine the protocol
    $proto = 'http';
    if ( is_ssl() ) {
        $proto = 'https';
    }
    // Partner Interface URL
    $cc_partner_interface_url = "$proto://creativecommons.org/license/";

    // Collect Query Arguments
    $partner = urlencode( 'WordPress-Creative-Commons-Configurator-Plugin' );
    $partner_icon_url = admin_url( 'images/wordpress-logo.png' );
    $jurisdiction_choose = '1';
    $lang = urlencode( get_bloginfo('language') );
    $exit_url = urlencode( admin_url( 'options-general.php?page=cc-configurator-options' ) . 
        "&license_url=[license_url]&license_name=[license_name]&license_button=[license_button]&deed_url=[deed_url]&new_license=1" );

    // Construct Query String
    $cc_partner_interface_query_string = "partner=$partner&partner_icon_url=$partner_icon_url&jurisdiction_choose=$jurisdiction_choose&lang=$lang&exit_url=$exit_url";

    // Not currently used. Could be utilized to present the partner interace in an iframe.
    //$Partner_Interface_URI = htmlspecialchars("$proto://creativecommons.org/license/?");
    //$Partner_Interface_URI = "$proto://creativecommons.org/license/?" . $cc_partner_interface_query_string;


    print('
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br /></div>
        <h2>'.__('License Settings', 'cc-configurator').'</h2>
        <p>'.__('Welcome to the administration panel of the Creative-Commons-Configurator plugin for WordPress.', 'cc-configurator').'</p>

        <h2>'.__('Select License', 'cc-configurator').'</h2>
        <p>'.__('A license has not been set for your content. By pressing the following link you will be taken to the license selection wizard hosted by the Creative Commons Corporation. Once you have completed the license selection process, you will be redirected back to this page.', 'cc-configurator').'</p>

        <form name="formnewlicense" id="bccl-new-license-form" method="get" action="' . $cc_partner_interface_url . '">
            <input type="hidden" name="partner" value="' . $partner . '" />
            <input type="hidden" name="partner_icon_url" value="' . $partner_icon_url . '" />
            <input type="hidden" name="jurisdiction_choose" value="' . $jurisdiction_choose . '" />
            <input type="hidden" name="lang" value="' . $lang . '" />
            <input type="hidden" name="exit_url" value="' . $exit_url . '" />

            <p class="submit">
                <input id="submit" class="button-primary" type="submit" value="'.__('New License', 'cc-configurator').'" name="new-license-button" />
            </p>
        </form>

    </div>');

    /**
     * See here for info about displaying the CC Partner Interface in thickbox:
     * http://www.codetrax.org/issues/1111
     */

}


function bccl_set_license_options($cc_settings) {
    /*
    CC License Options
    */
    global $wp_version;

    print('
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br /></div>
        <h2>'.__('License Settings', 'cc-configurator').'</h2>

        <p style="text-align: center;"><big>' . bccl_get_full_html_license() . '</big></p>
        <form name="formlicense" id="bccl_reset" method="post" action="' . admin_url( 'options-general.php?page=cc-configurator-options' ) . '">
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Current License', 'cc-configurator').'</span></legend>
                <p>'.__('A license has been set and will be used to license your work.', 'cc-configurator').'</p>
                <p>'.__('If you need to set a different license, press the <em>Reset License</em> button below. By reseting the license, the saved plugin options are removed from the WordPress database.', 'cc-configurator').'</p>
            </fieldset>
            <p class="submit">
                <input type="submit" class="button-primary" name="license_reset" value="'.__('Reset License', 'cc-configurator').'" />
            </p>
        </form>
    </div>

    <div class="wrap" style="background: #EEF6E6; padding: 1em 2em; border: 1px solid #E4E4E4;' . (($cc_settings["cc_i_have_donated"]=="1") ? ' display: none;' : '') . '">
        <h2>'.__('Message from the author', 'cc-configurator').'</h2>
        <p style="font-size: 1.2em; padding-left: 2em;"><em>CC-Configurator</em> is released under the terms of the <a href="http://www.apache.org/licenses/LICENSE-2.0.html">Apache License version 2</a> and, therefore, is <strong>free software</strong>.</p>
        <p style="font-size: 1.2em; padding-left: 2em;">However, a significant amount of <strong>time</strong> and <strong>energy</strong> has been put into developing this plugin, so, its production has not been free from cost. If you find this plugin useful, you can show your appreciation of the effort by buying me an <a href="http://www.g-loaded.eu/about/donate/">extra cup of coffee</a>.</p>
        <p style="font-size: 1.2em; padding-left: 2em;">Thank you in advance</p>
        <div style="text-align: right;"><small>'.__('This message can de deactivated in the settings below.', 'cc-configurator').'</small></div>
    </div>

    <div class="wrap">
        <h2>'.__('Configuration', 'cc-configurator').'</h2>
        <p>'.__('Here you can choose where and how license information should be added to your blog.', 'cc-configurator').'</p>

        <form name="formbccl" method="post" action="' . admin_url( 'options-general.php?page=cc-configurator-options' ) . '">

        <table class="form-table">
        <tbody>

            <tr valign="top">
            <th scope="row">'.__('Syndicated Content', 'cc-configurator').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Syndicated Content', 'cc-configurator').'</span></legend>
                <input id="cc_feed" type="checkbox" value="1" name="cc_feed" '. (($cc_settings["cc_feed"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="cc_feed">
                '.__('Include license information in the blog feeds. (<em>Recommended</em>)', 'cc-configurator').'
                </label>
                <br />
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Page Head HTML', 'cc-configurator').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Page Head HTML', 'cc-configurator').'</span></legend>
                <input id="cc_head" type="checkbox" value="1" name="cc_head" '. (($cc_settings["cc_head"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="cc_head">
                '.__('Include license information in the page\'s HTML head. This will not be visible to human visitors, but search engine bots will be able to read it. Note that the insertion of license information in the HTML head is done in relation to the content types (posts, pages or attachment pages) on which the license text block is displayed (see the <em>text block</em> settings below). (<em>Recommended</em>)', 'cc-configurator').'
                </label>
                <br />
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Text Block', 'cc-configurator').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Text Block', 'cc-configurator').'</span></legend>

                <p>'.__('By enabling the following options, a small block of text, which contains links to the author, the work and the used license, is appended to the published content.', 'cc-configurator').'</p>

                <input id="cc_body" type="checkbox" value="1" name="cc_body" '. (($cc_settings["cc_body"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="cc_body">
                '.__('Posts: Add the text block with license information under the published posts. (<em>Recommended</em>)', 'cc-configurator').'
                </label>
                <br />

                <input id="cc_body_pages" type="checkbox" value="1" name="cc_body_pages" '. (($cc_settings["cc_body_pages"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="cc_body_pages">
                '.__('Pages: Add the text block with license information under the published pages.', 'cc-configurator').'
                </label>
                <br />

                <input id="cc_body_attachments" type="checkbox" value="1" name="cc_body_attachments" '. (($cc_settings["cc_body_attachments"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="cc_body_attachments">
                '.__('Attachments: Add the text block with license information under the attached content in attachment pages.', 'cc-configurator').'
                </label>
                <br />

                <p>'.__('By enabling the following option, the license image is also included in the license text block.', 'cc-configurator').'</p>

                <input id="cc_body_img" type="checkbox" value="1" name="cc_body_img" '. (($cc_settings["cc_body_img"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="cc_body_img">
                '.__('Include the license image in the text block.', 'cc-configurator').'
                </label>
                <br />
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Extra Text Block Customization', 'cc-configurator').'</th>
            <td>
            <p>'.__('The following settings have an effect only if the text block containing licensing information has been enabled above.', 'cc-configurator').'</p>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Extra Text Block Customization', 'cc-configurator').'</span></legend>

                <input id="cc_extended" type="checkbox" value="1" name="cc_extended" '. (($cc_settings["cc_extended"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="cc_extended">
                '.__('Include extended information about the published work and its creator. By enabling this option, hyperlinks to the published content and its creator/publisher are also included into the license statement inside the block. This, by being an attribution example itself, will generally help others to attribute the work to you.', 'cc-configurator').'
                </label>
                <br />
                <br />

                <select name="cc_creator" id="cc_creator">');
                $creator_arr = bccl_get_creator_pool();
                foreach ($creator_arr as $internal => $creator) {
                    if ($cc_settings["cc_creator"] == $internal) {
                        $selected = ' selected="selected"';
                    } else {
                        $selected = '';
                    }
                    printf('<option value="%s"%s>%s</option>', $internal, $selected, $creator);
                }
                print('</select>
                <br />
                <label for="cc_creator">
                '.__('If extended information about the published work has been enabled, then you can choose which name will indicate the creator of the work. By default, the blog name is used.', 'cc-configurator').'
                </label>
                <br />
                <br />

                <input name="cc_perm_url" type="text" id="cc_perm_url" class="code" value="' . $cc_settings["cc_perm_url"] . '" size="100" maxlength="1024" />
                <br />
                <label for="cc_perm_url">
                '.__('If you have added any extra permissions to your license, provide the URL to the webpage that contains them. It is highly recommended to use absolute URLs.', 'cc-configurator').'
                <br />
                <strong>'.__('Example', 'cc-configurator').'</strong>: <code>http://www.example.org/ExtendedPermissions</code>
                </label>
                <br />

            </fieldset>
            </td>
            </tr>

            
            <tr valign="top">
            <th scope="row">'.__('Colors of the text block', 'cc-configurator').'</th>
            <td>
            <p>'.__('The following settings have an effect only if the text block containing licensing information has been enabled above.', 'cc-configurator').'</p>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Colors of the text block', 'cc-configurator').'</span></legend>

                <input name="cc_color" type="text" id="cc_color" class="code" value="' . $cc_settings["cc_color"] . '" size="7" maxlength="7" />
                <label for="cc_color">
                '.__('Set a color for the text that appears within the block (does not affect hyperlinks).', 'cc-configurator').'
                <br />
                <strong>'.__('Default', 'cc-configurator').'</strong>: <code>#000000</code>
                </label>
                <br />
                <br />

                <input name="cc_bgcolor" type="text" id="cc_bgcolor" class="code" value="' . $cc_settings["cc_bgcolor"] . '" size="7" maxlength="7" />
                <label for="cc_bgcolor">
                '.__('Set a background color for the block.', 'cc-configurator').'
                <br />
                <strong>'.__('Default', 'cc-configurator').'</strong>: <code>#eef6e6</code>
                </label>
                <br />
                <br />

                <input name="cc_brdr_color" type="text" id="cc_brdr_color" class="code" value="' . $cc_settings["cc_brdr_color"] . '" size="7" maxlength="7" />
                <label for="cc_brdr_color">
                '.__('Set a color for the border of the block.', 'cc-configurator').'
                <br />
                <strong>'.__('Default', 'cc-configurator').'</strong>: <code>#cccccc</code>
                </label>
                <br />
                <br />

                <input id="cc_no_style" type="checkbox" value="1" name="cc_no_style" '. (($cc_settings["cc_no_style"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="cc_no_style">
                '.__('Disable the internal formatting of the license block. If the internal formatting is disabled, then the color selections above have no effect any more. You can still format the license block via your own CSS. The <em>cc-block</em> and <em>cc-button</em> classes have been reserved for formatting the license block and the license button respectively.', 'cc-configurator').'
                </label>
                <br />

            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Donations', 'cc-configurator').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Donations', 'cc-configurator').'</span></legend>
                <input id="cc_i_have_donated" type="checkbox" value="1" name="cc_i_have_donated" '. (($cc_settings["cc_i_have_donated"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="cc_i_have_donated">
                '.__('By checking this, the <em>message from the author</em> above goes away. Thanks for <a href="http://www.g-loaded.eu/about/donate/">donating</a>!', 'cc-configurator').'
                </label>
                <br />

            </fieldset>
            </td>
            </tr>


        </tbody>
        </table>

        <p class="submit">
            <input id="submit" class="button-primary" type="submit" value="'.__('Save Changes', 'cc-configurator').'" name="info_update" />
            <input id="reset" class="button-primary" type="submit" value="'.__('Reset to defaults', 'cc-configurator').'" name="info_reset" />
        </p>

        </form>

    </div>

    <div class="wrap">

        <h2>'.__('Advanced Info', 'cc-configurator').'</h2>
        <p>'.__('Apart from the options above for the inclusion of licensing information in your blog, this plugin provides some <em>Template Tags</em>, which can be used in your theme templates. These are the following:', 'cc-configurator').'
        </p>
        
        <table class="form-table">
        <tbody>

            <tr valign="top">
            <th scope="row">'.__('Text Hyperlink', 'cc-configurator').'</th>
            <td>
                <code>bccl_get_license_text_hyperlink()</code> - '.__('Returns the text hyperlink of your current license for use in the PHP code.', 'cc-configurator').'
                <br />
                <code>bccl_license_text_hyperlink()</code> - '.__('Displays the text hyperlink.', 'cc-configurator').'
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Image Hyperlink', 'cc-configurator').'</th>
            <td>
                <code>bccl_get_license_image_hyperlink()</code> - '.__('Returns the image hyperlink of the current license.', 'cc-configurator').'
                <br />
                <code>bccl_license_image_hyperlink()</code> - '.__('Displays the image hyperlink of the current license.', 'cc-configurator').'
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('License URIs', 'cc-configurator').'</th>
            <td>
                <code>bccl_get_license_url()</code> - '.__('Returns the license\'s URL.', 'cc-configurator').'
                <br />
                <code>bccl_get_license_deed_url()</code> - '.__('Returns the license\'s Deed URL. Usually this is the same URI as returned by the bccl_get_license_url() function.', 'cc-configurator').'
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Full HTML Code', 'cc-configurator').'</th>
            <td>
                <code>bccl_get_full_html_license()</code> - '.__('Returns the full HTML code of the license. This includes the text and the image hyperlinks.', 'cc-configurator').'
                <br />
                <code>bccl_full_html_license()</code> - '.__('Displays the full HTML code of the license. This includes the text and the image hyperlinks.', 'cc-configurator').'
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Complete License Block', 'cc-configurator').'</th>
            <td>
                <code>bccl_license_block($work, $css_class, $show_button)</code> - '.__('Displays a complete license block. This template tag can be used to publish specific original work under the current license or in order to display the license block at custom locations on your website. This function supports the following arguments', 'cc-configurator').':
                <ol>
                    <li><code>$work</code> ('.__('alphanumeric', 'cc-configurator').') : '.__('This argument is used to define the work to be licensed. Its use is optional, when the template tag is used in single-post view. If not defined, the user-defined settings for the default license block are used.', 'cc-configurator').'</li>
                    <li><code>$css_class</code> ('.__('alphanumeric', 'cc-configurator').') : '.__('This argument sets the name of the CSS class that will be used to format the license block. It is optional. If not defined, then the default class <em>cc-block</em> is used.', 'cc-configurator').'</li>
                    <li><code>$show_button</code> ('.__('alphanumeric', 'cc-configurator').') - ("default", "yes", "no") : '.__('This argument is optional. It can be used in order to control the appearance of the license icon.', 'cc-configurator').'</li>
                </ol>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Licence Documents', 'cc-configurator').'</th>
            <td>
                <code>bccl_license_summary($width, $height, $css_class)</code> - '.__('Displays the license\'s summary document in an <em>iframe</em>.', 'cc-configurator').'
                <br />
                <code>bccl_license_legalcode($width, $height, $css_class)</code> - '.__('Displays the license\'s full legal code in an <em>iframe</em>.', 'cc-configurator').'
            </td>
            </tr>

        </tbody>
        </table>

    </div>

    ');
}



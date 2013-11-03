<?php



function bccl_license_options () {
    // Permission Check
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    // Default CC-Configurator Settings
    $default_cc_settings = array(
        "license_url"   => "",
        "license_name"  => "",
        "license_button"=> "",
        "deed_url"      => "",
        "options"       => array(
            "cc_head"       => "0",
            "cc_feed"       => "0",
            "cc_body"       => "0",
            "cc_body_pages" => "0",
            "cc_body_attachments"   => "0",
            "cc_body_img"   => "0",
            "cc_extended"   => "0",
            "cc_creator"    => "blogname",
            "cc_perm_url"   => "",
            "cc_color"      => "#000000",
            "cc_bgcolor"    => "#eef6e6",
            "cc_brdr_color" => "#cccccc",
            "cc_no_style"   => "0",
            "cc_i_have_donated" => "0",
        )
    );

    /*
    It is checked if a specific form (options update, reset license) has been
    submitted or if a new license is available in a GET request.
    
    Then, it is determined which page should be displayed to the user by
    checking whether the license_url exists in the cc_settings or not.
    license_url is a mandatory attribute of the CC license.
    */
    if (isset($_POST["options_update"])) {
        /*
         * Updates the CC License options only.
         * It will never enter here if a license has not been set, so it is
         * taken for granted that "cc_settings" exist in the database.
         */
        $cc_settings = get_option("cc_settings");
        $cc_settings["options"] = array(
            "cc_head"       => $_POST["cc_head"],
            "cc_feed"       => $_POST["cc_feed"],
            "cc_body"       => $_POST["cc_body"],
            "cc_body_pages" => $_POST["cc_body_pages"],
            "cc_body_attachments" => $_POST["cc_body_attachments"],
            "cc_body_img"   => $_POST["cc_body_img"],
            "cc_extended"   => $_POST["cc_extended"],
            "cc_creator"    => $_POST["cc_creator"],
            "cc_perm_url"   => $_POST["cc_perm_url"],
            "cc_color"      => $_POST["cc_color"],
            "cc_bgcolor"    => $_POST["cc_bgcolor"],
            "cc_brdr_color" => $_POST["cc_brdr_color"],
            "cc_no_style"   => $_POST["cc_no_style"],
            "cc_i_have_donated" => $_POST["cc_i_have_donated"],
            );
        
        update_option("cc_settings", $cc_settings);
        bccl_show_info_msg(__('Creative Commons license options saved.', 'cc-configurator'));

    } elseif (isset($_POST["license_reset"])) {
        /*
         * Reset all options to the defaults.
         */
        delete_option("cc_settings");
        update_option("cc_settings", $default_cc_settings);
        bccl_show_info_msg(__("Creative Commons license options deleted from the WordPress database.", 'cc-configurator'));

    } elseif (isset($_GET["new_license"])) {
        /*
         * Saves the new license settings to database.
         * The ``new_license`` query argument must exist in the GET request.
         *
         * Also, saves the default colors to the options.
         */
        $cc_settings = $default_cc_settings;
        // Replace the base CC license settings
        $cc_settings["license_url"] = htmlspecialchars(rawurldecode($_GET["license_url"]));
        $cc_settings["license_name"] = htmlspecialchars(rawurldecode($_GET["license_name"]));
        $cc_settings["license_button"] = htmlspecialchars(rawurldecode($_GET["license_button"]));
        $cc_settings["deed_url"] = htmlspecialchars(rawurldecode($_GET["deed_url"]));
        
        update_option("cc_settings", $cc_settings);
        bccl_show_info_msg(__('Creative Commons license saved.', 'cc-configurator'));

    } elseif (!get_option("cc_settings")) {

        // CC-Configurator settings do not exist in the database.
        // This is the first run, so set our defaults.
        update_option("cc_settings", $default_cc_settings);
    }
    
    /*
    Decide if the license selection frame will be shown or the license options page.
    */
    $cc_settings = get_option("cc_settings");

    //var_dump($cc_settings);

    if (empty($cc_settings["license_url"])) {
        bccl_select_license();
    } else {
        bccl_set_license_options($cc_settings);
    }

}




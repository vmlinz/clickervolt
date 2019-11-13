<?php

namespace ClickerVolt;


if ( !function_exists( '\\ClickerVolt\\cli_fs' ) ) {
    // Create a helper function for easy SDK access.
    function cli_fs()
    {
        global  $cli_fs ;
        
        if ( !isset( $cli_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $cli_fs = fs_dynamic_init( array(
                'id'              => '3482',
                'slug'            => 'clickervolt',
                'type'            => 'plugin',
                'public_key'      => 'pk_28ff3cf5fc9c70c194222c8c1b7e5',
                'is_premium'      => false,
                'has_addons'      => false,
                'has_paid_plans'  => true,
                'has_affiliation' => 'selected',
                'menu'            => array(
                'slug'    => 'clickervolt',
                'support' => false,
            ),
                'is_live'         => true,
            ) );
        }
        
        return $cli_fs;
    }
    
    // Init Freemius.
    cli_fs();
    // Signal that SDK was initiated.
    do_action( 'cli_fs_loaded' );
}


if ( !function_exists( '\\ClickerVolt\\cli_fs_custom_connect_message_on_update' ) ) {
    function cli_fs_custom_connect_message_on_update(
        $message,
        $user_first_name,
        $plugin_title,
        $user_login,
        $site_link,
        $freemius_link
    )
    {
        return sprintf(
            __( 'Hey %1$s' ) . ',<br>' . __( 'never miss an important update -- opt-in to our security and feature updates notifications, and non-sensitive diagnostic tracking with %5$s.', 'clickervolt' ),
            $user_first_name,
            '<b>' . $plugin_title . '</b>',
            '<b>' . $user_login . '</b>',
            $site_link,
            $freemius_link
        );
    }
    
    cli_fs()->add_filter(
        'connect_message_on_update',
        '\\ClickerVolt\\cli_fs_custom_connect_message_on_update',
        10,
        6
    );
}

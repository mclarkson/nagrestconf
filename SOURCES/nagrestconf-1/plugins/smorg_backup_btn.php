<?php

    /***********************************************************************
     *
     * DEFINE THE NAMESPACE
     *
     ***********************************************************************
     */

    namespace Smorg\backup_btn;

    define(__NAMESPACE__ . '\NS', __NAMESPACE__ . '\\');


    /***********************************************************************
     *
     * PLUGIN CALLBACK FUNCTIONS
     *
     ***********************************************************************
     */
 
    # ------------------------------------------------------------------------
    function initialize_plugin() {
    # ------------------------------------------------------------------------

        #
        # Don't do any more if this is not the 'Hosts' tab.
        #

        #if( spi_get_tab_name( spi_get_tab_idx() ) != 'hosts' ) return;

        #
        # So, this is the 'Services' tab - add callbacks and
        # include the implementation using plugins_load().
        #

        plugins_load( "plugins-lib/smorg_backup_btn_impl.php" );

        spi_add_action( 'button', NS . 'backup_button_html' );
        spi_add_action( 'dlgdiv', NS . 'add_backup_dlg_div' );
        spi_add_action( 'action', NS . 'backup_page_actions' );
    }
 
    /***********************************************************************
     *
     * SETUP CALLBACKS
     *
     ***********************************************************************
     */

    spi_add_action( 'init', NS . 'initialize_plugin' );

?>

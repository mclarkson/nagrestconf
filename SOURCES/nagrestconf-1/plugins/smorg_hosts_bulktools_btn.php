<?php

    /***********************************************************************
     *
     * DEFINE THE NAMESPACE
     *
     ***********************************************************************
     */

    namespace Smorg\hosts_bulktools_btn;

    define(__NAMESPACE__ . '\NS', __NAMESPACE__ . '\\');


    /***********************************************************************
     *
     * PLUGIN CALLBACK FUNCTIONS
     *
     ***********************************************************************
     */
 
    # ------------------------------------------------------------------------
    function button_html( ) {
    # ------------------------------------------------------------------------
        print '<input id="bulkhsttool" type="button" value="Bulk Tools" />';
        print '<hr />';
    }

    # ------------------------------------------------------------------------
    function add_dlg( ) {
    # ------------------------------------------------------------------------

        # Output divs to contain dialog boxes
        show_delete_service_dlg_div( );
        show_edit_service_dlg_div( );
    }
        
    # ------------------------------------------------------------------------
    function initialize_plugin() {
    # ------------------------------------------------------------------------

        #
        # Don't do any more if this is not the 'Services' tab.
        #

        if( spi_get_tab_name( spi_get_tab_idx() ) != 'hosts' ) return;

        #
        # So, this is the 'Services' tab - add callbacks and
        # include the implementation using plugins_load().
        #

        #plugins_load( "plugins-lib/smorg_services_tab_impl.php" );

        spi_add_action( 'button', NS . 'button_html' );
    }
 
    /***********************************************************************
     *
     * SETUP CALLBACKS
     *
     ***********************************************************************
     */

    spi_add_action( 'init', NS . 'initialize_plugin' );

?>

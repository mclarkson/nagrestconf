<?php

    /***********************************************************************
     *
     * DEFINE THE NAMESPACE
     *
     ***********************************************************************
     */

    namespace Smorg\services_tab;

    define(__NAMESPACE__ . '\NS', __NAMESPACE__ . '\\');


    /***********************************************************************
     *
     * PLUGIN CALLBACK FUNCTIONS
     *
     ***********************************************************************
     */
 
    # ------------------------------------------------------------------------
    function process_tab( ) {
    # ------------------------------------------------------------------------
        parse_str( $_SERVER['QUERY_STRING'], $query_str );

        # Show html fragments or run REST actions
        services_page_actions( $query_str );

        #session_start( );
        show_html_header();

        check_REST_connection();

        show_services_page( );

        # Output divs to contain dialog boxes
        show_delete_service_dlg_div( );
        show_edit_service_dlg_div( );
    }
        
    # ------------------------------------------------------------------------
    function initialize_plugin() {
    # ------------------------------------------------------------------------
        #
        # Always add the 'Services' tab to the GUI.
        #

        # Built-in tab names from spi_get_tab_name() are:
        # 'hosts', 'servicesets', 'templates', 'contacts',
        # 'groups', 'commands' and 'timeperiods'.
        $tablist =& spi_get_tab_names_array( );

        # Get max tab id
        $max_tabid = 0;
        foreach( $tablist as $key => $tabitem ) {
            if( $tabitem[2] > $max_tabid ) $max_tabid=$tabitem[2];
        }
        # Insert the new 'services' tab after 'hosts' tab.
        foreach( $tablist as $key => $tabitem ) {
            $newarr[] = $tabitem;
            if( $tabitem[0] == "hosts" ) {
                $newarr[] = array( "services", "Services", $max_tabid + 1 );
            }
        }
        # Copy the new array back
        $tablist = $newarr;

        #
        # Don't do any more if this is not the 'Services' tab.
        #

        if( spi_get_tab_name( spi_get_tab_idx() ) != 'services' ) return;

        #
        # So, this is the 'Services' tab - add callbacks and
        # include the implementation using plugins_load().
        #

        plugins_load( "plugins-lib/smorg_services_tab_impl.php" );

        spi_add_action( 'tab', NS . 'process_tab' );
    }
 
    /***********************************************************************
     *
     * SETUP CALLBACKS
     *
     ***********************************************************************
     */

    spi_add_action( 'init', NS . 'initialize_plugin' );

?>

<?php
  function output_sidebar( $location )
  {
    print <<<EnD
    <div class="col-xs-6 col-sm-3 sidebar-offcanvas" id="sidebar" role="navigation">
    <div class="list-group">
EnD;

    $items = array( 
      array( "/", "Home" ),
      array( "/downloads.php", "Downloads" ),
      array( "/documentation.php", "Documentation" ),
      array( "/installguide.php", "Installation Guides" ),
      array( "/sourcecode.php", "Source Code" ),
      array( "/news.php", "News" ),
      array( "/gallery.php", "Gallery" ),
      array( "/support.php", "Support" ),
      array( "http://blogger.smorg.co.uk", "Blog" )
    );

    foreach( $items as $item ) {
      $active="";
      if( $item[1] == $location ) $active=" active";
      print '<a href="'.$item[0].'" class="list-group-item'.$active.'">'.$item[1].'</a>';
    }
   
    print <<<EnD
    </div>
    <img alt="Download nagrestconf" src="https://img.shields.io/sourceforge/dw/nagrestconf.svg">
    </div><!--/span-->
    </div><!--/row-->
EnD;
  }
?>

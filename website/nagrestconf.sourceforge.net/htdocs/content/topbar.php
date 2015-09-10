<?php
  function output_topbar( $location )
  {
    print <<<EnD
      <div class="navbar navbar-fixed-top navbar-inverse" role="navigation">
      <div class="container">
      <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/" style="padding-top: 10px; padding-bottom: 0px;"><img src="/images/meerkat_32x32.png" style="padding-right: 4px;">Nagrestconf</a>
      </div>
      <div class="collapse navbar-collapse">
      <ul class="nav navbar-nav">
EnD;

    $items = array( 
      array( "/", "Home" ),
      array( "/history.php", "About" ),
      array( "/contact.php", "Contact" ),
      array( "/synagios.php", "Synagios" ),
      array( "/documentation.php", "Documentation" ),
      array( "/support.php", "Support" ),
    );

    foreach( $items as $item ) {
      $active="";
      if( $item[1] == $location ) $active=' class="active"';
      print '<li'.$active.'><a href="'.$item[0].'">'.$item[1].'</a></li>';
    }
   
    print <<<EnD
      </ul>
      </div><!-- /.nav-collapse -->
      </div><!-- /.container -->
      </div><!-- /.navbar -->
EnD;
  }
?>

<?php
# ----------------------------------------------------------------------------
    header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
    header('Pragma: no-cache'); // HTTP 1.0.
    header('Expires: 0'); // Proxies.

    print <<<EnD
<!DOCTYPE html>
<html lang="en">
<head>
EnD;

    print file_get_contents ( "../content/header.frag" );
    print "<title>Nagrestconf - Troubleshooting</title>";
# ----------------------------------------------------------------------------
?>

</head>
<body>

<?php
# ----------------------------------------------------------------------------
    include "../content/topbar.php";
    output_topbar( "Documentation" );
# ----------------------------------------------------------------------------
?>

<div class="container">
<div class="row row-offcanvas row-offcanvas-right">
<div class="col-xs-12 col-sm-9">
<p class="pull-right visible-xs">
<button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">Toggle nav</button>
</p>

<?php
# ----------------------------------------------------------------------------
    print file_get_contents ( "content/troubleshooting.html.frag" );
# ----------------------------------------------------------------------------
?>

</div>

<?php
# ----------------------------------------------------------------------------
    include "../content/sidebar.php";
    output_sidebar( "Documentation" );
    print file_get_contents ( "../content/footer.frag" );
# ----------------------------------------------------------------------------
?>

</div><!--/.container-->

<?php
# ----------------------------------------------------------------------------
    print file_get_contents ( "../content/footerscripts.frag" );
# ----------------------------------------------------------------------------
?>

<script>
  $("#cookbook > ol > li > a").click( function() {
    $(this).parent().children("div").toggle(200);
  });
</script>

</body>
</html>


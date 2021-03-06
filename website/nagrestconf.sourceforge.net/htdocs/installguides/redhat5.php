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
    print "<title>Nagrestconf - Install on Redhat/Centos 5</title>";
# ----------------------------------------------------------------------------
?>

</head>
<body>

<?php
# ----------------------------------------------------------------------------
    include "../content/topbar.php";
    output_topbar( "Installation Guides" );
# ----------------------------------------------------------------------------
?>

<div class="container">
<div class="row row-offcanvas row-offcanvas-right">

<?php
# ----------------------------------------------------------------------------
    print file_get_contents ( "content/redhat5.html.frag" );
# ----------------------------------------------------------------------------
?>

<?php
# ----------------------------------------------------------------------------
    include "../content/sidebar.php";
    output_sidebar( "Installation Guides" );
    print file_get_contents ( "../content/footer.frag" );
# ----------------------------------------------------------------------------
?>

</div><!--/.container-->

<?php
# ----------------------------------------------------------------------------
    print file_get_contents ( "../content/footerscripts.frag" );
# ----------------------------------------------------------------------------
?>

</body>
</html>


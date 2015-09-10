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

    print file_get_contents ( "content/header.frag" );
    print "<title>Nagrestconf Home</title>";
# ----------------------------------------------------------------------------
?>

</head>
<body>

<?php
# ----------------------------------------------------------------------------
    include "content/topbar.php";
    output_topbar( "Home" );
# ----------------------------------------------------------------------------
?>

<div class="container">
<div class="row row-offcanvas row-offcanvas-right">

<?php
# ----------------------------------------------------------------------------
    print file_get_contents ( "content/index.html.frag" );
# ----------------------------------------------------------------------------
?>

<?php
# ----------------------------------------------------------------------------
    include "content/sidebar.php";
    output_sidebar( "Home" );
    print file_get_contents ( "content/footer.frag" );
# ----------------------------------------------------------------------------
?>

</div><!--/.container-->

<?php
# ----------------------------------------------------------------------------
    print file_get_contents ( "content/footerscripts.frag" );
# ----------------------------------------------------------------------------
?>

</body>
</html>


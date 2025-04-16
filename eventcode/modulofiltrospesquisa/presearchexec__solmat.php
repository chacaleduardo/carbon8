<?php
    if($_GET['_modulo']=="solmatmeios")
	    $_SESSION["SEARCH"]["WHERE"][] = " tipo in ('MEIOS', 'ESTÉRIL')";
    elseif($_GET['_modulo']=="solmat")
	    $_SESSION["SEARCH"]["WHERE"][] = " tipo='MATERIAL'";
?>
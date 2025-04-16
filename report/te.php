
<!DOCTYPE HTML>
<html>
<head>
	<link rel="shortcut icon" type="image/ico" href="../inc/img/favicon.ico"/>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<meta name="robots" content="noindex">
	<meta name="googlebot" content="noindex">
	<link rel="icon" href=".../.../favicon.ico">

	<title id="cbTitle"></title>

	<link rel="stylesheet" href="../inc/css/bootstrap/css/bootstrap.min.css" />
<script>
/*
 * Detecta versão inválida do IE
 * Referência: http://browserhacks.com/
 */
function ieInvalido(){

        var isIElte8_1 = !+'\v1';
        var isIElte8_2 = '\v'=='v';
        var isIElte8_3 = document.all && !document.addEventListener;
        var isIElte8_4 = document.all && document.querySelector && !document.addEventListener;

        if(isIElte8_1 || isIElte8_2 || isIElte8_3 || isIElte8_4){
                return true;
        }else{
                return false;
        }
}
if(ieInvalido()){
        alert("VocÃª estÃ¡ utilizando uma versÃ£o invÃ¡lida do Internet Explorer.\n\nRecomendaÃ§Ã£o: Utilize o Firefox ou Google Chrome!");
}
//# sourceURL=/index.php_ieinvalido
</script>
	<!-- O Jquery e o outdatedbrowser devem estar fora do mergearquivos -->
	<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>

	<script src="../inc/js/jquery/jquery-ui.js"></script>
	<script src="../inc/js/jquery/jquery.autosize-min.js"></script>
	<script src="../inc/css/bootstrap/js/bootstrap.min.js"></script>
	<script src="../inc/js/moment/moment.min.js"></script>
	<script src="../inc/js/htmlentities/he.js"></script>
	<script src="../inc/js/daterangepicker/daterangepicker.js"></script>
	<script src="../inc/js/notifications/smart.js"></script>
	<script src="../inc/js/webuipopover/jquery.webui-popover.js"></script>
	<script src="../inc/js/accent-fold.js"></script>
	<script src="../inc/js/bootstrap-select/bootstrap-select.js"></script>
	<script src="../inc/js/bootstrap-select/i18n/defaults-pt_BR.js"></script>
	<script src="../inc/js/bootstrap-select/i18n/defaults-pt_BR.js"></script>
	<script src="../inc/js/tinymce/tinymce.min.js"></script>
	<script src="../inc/lbox/js/lightbox.js"></script>
	<script src="../inc/js/diagrama/vendor/raphael.js"></script>
	<script src="../inc/js/colorpalette/js/bootstrap-colorpalette.js"></script>
	<script src="../inc/js/cookie/js.cookie.js"></script>
	<script src="../inc/js/ping/ping.js"></script>
	<script src="../inc/js/autosize/autosize.min.js"></script>
	<script src="../inc/fullcalendar/fullcalendar.min.js"></script>
	<script src="../inc/fullcalendar/locale/pt-br.js"></script>
	<script src="../inc/js/functions.js"></script>
	<script src="../inc/js/cookie/js.cookie.js"></script>
	<script src="../inc/js/bowser/es5.js"></script>
	<script src="../inc/tmp/feriado.js"></script>
	<script src="../inc/tmp/calendarioferiado.js"></script>
<style>

	._btnAssinatura {
		display: none !important;
	}

	button.btn.btn-default.fa.fa-edit.hoverlaranja.pointer {
		display: none !important;
	}

	.message-text img {
		cursor: zoom-in;
	}

	.ui-autocomplete {
		max-height: 200px;
		overflow-y: auto;
		/* prevent horizontal scrollbar */
		overflow-x: hidden;
		/* add padding to account for vertical scrollbar */
		padding-right: 20px;
	}
</style>

	<!-- CSS Carbon -->
	<link href="../inc/css/carbon.css?_24092020010952" rel="stylesheet">
	<link href="../inc/css/sislaudo.css?_24092020010952" rel="stylesheet">
	<!-- link href="../inc/js/diagrama/Treant.css?_24092020010952" rel="stylesheet" -->
	
	<style>
*, *:before, *:after {box-sizing:  border-box !important;}


.row {
 -moz-column-width: 25em;
 -webkit-column-width: 25em;
 -moz-column-gap: .5em;
 -webkit-column-gap: .5em; 
  
}

.panel {
 display: inline-block;
 margin:  .5em;
 padding:  0; 
 width:98%;
}
</style>
<div class="container">
  <h3>Masonry with Bootstrap Panels (separate columns)</h3>
  <div class="row">
    <div class="panel panel-default">
 		<div class="panel-heading">Title</div>
 		<div class="panel-body">Content here.. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis pharetra varius quam sit amet vulputate. 
        Quisque mauris augue, gravida a libero. Aenean sit amet felis 
        dolor, in sagittis nisi. Sed ac orci quis tortor imperdiet venenatis. Duis elementum auctor accumsan. 
        Aliquam in felis sit amet augue.</div>
 	</div>
	<div class="panel panel-default">
 		<div class="panel-heading">Title</div>
 		<div class="panel-body">Content here.. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis pharetra varius quam sit amet vulputate. 
        Quisque mauris augue orci quis tortor imperdiet venenatis. Duis elementum auctor accumsan. 
        Aliquam in felis sit amet augue.</div>
 	</div>
	<div class="panel panel-default">
 		<div class="panel-heading">Title</div>
 		<div class="panel-body">Content here.. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis pharetra varius quam sit amet vulputate. 
        Quisque mauris augue orci quis tortor imperdiet venenatis. Duis elementum auctor accumsan. 
        Aliquam in felis sit amet augue.</div>
 	</div>
    <div class="panel panel-default">
 		<div class="panel-heading">Panel</div>
 		<div class="panel-body">Content here.. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
        Aliquam in felis sit amet augue.</div>
 	</div>
	<div class="panel panel-default">
 		<div class="panel-heading">Title</div>
 		<div class="panel-body">Content here.. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis pharetra varius quam sit amet vulputate. 
        Quisque mauris augue orc. Duis elementum auctor accumsan. 
        Aliquam in felis sit amet augue.</div>
 	</div>
	<div class="panel panel-default">
 		<div class="panel-heading">Title</div>
 		<div class="panel-body">Content here.. 
        Aliquam in felis sit amet augue.</div>
 	</div>
	<div class="panel panel-default">
 		<div class="panel-heading">Panel</div>
 		<div class="panel-body">Content here.. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis pharetra varius quam sit amet vulputate. 
        Quisque imperdiet venenatis. Duis elementum auctor accumsan. 
        Aliquam in felis sit amet augue.</div>
 	</div>
	<div class="panel panel-default">
 		<div class="panel-heading">Title</div>
 		<div class="panel-body">ng elit. Duis pharetra varius quam sit amet vulputate. 
        Quisque mauris augue orci quis tortor imperdiet venenatis. Duis elementum auctor accumsan. 
        Aliquam in felis sit amet augue.</div>
 	</div>
</div>
</div>

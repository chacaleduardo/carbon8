<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

//error_reporting(E_WARNING);

$idobjeto = $_POST["idobjeto"];
$tipoobjeto = $_POST["tipoobjeto"];
$tipoarquivo = $_POST["tipoarquivo"];
$caminho = $_POST["caminho"];
$fileElementName = "arquivo";

$mostraarquivo=$_GET["mostraarquivo"];
$mostraanexar=$_GET["mostraanexar"];
$mostraexcluir=$_GET["$mostraexcluir"];




function tradbytes($bytes)
{
    $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
    for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
    return( round( $bytes, 2 ) . " " . $types[$i] );
}
//verifica se o arquivo foi enviado via post

if(!empty($_POST)){
	
	
	//print_r($_GET);
//print_r($_SERVER); die;
	
	if(empty($idobjeto) or empty($tipoobjeto) or empty($tipoarquivo) or empty($caminho)){
		
		msgupload(false,"Parametros POST nao enviados corretamente!");
		
	}else{
		//pega o tamanho do arquivo
		$tamanho = $_FILES[$fileElementName]["size"];
		
		//Gera um nome único para o arquivo e retira caracteres indesejados
		$arq_nome = nomenovoarq($_FILES[$fileElementName]['name']); 
		
		//concatena o caminho que foi passado via GET
		$arq_final = $caminho . $arq_nome;
		//$arq_nome = $_SERVER["DOCUMENT_ROOT"]."".$arq_nome;
		
		// Coloca o arquivo na pasta finnal
		$booupload = move_uploaded_file($_FILES[$fileElementName]['tmp_name'], $arq_final);
		
		//Se a pasta nao existir ou alguma falha ocorrer
		if(!$booupload){
			
			msgupload(false,"Falha ao mover o arquivo [".$arq_final."]");
					
		}else{
	
			//Tenta inserir no banco os dados do arquivo
			$sqlarquiv = "insert into arquivo (tipoarquivo,caminho,tamanho,idobjeto,tipoobjeto,nome) 
			values ('".$tipoarquivo."','".$arq_final."','".tradbytes($tamanho)."',".$idobjeto.",'".$tipoobjeto."','".$arq_nome."')";
			
			$booins = d::b()->query($sqlarquiv);
			
			//se houver algum erro deletar o arquivo enviado
			if(!$booins){
				//deleta o arquivo gerado
				@unlink($arq_nome);
				msgupload(false,"Erro ao gravar dados do arquivo no Banco de Dados:\n<br>".mysqli_error()."\n<br>Sql:".$sqlarquiv);
						
			}else{
				
				msgupload(true);
				
			}
		}	
	}
}//if(!empty($_POST)){
?>
<html>
<head>
	<link href="../inc/css/sislaudo.css" rel="stylesheet" type="text/css" />
	<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
	<script src="../inc/js/functions.js"></script>

<style>
html{
	background-color: transparent;
}
</style>

</head>

<script language="javascript">
	$(document).ready(function(){

		$("#arquivo").change(function(){
			//$("#processando").show();
			blink(1,'processando');
		})

	});

var SITE = SITE || {};

SITE.fileInputs = function() {
  var $this = $(this),
      $val = $this.val(),
      valArray = $val.split('\\'),
      newVal = valArray[valArray.length-1],
      $button = $this.siblings('.button'),
      $fakeFile = $this.siblings('.file-holder');
  if(newVal !== '') {
    $button.text('Enviar Arquivo');
    if($fakeFile.length === 0) {
      $button.after('<span class="file-holder">' + newVal + '</span>');
    } else {
      $fakeFile.text(newVal);
    }
  }
};

function ajustaframepar(){

	// Verifica se existe o parent
    if (parent != null) {

    	//captura colecao com todos os IFRAME do parent
        var frameList = parent.document.body.getElementsByTagName("iframe");

        //Se retornar algum, prossegue
        if (frameList != null) {

            for (var i = 0; i < frameList.length; i++) {

                var srcName = frameList[i].src;//url do objeto iframe

                //srcName = srcName.substr(srcName.lastIndexOf("/"));

                var srcParent = document.location.href;//url documento filho

                //alert("Parent=" + srcParent + "\nFrame="+ srcName);

                if (srcParent == srcName) {

                    // Ajusta o tamanho
										if(document.body.offsetHeight>0){
											frameList[i].height = document.body.offsetHeight;
										}else{
											frameList[i].height = 200;//o por cento nao funciona
										}

                    // tira a barra de rolagem
                    frameList[i].scrolling = "no";

                }

            }

        }

    }
}


</script>
<?//botão para clicar e anexar o arquivo
$idobjeto = $_GET["idobjeto"];
$escondedata=$_GET["escondedata"];
$escondetamanho=$_GET["escondetamanho"];
$mostraminiatura=$_GET["mostraminiatura"];
$refreshparent=$_GET["refreshparent"];


//escreve o cabeçalho de um fieldset
?>
<br>

<?
if($mostraarquivo=='S' or empty($mostraarquivo)){
	if(!empty($idobjeto)){
		$sqlarq = "select a.*, dmahms(criadoem) as datacriacao 
					from arquivo a 
					where 
						a.tipoobjeto = '".$_GET["tipoobjeto"]."' 
						and a.idobjeto = ".$idobjeto." 
						and tipoarquivo = '".$_GET["tipoarquivo"]."' 
					order by idarquivo asc";
	
		//echo $sqlarq."<br>";
		$res = d::b()->query($sqlarq) or die("Erro ao pesquisar arquivos:".mysqli_error(d::b()));
		$numarq= mysqli_num_rows($res);
		
		if($numarq>0){
?>
<table border='0' style="border-collapse: collapse;">
<tr style="border-bottom:1px solid silver;">
	<td style='font-size:9px;padding-left: 10px;'>Arquivos Enviados (<?=$numarq?>)</td>
<?
			if($escondedata!='Y'){
?>
	<td style='font-size:9px;padding-left: 10px;'>Data</td>
<?
			}
?>
<?
			if($escondetamanho!='Y'){
?>
	<td style='font-size:9px;padding-left: 10px;'>Tamanho</td>
<?
			}
?>
</tr>
<?
			while ($row = mysqli_fetch_array($res)) {
?>
<tr style="border-bottom:1px dotted silver;">
	<td style="font-size: 11px;padding-left: 10px;vertical-align:middle;" nowrap>
		<img border="0" src="../inc/img/list.gif" title="Abrir arquivo"></img>
<?
				if($mostraminiatura=='Y'){
?>
		<a title="Abrir arquivo" target="_blank"  href="../upload/<?=$row["nome"]?>">
			<img width="15" height="15	" src="../upload/<?=$row["nome"]?>" border="0" title="<?=$row["nome"]?>">
		</a>
<?
				}else{
?>
		<a title="Abrir arquivo" target="_blank"  href="../upload/<?=$row["nome"]?>"><?=$row["nome"]?></a>
<?
				}
?>
	</td>
<?
				if($escondedata!='Y'){
?>
	<td style="font-size: 9px;padding-left: 10px;"><?=$row["datacriacao"]?></td>
<?
				}
				
				if($escondetamanho!='Y'){
?>
	<td style="font-size: 9px;padding-left: 10px;"><?=$row["tamanho"]?></td>
<?
				}

?>
</tr>
<?
			}//while
		}//$numarq
	
	}else{
		echo "<script language='javascript'>alert('Erro: idobjeto vazio!')</script>";
	}

?>
</table>
<?
}//mostraarquivo
?>

</body>
</html>


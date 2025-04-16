<?
session_start();
require_once("../inc/php/functions.php");
//require_once("../inc/php/validaacesso.php");

function buscatitulo($aux){
	switch($aux){
		case 'COTACAO':
			$_titulo = 'Cotação';
			break;
		case 'ORCPROD':
			$_titulo = 'Orçamento de Produto';
			break;
		case 'NFP':
			$_titulo = 'Nota Fiscal de Produto';
			break;
		case 'NFPS':
			$_titulo = 'Nota Fiscal de Produto';
			break;
		case 'NFS':
			$_titulo = 'Nota Fiscal de Serviço';
			break;
		case 'COTACAOAPROVADA':
			$_titulo = 'Cotação Aprovada';
			break;
		case 'ORCSERV':
			$_titulo = 'Orçamento de Serviços';
			break;
		case 'DETALHAMENTO':
			$_titulo = 'Detalhamento';
			break;
		case 'RESULTADOOFICIAL':
			$_titulo = 'Resultados Oficiais';
			break;
		case 'vertodos':
			return false;
			break;
		default: 
			die("Parâmetros Inválidos!");
			break;
	}
	
	return $_titulo;
}

if(!empty($_GET["idempresa"]) and !empty($_GET["tipoemail"])){
	$_idempresa=$_GET["idempresa"];
	$_tipoemail=$_GET["tipoemail"];
	
	$_titulo = buscatitulo($_tipoemail);
	
	if($_titulo){
		$sqlempresaemail = "SELECT ev.email_original AS dominio
							FROM empresaemails em 
							JOIN emailvirtualconf ev ON (em.idemailvirtualconf = ev.idemailvirtualconf)
							WHERE em.tipoenvio = '{$_tipoemail}'
							AND em.idempresa ={$_idempresa}
							AND ev.status = 'ATIVO'
							ORDER BY em.idempresaemails asc limit 1";
		$resempresaemail=d::b()->query($sqlempresaemail) or die("Erro ao buscar email da empresa sql=".$sqlempresaemail);
		$rowempresaemail = mysqli_fetch_assoc($resempresaemail);
		$qtdempresaemail= mysqli_num_rows($resempresaemail);
		if($qtdempresaemail>0){
			$_aux = imagemtipoemailempresa($_tipoemail,$_idempresa,$rowempresaemail["dominio"]);
			?>
			<div id="rodape">
				<p><?=$_titulo?></p>
				<?=$_aux?>
			</div>
		<?}
		else{
			echo "Não há email configurado para essa empresa";
		}
	}else{
		$sqlrodape="SELECT DISTINCT e.tipoenvio,
					(
						SELECT ev.email_original AS dominio
						FROM empresaemails em 
						JOIN emailvirtualconf ev ON (em.idemailvirtualconf = ev.idemailvirtualconf)
						WHERE em.tipoenvio = e.tipoenvio 
						AND em.idempresa = e.idempresa 
						AND ev.status = 'ATIVO'
						ORDER BY em.idempresaemails asc limit 1
					) AS dominio 
					FROM empresarodapeemail e
					WHERE e.idempresa=$_idempresa";
		$resrodape=d::b()->query($sqlrodape) or die("Erro ao buscar rodapés de email : " . mysqli_error(d::b()) . "<p>SQL:".$sqlrodape);
        $qtdrodape= mysqli_num_rows($resrodape);
		if($qtdrodape>0){
			while($rowrodape=mysqli_fetch_assoc($resrodape)){
				if(!empty($rowrodape["dominio"])){
					$_titulo = buscatitulo($rowrodape["tipoenvio"]);
					$_aux = imagemtipoemailempresa($rowrodape["tipoenvio"],$_idempresa,$rowrodape["dominio"]);
				?>
				<div>
					<p><?=$_titulo?></p>
					<?=$_aux?>
				</div>
				<?}else{
					echo "Não há email configurado para essa empresa<br>";
				}
			}
		}
	}
	
}else{
	echo "Parâmetros Inválidos!";
}

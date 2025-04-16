<?
require_once("../inc/php/functions.php");
require_once("../inc/php/cmd.php");
//Jwt
require_once '../inc/php/jwt/firebase/php-jwt/src/BeforeValidException.php';
require_once '../inc/php/jwt/firebase/php-jwt/src/ExpiredException.php';
require_once '../inc/php/jwt/firebase/php-jwt/src/SignatureInvalidException.php';
require_once '../inc/php/jwt/firebase/php-jwt/src/JWT.php';
use \Firebase\JWT\JWT;

if($_POST){

	$_headers = array_change_key_case($_headers, CASE_LOWER);

	if(!$_headers["jwt"]){
		die("Token não enviado");
	}

	try{
		$decoded = JWT::decode($_headers["jwt"], "biofabricashubio", array('HS256'));
		//echo var_dump($decoded);
	}catch(Exception $e){
		die("Falha de token");
	}

	if(empty($_POST["bf"]) or empty($_POST["ip"]) or empty($_POST["js"])){
		die("Parametros nao enviados");
	}else{

	$_POST["bf"]=trim(d::b()->real_escape_string($_POST["bf"]));
	$_POST["local"]=trim(d::b()->real_escape_string($_POST["local"]));
	$_POST["ip"]=trim(d::b()->real_escape_string($_POST["ip"]));
	$_POST["js"]=trim(d::b()->real_escape_string($_POST["js"]));
    $_POST["ipext"]=trim(d::b()->real_escape_string($_POST["ipext"]));

        $sqlbfl = "insert into biofabricastatuslog (biofabrica, ip, ipext, criadoem) values ('".$_POST["bf"]."','".$_POST["ip"]."','".$_POST["ipext"]."',now())";
        d::b()->query($sqlbfl);

		$sqlbf = "insert into biofabricastatus (biofabrica,local,ip,ipext,arquivojs,criadoem,alteradoem)
	values (
	'".$_POST["bf"]."',
	'".$_POST["local"]."',
	'".$_POST["ip"]."',
	'".$_POST["ipext"]."',
	'".$_POST["js"]."'
	,now(),now()) 
	ON DUPLICATE KEY UPDATE local='".$_POST["local"]."', ip='".$_POST["ip"]."', ipext='".$_POST["ipext"]."',arquivojs='".$_POST["js"]."',ultimoping=now()";

		//die($sqlbf);
		$resi=d::b()->query($sqlbf);

		if(!$resi){
			die(mysqli_error(d::b()));
		}else{
		    echo "ok: ".$_POST["bf"]." / ".$_POST["local"]." / ".$_POST["ip"]." / ".$_POST["ipext"]." / ".$_POST["js"];
			die;
		}
	}
}else{
	$sbio="select @atrasominutos:=timestampdiff(minute,ultimoping,now()) as atrasominutos, idbiofabricastatus, local, biofabrica, if(@atrasominutos>5,'',ip)as ip, ipext, arquivojs, ultimoping  
	from biofabricastatus order by biofabrica";

	$resb=d::b()->query($sbio);
	if(!$resb){
        	die(mysqli_error(d::b()));
	}
?>
<table class="table table-hover">
	<tr>
		<td>Biofábrica</td>
		<td>Local</td>
                <td title="Ip">Ip</td>
                <td title="Ip link Internet">IpExt</td>
		<td>Js</td>
                <td>Visto por último</td>
	</tr>
<?
	while ($r = mysqli_fetch_assoc($resb)) {
	    $lbultimoping=$r["atrasominutos"]>5?"<label class='blink'>🔴</label>&nbsp;":"<label class=''>🟢</label>&nbsp;";
?>
	<tr>
		<td><a target=_blank href="https://<?=$r["biofabrica"]?>.hubioagro.com.br"><?=$r["biofabrica"]?></a></td>
		<td><?=$r["local"]?></td>
		<td><?=$r["ip"]?></td>
		<td><?=$r["ipext"]?></td>
		<td><?=$r["arquivojs"]?></td>
		<td><?=$lbultimoping?><?=dmahms($r["ultimoping"],true)?></td>
	</tr>
<?
	}
?>
</table>
<?
}


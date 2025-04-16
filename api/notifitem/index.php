<?
require_once "../../inc/php/functions.php";

$vjwt = validaTokenReduzido();

$jwt = $vjwt["token"];

if( $vjwt["sucesso"] === true and $jwt->idtipopessoa == 1 ){

	requestControl($vjwt);

    $arrnotif=re::dis()->lrange('_notifitem:'.$jwt->idpessoa, 0, -1);

	echo '{ "messages": ['.implode(',',$arrnotif).'] }';

    re::dis()->del('_notifitem:'.$jwt->idpessoa);

}else{
	//O Erro de validacao de token esta sendo feito neste local, para que providencias sejam tomadas caso o usuario nao esteja usando o App
	if($vjwt["code"]===0){
		header("HTTP/1.0 402 Token Expirado");
	}else{
		header("HTTP/1.0 403 ".$vjwt["erro"]);
	}
}

<?
$iu = empty($_SESSION['arrpostbuffer']['1']['u']['solcom']['idsolcom']) ? 'i' : 'u';

if(empty($_SESSION['arrpostbuffer']['99']['i']['modulocom']['descricao'])){
    unset($_SESSION['arrpostbuffer']['99']);
}
$_arrtabdef = retarraytabdef('solcomitem');

$arrsolcomitem = array();
foreach($_POST as $k => $v) 
{
	if(preg_match("/_x(\d)_u_solcomitem_(.*)/", $k, $res))
    {
        $arrsolcomitem[$res[1]][$res[2]]=$v;
	}
}

if(!empty($arrsolcomitem))
{
   // LOOP NAS QTDC DA TELA
   foreach($arrsolcomitem as $k=>$v)
   {
        $qtdc=$v['qtdc'];
        if(empty($qtdc)){die("preencha a quantidade(Qtd)");}  
   }
}

$arrInsProd = array();
foreach($_POST as $k => $v) 
{
	if(preg_match("/_(\d*)#(.*)/", $k, $res))
    {
		$arrInsProd[$res[1]][$res[2]]=$v;
	}

    if(preg_match("/modulocom_descricao/", $k, $res))
    {
        $indice = explode("_", $k);
        if($_POST[$k] == "")
        {
            unset($_SESSION['arrpostbuffer'][$indice[1]]);
        } elseif($indice[1] != "99") {
            $produto = $_SESSION['arrpostbuffer'][substr($indice[1], 2)]['u']['solcomitem']['descr'];
            $motivo = $_SESSION['arrpostbuffer'][$indice[1]]['i']['modulocom']['descricao'];
            $motivo = ' reprovou o item da compra: '.$produto.' </br></br> Motivo da Reprovação: <b> '.$motivo.' </b>';
            $_SESSION['arrpostbuffer'][$indice[1]]['i']['modulocom']['descricao'] = $motivo;
        }
    }
}

if(empty($_SESSION["arrpostbuffer"]["1"]["u"]["solcom"]["idsolcom"])){
    $idsolcom = $_SESSION["_pkid"];
} else {
    $idsolcom = $_SESSION["arrpostbuffer"]["1"]["u"]["solcom"]["idsolcom"];
}

if(!empty($arrInsProd))
{
   $i = 99977;
   // LOOP NOS ITENS DO + DA TELA
   foreach($arrInsProd as $k=>$v)
   {
      // print_r($v);die();
       $i = $i + 1;

        $idprodserv = $v['idprodserv'];
	    $prodservdescr = $v['prodservdescr'];

        if(!empty($idprodserv) OR !empty($prodservdescr) )
        {
            if(empty($idsolcom)){die("[saveprechange_solcom]-Não foi possivel identificar o ID da solicitacao!!!");}   

            // montar o item para insert
            $_SESSION['arrpostbuffer'][$i]['i']['solcomitem']['qtdc'] = $v["quantidade"];
            $_SESSION['arrpostbuffer'][$i]['i']['solcomitem']['un'] = $v["un"];
            $_SESSION['arrpostbuffer'][$i]['i']['solcomitem']['obs'] = $v["obs"];
            $_SESSION['arrpostbuffer'][$i]['i']['solcomitem']['status'] = 'PENDENTE';
            $_SESSION['arrpostbuffer'][$i]['i']['solcomitem']['idempresa'] = $_SESSION["SESSAO"]["IDEMPRESA"];
            if(!empty($v["idprodserv"])){
                $_SESSION['arrpostbuffer'][$i]['i']['solcomitem']['idprodserv'] = $v["idprodserv"];                
            }else{
                $_SESSION['arrpostbuffer'][$i]['i']['solcomitem']['descr'] = strtoupper($v["prodservdescr"]);
            }
            $_SESSION['arrpostbuffer'][$i]['i']['solcomitem']['idsolcom'] = $idsolcom;
    
        }
   } //foreach($arrInsProd as $k=>$v){
  
}//if(!empty($arrInsProd)){

// tira a session dos comentarios
if(empty( $_SESSION['arrpostbuffer']['xa']['i']['solcomicoment']['comentario']) ){
    unset($_SESSION['arrpostbuffer']['xa']['i']['solcomicoment']['comentario']);
}

if(!empty($_SESSION['arrpostbuffer']['1'][$iu]['solcom']['idsolcom']) and !empty($_SESSION["arrpostbuffer"]["1"][$iu]["solcom"]["titulo"]))
{
    $_SESSION["arrpostbuffer"]["1"][$iu]["solcom"]["titulo"] = strtoupper($_SESSION["arrpostbuffer"]["1"][$iu]["solcom"]["titulo"]);
}

if($_POST["statusant"] != $_SESSION["arrpostbuffer"]["1"][$iu]["solcom"]["status"] &&
    $_SESSION["arrpostbuffer"]["1"][$iu]["solcom"]["idpessoa"] != $_SESSION['SESSAO']['IDPESSOA'] &&
    !empty($_SESSION["arrpostbuffer"]["1"][$iu]["solcom"]["idsolcom"]) && 
    $_SESSION["arrpostbuffer"]["1"][$iu]["solcom"]["status"] != 'CONCLUIDO'){
    $notif = Notif::ini()
                ->canal("browser")
                ->conf([
                    "mod" => $_GET["_modulo"],
                    "modpk" => "idsolcom", // 
                    "idmodpk" => $_SESSION["arrpostbuffer"]["1"][$iu]["solcom"]["idsolcom"],
                    "title" => "SOLCOM: ".$_SESSION["arrpostbuffer"]["1"][$iu]["solcom"]["idsolcom"],
                    "corpo" => "Alteração de status: ".$_SESSION["arrpostbuffer"]["1"][$iu]["solcom"]["status"],
                    "localizacao" => "dashboardsnippet",
                    "url" => "https://sislaudo.laudolab.com.br/?_modulo=solcom&_acao=u&idsolcom=".$_SESSION["arrpostbuffer"]["1"][$iu]["solcom"]["idsolcom"],
                ])
                ->addDest($_SESSION["arrpostbuffer"]["1"][$iu]["solcom"]["idpessoa"])
                ->send();
}
?>
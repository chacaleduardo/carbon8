<?
require_once("../inc/php/validaacesso.php");//Este require vai ocorrer no formulario principal. Portanto irá gerar erro em caso de "include ou require sem o ONCE, na primeira chamada"
if(!empty($_POST)){
	baterponto();
}

//var_dump($_SESSION["SESSAO"]["REPPONTOEXTERNO"]);

$uvpn=ipv4_in_range($_SERVER["REMOTE_ADDR"], "10.0.8.*");
//var_dump($uvpn);
//Registro de Ponto externo bloqueado temporariamente
if(!$uvpn){
 //@todo:log stat login vpn
 if($_SESSION["SESSAO"]["REPPONTOEXTERNO"]=="NAO"){
  //@todo:log stat acesso externo
  if($_SERVER["REMOTE_ADDR"] == "192.168.0.99"
   or ($_SERVER["SERVER_NAME"]=="sislaudo.laudolab.com.br" and !ipv4_in_range($_SERVER["REMOTE_ADDR"], "192.168.*.*"))){
    //@todo:log stat
    echo '<p/>&nbsp;<p/><div class="alert alert-info fonte18">';
    echo "<i class='fa fa-info-circle'></i>&nbsp;&nbsp;<span class='bold'>Registro de Ponto bloqueado temporariamente para usuários externos.</span>";
    echo "<br/><br/>Se você estiver presente nas dependências da empresa: desligue seu 3G e conecte-se na rede Wi-fi.";
    echo "<br/><br/><span>Caso acredite se tratar de um problema: informe ao suporte de TI o seu ip de conexão: ".$_SERVER["REMOTE_ADDR"]."</span>";
    echo '</div>';
    die();
  }
 }
}

//var_dump($_SESSION["SESSAO"]["PONTOWEB"]);
//var_dump($_SESSION["SESSAO"]["ACESSO"]);

//Verifica se o usuário está configurado para bloqueio de "acesso ao sistema", e se possui algum REP configurado para ele
if($_SESSION["SESSAO"]["PONTOWEB"]=='Y' and 
	(empty($_SESSION["SESSAO"]["ACESSO"]) || $_SESSION["SESSAO"]["ACESSO"] == 'N')){

	//Verifica o ultimo status da tabela, mesmo que nao tenha sido aprovado
	$sp = "select status, criadoem
	from ponto p 
	where alteradoem > '".date('Y-m-d')." 00:00:00'
	and p.idpessoa = /* 112072 -- */ ".$_SESSION["SESSAO"]["IDPESSOA"]." 
	and status in ('E','S')
	order by idponto desc limit 10";

    echo '<!-- '.$sp.' -->';
	$resp = d::b()->query($sp);

	if(!$resp){
		echo "<i class='fa fa-exclamation-triangle'></i>&nbsp;Falha ao verificar status das batidas de Ponto anteriores";
	}else{

		$ibatidas = mysqli_num_rows($resp);

		if($ibatidas==0){
			//Caso nenhum registro seja encontrado, considerar saída
			$ultimabatida="S";
		}else{
			//Verifica batida mais recente
			$rbatidarecente=re::dis()->hGet('_estado:'.$_SESSION["SESSAO"]["IDPESSOA"].':pessoa','statusponto');
			//Esta condicao previne que a consulta seja alterada e ouros status nao previstos preencham essa variavel
			if($rbatidarecente=="Trabalhando"){
				$ultimabatida="E";
			}else{
				$ultimabatida="S";
			}
		}

		if($ultimabatida=="S"){
			$status="descanso";
			$statusrot="";
			$acao="iniciar";
			$acaorot="<i class='fa fa-sign-in'></i>&nbsp;Entrar";
		}else{
			$status="trabalhando";
			$statusrot="Trabalhando";
			$acao="parar";
			$acaorot="Descansar&nbsp;<i class='fa fa-sign-out'></i>";
		}

?>
<style>
ponto{
	display: flex;
	flex-direction: column;
	align-items: center;
}
ponto *:not(table,tr,td,table *){
	display: flex;
	justify-content: center;
	overflow: hidden;
}
ponto hdr{
	width: 100%;
	text-align: center;
	padding: 25px;
	flex-direction: column;
}
ponto hdr *{
	padding: 6px
}
ponto nome{
	width: 100%;
	font-size: 4em;
	font-weight: bold;
}
ponto horario{
  width: 100%;
	font-size: 6em;
	font-weight: bold;
}
ponto status{
	width: 100%;
	font-size: 2em;
	font-weight: bold;
}
ponto acao{
	border-radius: 15px;
	margin-top: 1em;
	margin-bottom: 1em;
	padding: 20px;
	font-size: 3em;
	font-weight: bold;
	line-height: 100%;
	cursor: pointer;
	min-width: 70vw;
	transition: background-color 100ms linear;
}
ponto hdr[status=trabalhando]{
	background-color: green;
	color: white;
}
ponto acao[acao=parar]{
	background-color: #990500;
	color: white;
}
ponto hdr[status=descanso]{
	background-color: rgb(240,240,240);
	color: black;
}
ponto acao[acao=iniciar]{
	background-color: green;
	color: white;
}
ponto acao[acao=iniciar]:hover{
	background-color: #00b200;
}

</style>

<script>
CB.oMenuSuperior.hide();
CB.oModuloHeader.hide();

var verificaPermissoesGeo = async function(){
  const { state } = await navigator.permissions.query({
    name: "geolocation"
  });
  switch (state) {
    case "granted":
      ostatus.innerHTML="Iniciando configuração de Posicionamento.";
      configuraGPS(true);
      break;
    case "prompt":
      ostatus.innerHTML="Verificando permissões de Posicionamento.";
      solicitaPermissaoGPS();
      break;
    case "denied":
      ostatus.innerHTML="Navegador com bloqueio para Posicionamento.";
      informaLiberacaoManualGPS();
      setTimeout(function(){
        console.log("Tentando novamente para o estado de ["+state+"]");
        verificaPermissoesGeo();
      }, 4000);
      break;
    default:
      ostatus.innerHTML="O GPS está no estado ["+state+"]";
      setTimeout(function(){
        console.log("Tentando novamente para o estado de ["+state+"]");
        verificaPermissoesGeo();
      }, 4000);
  }
}

const geolocationOptions = {
  enableHighAccuracy: true,
  maximumAge: 10000,
  timeout: 5000,
};

var solicitaPermissaoGPS = async function(){
  ostatus.innerHTML="Solicitando permissões do GPS";
  navigator.geolocation.getCurrentPosition(
    gpsSucesso,
    gpsErro,
    geolocationOptions
  );
}

var gpsSucesso = (geolocation) => {
  ostatus.innerHTML="Configurando GPS";
  configuraGPS(true);
};

var gpsErro = (error) => {
  switch(error.code){
    case 1:
      ostatus.innerHTML="Você deve dar permissões para que o browser utilize recursos de posicionamento. Clique no cadeado e configure corretamente.";
      break;
    case 2:
      ostatus.innerHTML="O brower falhou ao recuperar seu posicionamento. Recarregue a página, ou feche o browser e tente novamente.";
      break;
    case 3:
      ostatus.innerHTML="O browser demorou muito tempo a fornecer seu posicionamento. Recarregue a página, ou feche o browser e tente novamente.";
      break;
  }
  ostatus.innerHTML=ostatus.innerHTML+"<br>Caso precise de ajuda, entre em contato com o Suporte de TI.";
  console.log(error);
};

var informaLiberacaoManualGPS=function(){
  ostatus.innerHTML=`Você deve liberar o browser a obter seu posicionamento.
     <br>Para isso, clique no ícone de cadeado, na barra de endereços do navegador,
     <br>e habilite a opção relacionada à localização`;
  configuraGPS(true);
}
var configuraGPS = async function(inreload){
  navigator.geolocation.getCurrentPosition(
    (geo)=>{
      ostatus.innerHTML="Armazenando informações de posicionamento...";
      setCookie("x-gps-status","Habilitado",30);
      setCookie("x-gps-lat",geo.coords.latitude,1);
      setCookie("x-gps-lon",geo.coords.longitude,1);
      if(inreload && inreload===true){
      	window.location.reload();
      }
      console.log(geo);
    },
    (err)=>{
      console.log(err);
      ostatus.innerHTML="Clique no cadeado na barra de endereços e libere as opções de localização.<br>Em seguida, recarregue a página, ou feche seu browser e tente novamente.";
    },
    geolocationOptions
  );
}

var setCookie = function(cname, cvalue, exdays) {
  const d = new Date();
  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
  let expires = "expires="+d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

var ostatus=document.querySelector("ponto status");

//# sourceURL=/gps_scripts
</script>
<?
		if($_COOKIE["x-gps-status"]=="Habilitado" or $_SESSION["SESSAO"]["REPPONTOEXTERNO"]!=="SIMGPS"){
?>
<ponto>
	<hdr status="<?=$status?>">
		<nome><?=explode(' ',$_SESSION["SESSAO"]["NOME"])[0]?></nome>
		<horario id="relogioservidor">__:__:__</horario>
		<status><?=$statusrot?></status>
	</hdr>

	<acao acao="<?=$acao?>" onclick="bateponto(this)"><?=$acaorot?></acao>

	<listaponto>
		<table class="planilha grade">
			<tr><td colspan="99">Últimos Lançamentos:</td></tr>
<?
mysqli_data_seek($resp, 0);
while($p=mysqli_fetch_assoc($resp)){
?>
			<tr>
				<td><?=$p["status"]=="S"?"<i class='fa fa-sign-out vermelho' alt='Saída'></i>":"<i class='fa fa-sign-in verde' alt='Entrada'></i>"?></td>
				<td><?=dmahms($p["criadoem"],true)?></td>
			</tr>
<?}?>
		</table>
	</listaponto>
</ponto>
<button class="dropdown-item right" onclick="localStorage.removeItem('jwt');Cookies.remove('jwt');Cookies.remove('PHPSESSID');window.location.href='?_acao=logout';" style="position:fixed; right: 20px;bottom: 20px;">
	<i class="fa fa-power-off vermelho"></i>&nbsp;Logout
</button>
<script>
//Esta funcao deve ser invocada em toda requisicao.
//@todo: tratar casos em que o GPS esta habilitado, para uso somente nesses casos, capturando erros
setCookieGPS = async function(){
  return new Promise(function(resolve, reject) {
    navigator.geolocation.getCurrentPosition(
      (geo) => {
        setCookie("x-gps-status","Habilitado",30);
        setCookie("x-gps-lat",geo.coords.latitude,1);
        setCookie("x-gps-lon",geo.coords.longitude,1);
        resolve(geo); 
      },
      (err) => {
        console.log(err);
        ostatus.innerHTML="Clique no cadeado na barra de endereços e libere as opções de localização.<br>Em seguida, recarregue a página, ou feche seu browser e tente novamente. O seu browser também deve aceitar cookies.";
      },
      {
       enableHighAccuracy: true,
       maximumAge: 10000,
       timeout: 5000,
     });
  });
}

var bateponto = async function(inoa){

	ostatus.innerHTML="Armazenando cookies de posicionamento...";

	let oGeo = await setCookieGPS();
	ostatus.innerHTML=`<span style="color:silver;">Informando Lat: ${oGeo.coords.latitude} e Lon: ${oGeo.coords.longitude}</span>`;

	CB.post({
		//O modulo deve ir junto, para orientar o eventcode, e dar lock no formulario
		urlArquivo: "form/bateponto.php?"+CB.locationSearch,
		objetos: {
			"_acao": inoa.getAttribute("acao")
		},
		callback:function(jqXHR,data,objFoco){
			let ref='<?=$_SERVER["HTTP_REFERER"]?>';
			window.location.assign("./");
			//if(ref==window.location){

			//}
		}
		//refresh: "reload",
		//refreshPagina: true
	})
}

//Recuperar a hora do servidor, verificar se existe diferenca para o relogio do client, e computar essa diferenca para mostrar na tela
var serverTime=<?=time()*1000?>;
var localTime = +Date.now();
var timeDiff = serverTime - localTime;
function horaServidor() {
	let date = new Date(+Date.now() + timeDiff);
	let hh = date.getHours();
	let mm = date.getMinutes();
	let ss = date.getSeconds();
	hh = (hh < 10) ? "0" + hh : hh;
	mm = (mm < 10) ? "0" + mm : mm;
	ss = (ss < 10) ? "0" + ss : ss;
	let time = hh + ":" + mm + ":" + ss;
	document.getElementById("relogioservidor").innerText = time; 
	var t = setTimeout(function(){ horaServidor() }, 1000); 
}
horaServidor();
//# sourceURL=/bateponto_hora
</script>
<?
		}else{
?>
<ponto>
  <hdr>
	<status>Configurando GPS...</status>
  </hdr>
</ponto>
<script>

//window.addEventListener('DOMContentLoaded', (event) => {
  if(!navigator.geolocation){
    ostatus.innerHTML="Seu navegador não possui suporte para GPS. Atualize seu navegador.";
  }else{
    if(!navigator.permissions){
      ostatus.innerHTML="Seu navegador não permite configuração de GPS.<br>Utilize outro navegador!";
    }else{
      verificaPermissoesGeo();
    }
  }
//});

//# sourceURL=/iniciaGPS
</script>
<?
		}
	}//if(!$resp){
}else{
	
	echo '<!-- debug -->';
}//if($_SESSION["SESSAO"]["PONTOWEB"]=='Y'

function baterponto(){

	cbSetPostHeader("0","erro");

	require_once("../inc/php/cmd.php");

	if($_POST["_acao"]=="iniciar" or $_POST["_acao"]=="parar"){

		$act=$_POST["_acao"]=="iniciar"?"E":"S";
		
		//Deve sempre ser utilizada a data do servidor de aplicacao, que é o que aparece na tela do usuario
		$dt = new DateTime();

		//O rep virá nulo caso nao haja relacionamento entre o rep e o dominio utililado pelo usuario
		$sp="insert into ponto (idrep,idempresa,idpessoa,data,hora,batida,status,criadopor,criadoem,alteradopor,alteradoem,lat,lon)
				select
				(select idrep from rep where status='ATIVO' and tipo='REP-P' and ip='".$_SERVER["SERVER_NAME"]."' and idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]." limit 1)
				,".$_SESSION["SESSAO"]["IDEMPRESA"] ."
				,".$_SESSION["SESSAO"]["IDPESSOA"] ."
				,'".$dt->format('Y-m-d')."'
				,'".$dt->format('H:i:s')."'
				,'PENDENTE'
				,'".$act."'
				,'repp'
				,'".$dt->format('Y-m-d H:i:s')."'
				,'repp'
				,'".$dt->format('Y-m-d H:i:s')."'
				,'".$_COOKIE["x-gps-lat"]."'
				,'".$_COOKIE["x-gps-lon"]."'";

		$rp=d::b()->query($sp);

		if($rp){
			re::dis()->hMSet(
				'_estado:'.$_SESSION["SESSAO"]["IDPESSOA"].':pessoa',
				[
					'statusponto'        => ($act=="E"?"Trabalhando":"Descansando"),
				]
			);
			cbSetPostHeader("1","bool");
			die;
		}else{
			cbSetPostHeader("0","bool");
			echo "Não foi possivel registrar seu ponto";
			die;
		}
	}
	
}

//https://github.com/cloudflarearchive/Cloudflare-Tools/blob/master/cloudflare/ip_in_range.php

// ipv4_in_range
// This function takes 2 arguments, an IP address and a "range" in several
// different formats.
// Network ranges can be specified as:
// 1. Wildcard format:     1.2.3.*
// 2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
// 3. Start-End IP format: 1.2.3.0-1.2.3.255
// The function will return true if the supplied IP is within the range.
// Note little validation is done on the range inputs - it expects you to
// use one of the above 3 formats.
function ipv4_in_range($ip, $range) {
    if (strpos($range, '/') !== false) {
        // $range is in IP/NETMASK format
        list($range, $netmask) = explode('/', $range, 2);
        if (strpos($netmask, '.') !== false) {
            // $netmask is a 255.255.0.0 format
            $netmask = str_replace('*', '0', $netmask);
            $netmask_dec = ip2long($netmask);
            return ( (ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec) );
        } else {
            // $netmask is a CIDR size block
            // fix the range argument
            $x = explode('.', $range);
            while(count($x)<4) $x[] = '0';
            list($a,$b,$c,$d) = $x;
            $range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
            $range_dec = ip2long($range);
            $ip_dec = ip2long($ip);
            
            # Strategy 1 - Create the netmask with 'netmask' 1s and then fill it to 32 with 0s
            #$netmask_dec = bindec(str_pad('', $netmask, '1') . str_pad('', 32-$netmask, '0'));
            
            # Strategy 2 - Use math to create it
            $wildcard_dec = pow(2, (32-$netmask)) - 1;
            $netmask_dec = ~ $wildcard_dec;
            
            return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
        }
    } else {
        // range might be 255.255.*.* or 1.2.3.0-1.2.3.255
        if (strpos($range, '*') !==false) { // a.b.*.* format
            // Just convert to A-B format by setting * to 0 for A and 255 for B
            $lower = str_replace('*', '0', $range);
            $upper = str_replace('*', '255', $range);
            $range = "$lower-$upper";
        }
        
        if (strpos($range, '-')!==false) { // A-B format
            list($lower, $upper) = explode('-', $range, 2);
            $lower_dec = (float)sprintf("%u",ip2long($lower));
            $upper_dec = (float)sprintf("%u",ip2long($upper));
            $ip_dec = (float)sprintf("%u",ip2long($ip));
            return ( ($ip_dec>=$lower_dec) && ($ip_dec<=$upper_dec) );
        }
        return false;
    } 
}


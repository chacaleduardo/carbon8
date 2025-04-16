/*
<script src="/inc/js/cookie/js.cookie.js"></script>
<script src="/inc/js/moment/moment.min.js"></script>
<script src="https://unpkg.com/bowser@2.4.0/es5.js"></script>


<script>*/

var debug=false;
var host="<?=$_SERVER["HTTP_HOST"]?>";
var ws=false;
var lastPing=null;
var navegador=undefined;
var tentativasConexao=3;
var iTentativasConexao=0;

//Recuperar navegador e funções disponiveis
propNavegador=function(){
	if(!navegador){
		var bsr = bowser.getParser(window.navigator.userAgent);
		let browser = bsr.getBrowserName()+" "+bsr.getBrowserVersion().split(".")[0]+" "+bsr.getOSName()+" "+(bsr.getOSVersion()?bsr.getOSVersion()+" ":"")+bsr.getPlatformType()+(window.navigator.deviceMemory?" "+window.navigator.deviceMemory+"GB":"");
		Cookies.set("nav",browser);
		return navegador
	}else{
		return navegador;
	}
}

//Verifica o tempo decorrido entre a hora atual e a última registrada por algum websocket
conexaoWssExpirada=function(){
	let timeout=5;
	return parseInt(moment().format('X'))-parseInt(localStorage.getItem("wss_lastping"))>timeout?true:false;;
}

//Controla as conexões com o serviço WSS, e a comunicação
conexaoWss = function(){

	lastPing=localStorage.getItem("wss_lastping");

    if(gCbCanal && gCbCanal=="webview"){
        return false;
    }

	if(!iTentativasConexao===undefined || isNaN(iTentativasConexao) || iTentativasConexao > tentativasConexao){
		iTentativasConexao=undefined;
		return false;
	}

	if(!CB||!CB.logado){
		iTentativasConexao++;
		return false;
	}

	if(!lastPing || conexaoWssExpirada()){

		if(!ws.readyState || ws.readyState==WebSocket.CLOSED){
			Cookies.set("cb-plat","browser");

			propNavegador();

			ws = new WebSocket('wss://'+host+':50443');

			ws.onopen = function (e,o) {
				iTentativasConexao=0;
				localStorage.getItem("wss_lastping")
				console.log('Wss conectado');
			}

			ws.onmessage = function (ev) {
				iTentativasConexao=0;
				localStorage.getItem("wss_lastping");
				//console.log(ev);
				wsEvent=jsonStr2Object(ev.data);
				if(wsEvent.tipo=="ring"){
					let sTit="Ligação recebida "+moment().format("DD/MM H:m");
					let ico=wsEvent.origem&&wsEvent.origem=="URA"?self.icoRingExt:self.icoRingInt;
					console.log(sTit);
					notificacao({
						titulo: sTit,
						corpo: wsEvent.ring,
						icone: ico,
						id: new Date().getTime()
					});
				}else if(wsEvent.tipo=="chat"){
					if(wsEvent.online){
						chat.moverContatos({["pessoa_" + wsEvent.online]: "online"});
					}
					//console.log(ev);
				}else if(wsEvent.tipo=="_"){
					console.log(wsEvent["_"]);

				}else if(wsEvent["_s"]!==undefined){
					if(wsEvent["_s"]=="broadcast"){
						Object.entries(chat.jContatos).forEach(([k, c]) => {
							if(c.objetocontato=="pessoa"){
								if(wsEvent._uativos[c.idcontato]){
									sLastping = wsEvent._uativos[c.idcontato].lastping||"";
									sMod = wsEvent._uativos[c.idcontato].mod||"";
									sHora=moment(sLastping).format("HH:mm");
									//Ajusta o contato como online
									self.moverContatos({["pessoa_" + c.idcontato]: "online"});
									//Ajusta a observacao
									$("#cbChatContatosOnline #contato_pessoa_"+c.idcontato+" .time-meta.obs").html(sMod).attr("title","Trabalhando em "+sMod+" às "+sHora);

								//console.log("Contato online: "+wsEvent._uativos[c.idcontato].usuario);
								}else{
									self.moverContatos({["pessoa_" + c.idcontato]: "offline"});
									//console.log("Contato Offline:"+c.idcontato);
								}
							}
						});

						//Executa o filtro automático
						chat.refreshFiltroContatos();

					}else{
						console.log(wsEvent["_m"]);
					}
				}else if(wsEvent["wss.clients"]!==undefined){
					wssClients(wsEvent);
				}else{
					console.log(wsEvent);
				}
			}//ws.onmessage = function (ev) {

			ws.onerror = function(evt) {
				if (ws.readyState == 1) {
					console.warn('notificacoes: erro não previsto:');
					console.warn(evt);
				}else{
					iTentativasConexao++;
					if(debug)console.error(evt);
				}
			};

			ws.pong = function(window){
				if(ws.readyState==1 && window.CB.jsonModulo.pk){
					let spk = getUrlParameter(window.CB.jsonModulo.pk);
					let sacao = getUrlParameter("_acao");
					if(spk==""){
						ws.send(`{"atv":"pong","desc":"${CB.jsonModulo.rotulomenu}","mod":"${CB.modulo}"}`);
					}else if(spk.length){
						ws.send(`{"atv":"pong","desc":"${CB.jsonModulo.rotulomenu}","mod":"${CB.modulo}","pk":"${spk}"}`);
					}
				}
				//console.log(window.location.href);
			}

		}//if(ws.readyState!==WebSocket.OPEN){

	}else{
		iTentativasConexao++;
		console.log("Wss ativo");
	}
}

//Monitora a conexão
setInterval('conexaoWss()', 3000);

wssClients = function(o){
CB.oContainer.html("");
$.each(o["wss.clients"], function(i,o){
        let skhs="";
        $.each(o, function(ii,oo){
				let sbr="";
				sbr=/chrome/i.test(oo.nav)?"fa fa-chrome verde":"fa fa-chrome cinza";
				sbr=/firefox/i.test(oo.nav)?"fa fa-firefox laranja":sbr;
				sbr=/micros/i.test(oo.nav)?"fa fa-internet-explorer azul":sbr;
				ico=`<i class="${sbr}" title="${oo.nav}"></i>`;
                skhs+=`<table class="${i}" style='font-family:Monospace;font-size:8px;'><tr><td nowrap>${ico}${ii} ${oo.readyState}</td><td>${oo.atvRotuloMenu}</td></tr></table>`;
        });


        let hu=`<table class="sessoesusr planilha grade inlineblocktop">
                        <tr><td colspan=99>${i}</td>
                        <tr><td>${skhs}</td></tr>
     </div>`;

        CB.oContainer.append(hu);
})
}

monitorarConexoes=function(intimer){

	intimer=intimer||10000;

	setInterval(function(){
		ws.send("wss.clients");
	},intimer);
}


/*
</script>
*/

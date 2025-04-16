<script>
var scanner = QrScannerController.init("#scanner");
var $volumes = $("#volumes");
var volumesItens = {};
var arrPedidosEnviados = [];

scanner.onScann = ( result ) => {
    try{
        let jsonVolume = JSON.parse(result.data);

        if( jsonVolume["_mod"] == 'nfvolume' ){

            let idnf = jsonVolume["_cols"]["idnf"];
            // Converte p/ inteiro
            let vol = Math.floor(jsonVolume["_cols"]["vol"]);
            let tVol = Math.floor(jsonVolume["_cols"]["tvol"]);

            // Caso o IDNF já tenha sido inserido
            if(arrPedidosEnviados.includes(idnf)){
                return;
            }

            if( volumesItens[idnf] ){
                
                // Impede de duplicação dos volumes
                // e itens com o nº do volume maior que o total de volumes
                if( volumesItens[idnf]["arrVols"].length < volumesItens[idnf]["tvol"]
                        && !volumesItens[idnf]["arrVols"].includes(vol)
                        && vol <= volumesItens[idnf]["tvol"]
                        && tVol == volumesItens[idnf]["tvol"]
                        && vol <= tVol
                    ){
                    volumesItens[idnf]["arrVols"].push(vol);

                    let $volumeNaoScanneado = $(`#${idnf}-${vol}-${tVol}.nao-scanneado`);
                
                    $(layoutVolumeScanneado(idnf, vol, tVol))
                        .insertAfter($volumeNaoScanneado);

                    $volumeNaoScanneado.remove();

                    if(volumesItens[idnf]["arrVols"].length == volumesItens[idnf]["tvol"]){
                        $(`[idnf="${idnf}"]`).addClass("volumes-ok").removeClass("volumes-pendentes");
                        enviarVolumes(idnf, volumesItens[idnf]);
                    }
                }

            }else if(vol <= tVol){
                volumesItens[idnf] = {
                    tvol : tVol,
                    arrVols : [vol],
                };

                for( let i = 1; i <= tVol ; i++ ){
                    $volumes.append(layoutVolumeNaoScanneado(idnf, i, tVol));
                }

                let $volumeNaoScanneado = $(`#${idnf}-${vol}-${tVol}.nao-scanneado`);

                $(layoutVolumeScanneado(idnf, vol, tVol))
                    .insertAfter($volumeNaoScanneado);

                $volumeNaoScanneado.remove();

                if(tVol == 1){
                    $(`[idnf="${idnf}"]`).addClass("volumes-ok").removeClass("volumes-pendentes");
                    enviarVolumes(idnf, volumesItens[idnf]);
                }
            }
        }
    }catch(e){
        customConsole("[onScann]: "+e.toString(), "red");
    }
}

function layoutVolumeNaoScanneado ( idnf, vol, tVol ) {
    return `
        <div id="${idnf}-${vol}-${tVol}" idnf="${idnf}" vol="${vol}" tvol="${tVol}" class="col-sm-12 col-md-6 col-lg-4 nao-scanneado">
            <div class="volume">
                <div class="volume-count">
                    ${vol} de ${tVol}
                </div>
                <span class="volume-idnf">${idnf}</span>
                <i class="fa fa-times volume-remove" title="Remover"></i>
            </div>
        </div>
    `;
}

function layoutVolumeScanneado ( idnf, vol, tVol ) {
    return `
        <div id="${idnf}-${vol}-${tVol}" idnf="${idnf}" vol="${vol}" tvol="${tVol}" class="col-sm-12 col-md-6 col-lg-4 scanneado volumes-pendentes">
            <div class="volume">
                <div class="volume-count">
                    ${vol} de ${tVol}
                </div>
                <span class="volume-idnf">${idnf}</span>
                <i class="fa fa-times volume-remove" title="Remover" onclick="removerVolume('${idnf}', '${vol}', '${tVol}')"></i>
            </div>
        </div>
    `;
}

function removerVolume ( idnf, vol, tVol ) {
    let $volumeScanneado = $(`#${idnf}-${vol}-${tVol}.scanneado`);
    
    if(volumesItens[idnf]["arrVols"].length > 1){
        volumesItens[idnf]["arrVols"] = volumesItens[idnf]["arrVols"].filter(v => v != vol);

        $(layoutVolumeNaoScanneado(idnf, vol, tVol))
            .insertAfter($volumeScanneado);

        $volumeScanneado.remove();

        $(`[idnf="${idnf}"].scanneado`).addClass("volumes-pendentes").removeClass("volumes-ok");
    }else{
        removerVolumeTodos(idnf);
    }
}

async function enviarVolumes ( idnf, volumeInfo ) {
    
    if(volumeInfo.tvol != volumeInfo.arrVols.length){
        let pendentes = volumeInfo.tvol - volumeInfo.arrVols.length;
        let txtPendente = pendentes > 1 ? "itens pendentes":"item pendente";
        let volumesComErro = idnf + ": "+pendentes+" "+txtPendente;

        alertAtencao(volumesComErro, "Pedido com itens pendentes", 10000);
        return;
    }

    erro = true;

    try{

        let response = await fetch(`ajax/nfvolume_alterarstatus.php`,{
            method: "POST",
            headers: {
                "authorization" : Cookies.get('jwt') || localStorage.getItem("jwt") || "",
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `idnf=${idnf}&volumeinfo=${JSON.stringify(volumeInfo)}`
        });

        let headerFormato = response.headers.get('X-CB-FORMATO');
        let headerResposta = response.headers.get('X-CB-RESPOSTA');

        if( headerFormato == 'error' && headerResposta == '0' ) {
            
            let json = await response.json();

            if(json.success){
                alertSalvo(json.success);
                customConsole(json.success,"green");
                erro = false;
            }else{
                alertErro(json.error, "Erro:", 10000);
                customConsole(json.error, "red");
            }

        } else if ( headerFormato == 'error' && ( headerResposta == 'cnf' || headerResposta == 'fluxo' ) ) {

            let text = await response.text();

            let errorMsg = "Houve um erro ao ";

            errorMsg += (headerResposta == 'cnf') 
                ? "gerar fatura do Pedido ["+idnf+"]"
                : "atualizar status do Pedido ["+idnf+"]";
            
            customConsole(text, "red");
            alertErro(errorMsg, "Erro:", 10000);
        } else {
            customConsole('X-CB-FORMATO: '+headerFormato+" | X-CB-RESPOSTA: "+headerResposta, "red");
            alertErro("A resposta da requisição possui HEADERS inválidos", "Erro:", 10000);
        }

    }catch(e){
        alertErro(e.toString(), "Erro:", 10000);
        customConsole("[enviarVolumes]: "+e.toString(), "red");
    }

    if(!erro){
        arrPedidosEnviados.push(idnf);
        removerVolumeTodos(idnf);
    }else{
        $(`[idnf="${idnf}"]`).addClass("volumes-erro");
    }
}

function customConsole( msg = "", color = "green" ){
    console.log("%c"+msg,"color:"+color+";font-size:18px;");
}

function removerVolumeTodos( idnf ){
    delete volumesItens[idnf];
    $(`[idnf="${idnf}"]`).remove();
}

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
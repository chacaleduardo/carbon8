SH = new Share();
SH.carregarBotaoShare();

function Share()
{
    return {
        _idempresa: "",
        _modulo: "",
         _primary: "",
        _idobjeto:  "",
        _acao:  "",
        _permissao: "",

        inicializaVariaveis: function(){
            SH._modulo = CB.jsonModulo.modulo;
            SH._primary = CB.jsonModulo.pk;
            SH._idobjeto = getUrlParameter(CB.jsonModulo.pk);
        },

        carregarBotaoShare: function(){
            CB.on('posLoadUrl',function(data){
                //Valida se é i para não aparecer o Botão
                if(CB.acao != 'i') { 
                    SH.inicializaVariaveis();

                    //Chama a função que valida a permissão para aparecer o botão de Compartilhar Share
                    SH.permissaoShare();
                }
            });

            CB.on('post',function(data){
                //Valida se é i para não aparecer o Compartilhamento
                if(CB.acao != 'i') { 
                    SH.inicializaVariaveis();

                    //Chama a função que valida a permissão para aparecer o botão de Compartilhar Share
                    SH.permissaoShare();           
                } 
            });     
        },

        permissaoShare:function()
        {
            $.ajax({
                method: 'post'
                ,url: 'ajax/_share.php'
                ,data: {
                    "_modulo": SH._modulo,
                    "_primary": SH._primary,
                    "acao":'permissaoShare'
                }
            }).done(function(data){
                try{
                    SH._permissao = data;
                    if(SH._idobjeto && !$("#cbCompartilharShare").length && SH._permissao)
                    {
                        SH.inicializaBotaoShare(data);
                        CB.on('posFecharForm', function(){
                            $('#cbCompartilharShare').remove();
                        })
                    } 
                }catch(err){
                    console.warn(err)
                } 
            });
        },

        inicializaBotaoShare: function(){
             $('#cbModuloHeader').append(`
                <i id="cbCompartilharShare" class="fa fa fa-external-link fa-2x fade pointer hoverlaranja compartilhar" title="Compartilhar Share" onclick="SH.mostraModalEmpresa()" style="padding-left: 5px;"></i>
            `);
        },
        
        mostraModalEmpresa: function()
        {
            let div = '';
            $.ajax({
                method: 'post'
                ,url: 'ajax/_share.php'
                ,data: {
                    "_modulo": SH._modulo,
                    "_primary": SH._primary,
                    "_idobjeto": SH._idobjeto,
                    "acao":'mostraModalEmpresa'
                }
            }).done(function(data){
                let info = JSON.parse(data);
                let permissoesMatriz = gMatrizPermissoes.split(',');
                let click;
                div = '<div id="cbModalCorpo" class="modal-body">';
                    div += '<div class="row">';
                        div += '<div class="panel panel-default" style="margin-top: -25px !important;">';
                            div += '<div class="panel-body">';
                                div += '<div class="col-md-12">Escolha a(s) empresa(s) que deseja compartilhar este '+SH._modulo+':<br /></div>';
                                for(permissoesMatriz in  info)
                                {
                                    if (info[permissoesMatriz] != undefined && permissoesMatriz != 'idempresa') 
                                    {	
                                        if(info[permissoesMatriz].checked == 'checked')
                                        { 
                                            click = `SH.removerShareEmpresa(${permissoesMatriz})`;
                                            checkedinput = `checked=checked`;
                                        } else {
                                            click = `SH.insereShareEmpresa(${permissoesMatriz})`;
                                            checkedinput = ``;
                                        }
                                        div += `<div class="col-md-12"><input type="checkbox" name="altShare${permissoesMatriz}" id="altShare${permissoesMatriz}"  onclick="${click}" ${checkedinput}>&nbsp;&nbsp;&nbsp;${info[permissoesMatriz].nomefantasia}</div>`;
                                    }
                                }
                            div += '</div>';
                        div += '</div>';
                    div += '</div>';
                div += '</div>';

                CB.modal({
                    corpo: div,
                    titulo: "COMPARTILHAR "+SH._modulo,
                    classe: 'quarenta'
                });	
            });
        },

        insereShareEmpresa: function(idempresa)
        {
            CB.post({
                urlArquivo: 'ajax/_share.php?share=share&_modulo='+ST._modulo,
                refresh: 'refresh',      
                parcial: true,              
                objetos: {
                   "_modulo": ST._modulo,
                    "_primary": SH._primary,
                    "_idobjeto": ST._idobjeto,
                    "idempresa": idempresa,
                    "acao": "insereShareEmpresa"                     
                },
                posPost: function(data,texto,jqXHR){
                    if(jqXHR.getResponseHeader("X-CB-RESPOSTA") == 0){
                        alert(jqXHR.getResponseHeader("X-CB-FORMATO"));
                    }
                    $("#altShare"+idempresa).attr('onclick', '');
                    $("#altShare"+idempresa).attr('onclick', 'SH.removerShareEmpresa('+idempresa+')');
                }
            });
        },

        removerShareEmpresa: function(idempresa)
        {
            CB.post({
                urlArquivo: 'ajax/_share.php?share=share&_modulo='+ST._modulo,
                refresh: 'refresh',   
                parcial: true,                 
                objetos: {
                   "_modulo": ST._modulo,
                    "_primary": SH._primary,
                    "_idobjeto": ST._idobjeto,
                    "idempresa": idempresa,
                    "acao": "deletaShareEmpresa"                     
                },
                posPost: function(data,texto,jqXHR){
                    if(jqXHR.getResponseHeader("X-CB-RESPOSTA") == 0){
                        alert(jqXHR.getResponseHeader("X-CB-FORMATO"));
                    }
                    $("#altShare"+idempresa).attr('onclick', '');
                    $("#altShare"+idempresa).attr('onclick', 'SH.insereShareEmpresa('+idempresa+')');
                }
            });
        }
    }
}
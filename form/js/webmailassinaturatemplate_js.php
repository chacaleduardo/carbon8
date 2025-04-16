<script>
    <?if(!empty($_1_u_webmailassinaturatemplate_idwebmailassinaturatemplate)){?>
    
    // jOpcoes: possui todas as opções do autocomplete. Caso uma opção seja selecionada, ela é removida desse Array.
    // jOpcoesSelecionadas: possui o identificador de todas as opções que foram selecionadas. Caso uma opção seja removida, ela é removida desse Array também.
    // jRecuperaOpcoes: possui o objeto de todas as opções que foram selecionadas, para caso a opção seja removida, ela seja adicionada novamente no Array jOpcoes, ficando disponível para ser selecionada novamente.
    var jOpcoes, jOpcoesSelecionadas = [], jRecuperaOpcoes = [];
    <?if($_1_u_webmailassinaturatemplate_tipo == 'COLABORADOR'){?>
        jOpcoes = <?=getPessoas();?>;
    <?}else if($_1_u_webmailassinaturatemplate_tipo == 'EMAILVIRTUAL'){?>
        jOpcoes = <?=getEmailvirtual();?>;
    <?}?>

    var jImg = <?=getImagensRodape();?>;
    $("#gerartemplateinput").autocomplete({
        source: jOpcoes
        ,delay: 0
        ,create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $('<li>').append('<a>' + item.objeto + '</a>').appendTo(ul);
            };
        },select: function(event, ui){
            
            if(ui.item.tipo != 'pessoa' && ui.item.pessoaobjeto){
                let $obj = `<div style="margin: 10px;" id="${ui.item.tipo}_${ui.item.idobjeto}"><fieldset class="scheduler-border"><legend style="font-size: 13px;margin-bottom: 10px;font-weight: bold;" class="scheduler-border">${ui.item.objeto} <i class="fa fa-trash hoververmelho pointer" style="float: right;" onclick="removeropcaoselecionada('${ui.item.tipo}','${ui.item.idobjeto}')"></i></legend>`;
                jRecuperaOpcoes.push(ui.item);
                let l;
                for(let pessoa of ui.item.pessoaobjeto){
                    $(`#pessoa_${pessoa.idobjeto}`).remove();
                    $obj += `<div id="pessoa_${pessoa.idobjeto}" identificador="${pessoa.idobjeto}">${pessoa.objeto}</div>`;
                    jOpcoesSelecionadas.indexOf(pessoa.idobjeto) === -1 ? jOpcoesSelecionadas.push(pessoa.idobjeto) : null;
                    l = jOpcoes.filter(o => o.idobjeto === pessoa.idobjeto && o.tipo === 'pessoa');
                    if(l[0]){
                        jRecuperaOpcoes.push(l[0]);
                    }
                    jOpcoes = jOpcoes.filter(o => o.idobjeto !== pessoa.idobjeto && o.objeto !== pessoa.objeto);
                }

                $obj += `</fieldset></div>`;
                $("#gerartemplatelistaselecionados").append($obj);
            }else if(ui.item.tipo == 'pessoa'){
                $("#gerartemplatelistaselecionados").prepend(`<div style="margin: 10px;font-weight: bold;" id="pessoa_${ui.item.idobjeto}">${ui.item.objeto} <i class="fa fa-trash hoververmelho pointer" style="float: right;" onclick="removeropcaoselecionada('${ui.item.tipo}','${ui.item.idobjeto}')"></i></div>`);

                jOpcoesSelecionadas.indexOf(ui.item.idobjeto) === -1 ? jOpcoesSelecionadas.push(ui.item.idobjeto) : null;
                jRecuperaOpcoes.push(ui.item);
            }else{
                alertAtencao(`${ui.item.objeto} não possui pessoas associadas.`)
            }

            jOpcoes = jOpcoes.filter(o => o.idobjeto !== ui.item.idobjeto && o.objeto !== ui.item.objeto);

            $('#gerartemplateinput').autocomplete('option', 'source', jOpcoes)
        }

    });

    function removeropcaoselecionada(tipo, idobjeto){
        let div = $(`#${tipo}_${idobjeto}`);
        let elementoRecuperado;

        if(tipo != 'pessoa'){
            div.find('[id*=pessoa_]').each((i, o) => {
                let id = $(o).attr('identificador');
                jOpcoesSelecionadas = jOpcoesSelecionadas.filter(o => o !== id);
                elementoRecuperado = jRecuperaOpcoes.filter(o => o.idobjeto === id && o.tipo === 'pessoa');
                if(elementoRecuperado[0]){
                    jRecuperaOpcoes = jRecuperaOpcoes.filter(o => o.idobjeto !== id && o.objeto !== elementoRecuperado[0].objeto);
                    jOpcoes.push(elementoRecuperado[0]);
                }
            });
        }else{ 
            jOpcoesSelecionadas = jOpcoesSelecionadas.filter(o => o !== idobjeto);
        }

        elementoRecuperado = jRecuperaOpcoes.filter(o => o.idobjeto === idobjeto && o.tipo === tipo);
        if(elementoRecuperado[0]){
            jRecuperaOpcoes = jRecuperaOpcoes.filter(o => o.idobjeto !== idobjeto && o.objeto !== elementoRecuperado[0].objeto);
            jOpcoes.push(elementoRecuperado[0]);
        }
        
        $('#gerartemplateinput').autocomplete('option', 'source', jOpcoes);

        div.remove();
    }

    $("#gerartemplates").click(function(){
        var vtipo = $("[name*='_webmailassinaturatemplate_tipo']").val();

        if(vtipo !== "" && vtipo !== undefined){

            switch(vtipo){
                case 'COLABORADOR': 
                    aTipo = 'PESSOA';
                    break;
                case 'EMAILVIRTUAL':
                    aTipo = 'EMAILVIRTUAL';
                    break;
                default: 
                    console.warn("Nenhum tipo de WebMail Assinatura selecionado");
                    return;
            }

            if(jOpcoesSelecionadas.length == 0){
                alertAtencao(`Não há nenhum Colaborador ou Grupo de email selecionado para gerar template.`)
                return;
            }
            $("#gerartemplates").addClass("disabled");
            alertAzul("A tela será recarregada após o termino do processo.<br>Isso pode demorar um pouco!", "Gerando templates");
            $.ajax({
                type: "post",
                url : "ajax/replaceassinaturaemail.php",
                data: { 
                    template : $("[name='_1_u_webmailassinaturatemplate_htmltemplate']").val(),
                    id: jOpcoesSelecionadas || 0,
                    idtemplate: $("[name='_1_u_webmailassinaturatemplate_idwebmailassinaturatemplate']").val() || 0,
                    tipo: aTipo,
                    gerar: 'Y'
                },
                
                success: function(data, textStatus, jqXHR){
                    var dados = JSON.parse(data);
                    var arrtmp = new Array();
                    if(jqXHR.getResponseHeader('X-CB-RESPOSTA') == 'id'){
                        for (const [i, o] of dados.entries()) {
                            var aux = $("<div>"+o.html+"</div>");
                            $("body").append(`<div id="${i}_template">`+aux.find("#_temp").html()+"</div>");
                            var idwebmailassinatura = o.lastinsert;
                            iteradados(dados,i,aux,idwebmailassinatura).then((values) => {
                                arrtmp.push(values);
                                executacbpost(arrtmp,dados.length);
                            });
                        }
                    }
                },

                error: function(objxmlreq){
                    $("#gerartemplates").removeClass("disabled");
                    alertErro('Erro:<br>'+objxmlreq.status);
                }
            });
        }
        
    });

    function altprincipalempresa(v){
        CB.post({
            objetos: {
                "_x_u_webmailassinaturatemplate_idwebmailassinaturatemplate" : $("[name='_1_u_webmailassinaturatemplate_idwebmailassinaturatemplate']").val(),
                "_x_u_webmailassinaturatemplate_principalempresa" : v
            },
            parcial: true
        });
    }

    function executacbpost(arrtmp,tamanho){
        if(arrtmp.length === tamanho){
            objfinal = {}
            for(const [i, o] of arrtmp.entries()){
                objfinal = Object.assign(objfinal, o);
            }

            CB.post({
                urlArquivo: 'ajax/replaceassinaturaemail.php?salvar=Y&gerar=Y',
                refresh: 'refresh',
                parcial: true,
                objetos: objfinal,
                posPost: function(data,texto,jqXHR){
                    let resp = JSON.parse(data);
                    let listIdsComErro = [];
                    for(let r of resp){
                        if(r["erro"] && r["id"]){
                            listIdsComErro.push(r["id"]);
                        }
                    }
                    if(listIdsComErro.length > 0){
                        alertErro('Os seguintes idwebmailassinaturaobjeto apresentaram falha:<br>'+listIdsComErro.join())
                    }
                }
            });
        }else{
            return false;
        }
    };

    function iteradados(dados,i,aux,idwebmailassinatura) {
        return new Promise(resolve => {
            setTimeout(async function() {
                try{
                    let dataUrl = await domtoimage.toPng($(`#${i}_template`).find("#_template").get(0))
                    var img = new Image();
                    img.src = dataUrl;
                    aux.find("#_temp").html(img);

                    var obj = {
                        [`${i}#idwebmailassinaturaobjeto`] :idwebmailassinatura,
                        [`${i}#htmlassinatura`] :aux.html()
                    }
                    $(`#${i}_template`).remove();
                    resolve(obj);
                }catch(error) {
                    console.error('oops, something went wrong!', error);
                    $("#_temp").remove();
                }
            }, 500);
        });
    }


    
    
    $editor=$("#editor");
    //Resetar o objeto tinymce para não ficar desabilitado no refresh/reload
    if(tinyMCE.editors["editor"])tinyMCE.editors["editor"].remove();

    tinymce.init({
        selector: '#editor',
        language: 'pt_BR',
        plugins: ["code", "image", "preview", "imagetools", "table"],
        file_picker_types: 'image',
        toolbar: "undo redo | image | code | preview | fontselect",
        menubar: false,
        image_description: false,
        image_dimensions: false,
        image_list: jImg || '',
        setup: function (editor) {
            editor.on('init', function (e) {
                if($(":input[name=_1_"+CB.acao+"_webmailassinaturatemplate_htmltemplate]").length){
                    this.setContent($(":input[name=_1_"+CB.acao+"_webmailassinaturatemplate_htmltemplate]").val());
                }
            });

            editor.on('NodeChange', function (e) {
                if(e.element.tagName === "IMG"){          
                    e.element.style.width = "100%";
                }
            });
        },
        font_css: '../inc/css/fonts/laudofonts.css',
        font_formats: 'Monserrat-Medium = Monserrat-Medium;Monserrat-ligth = Monserrat-ligth;Andale Mono=andale mono,times; Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Book Antiqua=book antiqua,palatino; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Helvetica=helvetica; Impact=impact,chicago; Symbol=symbol; Tahoma=tahoma,arial,helvetica,sans-serif; Terminal=terminal,monaco; Times New Roman=times new roman,times; Trebuchet MS=trebuchet ms,geneva; Verdana=verdana,geneva; Webdings=webdings; Wingdings=wingdings,zapf dingbats'
    });

    //Antes de salvar atualiza o textarea
    CB.prePost = function(){
        var $editor=tinyMCE.get('editor');
        if($editor){
            //falha nbsp: oDescritivo.val( tinyMCE.get('diveditor').getContent({format : 'raw'}).toUpperCase());
            $(":input[name=_1_"+CB.acao+"_webmailassinaturatemplate_htmltemplate]").val($editor.getContent());
        }
    }
    <?}?>
</script>
<!-- script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"></script -->
<script src="inc/js/dom-to-image/dom-to-image.min.js"></script>
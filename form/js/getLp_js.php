<script>
    
    var jFuncionario = <?=_LpController::jsonFuncionariosSetoresDepartamentosAreaConselho($_POST['idlp'])?>;
    var idlp = <?=$_POST["idlp"]?>;
    var jEmpresas = <?=_LpController::jsonEmpresasNaoVinculadasALp($_idempresa,$_POST['idlp']);?>;
    var jRepDisponiveis = <?=_LpController::jsonRepDisponiveis($remp['idlp'])?>;
    var jDash = <?=empty($jDash)?'[]':$jDash?>;

    (function(){
        console.log(jDash)

    $('.selectpicker').selectpicker('render').on("change",(event)=>{debugger
        var icon = $(event.target).parent().parent().find('.btn-warning');
        if(icon.length <= 0){
            $(event.target).parent().parent().find('button.btn-warning').remove();
            $(event.target).parent().after(`&nbsp;<button class="btn btn-warning pointer" onclick="alteraobjetovinculo(${idlp},'lp','${$(event.target).val()}','${$(event.target).attr('cbpost')}',this)"> <i class="fa fa-warning "></i> Salvar alterações </button>`)
        }else{
            $(event.target).parent().parent().find('button.btn-warning').remove();
            $(event.target).parent().after(`&nbsp;<button class="btn btn-warning pointer" onclick="alteraobjetovinculo(${idlp},'lp','${$(event.target).val()}','${$(event.target).attr('cbpost')}',this)"> <i class="fa fa-warning "></i> Salvar alterações </button>`)
        }
    });

    // if(jDash != 0){
    //     for(let grupo of jDash["dashgrupo"]){
    //         construirDashGrupo('w', grupo["id"], idlp, jDash);
    //         for(let panel of grupo["dashpanel"]){
    //             construirDashPanel('w', panel["id"], grupo["id"], idlp, jDash);
    //             for(let card of panel["dashcard"]){
    //                 $(`.dashcard-disponivel[iddashcard="${card.iddashcard}"]`).hide();
    //                     if($(`[iddashcard="${card.iddashcard}"] `).length > 0){
    //                         card.titulo = (card.titulo =='')?$(`[iddashcard="${card.iddashcard}"] .card-body .text-xs.text-uppercase.mb-1`).text():card.titulo;
    //                         card.subtitulo =  (card.subtitulo =='')?$(`[iddashcard="${card.iddashcard}"] #card_title_sub`).text():card.subtitulo;
    //                     }else{
    //                         card.titulo = '';
    //                         card.subtitulo = '';
    //                     }

    //                 construirDashCard(card, panel["id"], grupo["id"], idlp);
    //                 $(`[iddashcard=${card.iddashcard}].hidden`).find('#card_title_sub').addClass("pointer")
    //                 $(`[iddashcard=${card.iddashcard}].hidden`).find('#card_title_sub').on('click', function(e){
    //                     janelamodal("?_modulo=dashcard&_acao=u&iddashcard="+card.iddashcard);
    //                 });

    //                 $(`[iddashcard=${card.iddashcard}].hidden`).removeClass('hidden')
    //             }
    //         }
    //     }
    //     $(`.dashcard-disponivel:visible`).find('.cardpanel').addClass("pointer");
    //     $(".dashcard-disponivel").find('.cardpanel').on('click', function(e){
    //         janelamodal("?_modulo=dashcard&_acao=u&iddashcard="+$(e.target).attr('iddashcard'));
    //     });
    // }
    })();


    if(jFuncionario){
        $("div#lp_"+idlp+" input.funcsetdeptvinc").autocomplete({
            source: jFuncionario
            ,delay: 0
            ,create: function(){
                $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                    lbItem = item.rot;			
                    return $('<li>')
                    .append('<a>' + lbItem + '</a>')
                    .appendTo(ul);
                };
            },select: function(e,ui){
                let id = ui.item.idobjeto;
                let tipoobj = ui.item.tipoobj;

                CB.post({
                    objetos:{
                        [`_${tipoobj}_i_lpobjeto_idlp`] : idlp,
                        [`_${tipoobj}_i_lpobjeto_idobjeto`] : id,
                        [`_${tipoobj}_i_lpobjeto_tipoobjeto`] : tipoobj,
                    },
                    parcial: true
                });
            }
        });
    }

    $("#lp_"+idlp+"_empresas_autocomplete").autocomplete({
        source: jEmpresas
        ,delay: 0
        ,create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {			
                return $('<li>')
                .append('<a>' + item.empresa + '</a>')
                .appendTo(ul);
            };
        },select: function(e,ui){

            CB.post({
                objetos:{
                    "_empresa_i__lpobjeto_idlp"         : idlp,
                    "_empresa_i__lpobjeto_idobjeto"     : ui.item.idempresa,
                    "_empresa_i__lpobjeto_tipoobjeto"   : 'empresa',
                },
                parcial: true
            });
        }
    });
    
    //Os objetos não selecionados podem somente ser arrastados
    $("#lp_"+idlp+" .targetDisponiveis .draggable").draggable();

    //O método Sortable compartilha propriedades de DRAGGABLE, permitindo aos objeto sorteáveis também serem arrastáveis e dropados em outros
    //Apà³s o término do 'sort', aplicar as alteraçàµes no Database
    // $("#lp_"+idlp+" .targetSelecionados").sortable({
    //     //Somente se algum objeto foi reordenado
    //     update: function(event, objUi){
    //         aplicarOrdenacaoModulos();
    //     }
    // });

    // $("#lp_"+idlp+" .targetFilhos").sortable({
    //     //Somente se algum objeto foi reordenado
    //     update: function(event, objUi){
    //         aplicarOrdenacaoModulos();
    //     }
    // });

    //Container Inferior Selecionados
    $("#lp_"+idlp+" .targetSelecionados.droppable").droppable({
        accept: ".targetDisponiveis table",
        activeClass: "ui-state-default",
        drop: function( event, objUi ) {
            var sMod=objUi.helper.attr("cbmodulo");
            relacionaLpModulo(sMod,"","w",$(event.target).attr("target-idlp"));
            // aplicarOrdenacaoModulos();
        }
    });

    //Ordenar os Mà³dulos Selecionados. O Droppable aqui serve SOMENTE para efetuar o highlight através da classe ui-state-default
    $("#lp_"+idlp+" .targetSelecionados table.droppable").droppable({
        accept: "#lp_"+idlp+" .targetSelecionados table",
        activeClass: "ui-state-default"
    });
    

        
    $oARelatorio = $("#associarRelatorio");
    jsonAc = jQuery.map(jRepDisponiveis, function(o, id) {
        return {"label": o.rep, value:o.idrep ,"cssicone":o.cssicone}
    });

    $oARelatorio.autocomplete({
        source: jsonAc
        ,delay: 0
        ,select: function(){
        CB.post({
            objetos:"_x_i__lprep_lp="+idlp+"&_x_i__lprep_idrep=",
            parcial:true
        });
        }
    });

<?
if($_GET['_showerrors']=='Y'){ 
	echo showControllerErrors(_LpController::$controllerErrors);
}
?>

</script>
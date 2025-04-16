<script type="text/Javascript">
    let tabelasDoBancoCarbonELaudo = <?= json_encode($tabelasDoBancoCarbonELaudo) ?>;
    function toggle(inId,inCol,inChk){

        var vYN = (inChk.checked)?"Y":"N";

        var strPost = "_ajax_u_etlconffiltros_idetlconffiltros="+inId
                    + "&_ajax_u_etlconffiltros_"+inCol+"="+vYN;

        CB.post({
            objetos: strPost
            ,parcial: true
        });
    }

    $('.selectpicker').selectpicker('render');

    function atualizavalor(vthis,idetlconffiltros){
        var strval= $(vthis).val();
        CB.post({
            objetos: {
                "_x_u_etlconffiltros_idetlconffiltros":idetlconffiltros
                ,"_x_u_etlconffiltros_valor":strval
            }
            ,parcial: true
            ,refresh:false
        });
    }

    //Autocomplete de Tabelas
    $(":input[name=_1_"+CB.acao+"_etlconf_tabela]").autocomplete({
        source: tabelasDoBancoCarbonELaudo
        ,delay: 0
        ,create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                vitem = "<span class='cinzaclaro'>"+item.db+".</span>" + item.value;
                return $('<li>')
                    .append('<a>' + vitem + '</a>')
                    .appendTo(ul);
            };
        }
    }); 

    function repetereg(vtab,vcampo,vid,vcheck){
                CB.post({
                    objetos: "_x_u_"+vtab+"_id"+vtab+"="+vid+"&_x_u_"+vtab+"_"+vcampo+"="+vcheck,
                    parcial:true
                });
            }
    //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
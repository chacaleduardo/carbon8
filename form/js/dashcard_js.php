<script>
    let lpsDisponiveisParaVinculo = <?= json_encode($lpsDisponiveisParaVinculo) ?>;
    let jModulo = <?= json_encode($modulosComChavePrimaria) ?>;    
    let tabelas = <?= json_encode($tabelas) ?>;

    tabelas = jQuery.map(tabelas, function(o, id) {
        return {"label": o.tab,"rotulomenu":o.tab }
    });

    jModulo = jQuery.map(jModulo, function(o, id) {
        return {"label": o.modulo,"rotulomenu":o.rotulomenu }
    });
    
    function novo(inobj){
        CB.post({
        objetos: "_x_i_"+inobj+"_iddashcard="+$("[name=_1_u_dashcard_iddashcard]").val()
        });
        
    }

    if($('#calculo').val() == "Y"){
        $('.tipocalc').show();
    }

    $('#calculo').change(function(event){
        var calc = event.currentTarget.value;
        if(calc == "SIM"){
            $('.tipocalc').show();
        }else{
            $('.tipocalc').hide();
        }
    });

    $("[name*=_dashcard_modulo]").autocomplete({
        source: jModulo
        ,delay: 0
        ,create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $('<li>').append("<a>"+item.label+"<span class='cinzaclaro'> "+item.rotulomenu+"</span></a>").appendTo(ul);
            };
        }	
    });

    $("[name*=_dashcard_tab]").autocomplete({
        source: tabelas
        ,delay: 0
        ,create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $('<li>').append("<a>"+item.label+"<span class='cinzaclaro'> "+item.rotulomenu+"</span></a>").appendTo(ul);
            };
        }	
    });
    $('.selectpicker').selectpicker('render');
    function atualizavalor(vthis,iddashcardfiltros){
        var strval= $(vthis).val();
        CB.post({
            objetos: {
                "_x_u_dashcardfiltros_iddashcardfiltros":iddashcardfiltros
                ,"_x_u_dashcardfiltros_valor":strval
            }
            ,parcial: true
            ,refresh:false
        });
    }

    //Autocomplete de Setores vinculados
    $("#lpvinc").autocomplete({
        source: lpsDisponiveisParaVinculo
        ,delay: 0
        ,create: function(){
            $(this).data('ui-autocomplete')._renderItem = function (ul, item) {

                lbItem = item.label;
                
                return $('<li>')
                    .append('<a>' + lbItem + '</a>')
                    .appendTo(ul);
            };
        }
        ,select: function(event, ui){
            //alert($(":input[name=_1_"+CB.acao+"_sgdepartamento_idsgdepartamento]").val());//id do departamento
        //	alert(ui.item.value);//id do setor
            
            CB.post({
                objetos: {
                    "_x_i__lpobjeto_idobjeto": $(":input[name=_1_"+CB.acao+"_dashcard_iddashcard]").val()
                    ,"_x_i__lpobjeto_tipoobjeto": "dashboard"
                    ,"_x_i__lpobjeto_idlp": ui.item.value
                }
                ,parcial: false
                
            });
        }
    });


    //Deletar LP do Departamento(Lidiane - 13-03-2020)
    function desvincularLp(inid){
        //debugger;
        CB.post({
            objetos: "_x_d__lpobjeto_idlpobjeto="+inid
            ,parcial:true
            ,posPost: function(){
            //  AtualizaBim();
            }
            
        });    	
        //AtualizaBim();
    }

    //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>
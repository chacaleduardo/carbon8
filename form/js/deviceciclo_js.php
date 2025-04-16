<script>
	function addAtiv(ordem){
        CB.post({
            objetos:{
                '_x_i_devicecicloativ_iddeviceciclo':$('[name="_1_u_deviceciclo_iddeviceciclo"]').val(),
                "_x_i_devicecicloativ_ordem":ordem
            },
        });	
    }

	function delAtiv(v){
        CB.post({
            objetos:{
                '_x_d_devicecicloativ_iddevicecicloativ':v
            },
            parcial:true
        });
    }

    function inserirAcao(vthis,acao,cont){
        var rotulo = $($(vthis).children("[value='"+vthis.value+"']")[0]).text();
        var mystr = vthis.value;
        var myarr = mystr.split("|");
        if(acao == 1 ){
            lin = 'min';
        }else{
            lin = 'max';
        }
        CB.post({
            objetos: '_x_i_devicecicloativacao_iddevicecicloativ='+$('[name="_'+cont+'_u_devicecicloativ_iddevicecicloativ"]').val()
                    +'&_x_i_devicecicloativacao_pino='+myarr[0]   
                    +'&_x_i_devicecicloativacao_estado='+myarr[1]
                    +'&_x_i_devicecicloativacao_rotulo='+rotulo
                    +'&_x_i_devicecicloativacao_acao='+lin
            ,refresh:"refresh"
            
        });
    }

    function deleteAcao(v){
        CB.post({
            objetos:{
                '_x_d_devicecicloativacao_iddevicecicloativacao':v
            },
            parcial:true
        });
    }

    /*
    * Duplicar ciclo [ctrl]+[d]
    */
    $(document).keydown(function(event) {

        if (!((event.ctrlKey || event.altKey) && event.keyCode == 68)) return true;
        if(!teclaLiberada(event)) return;//Evitar repetição do comando abaixo
        if(confirm("Deseja duplicar o ciclo?")){
            ST.desbloquearCBPost();
            CB.post({
                objetos: "_x_i_deviceciclo_iddeviceciclocop=<?=$_1_u_deviceciclo_iddeviceciclo?>"
                ,parcial:true
                ,posPost: function(data, textStatus, jqXHR){					
                    shciclo(CB.lastInsertId);
                }
            });
        }
        return false;
    });

    function shciclo(inidciclo){
        janelamodal('?_modulo=deviceciclo&_acao=u&iddeviceciclo='+inidciclo+'');
     }

    //Permite ordenação dos elementos
    $(".divbody").sortable({
        update: function(event, objUi){
            ordenaAtividades();
        }
    });

    function ordenaAtividades(){
        $.each($(".divbody").find(".divbodyitem"), function(i,otr){
            $(this).find(":input[name*=ordem]").val(i);
        });
    }

    function removerCiclo(iddeviceobj){
        if(confirm("Deseja realmente remover esse ciclo?")){
            CB.post({
                objetos:'_ajax_d_deviceobj_iddeviceobj='+iddeviceobj
                ,parcial:true
            });
        }
    }

    function inserirCiclo(vthis){
        if(vthis.value != ""){
            CB.post({
                objetos: "_x_i_deviceobj_iddevice="+vthis.value+"&_x_i_deviceobj_objeto="+$("[name=_1_u_deviceciclo_iddeviceciclo]").val()+"&_x_i_deviceobj_tipoobjeto=deviceciclo"
                ,parcial: true
            });
        }
    }

    $('.select-picker').selectpicker('render');
</script>
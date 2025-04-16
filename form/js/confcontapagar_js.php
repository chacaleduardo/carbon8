<script>
function preencheti(){
    
    $("#idtipoprodserv").html("<option value=''>Procurando....</option>");
	
	$.ajax({
                type: "get",
                url : "ajax/buscacontaitem.php",
                data: { idcontaitem : $("#idcontaitem").val() },

                success: function(data){
                        $("#idtipoprodserv").html(data);
                },

                error: function(objxmlreq){
                        alert('Erro:<br>'+objxmlreq.status); 

                }
        })//$.ajax
    
} 
function preenchetitem(vi){
    
    $("#idtipoprodserv"+vi).html("<option value=''>Procurando....</option>");
	
	$.ajax({
                type: "get",
                url : "ajax/buscacontaitem.php",
                data: { idcontaitem : $("#idcontaitem"+vi).val() },

                success: function(data){
                        $("#idtipoprodserv"+vi).html(data);
                },

                error: function(objxmlreq){
                        alert('Erro:<br>'+objxmlreq.status); 

                }
        })//$.ajax
    
} 
function altcheckV(vid,vcheck){
        
        if(vcheck =='Y'){
           var vcheck2='N'; 
           var vcheck1='N';
        }else{
           var vcheck2='N'; 
           var vcheck1='N';
        }
        CB.post({
                objetos: "_x_u_confcontapagar_idconfcontapagar="+vid+"&_x_u_confcontapagar_vigente="+vcheck+"&_x_u_confcontapagar_sequente="+vcheck2        
        }); 
    }  

    function altcheckS(vid,vcheck){
        
        if(vcheck =='Y'){
           var vcheck2='N'; 
           var vcheck1='N';
        }else{
           var vcheck2='N'; 
           var vcheck1='N';
        }
        CB.post({
                objetos: "_x_u_confcontapagar_idconfcontapagar="+vid+"&_x_u_confcontapagar_sequente="+vcheck+"&_x_u_confcontapagar_vigente="+vcheck2        
        }); 
    }  

function altcheckAP(vid,vcheck){
        
        if(vcheck =='Y'){
           var vcheck2='N'; 
           var vcheck1='N';
        }else{
           var vcheck2='N'; 
           var vcheck1='N';
        }
        CB.post({
                objetos: "_x_u_confcontapagar_idconfcontapagar="+vid+"&_x_u_confcontapagar_agruppessoa="+vcheck+"&_x_u_confcontapagar_agrupnota="+vcheck2        
        }); 
    }  
    
function altcheckAN(vid,vcheck){
        
        if(vcheck =='Y'){
           var vcheck2='N';
           var vcheck1='N';
        }else{
           var vcheck2='N';
           var vcheck1='N';
        }
        
        CB.post({
                objetos: "_x_u_confcontapagar_idconfcontapagar="+vid+"&_x_u_confcontapagar_agrupnota="+vcheck+"&_x_u_confcontapagar_agruppessoa="+vcheck2        
        }); 
    }   
    
    function novoobjeto(tab,vid){
        CB.post({
            objetos: "_x_i_"+tab+"_idconfcontapagar="+vid
            ,parcial: true
            
        });   
    }

    function excluir(tab,inid){
        if(confirm("Deseja retirar este?")){		
            CB.post({
            objetos: "_x_d_"+tab+"_id"+tab+"="+inid
            });
        }
        
    }

    function mostrarfin(vi,vthis){
        if($(vthis).val()=='C'){
            $('#rotf'+vi).removeClass('hide');
            $('#valf'+vi).removeClass('hide');
        }else{            
            $('#rotf'+vi).addClass('hide');
            $('#valf'+vi).addClass('hide');
        }
    }

    $('.selectpicker').selectpicker('render');

    $('#vcst').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue){
        if($(e.currentTarget).val())
        {
            $('#sel_picker').val($(e.currentTarget).val().join());
           
        } else {
            $('#sel_picker').val('');
           
        }

       
    });

</script>
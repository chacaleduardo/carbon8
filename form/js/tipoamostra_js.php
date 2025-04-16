<script>
function inserircampo(vthis,inidsubtipoamostra,vcoluna){
	var valor;
	var selector;
	switch(vcoluna){
		case 'campo':
			valor = $("select[name=amostracampos_idunidade]").val();
			selector = "select[name=amostracampos_idunidade]";
			break;
		case 'idunidade':
			valor = $("select[name=amostracampos_campo]").val();
			selector = "select[name=amostracampos_campo]";
			break;
		default:
			valor = 0;
			selector = "";
			break;
	}
	
	if(!valor){
		if(selector){
			if($(vthis).val()){
				$(selector).css('border','2px solid red');
			}else{
				$(selector).css('border','');
			}
		}else{
			alertAtencao("Valores da função 'inserircampo' inválidos ['selector']");
		}
	}else{
		if(vcoluna){
			if(vcoluna == 'campo'){
				CB.post({
					objetos: "_x_i_amostracampos_idsubtipoamostra="+inidsubtipoamostra+"&_x_i_amostracampos_campo="+$(vthis).val()+"&_x_i_amostracampos_idunidade="+valor
				});
			}else{
				CB.post({
					objetos: "_x_i_amostracampos_idsubtipoamostra="+inidsubtipoamostra+"&_x_i_amostracampos_campo="+valor+"&_x_i_amostracampos_idunidade="+$(vthis).val()
				});
			}
		}else{
			alertAtencao("Valores da função 'inserircampo' inválidos ['vcoluna']");
		}
	}
}

function retiraund(inidunidadeobjeto){
	CB.post({
		objetos: "_x_d_unidadeobjeto_idunidadeobjeto="+inidunidadeobjeto
		,parcial:true
	});
}
function inseriund(inidund){
	CB.post({
		objetos: "_x_i_unidadeobjeto_idobjeto="+$("[name=_1_u_subtipoamostra_idsubtipoamostra]").val()+"&_x_i_unidadeobjeto_idunidade="+inidund+"&_x_i_unidadeobjeto_tipoobjeto=subtipoamostra"
		,parcial:true
	});
}
</script>
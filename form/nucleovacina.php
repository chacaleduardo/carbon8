<?
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "nucleo";
$pagvalcampos = array(
	"idnucleo" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from nucleo where idnucleo = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>

<div class="col-md-12">
    <div class="panel panel-default" >
        <div class="panel-heading" style="background-color:#89CB89; color:#fff;">
	   		CADASTRAR NOVA VACINA
	</div>
	 <div class="panel-body"> 
	 <table>
	    <tr> 		    
		<td>
		    <input 
			    name="_1_i_nucleovacina_idnucleovacina" 
			    type="hidden" 			   
			    value="<?=$_1_u_nucleovacina_idnucleovacina?>" 
			    readonly='readonly'					>
		</td> 
	   
		<td>Nome da Vacina:</td> 
		<td>
		    <input 
			    name="_1_i_nucleovacina_vacina" 
			    type="text" 
			    value="<?=$_1_u_nucleovacina_nucleovacina?>" 
									>
		</td> 
	
	    <td> Núcleo:</td> 
		<td>
		    <select readonly name="_1_i_nucleovacina_idnucleo">
			<?fillselect("SELECT n.idnucleo
			, n.nucleo
			
		FROM nucleo n
			
		WHERE n.situacao = 'ATIVO'
			and n.idpessoa in (".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].")
			and n.idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]."

		ORDER BY n.nucleo",$idnucleo);?>		
		    </select>
		</td> 
		<td>Semana:</td> 
		<td>
		    <input autocomplete="off" 
			    name="_1_i_nucleovacina_datavacina" 
				type="number"
				min="1" step="1"
				onkeypress="return event.charCode >= 48 && event.charCode <= 57"
			    value="<?=$_1_u_nucleovacina_datavacina?>" 
									>
		</td>
		<td>
		<button  type="button" class="btn btn-success btn-xs" onclick="novo()" title="Salvar">
                <i class="fa fa-save"></i>Salvar
            </button>
		</td>
	    </tr>	    
	    </table>
	 </div>
    </div>
</div>


	<div class="col-md-12">
		<div class="panel panel-default">
				<div class="panel-heading" style="background-color:#e6e6e6; color:#666">VACINAS CADASTRADAS PARA O NÚCLEO <?=$nucleo;?></div>
				<div class="panel-body">


					<table class="table table-striped planilha">
							<thead>
								<tr>
								
									<th>VACINA</th>	
									<th>NUCLEO</th>
									<th>DATA</th>
									<th>EXCLUIR</th>
								</tr>
							</thead>
							<tbody>
							<?

						$sql2 = "select vacina, nucleo,  datavacina, idnucleovacina from nucleovacina nv join nucleo n on n.idnucleo = nv.idnucleo  where nv.idnucleo='".$_REQUEST['idnucleo']."' order by datavacina;";
							
							$res2 = d::b()->query($sql2) or die("A Consulta dos núcleos falhou:".mysql_error()."<br>Sql:".$sql1); 
							$qtdrows2= mysqli_num_rows($res2);

							if($qtdrows2 > 0){	
								while($row2 = mysqli_fetch_array($res2)){
									echo '<tr><td style="padding:8px;">'.$row2['vacina'].'</td><td style="padding:8px;">'.$row2['nucleo'].'</td><td style="padding:8px;">'.$row2['datavacina'].'</td><td><button type="button" class="btn btn-danger btn-xs" onclick="excluirvacina('.$row2['idnucleovacina'].')" title="Excluir">
									<i class="fa fa-trash"></i>Excluir</button></td></tr>';

								}
							}
							?>
							</tbody>
							</table>
				</div>
			</div>
		</div>


<script >
function excluirvacina(inid){
    if(confirm("Deseja excluir essa vacina para o núcleo?")){		
        CB.post({
        objetos: "_x_d_nucleovacina_idnucleovacina="+inid
		,parcial:true

        });
    }
    
}

function novo(inobj){
    CB.post({
	objetos: 

	"_x_i_nucleovacina_vacina="+$("[name=_1_i_nucleovacina_vacina]").val()+
	"&_x_i_nucleovacina_datavacina="+$("[name=_1_i_nucleovacina_datavacina]").val()+
	"&_x_i_nucleovacina_idnucleo="+$("[name=_1_i_nucleovacina_idnucleo").val()

	,parcial:true

    });
    
}
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape

$("#cbModuloHeader").hide()
</script>

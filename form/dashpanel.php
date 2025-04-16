<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}

// |||||||||||||||||||||||||||||||||||||| 04/12/2019 POR GABRIEL TIBURCIO ||||||||||||||||||||||||||||||||||||||||||||||||| //

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "dashpanel";
$pagvalcampos = array(
	"iddashpanel" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from dashpanel where iddashpanel = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>
<style>
	#campos{
		width:100%;
	}
	#campos button{
		width: 100%;
	}
</style>
<div class="row ">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading ">
			<table>
			<tr> 		    
			<td>
				<input 
					name="_1_<?=$_acao?>_dashpanel_iddashpanel" 
					type="hidden" 			   
					value="<?=$_1_u_dashpanel_iddashpanel?>" 
					readonly='readonly'					>
			</td> 
		
			<td>Rótulo:</td>
				<td>
					<input name="_1_<?=$_acao?>_dashpanel_paneltitle"  type="text" value="<?=$_1_u_dashpanel_paneltitle?>">
				</td>
			<td> Status:</td> 
			<td>
				<select name="_1_<?=$_acao?>_dashpanel_status">
				<?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_dashpanel_status);?>		
				</select>
			</td> 
			<td> Ordem:</td> 
            <td>
            <input 
                    name="_1_<?= $_acao ?>_dashpanel_ordem" 
                    type="text" 
                    value="<?= $_1_u_dashpanel_ordem ?>">
            </td>
			</tr>	    
			</table>
		</div>
		<div class="panel-body">
			<table>
			
			
			<tr>
				<td>Dashboard</td>
				<td nowrap="nowrap">
				<select name="_1_<?=$_acao?>_dashpanel_iddashgrupo">
				<?fillselect("select iddashgrupo, CONCAT(e.sigla,' - ',rotulo) AS rotulo from dashgrupo d JOIN empresa e ON d.idempresa = e.idempresa where d.status = 'ATIVO' order by rotulo ",$_1_u_dashpanel_iddashgrupo);?>		
				</select>
				<?if($_1_u_dashpanel_iddashgrupo){?>
					<a class="fa fa-bars pointer hoverazul" title="Panel" onclick="janelamodal('?_modulo=dashgrupo&_acao=u&iddashgrupo=<?=$_1_u_dashpanel_iddashgrupo?>')"></a>
				<?}?>
            	</td>	
			</tr>
                       
            </table>
		</div>
		
    </div> 
</div>
	

</div>

<?
 if(!empty($_1_u_dashpanel_iddashpanel)){
        $sqle="
		SELECT 
    c.iddashcard,
	c.ordem,
	c.status,
	c.tipoobjeto,
    CONCAT(IF(c.tipoobjeto = 'manual',
                e.sigla,
                '[FLUXO]'),
            ' - ',
            IFNULL(g.rotulo, ''),
            ' -> ',
            IFNULL(paneltitle, ''),
            ' -> ',
            IFNULL(cardtitle, '')
            ,
            ' - > ', 
            IFNULL(cardtitlesub, '')
            ) AS dashboard,
            group_concat(distinct l.idlp separator ' <br> ') as lps,
            group_concat(distinct concat('<a href=\"?_modulo=_lp&_acao=u&idlp=',l.idlp,'&_idempresa=',l.idempresa,'\">',el.sigla,' - ',descricao,'</a>') ORDER BY l.idempresa, l.descricao ASC separator ' <br>  ') as descricao

, group_concat(distinct concat('<a href=\"?_modulo=pessoa&_acao=u&idpessoa=',pe.idpessoa,'&_idempresa=',pe.idempresa,'\">',ep.sigla,' - ',pe.nomecurto,'</a>') ORDER BY ep.idempresa, pe.nomecurto ASC separator ' <br>  ') as pessoa
, group_concat(distinct concat('<a href=\"?_modulo=sgsetor&_acao=u&idsgsetor=',s.idsgsetor,'&_idempresa=',es.idempresa,'\">',es.sigla,' - ',s.setor,'</a>') ORDER BY es.idempresa, s.setor ASC separator ' <br>  ') as setor
, group_concat(distinct concat('<a href=\"?_modulo=sgdepartamento&_acao=u&idsgdepartamento=',d.idsgdepartamento,'&_idempresa=',ed.idempresa,'\">',ed.sigla,' - ',d.departamento,'</a>') ORDER BY ed.idempresa, d.departamento ASC separator ' <br>  ') as departamento
FROM
    dashcard c
        LEFT JOIN
    empresa e ON e.idempresa = c.idempresa
        JOIN
    dashpanel p ON p.iddashpanel = c.iddashpanel
   
        JOIN
    dashgrupo g ON g.iddashgrupo = p.iddashgrupo
 
        LEFT JOIN
    carbonnovo._lpobjeto o ON o.tipoobjeto = 'dashboard'
        AND o.idobjeto = c.iddashcard
        left join 
            carbonnovo._lp l on l.idlp = o.idlp and l.status = 'ATIVO'
		left join empresa el on el.idempresa = l.idempresa
             LEFT JOIN
    lpobjeto op ON op.tipoobjeto = 'pessoa'
        AND op.idlp = l.idlp
        left join pessoa pe on pe.idpessoa = op.idobjeto and pe.status = 'ATIVO'
        left join empresa ep on ep.idempresa = pe.idempresa
        LEFT JOIN
    lpobjeto os ON os.tipoobjeto = 'sgsetor'
        AND os.idlp = l.idlp
        left join sgsetor s on s.idsgsetor = os.idobjeto and s.status = 'ATIVO'
        left join empresa es on es.idempresa = s.idempresa
        LEFT JOIN
    lpobjeto od ON od.tipoobjeto = 'sgdepartamento'
        AND od.idlp = l.idlp
        left join sgdepartamento d on d.idsgdepartamento = od.idobjeto and d.status = 'ATIVO'
        left join empresa ed on ed.idempresa = d.idempresa
        where
        p.iddashpanel='".$_1_u_dashpanel_iddashpanel."' 
            group by
            c.iddashcard
			order by status, ordem, dashboard";
		echo '<!-- '.$sqle.'-->';
		$rese = d::b()->query($sqle) or die("A Consulta do dashcard falhou :".mysql_error()."<br>Sql:".$sqle);
        $qtde=mysqli_num_rows($rese);
        if($qtde>0){
    ?>
        <div class="col-md-12">
           <div class="panel panel-default">   
               <div class="panel-heading" >Indicadores</div>
               <div class="panel-body">
                   <table  class="table table-striped planilha "  > 
				   		<tr>
						   	<td style="text-align:left">Nome</td>
							   <td style="text-align:left">LPs</td>
							   
							   <td style="text-align:left">Pessoas</td>
							   <td style="text-align:left">Setores</td>
							<td style="text-align:right">Departamentos</td>
							<td style="text-align:left">Tipo</td>
							<td style="text-align:right">Ordem</td>
							<td style="text-align:right">Status</td>
						
						</tr>
                       <?
                       while($rowe=mysqli_fetch_assoc($rese)){
                       ?>
                       <tr>
					   		<td style="text-align:left"><a href="?_modulo=dashcard&_acao=u&iddashcard=<?=$rowe['iddashcard']?>"><?=$rowe['dashboard']?></a></td>
							   <td style="text-align:left"><?=$rowe['descricao']?></td>
							   <td style="text-align:left"><?=$rowe['pessoa']?></td>
							   <td style="text-align:left"><?=$rowe['setor']?></td>
							   <td style="text-align:left"><?=$rowe['departamento']?></td>
							   <td style="text-align:left"><?=$rowe['tipoobjeto']?></td>
							   <td style="text-align:right"><?=$rowe['ordem']?></td>
							   <td style="text-align:right"><?=$rowe['status']?></td>
							   
                           
                       </tr>
                        <?
                       }
                        ?>
						
                   </table>
               </div>            
           </div>   
        </div> 
		<div class="col-md-6">
		 	<div class="panel panel-default">   
               	<div class="panel-heading">Associar Botão Novo</div>
		   	
				<div class="panel-body">
					<table class="withd:100%">
						<tr>
							<td>Módulo a ser associado:</td>
							<td>
								<div class="input-group input-group-sm">
									<input type="text" name="modulo_panel" cbvalue="modulo_panel" value="">
								</div>
							</td>
						</tr>
						<tr><td colspan="2"><hr></td></tr>
						<tr>
							<td colspan="2">
								<table>
									<tr>
										<td>
											Módulos Associados
										</td>
										<td></td>
									</tr>
									<?
									$sqlObjVinc = "SELECT idobjetovinculo, m.modulo
													FROM objetovinculo ov JOIN "._DBCARBON."._modulo m ON m.idmodulo = ov.idobjetovinc AND tipoobjetovinc = 'modulo'
													WHERE ov.tipoobjeto = 'dashpanel' AND ov.idobjeto = $_1_u_dashpanel_iddashpanel
												ORDER BY m.modulo";
									$resObjVinc = d::b()->query($sqlObjVinc) or die("A consulta objetovinculo falhou:".mysql_error()."<br>Sql:".$sqlObjVinc);
									while($rowObjVinc = mysqli_fetch_assoc($resObjVinc))
									{
										?>
											<tr>
												<td><label><?=$rowObjVinc['modulo']?></label></td>
												<td align="center">	
													<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick='removerModulo(`<?=$rowObjVinc["idobjetovinculo"]?>`)' alt="Excluir Módulo!"></i>
												</td>
											</tr>
									<? } ?>
								</table>								
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
        <?        
        }// if($qtde>0){
 }
        ?>
<?
if(!empty($_1_u_dashpanel_iddashpanel)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_dashpanel_iddashpanel; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "dashpanel"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';

function getModulos()
{
	global $_1_u_dashpanel_iddashpanel;
	$sql = "SELECT m.idmodulo, m.modulo
			  FROM "._DBCARBON."._modulo m
			 WHERE m.status = 'ATIVO' AND m.rotulomenu IS NOT NULL AND m.btnovo = 'Y'
			 AND NOT EXISTS (SELECT 1 FROM objetovinculo ov 
			 						 WHERE ov.idobjetovinc = m.idmodulo 
									   AND ov.tipoobjeto = 'modulo' 
									   AND ov.tipoobjeto = 'pessoa'
									   AND ov.idobjeto = '".$_1_u_dashpanel_iddashpanel."' 
									   AND ov.tipoobjeto = 'dashpanel')
		  ORDER BY m.rotulomenu;";
    $res= d::b()->query($sql) or die("getModulo: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arr = array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arr[$r["idmodulo"]]["modulo"]=$r["modulo"];        
    }
	return $arr;
}
$arr = getModulos();
$getModulos = $JSON->encode($arr);
?>
<script>
	getModulos = <?=$getModulos?>;

	//mapear autocomplete de clientes
	getModulos = jQuery.map(getModulos, function(o, id) {
		return {"label": o.modulo, value:id+"" }
	});

	//autocomplete de clientes
	$("[name=modulo_panel]").autocomplete({
		source: getModulos
		,delay: 0
		,select: function(event, ui){
			CB.post({
				objetos: {
					"_x_i_objetovinculo_idobjeto": '<?=$_1_u_dashpanel_iddashpanel?>'
					,"_x_i_objetovinculo_tipoobjeto": 'dashpanel'
					,"_x_i_objetovinculo_idobjetovinc": ui.item.value
					,"_x_i_objetovinculo_tipoobjetovinc": 'modulo'
				}
				,parcial: true
			})
		},create: function(){
			$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
				return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
			};
		}	
	});

	function removerModulo(objetovinculo)
	{
		if(confirm("Deseja retirar este Módulo?"))
		{
			CB.post({
				objetos: "_x_d_objetovinculo_idobjetovinculo="+objetovinculo
				,parcial: true
			});
		}
	}
</script>
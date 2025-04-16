<script>
    	let tagTipo = <?= json_encode($tagTipo); ?>,
            idTagClass = <?= $_1_u_tagclass_idtagclass ?? 'false' ?>,
            acao = '<?= $_acao ?>';

        
        if(acao == 'u')
        {
            $('#calendario').on('change', function()
            {
                let value = 'N';

                $(this).attr('disabled', true);

                if(this.checked)
                {
                    value = 'Y';
                } 

                CB.post({
                    objetos: {
                        _x_u_tagclass_idtagclass: idTagClass, 
                        _x_u_tagclass_calendario: value
                    },
                    refresh: false,
                    parcial: true,
                    posPost: function()
                    {
                        $('#calendario').removeAttr('disabled', true);
                    }
                });
            });
        }

		function removerTagTipo(inid) {
			//debugger;
			CB.post({
				objetos: "_x_u_tagtipo_idtagtipo=" + inid +"&_x_u_tagtipo_idtagclass=''",
				parcial: true,
				posPost: function() {
				
				}
			});
		
		}
		
		//Autocomplete de Setores vinculados
		$("#tipotag").autocomplete({
			source: tagTipo,
			delay: 0,
			create: function() {
				$(this).data('ui-autocomplete')._renderItem = function(ul, item) {

					lbItem = item.label;

					return $('<li>')
						.append('<a>' + lbItem + '</a>')
						.appendTo(ul);
				};
			},
			select: function(event, ui) {
				CB.post({
					objetos: {
						"_x_u_tagtipo_idtagclass": $(":input[name=_1_" + CB.acao + "_tagclass_idtagclass]").val(),
						"_x_u_tagtipo_idtagtipo": ui.item.value
					},
					parcial: false
				});
			}
		});
</script>
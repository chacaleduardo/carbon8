<script type="text/Javascript">
    function addCampo(){
        $('#tipounidademed').show();
    }

    function addBloco(vthis){
        CB.post({
            objetos:{
                '_x_i_devicesensorbloco_tipo':vthis.value,
                '_x_i_devicesensorbloco_iddevicesensor':$('[name="_1_u_devicesensor_iddevicesensor"]').val(),
            },
            parcial:true
        });
    }

    function addPonto(v){
        CB.post({
            objetos:{
                '_x_i_devicesensorcalib_iddevicesensorbloco':v,
                '_x_i_devicesensorcalib_nomesensor':$('[name="_1_u_devicesensor_nomesensor"]').val(),
            },
            parcial:true
        });	
    }

    function delPonto(v){
        CB.post({
            objetos:{
                '_x_d_devicesensorcalib_iddevicesensorcalib':v
            },
            parcial:true
        });
    }

    function delBloco(v){
        CB.post({
            objetos:{
                '_x_d_devicesensorbloco_iddevicesensorbloco':v
            },
            parcial:true
        });
    }

    $(document).ready(function(){
        $(window).ready(function(){
            if($('#tipounidademed').children().length == 1 && $('#tipounidademed').children()[0].value == ""){
                $('#varfisica').hide()
            }
            $('[statusbloco="INATIVO"]').each(function(){
                $(this).children('.panel-body').find('table input').not('[type="hidden"]').prop('readonly',true).css('background-color','#e9e8e8'),
                $(this).children('.panel-body').find('.addPonto').hide();
            })
            
        })
    })

    $('.select-picker').selectpicker('render');
</script>
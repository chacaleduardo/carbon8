// Classe modelo de uma notificação vinda da "API"
class NotificacaoModel {
    constructor ( args ) {
        if (typeof args !== 'object')
            return false;

        this.keysNotificacaoModel = [
            'idnotificacao',
            'mod',
            'modpk',
            'idmod',
            'idmodpk',
            'title',
            'corpo',
            'status',
            'localizacao',
            'icon',
            'url',
            'criadoem',
            'restricaoPk',
            'restricaoMod',
            'restricaoIdNot',
        ];

        for(let key of this.keysNotificacaoModel){
            this[key] = args[key];
        }
    }

    toJson () {
        let retObj = {};

        for(let key of this.keysNotificacaoModel){
            retObj[key] = this[key];
        }

        return retObj;
    }

    static fromJson ( json ) {
        return new NotificacaoModel( json );
    }
}

// Classe responsável pela parte visual das notificações e da interação do usuário
class NotificacaoView {
    constructor () {
        this.controller;
        this.broadcast = new NotificacaoBroadcast();
        this.mod;
        this.isSnippet = true;
        this.loading = false;
        this.selector = null;
        this.selectorStr = null;
        this.views;
        this.eventos = new Map();

        this.$oDropdown;
        this.$oIndicador;
        this.$oDropItens;
        this.dropDownCarregado;

        this.init = ( selector, mod = "notificacoes" ) => {
            this.views = {};
            this.mod = mod;
            this.selector = selector;
            this.selectorStr = this.selector ? this.selector.selector : "[cbmodulo='notificacoes']";
            this.controller = new NotificacaoController(mod);
            
            this.trigger('preInit', this);

            // Caso a inicialização não ocorra como previsto, retornar FALSE
            if(!this.iniciarLayoutNotificacao()){
                return false;
            }

            if(mod == 'notificacoes'){
                let localizacoes = this.controller.carregarOutrasNotificacoesLocalStorage();

                for(let localizacao of localizacoes){
                    if(!this.views[localizacao] && localizacao != 'notificacoes'){
                        this.views[localizacao] = new NotificacaoView();
                        this.views[localizacao].init($(`[cbmodulo="${localizacao}"]`), localizacao);
                    }
                }
            }

            this.$oDropdown         = $(`${this.selectorStr} ._ncBell_`);
            this.$oIndicador        = $(`${this.selectorStr} ._ncBell_ ._ncBellIndicator_`);
            this.$oDropItens        = $(`${this.selectorStr} ._ncBell_ ._ncBellItens_`);
            this.$oBtnAcoes         = $(`${this.selectorStr} ._ncBell_ ._ncBellAcoes_`);
            this.$progressIndicator = $(`${this.selectorStr} ._ncBell_ ._ncCircularProgressIndicator_`);

            // Executa ao abrir o Dropdown das notificações
            this.$oDropdown.on('shown.bs.dropdown', this.mostrarPopUpNotificacoes);

            // Executa ao fechar o Dropdown das notificações
            this.$oDropdown.on('hidden.bs.dropdown', this.esconderPopUpNotificacoes);

            // Mostra o sino das notificações na navbar
            this.$oDropdown.removeClass('hidden');

            this.controller.resetarConfiguracoes();

            this.dropDownCarregado = false;

            // Mostra o indicador de notificações
            this.mostrarIndicadorNotificacoes();

            // Inicializa as funções de broadcast da aba atual
            this.declararFuncoesBroadcast();

            if( this.mod == 'notificacoes' && CB.modulo == 'notificacoes' ){
                this.mostrarPopUpNotificacoes();
                $(document).on('scroll', this.buscarMaisNotificacoes);
            }else{
                // Executa ao dar scroll no dropdown das notificações
                this.$oDropItens.on('scroll',this.buscarMaisNotificacoes);
            }
        }

        this.iniciarLayoutNotificacao = () => {
            
            let $insertLocation;

            // Caso seja módulo de notificações
            if(!this.isSnippet && this.selector && this.selector.length > 0){
                this.selector.append(this.templateInicialNotificacoes());

                $(`._ncBellNotifications_ a.fa-ellipsis-h`).hide();

                return true;
            }

            if( this.selector ){
                if( this.selector.length < 1 ) return false;

                this.selector.append(this.templateInicialNotificacoes());
                /*
                if(this.selector.find('ul').length > 0){
                    this.selector.find('ul').prepend(`
                        <li>
                            <a href="javascript:$('[cbmodulo=${this.mod}] ._ncBellIndicator_').dropdown('toggle')">
                                Notificações
                            </a>
                        </li>
                    `);
                }
                */
            // Caso seja o snippet padrão de notificações
            } else if( $("#cbMenuSuperior li[id^='cbSnippet']:first").length > 0 ){

                if((gIdtipopessoa == 1))
                {
                    if( $("#cbMenuSuperior a.snippet:last").length > 0 )
                    {
                        $insertLocation = $("#cbMenuSuperior a.snippet:last");
                        $(this.templateInicialNotificacoes()).insertAfter($insertLocation);
                    }
                } else 
                {
                    $insertLocation = $("#cbMenuSuperior li[id^='cbSnippet']:first");
                    $(this.templateInicialNotificacoes()).insertBefore($insertLocation);
                }

            }else if( $("#cbMenuSuperior li[cbidpessoa]").length > 0 ){

                $insertLocation = $("#cbMenuSuperior li[cbidpessoa]");
                $(this.templateInicialNotificacoes()).afterBefore($insertLocation);

            }else{
                return false;
            }

            return true;
        }

        this.recuperarNotificacaoHTML = () => {
            return `
                <li class="pull-right snippet pointer" cbmodulo>
                    ${this.$oDropdown.parent().html()}
                </li>`
            ;
        }

        this.mostrarPopUpNotificacoes = async () => {

            if( CB.modulo != 'notificacoes' && this.selector ){
                this.selector.css('background-color','#ebebeb');
            }

            this.controller.resetarIndicador();

            this.esconderIndicadorNotificacoes();
            
            this.broadcast.exec(this.mod+".esconderIndicadorNotificacoesBroadcast");

            if(this.dropDownCarregado)
                return;

            this.dropDownCarregado = true;

            let notifListApi = await this.controller.carregarNotificacoesApi();

            let contentDropHtml = (notifListApi.length > 0) 
                                    ? this.construirPopUpNotificacoes( notifListApi ) 
                                    : this.templateNaoPossuiNotificacao();

            this.$oDropItens.html( contentDropHtml );

            this.adicionarWebuiPopoverNotificacoes();

            this.$oBtnAcoes.removeClass('hidden');
        }

        this.buscarMaisNotificacoes = async ( event ) => {

            if(!this.controller.buscarMaisNotificacoes)
                return;

            let el = (event.target == document) ? event.target.body : event.currentTarget;

            if(el.scrollTop + el.clientHeight >= el.scrollHeight - 20) {
                this.$progressIndicator.show();

                if(this.loading)
                    return;

                this.loading = true;

                let notifListApi = await this.controller.carregarNotificacoesApi();

                if(notifListApi.length > 0){
                    let contentDropHtml = this.construirPopUpNotificacoes( notifListApi );

                    this.$oDropItens.append( contentDropHtml );

                    this.adicionarWebuiPopoverNotificacoes();
                }else{
                    this.controller.buscarMaisNotificacoes = false;
                }

                this.loading = false;

                this.$progressIndicator.hide();
            }
        }

        this.esconderPopUpNotificacoes = () => {
            if( CB.modulo != 'notificacoes' && this.selector ){
                this.selector.css('background-color','transparent');
            }
            for(let oNotif of this.controller.notificacoes){
                $(`${this.selectorStr} ._ncBellContentItemButton_${oNotif.idnotificacao}`).webuiPopover('hide');
            }
        }

        this.construirPopUpNotificacoes = ( notifList = [] ) => {
            let contentDrop = "";

            for(let oNotif of notifList){
                contentDrop += this.construirNotificacaoItem( oNotif );
            }

            return contentDrop;
        }

        this.construirNotificacaoItem = ( oNotif ) => {
            if(!oNotif.icon){
                let $o = $(`li[cbmodulo='${oNotif.localizacao}']`);
                let $iconMenuSuperior   = $o.find("i:not(.badgeindicator)");
                let $iconSnippet        = $o.find("a.snippet");

                if($iconMenuSuperior.length > 0){
                    oNotif.icon = NV.extrairClassIcon($iconMenuSuperior.attr('class').split(' '));
                }else if($iconSnippet.length > 0){
                    oNotif.icon = NV.extrairClassIcon($iconSnippet.attr('class').split(' '));
                }
            }

            return this.templateNotificacao(oNotif.status)
                        .replace(/{{idnotificacao}}/g, oNotif.idnotificacao)
                        .replace(/{{modulo}}/g, this.mod)
                        .replace(/{{icon}}/g, oNotif.icon)
                        .replace(/{{criadoem}}/g, oNotif.criadoem)
                        .replace(/{{localizacao}}/g, oNotif.localizacao)
                        .replace(/{{titulo}}/g, oNotif.title)
                        .replace(/{{corpo}}/g, oNotif.corpo);
        }

        this.extrairClassIcon = ( classList ) => {
            for(let classe of classList){
                if(classe.includes('fa-') || classe.includes('icon-'))
                    return classe;
            }
        }

        this.layoutFiltrosNotificacao = () => {
            let itens = "";
            let layout = `
                <div class="_ncBellFiltrosModulosItem_ pointer" onclick="NV.filtrarNotificacoesPorModulo(this, '{{modulo}}')" style="{{style}}">
                    <i class="fa {{classe}}"></i>
                </div>
            `;

            $("li[cbmodulo].dropdown").each(function(i, o){
                if($(o).attr('cbmodulo') != ''){
                    let $iconMenuSuperior   = $(o).find("i:not(.badgeindicator)");
                    let $iconSnippet        = $(o).find("a.snippet");

                    if($iconMenuSuperior.length > 0){
                        itens += layout
                                    .replace(/{{modulo}}/g, $(o).attr('cbmodulo'))
                                    .replace(/{{style}}/g, "margin-right: 10px;")
                                    .replace(/{{classe}}/g, NV.extrairClassIcon($iconMenuSuperior.attr('class').split(' ')))
                    }else if($iconSnippet.length > 0){
                        itens += layout
                                    .replace(/{{modulo}}/g, $(o).attr('cbmodulo'))
                                    .replace(/{{style}}/g, "margin-right: 10px;")
                                    .replace(/{{classe}}/g, NV.extrairClassIcon($iconSnippet.attr('class').split(' ')))
                    }
                }
            });

            return  `
                <div class="_ncBellFiltrosModulos_ hidden">
                    ${itens}
                    ${layout
                        .replace(/{{modulo}}/g, 'notificacoes')
                        .replace(/{{style}}/g, "")
                        .replace(/{{classe}}/g, "fa-bell")}
                </div>
            `;
        }

        this.templateInicialNotificacoes = ( ) => {
            
            let content = "";
            let alignMenu = "dropdown-menu-right";

            if([6494, 111565, 107524, 1098, 98070, 778, 799, 8211, 111319, 798, 114994, 112378, 115227, 107822, 115410, 115414, 1944, 115748, 115703, 115819, 115412].includes(parseInt(gIdpessoa)))
            {
                alignMenu = "dropdown-menu-right";
                content += this.selector ? `` : `<li class="snippet pointer" cbmodulo="notificacoes">`;
            } else 
            {
                content += this.selector ? `` : `<li class="pull-right snippet pointer" cbmodulo="notificacoes">`;
            }

            if(this.isSnippet){
                let top = "0px";

                if(this.selector){
                    if(this.selector.attr('id') && this.selector.attr('id').includes('cbSnippet')){
                        top = "0px";
                        alignMenu = "dropdown-menu-right left-0";
                    }else{
                        top = "-40px";
                        alignMenu = "";
                    }
                }
                
                if([6494, 111565, 107524, 1098, 98070, 778, 799, 8211, 111319, 798, 114994, 112378, 115227, 107822, 115410, 115414, 1944, 115748, 115703, 115819, 115412].includes(parseInt(gIdpessoa)))
                {
                    content += this.selector 
                    ? `<div class="d-flex flex-col align-items-center justify-content-center top-0 nav-item p-2 absolute _ncBell_ dropdown hidden" id="">
                        <span id="" style="z-index:1001;top:${top}" class="_ncBellIndicator_ newbadge fundovermelho dropdown-toggle hidden" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">0</span>`
                    : `<div class="d-flex flex-col align-items-center justify-content-center top-0 nav-item p-2 absolute _ncBell_ dropdown hidden" id="">
                        <a class="_ncBellIcon_ fa fa-bell dropdown-toggle" title="Notificações" role="button" id="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span id="" class="_ncBellIndicator_ newbadge fundovermelho hidden">0</span>
                        </a>
                        <span class='nav-link'>Notificações</span>`;
                } else 
                {
                    content += this.selector 
                    ? `<div class="_ncBell_ dropdown hidden" id="">
                        <span id="" style="z-index:1001;top:${top}" class="_ncBellIndicator_ newbadge fundovermelho dropdown-toggle hidden" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">0</span>`
                    : `<div class="_ncBell_ dropdown hidden" id="">
                        <a class="_ncBellIcon_ fa fa-bell dropdown-toggle" title="Notificações" role="button" id="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span id="" class="_ncBellIndicator_ newbadge fundovermelho hidden">0</span>
                        </a>`;
                }
            }else{
                content += `<div id="" class="_ncBell_">`;
            }

            let classe = this.isSnippet ? "dropdown-menu "+alignMenu : "";

            let filtrosModulo = "";
            let filtrosButton = "";
            if(this.mod == 'notificacoes' ){
                filtrosButton = `
                    <button type="button" class="btn btn-secondary" style="float: right;" onclick="NV.hideShowFiltros()">
                        <i class="fa fa-filter" style="margin-right:0px !important"></i>
                    </button>
                `;
                filtrosModulo = this.layoutFiltrosNotificacao();
            }

            content += `
                <div id="" class="_ncBellNotifications_ ${classe} inicial" aria-labelledby="_ncBellIcon_" onclick="event.stopPropagation()" style="min-width:420px;cursor:default;">
                    <div style="color:grey;padding:0px 10px;display: flex;justify-content: space-between;"">
                        <h2 class="menu-title" style="margin: 5px 0px;font-weight: bold;">Notificações</h2>
                        <a href="?_modulo=notificacoes" class="fa fa-ellipsis-h pointer" style="padding: 10px;color: #bcbcbc;"></a>
                    </div>
                    <div class="divider"></div>
                    <div id="" class="_ncBellAcoes_ hidden">
                        <button type="button" class="btn btn-secondary _ncBorderNaoLido_" onclick="NV.filtrarNotificacoesPorStatus(this, 'N')">Não Lidas</button>
                        ${filtrosButton}
                    </div>
                    ${filtrosModulo}
                    <div id="" class="_ncBellItens_">
                        <div style="margin:0px 10px;" id="" class="_ncBellItemLoad_">
                            <div class="ncbell-item">
                                <h5></h5>
                                <p></p>
                                <p></p>
                            </div>
                        </div>
                        <div style="padding: 5px;cursor: default;"></div>
                    </div>
                    <div id="" class="_ncCircularProgressIndicator_"></div>
                </div>
            `;

            content += this.selector 
                ? `</div>`
                : `</div></li>`;

            return content;
        }

        this.hideShowFiltros = () => {
            let $o = $(`${this.selectorStr} ._ncBellFiltrosModulos_`);

            $o.hasClass('hidden') 
                ? $o.removeClass('hidden') 
                : $o.addClass('hidden');
        }

        this.templateNotificacao = ( status ) => {
            let border = "";

            if(status == 'N'){
                border = "_ncBorderNaoLido_";
            }else if(status = 'L'){
                border = "";
            }

            return `<div id="" class="_ncBellContentItem_{{idnotificacao}} _ncBellContentItem_">
                        <div class="ncbell-item ${border}" onclick="NV.abrirLinkNotificacao({{idnotificacao}}, '{{localizacao}}', '{{modulo}}')">
                            <div style="align-self: center;padding: 5px;margin-right: 10px;">
                                <i class="fa {{icon}}" style="font-size: 22px !important;"></i>
                            </div>
                            <div style="width:100%">
                                <p>{{titulo}}</p>
                                <div style="font-size: 12px;color: #5a5a5a;min-height: 10px;text-align: justify;margin-right: 5px;">{{corpo}}</div>
                                <h5 style="color: grey;font-size:11px;text-align: left;margin-right: 5px;">Recebido em {{criadoem}}</h5>
                            </div>
                            <button type="button" class="_ncBellContentItemButton_{{idnotificacao}} close" id="" style="z-index:1001;padding:0px 5px;">
                                <span class="fa fa-ellipsis-v" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>`;
        }

        this.templateNaoPossuiNotificacao = () => {
            return `<div class="dropdown-item" style="margin: 10px 10px;text-align: center;">
                        <div>
                            <i class="fa icon-bell" style="font-size: 48px !important;color: #565656;"></i>
                        </div>
                        <div>
                            <h2 style="color: #777;">Você não tem notificações</h2>
                        </div>
                    </div>`;
        }

        this.mostrarIndicadorNotificacoes = () => {
            let valIndicador = this.controller.recuperarValorIndicador();
            if(valIndicador > 0)
                this.$oIndicador
                    .removeClass('hidden')
                    .text(valIndicador);
        }

        this.esconderIndicadorNotificacoes = () => {
            let valIndicador = this.controller.recuperarValorIndicador();
            if(valIndicador < 1)
                this.$oIndicador
                    .addClass('hidden')
                    .text(0);
        }

        this.adicionarWebuiPopoverNotificacoes = () => {

            for(let oNotif of this.controller.notificacoes){
                let marcarLidoNaoLidoVal, marcarLidoNaoLidoTxt, oContentRestricaoLiberacaoNotificacoes = "";

                $(`${this.selectorStr} ._ncBellContentItemButton_${oNotif.idnotificacao}`).webuiPopover('destroy');
                
                if(oNotif.status == 'N'){
                    marcarLidoNaoLidoVal = "L";
                    marcarLidoNaoLidoTxt = "Marcar como lido";
                }else{
                    marcarLidoNaoLidoVal = "N";
                    marcarLidoNaoLidoTxt = "Marcar como não lido";
                }

                oContentRestricaoLiberacaoNotificacoes += this.construirLayoutRestricaoLiberacaoNotificacoes({
                    notificacaoObj: oNotif,
                    tipo: 'pk',
                    chave: 'restricaoPk',
                    iconeRestricao: 'fa-times-circle',
                    iconeLiberacao: 'fa-check-circle',
                    mensagemRestricao: 'Não receber notificações desse ID',
                    mensagemLiberacao: 'Receber notificações desse ID',
                });
                oContentRestricaoLiberacaoNotificacoes += this.construirLayoutRestricaoLiberacaoNotificacoes({
                    notificacaoObj: oNotif,
                    tipo: 'modulo',
                    chave: 'restricaoMod',
                    iconeRestricao: 'fa-window-close',
                    iconeLiberacao: 'fa-window-maximize',
                    mensagemRestricao: 'Não receber notificações desse módulo',
                    mensagemLiberacao: 'Receber notificações desse módulo',
                });

                //@TODO: Voltar esse trecho de código após o término do módulo de configuração de notificações
                /*oContentRestricaoLiberacaoNotificacoes += this.construirLayoutRestricaoLiberacaoNotificacoes({
                    notificacaoObj: oNotif,
                    tipo: 'notificacao',
                    chave: 'restricaoIdNot',
                    iconeRestricao: 'fa-ban',
                    iconeLiberacao: 'fa-cicle',
                    mensagemRestricao: 'Não receber esse tipo de notificação',
                    mensagemLiberacao: 'Receber esse tipo de notificação',
                });*/
                
                $(`${this.selectorStr} ._ncBellContentItemButton_${oNotif.idnotificacao}`)
                    .webuiPopover({
                        placement: 'left-bottom',
                        width: 320,
                        content: `<ul class="_ncWebUiPopover_">
                                    <li onclick="NV.alterarStatusNotificacao(${oNotif.idnotificacao}, '${marcarLidoNaoLidoVal}')"><div><i class="fa fa-check"></i></div><div> ${marcarLidoNaoLidoTxt}</div></li>
                                    <li onclick="NV.alterarStatusNotificacao(${oNotif.idnotificacao}, 'X')"><div><i class="fa fa-times"></i></div><div> Remover essa notificação</div></li>
                                    ${oContentRestricaoLiberacaoNotificacoes}
                                </ul>`
                    });
            }
            
        }

        this.construirLayoutRestricaoLiberacaoNotificacoes = ( obj = {} ) => {
            return (obj.notificacaoObj[obj.chave] == 0) 
                ? `<li onclick="NV.restringirNotificacao(${obj.notificacaoObj.idnotificacao}, '${obj.tipo}')">
                        <div><i class="fa ${obj.iconeRestricao}"></i></div>
                        <div>${obj.mensagemRestricao}</div>
                    </li>` 
                : `<li onclick="NV.liberarNotificacao(${obj.notificacaoObj.idnotificacao}, '${obj.tipo}')">
                        <div><i class="fa ${obj.iconeLiberacao}"></i></div>
                        <div>${obj.mensagemLiberacao}</div>
                    </li>`;
        }

        this.alterarStatusNotificacao = ( idNotificacao, status, executarRequisicao = true, notificarAbas = true ) => {
            (executarRequisicao) 
                ? this.controller.alterarStatusPorId(idNotificacao, status)
                : this.controller.alterarStatusNotificacaoListaPorId(idNotificacao, status);

            $(`${this.selectorStr} ._ncBellContentItemButton_${idNotificacao}`).webuiPopover('hide');

            if(['N','L'].includes(status)){
                $(`${this.selectorStr} ._ncBellContentItem_${idNotificacao} .ncbell-item`).toggleClass('_ncBorderNaoLido_');
                this.adicionarWebuiPopoverNotificacoes();
            }else{
                this.controller.removerNotificacao(idNotificacao);
                if(this.controller.notificacoes.length == 0)
                    this.$oDropItens.html( this.templateNaoPossuiNotificacao() );
            }

            if(notificarAbas){
                this.broadcast.exec(this.mod+".alterarStatusNotificacaoBroadcast", {idNotificacao: idNotificacao, status: status});
            }
            
            event.stopPropagation();
        }

        this.novaNotificacao = ( novasNotifList = [] ) => {
            let len = novasNotifList.length;
            let notifList = [];
            let alerta = true;
            if(len < 1)
                return;

            for(let oNotif of novasNotifList){

                if(oNotif.localizacao != this.mod){
                    if(this.mod == 'notificacoes'){
                        notifList.push(oNotif);
                        alerta = false;
                    }

                    if(this.views[oNotif.localizacao]){
                        this.views[oNotif.localizacao].novaNotificacao([oNotif]);
                    }else{
                        this.views[oNotif.localizacao] = new NotificacaoView();
                        this.views[oNotif.localizacao].init($(`[cbmodulo="${oNotif.localizacao}"]`), oNotif.localizacao);
                        this.views[oNotif.localizacao].novaNotificacao([oNotif]);
                    }
                }else{
                    notifList.push(oNotif);
                }
            }

            if(notifList.length < 1)
                return;
            
            this.broadcast.exec(this.mod+".novaNotificacaoItemBroadcast", notifList);

            notifList = this.novaNotificacaoItem( notifList );
            
            if(alerta){
                this.controller.incrementarIndicador( len );

                this.mostrarIndicadorNotificacoes();

                if(!this.controller.verificarPermissoes())
                    return;

                for(let oNotif of notifList){
                    let notificacaoDesktop = new Notification("Você possui uma nova mensagem", {
                        body: oNotif.title,
                    });
    
                    notificacaoDesktop.onclick = (event) => {
                        event.preventDefault();
    
                        window.open(oNotif.url, '_blank');
    
                        this.alterarStatusNotificacao(oNotif.idnotificacao, 'L');
    
                        this.controller.decrementarIndicador();
                        
                        if(this.controller.recuperarValorIndicador() > 0){
                            this.mostrarIndicadorNotificacoes();
                        }else{
                            this.esconderIndicadorNotificacoes();
                        }
                        
                    }
                }
            }
        }

        this.novaNotificacaoItem = ( novasNotifList = [] ) => {
            let len = this.controller.notificacoes.length;

            this.controller.concatenarNovasNotificacoes( novasNotifList );

            if(this.dropDownCarregado){
                let contentDrop = "";

                for(let oNotif of novasNotifList){
                    contentDrop += this.construirNotificacaoItem( oNotif );
                }

                if(len > 0)
                    this.$oDropItens.prepend( contentDrop );
                else
                    this.$oDropItens.html( contentDrop );

                this.adicionarWebuiPopoverNotificacoes();
            }
            return novasNotifList;
        }

        this.abrirLinkNotificacao = ( idNotificacao, localizacao, modulo ) => {

            if( this.mod == localizacao || CB.modulo == 'notificacoes' || this.mod == modulo ){
                let oNotif = this.controller.recuperarNotificacaoPorId(idNotificacao);

                if(oNotif.status == 'N'){
                    this.alterarStatusNotificacao(oNotif.idnotificacao, 'L');
                }

                window.open(oNotif.url, '_blank');
            }else{
                this.views[localizacao].abrirLinkNotificacao( idNotificacao, localizacao, modulo );
            }

        }

        this.filtrarNotificacoesPorStatus = async ( vthis, status ) => {

            if($(vthis).hasClass('_ncbuttonativo_')){
                this.controller.filtroStatus = this.controller.filtroStatus.filter( s => s != status );
                $(vthis).removeClass('_ncbuttonativo_');
            }else{
                this.controller.filtroStatus.push(status);
                $(vthis).addClass('_ncbuttonativo_');
            }

            this.filtrarNotificacoes();
        }

        this.filtrarNotificacoesPorModulo = ( vthis, mod ) => {
            if(mod != ''){

                if($(vthis).hasClass('filtroModuloAtivo')){
                    this.controller.filtroModulos = this.controller.filtroModulos.filter( m => m != mod );
                    $(vthis).removeClass('filtroModuloAtivo');
                }else{
                    this.controller.filtroModulos.push(mod);
                    $(vthis).addClass('filtroModuloAtivo');
                }

                this.filtrarNotificacoes();
            }
        }

        this.filtrarNotificacoes = async () => {
            for(let oNotif of this.controller.notificacoes){
                $(`${this.selectorStr} ._ncBellContentItemButton_${oNotif.idnotificacao}`).webuiPopover('hide');
            }

            this.$progressIndicator.show();

            this.$oDropItens.html("");

            this.controller.resetarConfiguracoes();

            let notifListApi = await this.controller.carregarNotificacoesApi();

            let contentDropHtml = (notifListApi.length > 0) 
                                    ? this.construirPopUpNotificacoes( notifListApi ) 
                                    : this.templateNaoPossuiNotificacao();

            this.$progressIndicator.hide();
            this.$oDropItens.html( contentDropHtml );

            this.adicionarWebuiPopoverNotificacoes();
        }

        this.restringirNotificacao = async ( idNotificacao, tipo ) => {
            event.stopPropagation();

            $(`${this.selectorStr} ._ncBellContentItemButton_${idNotificacao}`).webuiPopover('hide');
            $("${this.selectorStr} ._ncWebUiPopover_").addClass('_ncDisableSpace_');

            let response = await this.controller.restringirNotificacaoPorId( idNotificacao, tipo );

            if(response){
                this.adicionarWebuiPopoverNotificacoes();
                this.broadcast.exec(this.mod+".reconstruirWebuiPopoverNotificacoesBroadcast", this.controller.notificacoes);
            }

            $(`${this.selectorStr}  ._ncWebUiPopover_`).removeClass('_ncDisableSpace_');
        }

        this.liberarNotificacao = async ( idNotificacao, tipo ) => {
            event.stopPropagation();

            $(`${this.selectorStr} ._ncBellContentItemButton_${idNotificacao}`).webuiPopover('hide');
            $(`${this.selectorStr} ._ncWebUiPopover_`).addClass('_ncDisableSpace_');

            let response = await this.controller.liberarNotificacaoPorId( idNotificacao, tipo );
            
            if(response){
                this.adicionarWebuiPopoverNotificacoes();
                this.broadcast.exec(this.mod+".reconstruirWebuiPopoverNotificacoesBroadcast", this.controller.notificacoes);
            }

            $(`${this.selectorStr} ._ncWebUiPopover_`).removeClass('_ncDisableSpace_');
        }

        this.declararFuncoesBroadcast = () => {

            this.broadcast.add({
                [this.mod+".mostrarIndicadorNotificacoesBroadcast"] : this.mostrarIndicadorNotificacoes,
                [this.mod+".esconderIndicadorNotificacoesBroadcast"] : this.esconderIndicadorNotificacoes,
                [this.mod+".novaNotificacaoItemBroadcast"] : ( notifList ) => {
                    this.mostrarIndicadorNotificacoes();
                    this.novaNotificacaoItem( notifList );
                },
                [this.mod+".alterarStatusNotificacaoBroadcast"] : ( obj ) => {
                    this.alterarStatusNotificacao(obj.idNotificacao, obj.status, false, false);
                    if(this.controller.recuperarValorIndicador() > 0){
                        this.mostrarIndicadorNotificacoes();
                    }else{
                        this.esconderIndicadorNotificacoes();
                    }
                },
                [this.mod+".reconstruirWebuiPopoverNotificacoesBroadcast"] : ( lNotif ) => {
                    this.controller.notificacoes = lNotif.map((oNotif) => NotificacaoModel.fromJson( oNotif ));
                    this.adicionarWebuiPopoverNotificacoes();
                }
            });

        }

        this.on = (label, fn) => {

			if((fn && typeof fn === 'function') && (label && typeof label === 'string')){

				var events = this.eventos;

				if(events.has(label)){ // Verifica a existência do label passado como parâmento na lista de eventos do Carbon.

					const oldEvents = events.get(label); // Recupera lista de funções que possuem o respectivo label como chave.

					let exists = false;

					for(let f of oldEvents){
						if(f){
							if(f.toString() === fn.toString()){
								exists = true;
								break;
							}
						}
					}

					if(!exists){
						events.set(label, [ ...oldEvents, fn ]) // Set uma nova lista de funções para o label, mesclando com a lista antiga.
						return oldEvents.length;
					}else{
						return false;
					}
					
				}else{

					events.set(label, [ fn ]); // Inicia uma lista de funções para o respectivo label.

					return 0;
				}

			}else{

				console.error("Function .on: Verifique os parâmetros da função");
				return false;
			}

		}

        this.trigger = (label, ...data) =>{

			if(label && typeof label === 'string'){

				var events = this.eventos;

				const listeners = events.get(label); // Recupera lista de funções que possuem o respectivo label como chave.

				if (Array.isArray(listeners) && listeners.length) { // Verifica se listeners é um Array e se o label existe neste Array .

					returnList = [];
					listeners.forEach(event => {
						if(event){
							returnList.push( event(...data));
						}
					}); // Executa todas as funções associadas ao respectivo label.

					return returnList;
				}

				return [];
			}else{

				console.error("Function .trigger: Verifique os parâmetros da função");
				return [];
			}

		}
    }
    
}

// Classe responsável pelo controle das notificações
// como o valor dos indicadores, a lista de notificações e chamada ao repositório
class NotificacaoController {

    constructor ( mod ) {
        this.localStorageKey = 'notificacoes';
        this.mod = mod;
        this.filtroStatus = [];
        this.filtroModulos = [];

        this.verificarPermissoes = () => {
            if (!("Notification" in window)) {
                console.warn("Este browser não suporta notificações de Desktop");
                return false;
            }else if (Notification.permission === "granted") {
                return true;
            }else if (Notification.permission !== "denied") {
                Notification.requestPermission();
                return false;
            }else{
                return false;
            }
        }

        this.verificarDependencias = () => {
            if(FC && typeof FC === 'object'
                && CB && typeof CB === 'object'
                && localStorage && typeof localStorage === 'object'
                && NotificacaoRepository && typeof NotificacaoRepository === 'function'
            ){
                return true;
            }else{
                return false;
            }
        }

        this.carregarNotificacoesApi = async () => {
            let filtros = {
                status: this.filtroStatus,
                modulos: this.filtroModulos,
            }

            let notifList = await this.repository.recuperarUltimasNotificacoes( this.offset, JSON.stringify(filtros) );
    
            // Verifica se o usuário recebeu uma notificação sem que o dropdown tenha sido construído
            // Isso ocorre na função NotificacaoView.novaNotificacao();
            // Se sim, é necessário resetar o array de notificações p/ não existir notificações repetidas
            if(this.offset == 0 && this.notificacoes.length > 0){
                this.notificacoes = [];
            }
    
            this.offset += notifList.length;
    
            notifList = notifList.map((notif) => NotificacaoModel.fromJson( notif ));
    
            this.notificacoes = this.notificacoes.concat( notifList );
    
            return notifList;
        }

        this.concatenarNovasNotificacoes = ( novasNotifList = [] ) => {
            let nNotifList = novasNotifList.map((notif) => NotificacaoModel.fromJson( notif ));
            this.notificacoes = this.notificacoes.concat( nNotifList );
        }
    
        this.alterarStatusPorId = ( idNotificacao, status ) => {
            if(!idNotificacao && typeof idNotificacao != 'number'){
                console.error('[alterarStatusNotificacao]: idNotificacao inválido');
                return;
            }
    
            if(!status && typeof idNotificacao != 'string' && !['N','L','X'].includes(status)){
                console.error('[alterarStatusNotificacao]: status inválido');
                return;
            }
    
            let oNotif = this.recuperarNotificacaoPorId(idNotificacao);
            oNotif.status = status;
    
            this.repository.alterarStatusNotificacaoPorId(idNotificacao, status);
        }

        this.restringirNotificacaoPorId = async ( idNotificacao, tipoRestricao ) => {
            let idObjeto;
            let tipoObjeto;
            let campoRestricao;
            let lAlterarNotificacoes;
            
            let oNotif = this.recuperarNotificacaoPorId( idNotificacao );
    
            switch( tipoRestricao ){
                case 'pk':
                    idObjeto = oNotif.idmodpk;
                    tipoObjeto = oNotif.modpk;
                    campoRestricao = 'restricaoPk';
                    lAlterarNotificacoes = this.recuperarNotificacoesPorIdPkeIdModulo( oNotif.idmodpk, oNotif.idmod );
                    break;
                case 'modulo':
                    idObjeto = oNotif.idmod;
                    tipoObjeto = oNotif.mod;
                    campoRestricao = 'restricaoMod';
                    lAlterarNotificacoes = this.recuperarNotificacoesPorIdModulo( oNotif.idmod );
                    break;
                case 'notificacao':
                    //@TODO: complementar após a criação do módulo de configuração de notificações
                    campoRestricao = 'restricaoIdNot';
                    return false;
                default:
                    return;
            }
    
            let response = await this.repository.adicionarRestricaoNotificacao( idObjeto, tipoObjeto, tipoRestricao );

            if(response && response["message"] == "OK"){
                lAlterarNotificacoes.map(notif => notif[campoRestricao] = response["INSERT_ID"]);
                return true;
            }else{
                console.error("[Error][restringirNotificacaoPorId]: "+response["info"]);
                return false;
            }
        }

        this.liberarNotificacaoPorId = async ( idNotificacao, tipoRestricao ) => {
            let campoRestricao;
            let lAlterarNotificacoes;
            
            let oNotif = this.recuperarNotificacaoPorId( idNotificacao );
    
            switch( tipoRestricao ){
                case 'pk':
                    campoRestricao = 'restricaoPk';
                    lAlterarNotificacoes = this.recuperarNotificacoesPorIdPkeIdModulo( oNotif.idmodpk, oNotif.idmod );
                    break;
                case 'modulo':
                    campoRestricao = 'restricaoMod';
                    lAlterarNotificacoes = this.recuperarNotificacoesPorIdModulo( oNotif.idmod );
                    break;
                case 'notificacao':
                    //@TODO: complementar após a criação do módulo de configuração de notificações
                    campoRestricao = 'restricaoIdNot';
                    return false;
                default:
                    return;
            }
    
            let response = await this.repository.removerRestricaoNotificacao( oNotif[campoRestricao] );

            if(response && response["message"] == "OK"){
                lAlterarNotificacoes.map(notif => notif[campoRestricao] = 0);
                return true;
            }else{
                console.error("[Error][restringirNotificacaoPorId]: "+response["info"]);
                return false;
            }
        }
    
        this.alterarStatusNotificacaoListaPorId = ( idNotificacao, status ) => {
            let oNotif = this.recuperarNotificacaoPorId(idNotificacao, status);
            oNotif.status = status;
        }
    
        this.recuperarNotificacaoPorId = ( idNotificacao ) => {
            return this.notificacoes.find(oNotif => oNotif.idnotificacao == idNotificacao);
        }

        this.recuperarNotificacoesPorIdPkeIdModulo = ( idmodpk, idmodulo ) => {
            return this.notificacoes.filter(oNotif => oNotif.idmodpk == idmodpk && oNotif.idmod == idmodulo);
        }

        this.recuperarNotificacoesPorIdModulo = ( idmodulo ) => {
            return this.notificacoes.filter(oNotif => oNotif.idmod == idmodulo);
        }
    
        this.incrementarIndicador = ( nSum = 1 ) => {
            let storage = localStorage.getItem(this.localStorageKey);
            let json    = JSON.parse(storage);
            json[this.mod].indicador += nSum;
            localStorage.setItem(this.localStorageKey, JSON.stringify(json));
        }
    
        this.decrementarIndicador = ( nSum = 1 ) => {
            let storage = localStorage.getItem(this.localStorageKey);
            let json    = JSON.parse(storage);
            if(json[this.mod].indicador > 0){
                json[this.mod].indicador -= nSum;
                localStorage.setItem(this.localStorageKey, JSON.stringify(json));
            }
        }
    
        this.removerNotificacao = ( idNotificacao ) => {
            $(`._ncBellContentItem_${idNotificacao}`).remove();
            this.notificacoes = this.notificacoes.filter(oNotif => oNotif.idnotificacao != idNotificacao);
        }
    
        this.recuperarValorIndicador = () => {
            let storage = localStorage.getItem(this.localStorageKey);
            let json    = JSON.parse(storage);
    
            return json[this.mod].indicador;
        }
    
        this.resetarIndicador = () => {
            let storage = localStorage.getItem(this.localStorageKey);
            let json    = JSON.parse(storage);
    
            json[this.mod].indicador = 0;
            localStorage.setItem(this.localStorageKey, JSON.stringify(json));
        }
    
        this.resetarConfiguracoes = () => {
    
            this.offset = 0;
            this.notificacoes = [];
            this.buscarMaisNotificacoes = true;

            if(this.mod != 'notificacoes'){
                this.filtroModulos.push(this.mod);
            }
    
            this.repository = new NotificacaoRepository();
    
            let storage = localStorage.getItem(this.localStorageKey);
            if(storage == null){
                localStorage.setItem(this.localStorageKey,`{"${this.mod}":{"indicador":0}}`);
            }else{
                let json = JSON.parse(storage);
                if(!json[this.mod]){
                    json[this.mod] = {"indicador":0};
                    localStorage.setItem(this.localStorageKey, JSON.stringify(json));
                }
            }
        }

        this.carregarOutrasNotificacoesLocalStorage = () => {
            let localizacoes = [];
            let storage = localStorage.getItem(this.localStorageKey);
            let json = JSON.parse(storage);

            if(json){
                for(let localizacao in json){
                    localizacoes.push(localizacao);
                }
            }

            return localizacoes;
        }

        if(!this.verificarDependencias()){
            console.warn(`[NotificacaoController] Não foi possível instanciar o objeto`);
            return;
        }

        this.resetarConfiguracoes();
    }
}

// Classe responsável pela comunicação com o backend
class NotificacaoRepository {
    constructor () {
        this.route = "api/notifitem/notificacao_middleware.php";
        this.defaultHeaders = {
            "authorization" : Cookies.get('jwt') || localStorage.getItem("jwt") || "",
            "Content-Type": "application/x-www-form-urlencoded",
        }
    }

    async recuperarUltimasNotificacoes ( offset = 0, filtros = "" ) {
        try{
            let response = await fetch(`${this.route}?__cmd=ultimasNotificacoes&offset=${offset}&filtros=${filtros}`,{
                method: "GET",
                headers: this.defaultHeaders
            });
            return await response.json();
        }catch(e){
            console.error("[recuperarUltimasNotificacoes]: "+e.toString());
            return [];
        }
    }

    async alterarStatusNotificacaoPorId ( idNotificacao, status ) {

        try{
            let response = await fetch(`${this.route}?__cmd=alterarStatusNotificacaoPorId`,{
                method: "POST",
                headers: this.defaultHeaders,
                body: `idnotificacao=${idNotificacao}&status=${status}`
            });
            return await response.json();
        }catch(e){
            console.error("[alterarStatusNotificacaoPorId]: "+e.toString());
        }
    }

    async adicionarRestricaoNotificacao ( idObjeto, tipoObjeto, tipoRestricao ) {
        try{
            let response = await fetch(`${this.route}?__cmd=adicionarRestricaoNotificacaoUsuario`,{
                method: "POST",
                headers: this.defaultHeaders,
                body: `tipoRestricao=${tipoRestricao}&idObjeto=${idObjeto}&tipoObjeto=${tipoObjeto}`
            });
            return await response.json();
        }catch(e){
            console.error("[adicionarRestricaoNotificacao]: "+e.toString());
            return {"message": "Erro"};
        }
    }

    async removerRestricaoNotificacao ( idNotificacaoRestricao ) {
        try{
            let response = await fetch(`${this.route}?__cmd=removerRestricaoNotificacao`,{
                method: "POST",
                headers: this.defaultHeaders,
                body: `idNotificacaoRestricao=${idNotificacaoRestricao}`
            });
            return await response.json();
        }catch(e){
            console.error("[removerRestricaoNotificacao]: "+e.toString());
            return {"message": "Erro"};
        }
    }
}

// Classe responsável por fazer o broadcast das notificações entre as abas do navegador do usuário
class NotificacaoBroadcast {

    add (objFunc) {
        if(objFunc && typeof objFunc == 'object'){

            for(let nomeFunc of Object.keys(objFunc)){

                let callback = objFunc[nomeFunc];

                if(nomeFunc && typeof nomeFunc == 'string' && callback && typeof callback =='function'){
                    // Remove qualquer callback já existentes para as funções de Broadcast das notificações
                    // para não ocorrer duplicidade de chamadas das funções
                    FC.off(nomeFunc);

                    // Adiciona um callback (objFunc[nomeFunc]) para o rótulo 'nomeFunc'
                    FC.on(nomeFunc, callback);
                }

            }
        }
    }

    exec (nomeFunc, dados) {
        FC.send(nomeFunc, dados, false, true);
    }
}

var NV = new NotificacaoView();

// Inicializa as notificações após o carregamento do módulo caso o usuário esteja logado
CB.on('posInit', () => (CB.logado && CB.modulo != 'notificacoes') ? NV.init() : null);
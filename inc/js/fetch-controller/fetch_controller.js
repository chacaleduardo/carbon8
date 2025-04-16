/*
    GVT - 03/03/2021

    FecthController

        ** Classe responsável por realizar requisições em loop e compartilhar **
        ** a resposta para todas as abas conectadas no BroadcastChannel **

        - Propriedade:
            -> _TIMER_PADRAO_   : <Integer> valor padrão para loop de requisições caso não informado;
            -> callbacks        : <Object> resposável pelo controle de chamada de callback de uma requisição;
            -> requests             : <Map> dicionário de requições a serem executadas, pausadas ou removidas;
            -> canal            : <String> nome do canal que a aba irá se conectar;
            -> channel          : <BroadcastChannel> canal de comunicação entre as abas conectadas.

        - Métodos:
            -> init()                                                   : <Void> inicializa a classe;
            -> setId()                                                  : <Void> cria/atualiza um ID aleatório para a aba na sessionStorage;
            -> getId()                                                  : <String> recupera o ID criado;
            -> connectBroadCastChannel()                                : <Void> conecta-se ao canal especificado;
            -> start()                                                  : <Void> percorre o Map(reqs) executando a função associada a chave;
            -> request(<Object> obj)                                 : <Void> adiciona um loop de fecths em um Map(reqs) a ser executado posteriormente;
            -> on(<String> name, <Function> callback)                   : <Void> atribui um callback a uma requição que possui o 'name' correspondente;
            -> off(<String> name, <Function> callback)                  : <Void> remove um callback associado a um 'name';
            -> broadcast(<String> name, <String> data)                  : <Void> executa o callback associado a requição 'name' disponibilizando o retorno 'data' da requisição;
            -> send(<String> name, <string> data [, includeSelf=true])  : <Void> compartilha a resposta 'data' da requisição 'name' para todas as abas conectadas ao canal, 
                                                                                 onde, por padrão o executor da requisição também recebe a resposta.
*/
class FetchController{

    constructor ( canal = 'sislaudo_channel' ) {

        this._TIMER_PADRAO_ = 5;
        this.callbacks = {};
        this.requestsOn = new Map();
        this.requestsOff = new Map();
        this.canal = canal;
        this.started = false;
        this.posInit = null;
    }

    init () {

        this.setId();
        this.connectBroadCastChannel();
    
        if (this.posInit && typeof(this.posInit) === "function"){
            this.posInit();
        }
    }

    setId () {

        let s = () => {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
        }
        //retorna id no formato 'aaaaaaaa'-'aaaa'-'aaaa'-'aaaa'-'aaaaaaaaaaaa:0000000000000'
        let id =  s() + s() + '-' + s() + '-' + s() + '-' + s() + ':' + new Date().getTime() + "=";
        sessionStorage.setItem('idTab', id);

    }

    getId () {

        return sessionStorage.getItem('idTab');

    }

    getTimestemp () {
        return Math.round(new Date().getTime() / 1000);
    }

    connectBroadCastChannel () {

        this.channel = new BroadcastChannel(this.canal);

        this.channel.onmessage = ev => {
            this.broadcast(ev.data.nome,ev.data.dados)
        };

    }

    start (name) {
        let f = this.requestsOn.get(name);
        if(f){
            f.func();
            this.requestsOff.set(name,setInterval(() => {
                if(!f.initialized){
                    //console.log(`Executei: ${this.getTimestemp()}`);
                    f.func()
                }/*else{
                    console.log(`Já estou sendo executado: ${this.getTimestemp()}`);
                }*/
            },f.timer*1000));
        }
    }

    startAll () {

        if(!this.started){
            this.started = true;

            this.requestsOn.forEach( (v, k) => {
                v.func();
                this.requestsOff.set(k,setInterval(() => {
                    if(!v.initialized){
                        //console.log(`Executei: ${this.getTimestemp()}`);
                        v.func()
                    }/*else{
                        console.log(`Já estou sendo executado: ${this.getTimestemp()}`);
                    }*/
                },v.timer*1000));
            });

        }

    }

    stop (name) {
        let f = this.requestsOff.get(name);
        if(f) clearInterval(f);
    }

    stopAll () {

        if(this.started){
            this.started = false;
            this.requestsOff.forEach( (v, k) => {
                clearInterval(v);
            });
        }

    }

    /*
        <Void> requisicacao(<Object> obj)

        * Parâmetros Obrigatórios

        obj:
            *name: <String> nome da requição e do callback;
            *url: <String> endereço da requisição;
            method: <String> método da requisição (GET, POST, PUT, DELETE, etc.), por padrão POST;
            headers: <Object> headers da requisição, por padrão {};
            body: <Object> corpo da requisição, por padrão {};
            timer: <Integer> intervalo do loop da requisição, por padrão _TIMER_PADRAO_.
    */

    request ( obj ) {

        if(!obj.name || typeof obj.name != "string"){
            console.warn("FC.requisicao: atributo 'name' obrigatório");
            return null;
        }

        if(!obj.url || typeof obj.url != "string"){
            console.warn("FC.requisicao: atributo 'url' obrigatório");
            return null;
        }

        obj.method = obj.method || "POST";
        obj.body = obj.body || {};
        obj.timer = (!obj.timer || obj.timer <= 0) ? this._TIMER_PADRAO_ : obj.timer;

        // Headers padrões para toda requisição do controller
        var o = {
            "authorization" : Cookies.get('jwt') || localStorage.getItem("jwt") || "",
            "hdrctrlid" : this.getId() || "",
            "hdrctrlreq" : obj.name || "",
            "hdrctrltimer" : obj.timer,
            "Content-Type": "application/x-www-form-urlencoded"
        }

        obj.headers = obj.headers || {};
        obj.headers = Object.assign({}, obj.headers, o);

        obj.headers = new Headers(obj.headers);

        var options = {};

        // Verifica se o atributo 'body' foi preenchido e se a requisição é diferente de GET ou HEAD
        // GET e HEAD não aceitam 'body' na requisição
        if(Object.keys(obj.body).length > 0 && !(["GET","HEAD"].includes(obj.method.toLocaleUpperCase()))){
            obj.body = Object.keys(obj.body)
                .map(k => `${encodeURIComponent(k)}=${encodeURIComponent(obj.body[k])}`)
                .join('&');

            options = {
                method: obj.method, 
                headers: obj.headers, 
                body: obj.body
            }
        }else{
            options = {
                method: obj.method, 
                headers: obj.headers
            }
        }
        
        var vthis = this;

        if(!(obj.name in this.callbacks)){
            this.callbacks[obj.name] = []
        }
        this.callbacks[obj.name].push(obj.callback);

        this.requestsOn.set(obj.name, 
            {
                timer: obj.timer,
                initialized: false,
                func: async function () {
                    let myself = vthis.requestsOn.get(obj.name);

                    myself.initialized = true;

                    let res = await fetch(obj.url, options);
                    
                    if(res.status == 200 && res.headers.get("hdrctrlresp") == 298){
                        let data = await res.text()

                        if(obj.posFetch && typeof obj.posFetch  === "function"){
                            obj.posFetch(data);
                        }

                        // Avisar para todas as abas
                        vthis.send(obj.name, data, obj.includeSelf, obj.share)
                    }

                    if(!res.ok){
                        console.error("FC: Falha na requisição");
                    }

                    myself.initialized = false;
                }
            }
        );

    }

    
    on ( name, callback ) {

        if(!(name in this.callbacks)){
            this.callbacks[name] = []
        }
        this.callbacks[name].push(callback);

    }

    off( name, callback ) {

        if (name in this.callbacks) {
            if (typeof callback === 'function') {
                const index = this.callbacks[name].indexOf(callback);
                this.callbacks[name].splice(index, 1);
            }
            if (typeof callback !== 'function' || this.callbacks[name].length === 0) {
                delete this.callbacks[name];
            }
        }

    }

    broadcast( name, data ) {

        if (name in this.callbacks) {
            this.callbacks[name].forEach(callback => callback(data));
        }

    }

    send( name, data, includeSelf=true, share = true) {
        if(share){
            this.channel.postMessage({nome:name, dados:data});
        }
        
        if (includeSelf) {
            this.broadcast(name, data);
        }

    }

}

var FC = new FetchController();

const _loadRequests = () => {

    FC.request({
        name: "notification",
        url: "api/notifitem/",
        method: "post",
        timer: 30,
        includeSelf: true,
        posFetch: function(InMsgs){ // Somente a aba que recebeu a resposta executa
            let InObj = jsonStr2Object(InMsgs);
            NV.novaNotificacao(InObj['messages']);
        },
        callback: function(data){ // Todas as abas executam após uma resposta de sucesso. "BROADCAST"
            //NC.showBadges();
            //NC.showNotifications();
        }
    });

/* Recuperado da maquina gabriel: requisicoes,js
FC.request({
    name: "bim",
    url: "inc/php/im/bim.php",
    method: "post",
    headers: {
        "cache-control": "no-cache",
    },
    body: {
        'call':"refresh"
    },
    timer: 5,
})
*/

}

CB.on('posInit', function (data){
    FC.init();

    if(CB.logado){

        _loadRequests();

        FC.startAll();
    }

});
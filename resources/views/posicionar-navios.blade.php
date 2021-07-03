@extends('layouts.posicionar')

@section('content')

    <div class="img-background">
        <div class="img-wrap">
            <div id="img">
                <img src={{secure_asset('img/battle2.jpg')}} alt="battle">
            </div>
        </div>
    </div>

    <div class="caption text-center">
        <h2>POSICIONE AS EMBARCAÇÕES</h2>
    </div>

    <div class="buttons">
        <div class="button-comecar" onclick="setNaviosCasas()">
            <input href="#" type="button" class="button comecar" value="Começar">
        </div>

        <div class="button-resetar" id = "resetarNavios">
            <input href="#" type="button" class="button resetar" value="Resetar">
        </div>

        <div class="button-voltar" onclick="window.location.href='{{route('index')}}'">
            <input href="" type="button" class="button voltar" value="Voltar">
        </div>

        <div class="button-voltar" onclick="enableMute()">
            <input href="" type="button" class="button voltar" value="Som">
        </div>
    </div>

    <div id="container" class="config"></div>

    <div id="casas" class="classHouse">
        @foreach ($jogo->tabuleiros[0]->casas as $casa)
            <input type="hidden" id="casa{{$casa->linha}}x{{$casa->coluna}}" value="{{$casa->id}}">
        @endforeach
    </div>

    <div id="navios" class="classNavios">
        @foreach ($jogo->tabuleiros[0]->navios as $i => $navio)
            <input type="hidden" id="navio{{$i+1}}" value="{{$navio->id}}">
            <input type="hidden" id="tamanho_navio{{$i+1}}" value="{{$navio->tamanho}}">
        @endforeach
    </div>

    <form id="salvar-navios-form" method="POST" action="{{route('save.navios')}}">
      @csrf
      <input type="hidden" name="jogo_id" value="{{$jogo->id}}">
      @foreach ($jogo->tabuleiros[0]->casas as $casa)
          <div id="casa_{{$casa->id}}" style="display: none;">
              <input type="text" name="casas_id[]" value="{{$casa->id}}">
              <input type="text" name="navio_id[]" value="">
              <input type="text" name="posicoes_id[]" value="">
          </div>
      @endforeach
    </form>

    <audio id="background-sound" controls loop muted autoplay hidden>
        <source src="{{secure_asset('audio/suspense.mp3')}}" type="audio/mpeg">
    </audio>

    <script>
      var telaLargura = 930;
      var telaAltura = 615;
      var navios = {//aqui defini onde os navios vao spawnar na tela e a posicao
      };
      var casas = {//object pra guardar as casas
      };
      const tamanhoTabuleiro = 10;
      const espacoEntreCasas = 55; //relativo ao tamanho em pixels da celula
      const espacoDeEncaixe = 28; //pra encaixar o navio na celula
      const anguloDeRotacao = 270; //rotacao de click no navio
      const quantidadeNavios = 5;

      var somPop = new loadSound('{{secure_asset('audio/pop.mp3')}}');
      var somPopPlace = new loadSound('{{secure_asset('audio/pop.mp3')}}');

      var stage = new Konva.Stage({//stage padrao pra jogar os elementos na tela
        container: 'container',
        width: telaLargura,
        height: telaAltura,
      });
      var images = {};

      function loadImages(sources, callback) { //carrega as imagens definidas em sources e as propriedades de initStage
        var loadedImages = 0;
        var numImages = 0;
        for (let src in sources) {
          numImages++;
        }
        for (let src in sources) {
          images[src] = new Image();
          images[src].onload = function () {
            if (++loadedImages >= numImages) {
              callback(images);
            }
          };
          images[src].src = sources[src];
        }
      };

        function loadSound(src) {
            this.sound = document.createElement("audio");
            this.sound.src = src;
            this.sound.setAttribute("preload", "auto");
            this.sound.setAttribute("controls", "none");
            this.sound.style.display = "none";
            document.body.appendChild(this.sound);
            this.play = function(){
                this.sound.play();
            }
            this.stop = function(){
                this.sound.pause();
            }
        }

        var bgaudio = document.getElementById("background-sound");

        function enableMute() {
            if (bgaudio.muted){
                bgaudio.muted = false;
            } else {
                bgaudio.muted = true;
            }
        }

      function getCasasProximo(angulo, navio, casas){//Pega a casa mais proxima do navio pra fazer o encaixe, se existir uma
          let selecionada = [];
          for (var casa in casas) {
            let a = navio;
            let o = casas[casa];
            let ax = a.x();
            let ay = a.y();
            if(angulo == anguloDeRotacao){
              if(ax > (o.x-espacoDeEncaixe) && ax < (o.x+espacoDeEncaixe) && ay-espacoDeEncaixe > (o.y-espacoDeEncaixe) && ay-espacoDeEncaixe < (o.y+espacoDeEncaixe)){
                selecionada.push(casa);
              }
            }else{
              if(ax > (o.x-espacoDeEncaixe) && ax < (o.x+espacoDeEncaixe) && ay > (o.y-espacoDeEncaixe) && ay < (o.y+espacoDeEncaixe)){
                selecionada.push(casa);
              }
            }
          }
          return selecionada;
      };

      function getLinhaCasa(casa){
        return casa.linha;
      };

      function getColunaCasa(casa){
        return casa.coluna;
      };

      function getNavioPosicionado(nav){
          return nav.posicionado;
      };

      function getNavioTamanho(nav){
          return nav.tamanho;
      };

      function setCasaOcupada(casa, valor, id_navio, posicao){
        casa.ocupada = valor;
        casa.navio = id_navio;
        casa.posicao = posicao;
      };

      function setNavioPosicionado(nav, valor){
          nav.posicionado = valor;
      };

      function setRollCasasAcima(nav, casa, casas, valor, id_navio){
        if (valor) {
          for(let i = 0; i < getNavioTamanho(nav); i++){
            let cas = casas['casa'+(getLinhaCasa(casa)-i)+'x'+getColunaCasa(casa)];
            setCasaOcupada(cas, valor, id_navio, getNavioTamanho(nav)-i);
          }
        } else {
          for(let i = 0; i < getNavioTamanho(nav); i++){
            let cas = casas['casa'+(getLinhaCasa(casa)-i)+'x'+getColunaCasa(casa)];
            setCasaOcupada(cas, valor, id_navio, null);
          }
        }
      };

      function setRollCasasDireita(nav, casa, casas, valor, id_navio){
        if (valor) {
          for(let i = 0; i < getNavioTamanho(nav); i++){
            let cas = casas['casa'+getLinhaCasa(casa)+'x'+(getColunaCasa(casa)+i)];
            setCasaOcupada(cas, valor, id_navio, (i+1));
          }
        } else {
          for(let i = 0; i < getNavioTamanho(nav); i++){
            let cas = casas['casa'+getLinhaCasa(casa)+'x'+(getColunaCasa(casa)+i)];
            setCasaOcupada(cas, valor, id_navio, null);
          }
        }
      };

      function setRollCasas(angulo, nav, casa, casas, valor, id_navio){
          if(angulo == anguloDeRotacao){
            setRollCasasAcima(nav, casa, casas, valor, id_navio);
          }else{
            setRollCasasDireita(nav, casa, casas, valor, id_navio);
          }
      };

      function foraDaTela(navio){
        if(navio.x() > telaLargura-50 || navio.x() < 0 || navio.y() > telaAltura-50 || navio.y() < 0){
          return true;
        }else{
          return false;
        }
      };

      function verificarAcima(nav, casa, casas){
          if(getLinhaCasa(casa)-getNavioTamanho(nav) >= 0){
            for(let i = 0; i < getNavioTamanho(nav); i++){
                let cas = casas['casa'+(getLinhaCasa(casa)-i)+'x'+getColunaCasa(casa)];
                if(cas.ocupada){
                    return false;
                }
            }
            return  true;
          }else{
              return false;
          }
      };

      function verificarDireita(nav, casa, casas){
          if(getColunaCasa(casa)+getNavioTamanho(nav)-1 <= 10){
            for(let i = 0; i < getNavioTamanho(nav); i++){
                let cas = casas['casa'+getLinhaCasa(casa)+'x'+(getColunaCasa(casa)+i)];
                if(cas.ocupada){
                    return false;
                }
            }
            return  true;
          }else{
              return false;
          }
      };

      function espacoSuficiente(angulo, nav, casa, casas){
          if(angulo == anguloDeRotacao){
            return verificarAcima(nav, casa, casas);
          }else{
            return verificarDireita(nav, casa, casas);
          }
      };

      function limparRollCasas(angulo, navio, nav, casas){
          let resultado = getCasasProximo(angulo, navio, casas);
          if (!resultado.length == 0){
            let casa = casas[resultado[0]];
            if(angulo == anguloDeRotacao && getLinhaCasa(casa)-getNavioTamanho(nav) >= 0){
                setRollCasas(angulo, nav, casa, casas, false, 'id_navio');
            }else{
                if(angulo == 0 && getColunaCasa(casa)+getNavioTamanho(nav)-1 <= 10){
                    setRollCasas(angulo, nav, casa, casas, false, 'id_navio');
                }
            }
        }

      };

      function verificarTodosPosicionados(navios){
          for (let key in navios){
            let nav = navios[key];
            if(!nav.posicionado){
                return false;
            }
          }
          return true;
      };

      function setNaviosCasas(){
        if(verificarTodosPosicionados(navios)){
          for(let key in casas){
            let cas = casas[key];
            if(cas.ocupada){
              document.getElementById('casa_'+cas.id).children[1].value = cas.navio;
              document.getElementById('casa_'+cas.id).children[2].value = cas.posicao;
            }
          };
          document.getElementById('salvar-navios-form').submit();
        }else{
          alert('Posicione todos os navios nas casas');
        }
      };



      function voltarPosicaoInicial(navio, nav){
          navio.position({
              x: nav.x,
              y: nav.y,
        });
        navio.rotation(0);
      }

      function initNaviosCasas(){
        for(let i = 1; i <= tamanhoTabuleiro; i++){ //criacao das casas
          for(let j = 1; j <= tamanhoTabuleiro; j++){
            casas['casa'+j+'x'+i] = {x: espacoEntreCasas*i, y: espacoEntreCasas*j, linha: j, coluna: i, ocupada: false, navio: 'id_navio', id: document.getElementById('casa'+j+'x'+i).value, posicao: null}; //cria as casas dando espaco e nome unico
          }
        };
        for(let i = 1; i <= quantidadeNavios; i++){
            navios['navio'+i] = {x: espacoEntreCasas*(tamanhoTabuleiro+1), y: (espacoEntreCasas*(i+1)), posicionado: false, tamanho: parseInt(document.getElementById('tamanho_navio'+i).value), id: document.getElementById('navio'+i).value};
        };
      };

      function initStageCasas(images){
        var casaLayer = new Konva.Layer();
        for (let key in casas) {//iterar sobre os objects casas pra adicionar a imagem relacionada e a posicao
          (function () {
            let imageObj = images[key];
            let cas = casas[key];

            let casa = new Konva.Image({
              image: imageObj,
              x: cas.x,
              y: cas.y,
            });

            casaLayer.add(casa);
          })();
        }
        stage.add(casaLayer);
      };

      function initStageNavios(images) {//inicializa as imagens
        var navioLayer = new Konva.Layer();
        for (let key in navios) {//faz o mesmo pros navios, itera sobre eles e cria o objeto do tipo Image do Konva pra colocar o navio
          (function () {
            var privKey = key;
            let nav = navios[key];

            let navio = new Konva.Image({
              image: images[key],
              x: nav.x,
              y: nav.y,
              draggable: true,
              name: 'navio',
              id: ''+key,
            });

            navio.on('dragstart', function () {
              this.moveToTop();
              if(nav.posicionado){
                limparRollCasas(navio.rotation(), navio, nav, casas);
                setNavioPosicionado(nav, false);
              }
            });

            navio.on('dragend', function () { //função pra quando arrastar, fazer o encaixe certinho
                let resultado = getCasasProximo(navio.rotation(), navio, casas);
                if (!resultado.length == 0){
                    let casa = casas[resultado[0]];
                    if(espacoSuficiente(navio.rotation(), nav, casa, casas)){
                        setRollCasas(navio.rotation(), nav, casa, casas, true, nav.id);
                        if (!navio.inRightPlace) {
                            setNavioPosicionado(nav, true);
                            if(navio.rotation() == 0){//essa variação aqui é por causa que depende se o navio ta em uma posicao diferente
                                navio.position({
                                    x: casa.x+(5),
                                    y: casa.y+(5),
                                });
                                navio.image(images[privKey+'glow']);
                                somPopPlace.play();
                            }else{
                                navio.position({
                                    x: casa.x+(5),
                                    y: casa.y+espacoDeEncaixe+15,
                                });
                                navio.image(images[privKey+'glow']);
                                somPopPlace.play();
                            }
                        }else{
                            if(getNavioPosicionado(nav)){
                                setNavioPosicionado(nav, false);
                                navio.image(images[privKey]);
                            }
                        }
                    }else{
                        voltarPosicaoInicial(navio, nav);
                        navio.image(images[privKey]);
                    }
                }else{
                    navio.image(images[privKey]);
                    if(getNavioPosicionado(nav)){
                        setNavioPosicionado(nav, false);
                    }
                    if (foraDaTela(navio)){
                      voltarPosicaoInicial(navio, nav);
                    }
                }
            });
            navio.on('mouseover', function () {
              document.body.style.cursor = 'pointer';
            });

            navio.on('mouseout', function () {
              document.body.style.cursor = 'default';
            });

            navio.on('dragmove', function () {
              somPop.play();
              document.body.style.cursor = 'pointer';
            });

            navio.on('click', function() {//clicar nele gira o bagulho :)
                setNavioPosicionado(nav, false);
                limparRollCasas(navio.rotation(), navio, nav, casas);
                if(navio.rotation() == anguloDeRotacao){
                    navio.rotation(0)
                    navio.image(images[privKey]);
                    navioLayer.draw();
                }else{
                    navio.rotation(anguloDeRotacao);
                    navio.image(images[privKey]);
                    navioLayer.draw();
                };
            });

            navioLayer.add(navio);
          })();
        }

        stage.add(navioLayer);
      }

      var naviosSources = {//source de onde fica os navios
        navio1: '{{secure_asset('img/navios/portaaviao.png')}}',
        navio2: '{{secure_asset('img/navios/guerra.png')}}',
        navio3: '{{secure_asset('img/navios/encouracado.png')}}',
        navio4: '{{secure_asset('img/navios/encouracado.png')}}',
        navio5: '{{secure_asset('img/navios/submarino.png')}}',
        navio1glow: '{{secure_asset('img/navios/portaaviaoglow.png')}}',
        navio2glow: '{{secure_asset('img/navios/guerraglow.png')}}',
        navio3glow: '{{secure_asset('img/navios/encouracadoglow.png')}}',
        navio4glow: '{{secure_asset('img/navios/encouracadoglow.png')}}',
        navio5glow: '{{secure_asset('img/navios/submarinoglow.png')}}',
      };

      var casasSources = {};

      for(let i = 1; i <= tamanhoTabuleiro; i++){//cria um source pra cada casa e coloca em sources
          for(let j = 1; j <= tamanhoTabuleiro; j++){
            casasSources['casa'+i+'x'+j] = '{{secure_asset('img/cell_board.png')}}';
          }
      };

      document.getElementById('resetarNavios').addEventListener(
        'click',
        function () {
            let imagesNavios = stage.find('.navio');
            for(let key in imagesNavios){
                let navio = imagesNavios[key];
                let nav = navios[navio.id()];
                limparRollCasas(navio.rotation(), navio, nav, casas);
                setNavioPosicionado(nav, false);
                navio.image(images[navio.id()]);
                voltarPosicaoInicial(navio, nav);
            }
        },
        false
      );

      initNaviosCasas();
      loadImages(casasSources, initStageCasas);//carrega o stage pra iniciar os bagulhos
      loadImages(naviosSources, initStageNavios);//carrega o stage pra iniciar os bagulhos

    </script>
@endsection

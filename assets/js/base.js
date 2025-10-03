

const loadHeaderSp = () =>{

let spgov = document.getElementById('section')
spgov.innerHTML='  <section class="govsp-topo">'+
    '<link rel="stylesheet" type="text/css" href="https://saopaulo.sp.gov.br/barra-govsp/css/topo-padrao-govsp.min.css">'+
    '<link rel="stylesheet" type="text/css" href="https://saopaulo.sp.gov.br/barra-govsp/css/barra-contraste-govsp.min.css">  '+
        '<div id="govsp-topbarGlobal" class="blu-e">'+
                '<div id="topbarGlobal">'+
                    '<div id="topbarLink" class="govsp-black">'+
                    '<div class="govsp-portal">'+
                        '<a href="https://www.saopaulo.sp.gov.br/" target="_blank">'+
                            '<img decoding="async" src="https://saopaulo.sp.gov.br/barra-govsp/img/logo-governo-do-estado-sp.png" alt="Governo do Estado de São Paulo" height="38" class="logo">'+
                        '</a>'+
                    '</div> '+
                '</div>'+
                '<nav class="govsp-navbar govsp-navbar-expand-lg">'+
                    
                        '<a class="govsp-link digital" href="https://spmaisdigital.sp.gov.br" target="_blank">SP + Digital</a>'+
    
                        '<a class="govsp-social" href="https://www.flickr.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-flickr.png" alt="Flickr Governo de São Paulo"></a>'+
                                           
                        '<a class="govsp-social" href="https://www.linkedin.com/company/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-linkedin.png" alt="Linkedin Governo de São Paulo"></a>'+
    
                        '<a class="govsp-social" href="https://www.tiktok.com/@governosp" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-tiktok.png" alt="TikTok Governo de São Paulo"></a>'+
                        
                        '<a class="govsp-social" href="https://www.youtube.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-youtube.png" alt="Youtube Governo de São Paulo"></a>'+
                        
                        '<a class="govsp-social" href="https://www.twitter.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-twitter.png" alt="Facebook Governo de São Paulo"></a>'+
    
                        '<a class="govsp-social" href="https://www.instagram.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-insta.png" alt="Instagram Governo de São Paulo"></a>     '+
                                    
                        '<a class="govsp-social" href="https://www.facebook.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-facebook.png" alt="Facebook Governo de São Paulo"></a>'+
                        
                        '<p class="govsp-social">/governosp</p>'+
                        '<div id="separador-nav"></div>'+
                        '<a class="govsp-acessibilidade" href="javascript:mudaTamanho("body", 1);"><img decoding="async" class="govsp-acessibilidade" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-big-font.png" alt="Aumentar Fonte"></a>'+
                        '<a class="govsp-acessibilidade" href="javascript:mudaTamanho("body", -1);"><img decoding="async" class="govsp-acessibilidade" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-small-font.png" alt="Diminuir Fonte"></a>'+
                      
                        '<a class="govsp-acessibilidade" href="#" id="altocontraste" accesskey="3" onclick="window.toggleContrast()" onkeydown="window.toggleContrast()"><img decoding="async" class="govsp-acessibilidade" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-contrast.png" alt="Contraste"></a>'+
                        '<a class="govsp-acessibilidade" href="https://www.saopaulo.sp.gov.br/fale-conosco/comunicar-erros/" title="Comunicar Erros" target="_blank"><img decoding="async" class="govsp-acessibilidade" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-error-report.png"></a>'+
                        
                '</nav>'+
            '</div>'+
            '<div class="govsp-kebab">'+
                    '<figure></figure>'+
                    '<figure class="govsp-middle"></figure>'+
                    '<p class="govsp-cross"></p>'+
                    '<figure></figure>'+
                    '<ul class="govsp-dropdown" id="govsp-kebab">               '+
                        '<li><a class="govsp-link digital" href="https://spmaisdigital.sp.gov.br" target="_blank">SP + Digital</a>'+

                        '</li><li><a class="govsp-social" href="https://www.flickr.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-flickr.png" alt="Flickr Governo de São Paulo"></a></li>'+
                        
                        '<li><a class="govsp-social" href="https://www.linkedin.com/company/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-linkedin.png" alt="Linkedin Governo de São Paulo"></a></li>'+

                        '<li><a class="govsp-social" href="https://www.tiktok.com/@governosp" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-tiktok.png" alt="TikTok Governo de São Paulo"></a></li>'+

                        '<li><a class="govsp-social" href="https://www.twitter.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-twitter.png" alt="Twitter Governo de São Paulo"></a></li>'+

                        '<li><a class="govsp-social" href="https://www.youtube.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-youtube.png" alt="Youtube Governo de São Paulo"></a></li>                      '+
                       
                        '<li><a class="govsp-social" href="https://www.instagram.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-insta.png" alt="Instagram Governo de São Paulo"></a></li>'+
                      
                        '<li><a class="govsp-social" href="https://www.facebook.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-facebook.png" alt="Facebook Governo de São Paulo"></a></li>'+
                        
                        '<li><p class="govsp-social">/governosp</p></li>'+
                    '</ul> '+
            '</div>'+
        '</div>'+
 
    '</section>';

}

const loadHeader = () =>{

    let el = document.getElementById('header')

    el.innerHTML =
    '<div class="container d-flex align-items-center justify-content-between">'+

      // '<div class="logo">'+
      //   '<a href="index.html"><img src="http://www.daee.sp.gov.br/site/wp-content/uploads/2023/05/LogoSite.png" style="width="210px; height="60px"; class="logo"></a>'+
      // '</div>'+

        '<div class="logo">'+
        '<a href="index.html"><img src="	http://www.daee.sp.gov.br/site/wp-content/uploads/2024/09/SP-4.png" style="width="auto; height="503px"; class="logo"></a>'+
      '</div>'+

      '<nav id="navbar" class="navbar ">'+
        '<ul>'+
          '<li><a class="active" href="index.html">Home</a></li>'+
          // '<li><a href="http://hidroapp.daee.sp.gov.br/" target="_blank">Indicadores</a></li>'+
          // '<li><a href="noticias.html">Notícias</a></li>'+
          '<li><a href="sobre.html">Visitas</a></li>'+
          '<li><a href="salas-de-situacao.html">Salas de Situação</a></li>'+
          
          // '<li class="dropdown"><a href="#"><span>Sala de Situação</span> <i class="bi bi-chevron-down"></i></a>'+

          //   '<ul>'+
          //     '<li><a href="sobre.html">Sobre</a></li>'+
          //     '<li><a href="salas-de-situacao.html">Salas de Situação</a></li>'+
              // '<li><a href="monitoramento.html">Monitoramento</a></li>'+
              // '<li><a href="equipe.html">Equipe</a></li>'+
              // '<li><a href="galeria.html"> Galeria</a></li>'+
          //   '</ul>'+
          // '</li>'+

          '<li class="dropdown"><a href="#"><span>Boletins</span> <i class="bi bi-chevron-down"></i></a>'+
            '<ul>'+
            // '<li><a href="boletim.html">Boletim</a></li>'+

              '<li><a href="boletinsDiario.html">Boletim Diário</a></li>'+
              // '<li><a href="https://drive.google.com/drive/folders/0B4yicqLa_Dj8YTE5ZDUyNTItMjkzYS00ZGJlLTg2M2ItZTI0ZjRjODQ3ZDNk?resourcekey=0-n6Kjkz-jNDVJdgI1dJ1A1A" target="_black">Boletim Diário</a></li>'+

              // '<li><a href="https://drive.google.com/drive/folders/0B4yicqLa_Dj8VFFVb3B4bW5xZkk?resourcekey=0-9rWDTUK16xaKOQnDIPFlnw" target="_black">Boletim Mensal</a></li>'+
              '<li><a href="boletinsMensais.html">Boletim Mensais</a></li>'+
              // '<li><a href="https://docs.google.com/document/d/11qxQPqNc1RW7mMYw_uXdw-Gmh7lr8UUbaH2AnG6k6g0/edit#heading=h.7vzq6ltz512f" target="_black">Último Boletim</a></li>'+
            '</ul>'+
          '</li>'+
   
          '<li><a href="hidrologia.html"><span>Hidrologia</span></a>'+
          '</li>'+
          '<li><a href="contato.html"> Contato</a></li>'+
          '<li class="logo"><a href="https://sp.gov.br/sp" target=_blank> <img src="https://sp.gov.br/statics/img/logo-gov.png"></a></li>'+
        '</ul>'+
        '<i class="bi bi-list mobile-nav-toggle"></i>'+
      '</nav>'+

    '</div>';

}

const loadFooter = () =>{
    let rodape = document.getElementById('footer')

    rodape.innerHTML = 

    '<div class="footer-top">'+
    '<div class="container">'+
      '<div class="row">'+

        '<div class="col-lg-4 footer-info">'+
        '<a href="index.html"><img src="https://www.spaguas.sp.gov.br/site/wp-content/uploads/2024/09/SP-4.png" style="width="260px; height="90px;" class="logo"></a>'+'<br/><br/>'+
        '<h2><STRONG>Sala de Situação do Estado de São Paulo</STRONG></h2>'+ 
        '</div>'+

        '<div class="col-lg-2 footer-links">'+
          '<h4>Boletins</h4>'+
          '<ul>'+
            '<li><a href="boletinsDiario.html" target=_blank>Boletins Diários</a></li>'+
            '<li><a href="boletinsMensais.html" target=_blank>Boletins Mensais</a></li>'+
            
          '</ul>'+
           '<h4>-</h4>'+
           '<ul>'+
            '<li><a href="sobre.html">Visitas</a></li>'+
            '<li><a href="hidrologia.html">Hidrologia</a></li>'+
            '<li><a href="salas-de-situacao.html">Salas de Situação</a></li>'+
            // '<li><a href="noticias.html">Notícias</a></li>'+
            // '<li><a href="equipe.php">Equipes</a></li>'+
          '</ul>'+
        '</div>'+
        '<div class="col-lg-3 footer-links">'+
        '<h4> veja mais</h4>'+
        '<ul>'+
          '<li><a href="https://cth.daee.sp.gov.br/sibh/chuva_agora" target=_blank>Chuva Agora</a></li>'+
          '<li><a href="https://hidroapp.spaguas.sp.gov.br/mapa" target=_blank>HidroApp</a></li>'+
          '<li><a href="http://ph.daee.sp.gov.br/" target=_blank>Portal Hidrologia</a></li>'+
          // '<li><a href="estacoes.html">Estações</a></li>'+
        
        '</ul>'+
          '<h4>Links Úteis</h4>'+
          '<ul>'+
            '<li><a href="https://www.defesacivil.sp.gov.br/" target=_blank>Defesa Civil</a></li>'+
            '<li><a href="https://www.gov.br/cemaden/pt-br/assuntos/monitoramento" target=_blank>CEMADEN</a></li>'+
            '<li><a href="https://terrabrasilis.dpi.inpe.br/queimadas/portal/" target=_blank>Portal Queimadas</a></li>'+
            '<li><a href="https://tempo.cptec.inpe.br/" target=_blank>CPTEC/INPE</a></li>'+
          '</ul>'+
          
        '</div>'+

        '<div class="col-lg-3 col-md-6 footer-contact">'+
          '<h4>Contato</h4>'+
          '<p>'+
            'Rua Boa Vista, 170 <br>'+
            '11º andar, Bloco 5<br>'+
           'São Paulo / SP <br>'+
            '<strong>Telefone:</strong> (11) 3293 - 8461<br>'+
            '<strong>Email:</strong> salasituacaosp@spaguas.sp.gov.br<br>'+
          '</p>'+

          '<div class="social-links"> <p>Redes Sociais SP-Águas</p>'+
            '<a href="https://www.facebook.com/spaguas/" target="_black" class="facebook"><i class="bi bi-facebook"></i></a>'+
            '<a href="https://www.instagram.com/spaguas/" target="_black" class="instagram"><i class="bi bi-instagram"></i></a>'+
            

            '<a href="https://www.youtube.com/channel/UCOEBfJpTq7LE25rdjX9tdUQ" target="_black" class=""><i class="bi bi-youtube"></i></a>'+
          '</div>'+

        '</div>'+

      '</div>'+
    '</div>'+
  '</div>'+

  
    '<div class="copyright">'+
      '&copy;  <strong><span>SSSP/ SP Águas</span></strong>. '+
    '</div>'+
    '<div class="credits">'+

  '</div>'+

  '<section id="govsp-rodape">'+

  '<link rel="stylesheet" type="text/css" href="https://www.saopaulo.sp.gov.br/barra-govsp/css/rodape-padrao-govsp.min.css">'+

  '<div class="container">'+
   '<div class="linha-botoes">'+
    '<div class="coluna-4">'+
          '<a href="https://www.ouvidoria.sp.gov.br/Portal/Default.aspx" target="_blank" class="btn btn-model">Ouvidoria</a>'+
        '</div> '+

        '<div class="coluna-4">'+
          '<a href="http://www.transparencia.sp.gov.br/" target="_blank" class="btn btn-model">Transparência</a>'+
        '</div>'+ 

        '<div class="coluna-4">'+
          '<a href="http://www.sic.sp.gov.br/" target="_blank" class="btn btn-model">SIC</a>'+
        '</div>'+ 
    '</div>'+
    '</div>'+

    '<div class="container rodape">'+    
      '<div class="logo-rodape">'+
        '<a href="https://www.saopaulo.sp.gov.br/" target="_blank">'+
        '<img decoding="async" loading="lazy" src="https://www.saopaulo.sp.gov.br/barra-govsp/img/logo-rodape-governo-do-estado-sp.png" alt="Governo do Estado de SP" width="206" height="38"></a>'+
      '</div>'+

    '</div>'+
'</section>'


}

loadHeader()
loadFooter()
loadHeaderSp()

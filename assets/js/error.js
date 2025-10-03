
// const loadHeaderSp = () =>{

//     let spgov = document.getElementById('header')
//     spgov.innerHTML='  <section class="govsp-topo">'+
//         '<link rel="stylesheet" type="text/css" href="https://saopaulo.sp.gov.br/barra-govsp/css/topo-padrao-govsp.min.css">'+
//         '<link rel="stylesheet" type="text/css" href="https://saopaulo.sp.gov.br/barra-govsp/css/barra-contraste-govsp.min.css">  '+
//             '<div id="govsp-topbarGlobal" class="blu-e">'+
//                     '<div id="topbarGlobal">'+
//                         '<div id="topbarLink" class="govsp-black">'+
//                         '<div class="govsp-portal">'+
//                             '<a href="https://www.saopaulo.sp.gov.br/" target="_blank">'+
//                                 '<img decoding="async" src="https://saopaulo.sp.gov.br/barra-govsp/img/logo-governo-do-estado-sp.png" alt="Governo do Estado de São Paulo" height="38" class="logo">'+
//                             '</a>'+
//                         '</div> '+
//                     '</div>'+
//                     '<nav class="govsp-navbar govsp-navbar-expand-lg">'+
                        
//                             '<a class="govsp-link digital" href="https://spmaisdigital.sp.gov.br" target="_blank">SP + Digital</a>'+
        
//                             '<a class="govsp-social" href="https://www.flickr.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-flickr.png" alt="Flickr Governo de São Paulo"></a>'+
                                               
//                             '<a class="govsp-social" href="https://www.linkedin.com/company/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-linkedin.png" alt="Linkedin Governo de São Paulo"></a>'+
        
//                             '<a class="govsp-social" href="https://www.tiktok.com/@governosp" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-tiktok.png" alt="TikTok Governo de São Paulo"></a>'+
                            
//                             '<a class="govsp-social" href="https://www.youtube.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-youtube.png" alt="Youtube Governo de São Paulo"></a>'+
                            
//                             '<a class="govsp-social" href="https://www.twitter.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-twitter.png" alt="Facebook Governo de São Paulo"></a>'+
        
//                             '<a class="govsp-social" href="https://www.instagram.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-insta.png" alt="Instagram Governo de São Paulo"></a>     '+
                                        
//                             '<a class="govsp-social" href="https://www.facebook.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-facebook.png" alt="Facebook Governo de São Paulo"></a>'+
                            
//                             '<p class="govsp-social">/governosp</p>'+
//                             '<div id="separador-nav"></div>'+
//                             '<a class="govsp-acessibilidade" href="javascript:mudaTamanho("body", 1);"><img decoding="async" class="govsp-acessibilidade" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-big-font.png" alt="Aumentar Fonte"></a>'+
//                             '<a class="govsp-acessibilidade" href="javascript:mudaTamanho("body", -1);"><img decoding="async" class="govsp-acessibilidade" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-small-font.png" alt="Diminuir Fonte"></a>'+
                          
//                             '<a class="govsp-acessibilidade" href="#" id="altocontraste" accesskey="3" onclick="window.toggleContrast()" onkeydown="window.toggleContrast()"><img decoding="async" class="govsp-acessibilidade" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-contrast.png" alt="Contraste"></a>'+
//                             '<a class="govsp-acessibilidade" href="https://www.saopaulo.sp.gov.br/fale-conosco/comunicar-erros/" title="Comunicar Erros" target="_blank"><img decoding="async" class="govsp-acessibilidade" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-error-report.png"></a>'+
                            
//                     '</nav>'+
//                 '</div>'+
//                 '<div class="govsp-kebab">'+
//                         '<figure></figure>'+
//                         '<figure class="govsp-middle"></figure>'+
//                         '<p class="govsp-cross"></p>'+
//                         '<figure></figure>'+
//                         '<ul class="govsp-dropdown" id="govsp-kebab">               '+
//                             '<li><a class="govsp-link digital" href="https://spmaisdigital.sp.gov.br" target="_blank">SP + Digital</a>'+
    
//                             '</li><li><a class="govsp-social" href="https://www.flickr.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-flickr.png" alt="Flickr Governo de São Paulo"></a></li>'+
                            
//                             '<li><a class="govsp-social" href="https://www.linkedin.com/company/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-linkedin.png" alt="Linkedin Governo de São Paulo"></a></li>'+
    
//                             '<li><a class="govsp-social" href="https://www.tiktok.com/@governosp" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-tiktok.png" alt="TikTok Governo de São Paulo"></a></li>'+
    
//                             '<li><a class="govsp-social" href="https://www.twitter.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-twitter.png" alt="Twitter Governo de São Paulo"></a></li>'+
    
//                             '<li><a class="govsp-social" href="https://www.youtube.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-youtube.png" alt="Youtube Governo de São Paulo"></a></li>                      '+
                           
//                             '<li><a class="govsp-social" href="https://www.instagram.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-insta.png" alt="Instagram Governo de São Paulo"></a></li>'+
                          
//                             '<li><a class="govsp-social" href="https://www.facebook.com/governosp/" target="_blank"><img decoding="async" class="govsp-icon-social" src="https://saopaulo.sp.gov.br/barra-govsp/img/i-facebook.png" alt="Facebook Governo de São Paulo"></a></li>'+
                            
//                             '<li><p class="govsp-social">/governosp</p></li>'+
//                         '</ul> '+
//                 '</div>'+
//             '</div>'+
     
//         '</section>';
    
//     }

//     loadHeaderSp()



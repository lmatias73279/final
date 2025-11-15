    <head>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    </head>
    <style>
        /* Aseg煤rate de que se aplique la fuente Roboto a todo el contenido del encabezado */
        .header-area .main-nav .nav li a,
        .header-area .main-nav .logo a,
        .header-area .main-nav a.menu-trigger {
            font-family: 'Roboto', sans-serif;
        }

        .header-area .main-nav .nav a {
            font-family: 'Roboto', sans-serif;
        }
    </style>
    <!-- ***** Header Area Start ***** -->
  <header class="header-area header-sticky">
      <div class="container">
          <div class="row">
              <div class="col-12">
                  <nav class="main-nav">  
                    <style>

                        .whatsapp-button {
                          position: fixed;
                          bottom: 20px; /* Ajusta según necesites */
                          right: 20px; /* Ajusta según necesites */
                          z-index: 9999;
                          animation: whatsapp-vibrate 4s infinite alternate;
                        }
                    
                        @keyframes whatsapp-vibrate {
                          0% { transform: translate(0, 0); }
                          2% { transform: translate(3px, 3px); }
                          4% { transform: translate(-3px, -3px); }
                          6% { transform: translate(3px, -3px); }
                          8% { transform: translate(-3px, 3px); }
                          10% { transform: translate(0, 0); }
                          100% { transform: translate(0, 0); }
                        }
                    
                        .whatsapp-button img {
                          width: 70px; /* Ajusta el tamaño del icono según necesites */
                          height: auto;
                        }
                        
                        .message-indicator {
                          position: absolute;
                          top: 5px;
                          right: 5px;
                          background-color: red;
                          color: white;
                          border-radius: 50%;
                          width: 20px;
                          height: 20px;
                          display: flex;
                          justify-content: center;
                          align-items: center;
                          font-size: 10px;
                        }
                        
                        .modal-backdrop {
                        background-color: transparent !important; /* Elimina la sombra visible */
                      }
                    
                    
                    </style>

                
                    <a href="https://api.whatsapp.com/send?phone=573214193875" class="whatsapp-button" target="_blank">
                      <img src="assets/images/whatsapp.png" alt="WhatsApp">
                      <div class="message-indicator">1</div>
                    </a>
                    
                      <!-- ***** Logo Start ***** -->
                      <a href="index" class="logo">
                        <div style="width: 130px; overflow: hidden;">
                            <img src="assets/images/icosm.png" alt="EduWell Template" style="width: 100%; height: auto;">
                        </div>
                      </a>                    
                      <!-- ***** Logo End ***** -->
                      <!-- ***** Menu Start ***** -->
                      <ul class="nav">
                          <li><a href="index">Inicio</a></li>
                          <li><a href="index#services">Servicios</a></li>
                          <li><a href="nosotros">Nosotros</a></li>
                          <li><a href="empresas">Empresas</a></li>
                          <li><a href="contacto">Contacto</a></li>
                          <li><a href="blog">Blog</a></li>
                          <a href="t_zone/dist" class="boton-brincador">
                            Iniciar Sesi贸n
                          </a>
                          
                          <style>
                          .boton-brincador {
                            background-color: rgb(0, 170, 255);
                            color: white;
                            padding: 10px 20px;
                            border: none;
                            border-radius: 5px;
                            font-size: 16px;
                            cursor: pointer;
                            text-decoration: none;
                            display: inline-block;
                            transition: all 0.3s ease;
                          }
                          
                          .boton-brincador:hover {
                            animation: brincar 0.5s ease;
                          }
                        
                          @keyframes brincar {
                            0%   { transform: translateY(0); }
                            30%  { transform: translateY(-10px); }
                            50%  { transform: translateY(0); }
                            70%  { transform: translateY(-6px); }
                            100% { transform: translateY(0); }
                          }
                          </style>

                      </ul>        
                      <a class='menu-trigger'>
                          <span>Menu</span>
                      </a>
                      <!-- ***** Menu End ***** -->
                  </nav>
              </div>
          </div>
      </div>
  </header>
  <!-- ***** Header Area End ***** -->
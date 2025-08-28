<?php include_once(__DIR__ . '/cookie.php'); ?>
<div class=nav_superior>
    <div class="contenedor_superior">
        <div class="contenedor_logo">
            <!-- <img class="logo" src="img/logo.svg" alt=""> -->
            <svg class="logo" viewBox="0 0 70 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path class="svg_color" d="M37.2551 1.61586C38.1803 0.653384 39.4368 0.112671 40.7452 0.112671C46.6318 0.112671 52.1793 0.112674 57.6424 0.112685C68.6302 0.112708 74.1324 13.9329 66.3629 22.0156L49.4389 39.6217C48.662 40.43 47.3335 39.8575 47.3335 38.7144V23.2076L49.2893 21.1729C50.8432 19.5564 49.7427 16.7923 47.5451 16.7923H22.6667L37.2551 1.61586Z"></path>
                <path class="svg_color" d="M32.7449 38.3842C31.8198 39.3467 30.5633 39.8874 29.2549 39.8874C23.3683 39.8874 17.8208 39.8874 12.3577 39.8874C1.36983 39.8873 -4.13236 26.0672 3.63721 17.9844L20.5612 0.378369C21.3381 -0.429908 22.6666 0.142547 22.6666 1.28562L22.6667 16.7923L20.7108 18.8271C19.1569 20.4437 20.2574 23.2077 22.455 23.2077L47.3335 23.2076L32.7449 38.3842Z"></path>
            </svg>
            <h2 class="nombre_negocio">Nombre Del Negocio</h2>
        </div>
        <div class="contenedor_usuario">
            <div class="botones_usuario">
                <a type="button" href="/Proyecto_Plantilla/public/principal" class="boton_usuario" id="casa" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Página Principal"><i class="fi-sr-house-chimney"></i></a>
                <a type="button" class="boton_usuario" id="ayuda" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ayuda"><i class="fi-sr-interrogation"></i></a>
                <a type="button" class="boton_usuario" id="modo_oscuro" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Tema de Color"><i class="fi <?php echo $class; ?>"></i></a>
                <a type="button" class="boton_usuario" id="noti" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Notificaciones"><i class="fi fi-sr-bell"></i></a>
                <div class="contenedor_notificaciones ocultar" id="contenedor_notificaciones">
                    <ul class="lista_noti">
                        <li class="titulo_noti">
                            <h1>Notificaciones</h1>
                        </li>
                        <li class="noti">
                            <i class="fi fi-sr-bell"></i>
                            <div class="info_noti">
                                <h2>Admin Notificaciones</h2>
                                <h3>Lorem ipsum dolor sit amet.</h3>
                            </div>
                            <span>6:32 pm</span>
                        </li>
                        <li class="noti">
                            <i class="fi fi-sr-bell"></i>
                            <div class="info_noti">
                                <h2>Admin Notificaciones</h2>
                                <h3>Lorem ipsum dolor sit amet.</h3>
                            </div>
                            <span>6:32 pm</span>
                        </li>
                        <li class="noti">
                            <i class="fi fi-sr-bell"></i>
                            <div class="info_noti">
                                <h2>Admin Notificaciones</h2>
                                <h3>Lorem ipsum dolor sit amet.</h3>
                            </div>
                            <span>6:32 pm</span>
                        </li>
                        <li class="noti">
                            <i class="fi fi-sr-bell"></i>
                            <div class="info_noti">
                                <h2>Admin Notificaciones</h2>
                                <h3>Lorem ipsum dolor sit amet.</h3>
                            </div>
                            <span>6:32 pm</span>
                        </li>
                        <li class="noti">
                            <i class="fi fi-sr-bell"></i>
                            <div class="info_noti">
                                <h2>Admin Notificaciones</h2>
                                <h3>Lorem ipsum dolor sit amet.</h3>
                            </div>
                            <span>6:32 pm</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="info_usuario" id="info_usuario">
                <i class="fi fi-sr-circle-user img_usuario"></i>
                <div class="contenedor_nombre">
                    <h3 class="nombre_usuario"><?php echo $_SESSION['nombre'] . ' ' . $_SESSION['apellido']; ?></h3>
                    <h4 class="tipo_usuario"><?php echo $_SESSION['rol']; ?></h4>
                </div>
                <i class="fi fi-br-angle-down flecha_usuario" id="flecha"></i>
            </div>
            <div class="menu_superior ocultar" id="menu_superior">
                <ul class="nav_contenedor_superior">
                    <li class="nav_opciones_superior">
                        <a type="button" id="salir" class="opciones_superior"><i class="fi-sr-sign-out-alt"></i> Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
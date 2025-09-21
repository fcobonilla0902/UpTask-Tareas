<?php

namespace Controllers;
use MVC\Router;
use Model\Proyecto;
use Model\Usuario;

class DashboardController {
    public static function index(Router $router){

        session_start();
        isAuth();

        $id = $_SESSION['id'];
        $proyectos = Proyecto::belongsTo('usuarioId', $id);

        $router->render('dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }


    public static function crear_proyecto(Router $router){
        session_start();

        isAuth();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $proyecto = new Proyecto($_POST);
            
            // validación
            $alertas = $proyecto->validarProyecto();

            if(empty($alertas)){
                // generar url única
                $hash = md5(uniqid());
                $proyecto->url = $hash;
                
                // almacenar el usuario
                $proyecto->usuarioId = $_SESSION['id'];

                // guardar el proyecto
                $proyecto->guardar();

                // redireccionar
                header('Location: /proyecto?id=' . $proyecto->url);
            }
        }

        $router->render('dashboard/crear-proyecto', [
            'titulo' => 'Crear Proyecto',
            'alertas' => $alertas
        ]);
    }


    public static function proyecto(Router $router){
        session_start();
        isAuth();

        $token = $_GET['id'];

        if(!$token) header('Location: /dashboard');

        // revisar que la persona que visita el proyecto, es quien lo creo
        $proyecto = Proyecto::where('url', $token);
        if($proyecto->usuarioId !== $_SESSION['id']){
            header('Location: /dashboard');
        }

        $router->render('dashboard/proyecto', [
            'titulo' => $proyecto->proyecto
        ]);
    }


    public static function perfil(Router $router){
        session_start();
        isAuth();
        $alertas = [];

        $usuario = Usuario::find($_SESSION['id']);

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario->sincronizar($_POST);

            $alertas = $usuario->validar_perfil();

            if(empty($alertas)){
                // revisar si existe el usuario
                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario && $existeUsuario->id !== $usuario->id){ // el usuario ya existe
                    Usuario::setAlerta('error', 'Email no válido');
                    $alertas = $usuario->getAlertas();
                } else { // guardar
                    //guardar el usuario
                    $usuario->guardar();

                    Usuario::setAlerta('exito', 'Guardado Correctamente');
                    $alertas = $usuario->getAlertas();

                    // asignar nombre nuevo a la barra
                    $_SESSION['nombre'] = $usuario->nombre;
                }
            }
        }

        $router->render('dashboard/perfil', [
            'titulo' => 'Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }


    public static function cambiar_password(Router $router){
        session_start();
        isAuth();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario = Usuario::find($_SESSION['id']);

            // sincronizar con los datos del usuario
            $usuario->sincronizar($_POST);

            $alertas = $usuario->nuevo_password();

            if(empty($alertas)){ // si no hay alertas
                $resultado = $usuario->comprobar_password();

                if($resultado){
                    $usuario->password = $usuario->password_nuevo;

                    // eliminar propiedades no necesarias
                    unset($usuario->password_nuevo);
                    unset($usuario->password_actual);

                    // hashear nuevo password
                    $usuario->hashPassword();

                    // actualizar
                    $resultado = $usuario->guardar();

                    if($resultado){
                        Usuario::setAlerta('exito', 'Password guardado correctamente');
                        $alertas = $usuario->getAlertas();                       
                    }


                } else {
                    Usuario::setAlerta('error', 'Password actual incorrecto');
                    $alertas = $usuario->getAlertas();
                }
            }
        }

        $router->render('dashboard/cambiar-password', [
            'titulo' => 'Cambiar Password',
            'alertas' => $alertas
        ]);
    }
}

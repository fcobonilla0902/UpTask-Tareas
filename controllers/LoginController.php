<?php

namespace Controllers;

use Classes\Email;
use MVC\Router;
use Model\Usuario;

class LoginController{

    public static function login(Router $router){

        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === "POST"){
            $usuario = new Usuario($_POST);

            $alertas = $usuario->validarLogin();

            if(empty($alertas)){
                // verificar que el usuario exista
                $usuario = Usuario::where('email', $usuario->email);

                if(!$usuario || !$usuario->confirmado){
                    Usuario::setAlerta('error', 'El usuario no registrado o no esta confirmado');
                } else {
                    // usuario existe
                    if(password_verify($_POST['password'], $usuario->password)){
                        // Iniciar sesion
                        
                        session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        // redireccionar
                        header('Location: /dashboard');
                        
                    } else {
                        Usuario::setAlerta('error', 'Password incorrecto');
                    }
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/login', [
            'titulo' => 'Iniciar Sesión',
            'alertas' => $alertas
        ]);
    }

    public static function logout(){
        session_start();
        $_SESSION = [];
        header('Location: /');
    }

    public static function crear(Router $router){
        $alertas = [];
        $usuario = new Usuario;

        if($_SERVER['REQUEST_METHOD'] === "POST"){
            $usuario->sincronizar($_POST);
            $alertas= $usuario->validarNuevaCuenta();

            if(empty($alertas)){
                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario){
                    Usuario::setAlerta('error', 'Este usuario ya esta registrado');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Hashear password
                    $usuario->hashPassword();

                    // Eliminar password2
                    unset($usuario->password2);

                    // Crear token 
                    $usuario->crearToken();

                    // Crear nuevo usuario
                    $resultado = $usuario->guardar();

                    // Enviar email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();

                    if($resultado){
                        header('Location: /mensaje');
                    }
                }
            }
        }

        $router->render('auth/crear', [
            'titulo' => 'Crear Cuenta',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function olvide(Router $router){
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === "POST"){
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)){
                // Buscar el usuario
                $usuario = Usuario::where('email', $usuario->email);

                if($usuario && $usuario->confirmado === "1"){
                    // Encontró el usuario

                    // Generar un nuevo token
                    $usuario->crearToken();
                    unset($usuario->password2);

                    // Actualizar el usuario
                    $usuario->guardar();

                    // Enviar email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                    // Imprimir alerta
                    Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');
                  
                } else {
                    Usuario::setAlerta('error', 'El usuario no existe o no esta confirmado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide', [
            'titulo' => 'Olvide mi Password',
            'alertas' => $alertas
        ]);
    }

    public static function reestablecer(Router $router){

        $token = s($_GET['token']);
        $mostrar = true;

        if(!$token) header('Location: /');

        // identificar usuario
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)){
            Usuario::setAlerta('error', 'Token no válido');
            $mostrar = false;
        }


        if($_SERVER['REQUEST_METHOD'] === "POST"){
            // Añadir el nuevo password
            $usuario->sincronizar($_POST);

            // Validar el password
            $alertas = $usuario->validarPassword();
            
            if(empty($alertas)){
                // Hashear nuevo password
                $usuario->hashPassword();

                // Eliminar password2
                unset($usuario->password2);

                // Eliminar token
                $usuario->token = '';

                // Guardar nueva password
                $resultado = $usuario->guardar();

                // redireccionar al usuario
                if($resultado){
                    header('Location: /');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer Password',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);       
    }

    public static function mensaje(Router $router){
        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta Creada'
        ]); 
    }

    public static function confirmar(Router $router){

        $token = s($_GET['token']);
        

        if(!$token) header('Location: /');

        // Encontrar al usuario
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)){
            // No se encontró un usuario con este token
            Usuario::setAlerta('error', 'Token no válido');
        } else {
            // Confirmar la cuenta
            $usuario->confirmado = 1;
            $usuario->token = '';
            unset($usuario->password2);

            // Guardar en la base de datos
            $usuario->guardar();

            Usuario::setAlerta('exito', 'Cuenta confirmada correctamente');
        }

        $alertas = Usuario::getAlertas();
        $router->render('auth/confirmar', [
            'titulo' => 'Cuenta Confirmada',
            'alertas' => $alertas
        ]); 
    }
}
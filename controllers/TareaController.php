<?php

namespace Controllers;
use Model\Proyecto;
use Model\Tarea;


class TareaController{
    public static function index(){
        $proyectoId = $_GET['id'];

        if(!$proyectoId) header('Location: /dashboard');

        $proyecto = Proyecto::where('url', $proyectoId);
        session_start();

        if(!$proyecto || $proyecto->usuarioId !== $_SESSION['id']) header ('Location: /404');

        $tareas = Tarea::belongsTo('proyectoId', $proyecto->id);
        echo json_encode(['tareas' => $tareas]);
    }

    public static function crear(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            session_start();

            $proyectoId = $_POST['proyectoId'];
            $proyecto = Proyecto::where('url', $proyectoId);

            if(!$proyecto || $proyecto->usuarioId !== $_SESSION['id']){
                $respuesta = [
                    'tipo' => 'error',
                    'textoSuperior' => 'Error',
                    'textoInferior' => 'Hubo un error al crear la tarea'
                ];
                echo json_encode($respuesta); // json para conunicarse con js
                return;          
            } 

            //  bien, instanciar y crear la tarea
            $tarea = new Tarea($_POST);
            $tarea->proyectoId = $proyecto->id;
            $resultado = $tarea->guardar();
            $respuesta = [
                'tipo' => 'success',
                'id' => $resultado['id'],
                'textoSuperior' => 'Éxito',
                'textoInferior' => 'Tarea creada correctamente',
                'proyectoId' => $proyecto->id
            ];
            echo json_encode($respuesta);

            

        }
    }


    public static function actualizar(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            // validar que el proyecto existe
            $proyecto = Proyecto::where('url', $_POST['proyectoId']);

            session_start();

            if(!$proyecto || $proyecto->usuarioId !== $_SESSION['id']){
                $respuesta = [
                    'tipo' => 'error',
                    'textoSuperior' => 'Error',
                    'textoInferior' => 'Hubo un error al actualizar la tarea',
                ];
                echo json_encode($respuesta); // json para conunicarse con js
                return;          
            }      

            $tarea = new Tarea($_POST);
            $tarea->proyectoId = $proyecto->id;
            $resultado = $tarea->guardar();

            if($resultado){
                
                $respuesta = [
                    'id' => $tarea->id,
                    'proyectoId' => $proyecto->id,
                    'textoSuperior' => 'Éxito',
                    'textoInferior' => 'Tarea Actualizada Correctamente',
                    'tipo' => 'success',
                ];

                echo json_encode(['respuesta' => $respuesta]);
            }

        }
    }


    public static function eliminar(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            // validar que el proyecto existe
            $proyecto = Proyecto::where('url', $_POST['proyectoId']);

            session_start();

            if(!$proyecto || $proyecto->usuarioId !== $_SESSION['id']){
                $respuesta = [
                    'tipo' => 'error',
                    'textoSuperior' => 'Error',
                    'textoInferior' => 'Hubo un error al actualizar la tarea'
                ];
                echo json_encode($respuesta); // json para conunicarse con js
                return;          
            }  
            
            $tarea = new Tarea($_POST);
            $resultado = $tarea->eliminar();

            $resultado = [
                'resultado' => $resultado,
                'textoSuperior' => 'Éxito',
                'textoInferior' => 'Tarea Eliminada Correctamente',
                'tipo' => 'success'
            ];

            echo json_encode($resultado);
        }
    }
}
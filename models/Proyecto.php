<?php

namespace Model;
#[\AllowDynamicProperties]



class Proyecto extends ActiveRecord{
    protected static $tabla = 'proyectos';
    protected static $columnasDB = ['id', 'proyecto', 'url', 'usuarioId'];

    public function __construct($args = []){
        $this->id = $args['id'] ?? null;
        $this->proyecto = $args['proyecto'] ?? '';
        $this->url = $args['url'] ?? '';
        $this->usuarioId = $args['usuarioId'] ?? null;
    }

    public function validarProyecto(){
        if(!$this->proyecto){
            self::$alertas['error'][] = 'El nombre del proyecto es obligatorio';
        }
        return self::$alertas;
    }
}
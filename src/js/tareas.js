(function(){ // todo lo que este aquí no saldrá de este archivo js (IIEF)

    obtenerTareas();
    let tareas = [];
    let filtradas = [];
    
    // boton para mostrar el modal de agregar tarea
    const nuevaTareaBtn = document.querySelector('#agregar-tarea');
    nuevaTareaBtn.addEventListener('click', function(){
        mostrarFormulario(); // parametro false por default
    });

    // Filtros de búsqueda 
    const filtros = document.querySelectorAll('#filtros input[type="radio"]');
    filtros.forEach(radio => {
        radio.addEventListener('input', filtrarTareas);
    })

    function filtrarTareas(e){
        const filtro = e.target.value;

        if(filtro !== ''){ // filtrar por completadas o pendientes
            filtradas = tareas.filter(tarea => tarea.estado === filtro);
        } else{ // mostrar todas
            filtradas = [];
        }

        mostrarTareas();
    }

    async function obtenerTareas(){
        try {
            const id = obtenerProyecto();
            const url = `/api/tareas?id=${id}`;
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();
            
            tareas = resultado.tareas;
            mostrarTareas();
        } catch (error) {
            console.log(error);
        }
    }

    function mostrarTareas(){
        limpiarTareas();
        totalPendientes();
        totalCompletas();

        const arrayTareas = filtradas.length ? filtradas : tareas;

        if(arrayTareas.length === 0){
            const contenedorTareas = document.querySelector('#listado-tareas');

            const textoNoTareas = document.createElement('LI');
            textoNoTareas.textContent = 'Aún no hay tareas';
            textoNoTareas.classList.add('no-tareas');

            contenedorTareas.appendChild(textoNoTareas);
            return;
        }

        const estados = {
            0: 'Pendiente',
            1: 'Completa'
        }

        arrayTareas.forEach(tarea => {
            const contenedorTarea = document.createElement('LI');
            contenedorTarea.dataset.tareaId = tarea.id;
            contenedorTarea.classList.add('tarea');

            const nombreTarea = document.createElement('P');
            nombreTarea.textContent = tarea.nombre;
            nombreTarea.ondblclick = function(){
                mostrarFormulario(editar = true, {...tarea});
            }

            const opcionesDiv = document.createElement('DIV');
            opcionesDiv.classList.add('opciones');

            // botones
            const btnEstadoTarea = document.createElement('BUTTON');
            btnEstadoTarea.classList.add('estado-tarea');
            btnEstadoTarea.classList.add(`${estados[tarea.estado].toLowerCase()}`);
            btnEstadoTarea.textContent = estados[tarea.estado];
            btnEstadoTarea.dataset.estadoTarea = tarea.estado; // atributo personalizado en html
            btnEstadoTarea.ondblclick = function(){ // ondblclick = doble click
                cambiarEstadoTarea({...tarea}); // ... copia
            }

            const btnEliminarTarea = document.createElement('BUTTON');
            btnEliminarTarea.classList.add('eliminar-tarea');
            btnEliminarTarea.dataset.idTarea = tarea.id;
            btnEliminarTarea.textContent = 'Eliminar';
            btnEliminarTarea.ondblclick = function(){
                confirmarEliminarTarea({...tarea});
            }

            opcionesDiv.appendChild(btnEstadoTarea);
            opcionesDiv.appendChild(btnEliminarTarea);

            contenedorTarea.appendChild(nombreTarea);
            contenedorTarea.appendChild(opcionesDiv);

            const listadoTareas = document.querySelector('#listado-tareas');
            listadoTareas.appendChild(contenedorTarea);
            //console.log(contenedorTarea);
        });
    }


    function totalPendientes(){
        const totalPendientes = tareas.filter(tarea => tarea.estado === "0");
        const pendientesRadio = document.querySelector('#pendientes');

        if(totalPendientes.length === 0){
            pendientesRadio.disabled = true;
        } else{
            pendientesRadio.disabled = false;
        }
    }


    function totalCompletas(){
        const totalCompletadas = tareas.filter(tarea => tarea.estado === "1");
        const completadasRadio = document.querySelector('#completadas');

        if(totalCompletadas.length === 0){
            completadasRadio.disabled = true;
        } else{
            completadasRadio.disabled = false;
        }
    }


    function mostrarFormulario(editar = false, tarea = {}){
        const modal = document.createElement('DIV');
        modal.classList.add('modal');
        modal.innerHTML = `
            <form class="formulario nueva-tarea">
                <legend>${editar ? 'Editar Tarea' : 'Agrega una nueva tarea'}</legend>
                <div class="campo">
                    <label>Tarea:</label>
                    <input 
                        type="text" name="tarea" id="tarea" 
                        placeholder="${editar ? 'Editar Tarea'  : 'Agregar Tarea al Proyecto Actual'}" 
                        value="${tarea.nombre ? tarea.nombre  : ''}"
                    />
                </div>
                <div class="opciones">
                    <input type="submit" class="submit-nueva-tarea" value="${editar ? 'Actualizar Tarea'  : 'Agregar Tarea'}"/>
                    <button type="button" class="cerrar-modal">Cancelar</button>
                </div>
            </form>
        `;

        setTimeout(() => {
            const formulario = document.querySelector('.formulario');
            formulario.classList.add('animar');
        }, 0);

        modal.addEventListener('click', function(e) {
            e.preventDefault();

            if(e.target.classList.contains('cerrar-modal')){
                const formulario = document.querySelector('.formulario');
                formulario.classList.add('cerrar');

                setTimeout(() => {
                    modal.remove();
                }, 500);
                
            } 

            if(e.target.classList.contains('submit-nueva-tarea')){
                const nombreTarea = document.querySelector('#tarea').value.trim();

                if(nombreTarea === ''){
                    // mostrar alerta de error
                    mostrarAlerta('El nombre de la tarea es obligatorio', 'error',
                    document.querySelector('.formulario legend'));

                    return;
                }

                if(editar){
                    tarea.nombre = nombreTarea;
                    actualizarTarea(tarea);
                } else{
                    agregarTarea(nombreTarea);
                }

            }
        });


        document.querySelector('.dashboard').appendChild(modal);
    }



    // muestra un mensaje en la interfaz
    function mostrarAlerta(mensaje, tipo, referencia){
        // prevenir la creación de multiples alertas
        const alertaPrevia = document.querySelector('.alerta');
        if(alertaPrevia){
            alertaPrevia.remove();
        }

        const alerta = document.createElement('DIV');
        alerta.classList.add('alerta', tipo);
        alerta.textContent = mensaje;

        // inserta alerta antes del legend
        referencia.parentElement.insertBefore(alerta, referencia.nextElementSibling);

        // eliminar la alerta 5 seg despues
        setTimeout(() =>{
            alerta.remove();
        }, 5000);
    }

    // consultar el servidor para agregar una nueva tarea
    async function agregarTarea(tarea){
        // Construir petición
        const datos = new FormData();
        datos.append('nombre', tarea);
        datos.append('proyectoId', obtenerProyecto());


        try {
            const url = '/api/tarea';
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });

            const resultado = await respuesta.json();
            //console.log(resultado);

            const modal = document.querySelector('.modal');
            modal.remove();
            Swal.fire(resultado.textoSuperior, resultado.textoInferior, resultado.tipo);
            
            if(resultado.tipo === 'success'){

                // agregar el objeto de tarea al global de tareas
                const tareaObj = {
                    id: String(resultado.id),
                    nombre: tarea,
                    estado: '0',
                    proyectoId: resultado.proyectoId
                }

                tareas = [...tareas, tareaObj]; // lo que estaba más lo nuevo
                mostrarTareas();
            }

        } catch (error) {
            console.log(error);
        }
    }


    function cambiarEstadoTarea(tarea){
        const nuevoEstado = tarea.estado === "1" ? "0" : "1";
        tarea.estado = nuevoEstado;
        actualizarTarea(tarea);
    }


    async function actualizarTarea(tarea){
        const {id, nombre, estado} = tarea;

        const datos = new FormData();
        datos.append('id', id);
        datos.append('nombre', nombre);
        datos.append('estado', estado);
        datos.append('proyectoId', obtenerProyecto());

        /* para ver los valores del FormData
        for(let valor of datos.values()){
            console.log(valor);
        }
        */

        try {
            const url = '/api/tarea/actualizar';

            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });
            const resultado = await respuesta.json();

            Swal.fire(resultado.respuesta.textoSuperior, resultado.respuesta.textoInferior, resultado.respuesta.tipo);

            if(resultado.respuesta.tipo === 'success'){
                /*mostrarAlerta(
                    resultado.respuesta.mensaje,
                    resultado.respuesta.tipo, 
                    document.querySelector('.contenedor-nueva-tarea')
                ); */


                const modal = document.querySelector('.modal');
                if(modal){
                    modal.remove();
                }

                tareas = tareas.map(tareaMemoria => { // itera en el arreglo de tareas y crea uno nuevo

                    if(tareaMemoria.id === id){
                        tareaMemoria.estado = estado;
                        tareaMemoria.nombre = nombre;
                    }

                    return tareaMemoria;
                }); 

                mostrarTareas();
            }

        } catch (error) {
            console.log(error);
        }

    }

    
    function confirmarEliminarTarea(tarea){
        Swal.fire({
            title: "¿Eliminar esta tarea?",
            showCancelButton: true,
            confirmButtonText: "Confirmar",
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarTarea(tarea);
            } 
        });
    }


    async function eliminarTarea(tarea){

        const {id, nombre, estado} = tarea;

        const datos = new FormData();
        datos.append('id', id);
        datos.append('nombre', nombre);
        datos.append('estado', estado);
        datos.append('proyectoId', obtenerProyecto());

        try {
            const url = '/api/tarea/eliminar';
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });

            const resultado = await respuesta.json();

            if(resultado.resultado){
                Swal.fire(resultado.textoSuperior, resultado.textoInferior, resultado.tipo);

                // crea un arreglo nuevo y trae todo menos una
                tareas = tareas.filter(tareaMemoria => tareaMemoria.id !== tarea.id);
                mostrarTareas();

            }


        } catch (error) {
            console.log(error);
        }
    }


    function obtenerProyecto(){
        const proyectoParams = new URLSearchParams(window.location.search);
        const proyecto = Object.fromEntries(proyectoParams.entries());
        return proyecto.id;
    }


    function limpiarTareas(){
        const listadoTareas = document.querySelector('#listado-tareas');
        
        while(listadoTareas.firstChild){
            listadoTareas.removeChild(listadoTareas.firstChild);
        }
    }
})();

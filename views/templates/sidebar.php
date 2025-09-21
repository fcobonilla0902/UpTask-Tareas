<aside class="sidebar">
    <div class="contenedor-sidebar">
        <a href="/dashboard">
            <h2>UpTask</h2>
        </a>

        <div class="cerrar-menu">
            <img src="build/img/cerrar.svg" alt="imagen cerrar menu" id="cerrar-menu">
        </div>
    </div>

    <nav class="sidebar-nav">
        <a class="<?php echo ($titulo === 'Proyectos') ? 'activo' : ''; ?>" href="/dashboard">Proyectos</a>
        <a class="<?php echo ($titulo === 'Crear Proyecto') ? 'activo' : ''; ?>" href="/crear-proyecto">Crear Proyecto</a>
        <a class="<?php echo ($titulo === 'Perfil') ? 'activo' : ''; ?>" href="/perfil">Perfil</a>
        
        <div class="cerrar-sesion-mobile">
            <a href="/logout" class="cerrar-sesion">Cerrar Sesión</a>
        </div>
    </nav>
</aside>
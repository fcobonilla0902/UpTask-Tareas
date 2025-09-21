<?php include_once __DIR__ . '/header-dashboard.php'; ?>

    <?php if(count($proyectos) === 0){ ?>
        <p class="no-proyectos">Aún no hay proyectos <a href="/crear-proyecta">Crear un proyecto</a></p>
    <?php } else{ ?>
        <ul class="listado-proyectos">
            <?php foreach($proyectos as $proyecto) { ?>
                <a href="/proyecto?id=<?php echo $proyecto->url; ?>">
                    <li class="proyecto">
                        <p><?php echo $proyecto->proyecto; ?></p>
                    </li>
                </a>
            <?php } ?>
        </ul>
    <?php } ?>

<?php include_once __DIR__ . '/footer-dashboard.php'; ?>
<div class="contenedor reestablecer">
    <?php include_once __DIR__ . '/../templates/nombre-sitio.php';?>

    <div class="contenedor-sm">
        <p class="descripcion-pagina">Coloca tu nuevo Password</p>

        <?php include_once __DIR__ . '/../templates/alertas.php';?>

        <?php if($mostrar) { ?>

        <form method="POST" class="formulario">
            <div class="campo">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Tu Password">
            </div>

            <div class="campo">
                <label for="password2">Confirma Password</label>
                <input type="password" id="password2" name="password2" placeholder="Confirma tu Password">
            </div>

            <input type="submit" class="boton" value="Guardar">
        </form>

        <?php } ?>

        <div class="acciones">
            <a href="/crear">¿Aún no tienes una cuenta? Crear una</a>
            <a href="/olvide">¿Olvidaste tu Password?</a>
        </div>
    </div> <!-- .contenedor-sm -->
</div>
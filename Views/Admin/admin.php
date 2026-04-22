<?php include("../../includes/auth.php");?> 

<?php include("../../includes/header.php");?>
<?php include("../../includes/navbar.php"); ?>
<!-- CONTENIDO -->
<div class="content" id="content">

    <h3 class="mb-4">Dashboard</h3>

    <div class="row g-4">

        <!-- CARD 1 --> 
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <i class="bi bi-people fs-1 text-primary"></i>
                <h5 class="mt-3">Usuarios</h5>
            </div>
        </div>

        <!-- CARD 2 --> 
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <i class="bi bi-laptop fs-1 text-success"></i>
                <h5 class="mt-3">Laboratorios</h5>
            </div>
        </div>

         <!-- CARD 3 --> 
        <div class="col-md-4"> 
            <div class="card p-4 text-center"> 
                <i class="bi bi-bar-chart fs-1 text-danger"></i> 
                <h5 class="mt-3">Reportes</h5> 
            </div> 
        </div> 

        <!-- MENSAJE --> 
        <div class="card mt-4 p-3"> <h5><i class="bi bi-info-circle"></i> Aviso</h5> 
            <p> Se les recuerda que está prohibido introducir alimentos o bebidas. Mantenga el uso adecuado del equipo de cómputo. </p> 
        </div> 
        
        <!--Noticias--> 
        <div class="card mt-4 p-3"> <h5><i class="bi bi-newspaper"></i> Noticias</h5> 
            <p> 
                <p>1.- Aviso Importante</p> 
                <p>El servicio de los laboratorios de centro de cómputo es de 9:00 am a 2:00 pm y de 4:00pm a 7:00pm, 
                    fuera de ese horario no se garantiza el servicio; para que tome sus debidas precauciones.
                </p> 
                <p>2.- Uso de Laboratorios</p> 
                <p>Se les Recuerda que esta prohibido introducir alimentos o bebidas; 
                les pedimos su cooperación y apoyo para hacer un uso correcto de los equipos de cómputo.
                </p> 
                <p>Por su atención, ¡Muchas Gracias!</p> 
            </p>
        </div>
    </div>
</div>

<script src="../../js/darkmode.js"></script>
<script src="../../js/logout.js"></script>

</body>
</html>
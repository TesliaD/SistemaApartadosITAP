document.addEventListener("DOMContentLoaded", function () {

    // FORMULARIO
    document.getElementById("formReservacion").addEventListener("submit", function(e){
        e.preventDefault();

        const formData = new FormData(this);

        fetch("../../controllers/guardar_reservacion.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === "success"){
                alert("Guardado");
                location.reload();
            } else {
                alert(data.error);
            }
        });
    });

    // CALENDARIO
    var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'timeGridWeek',
        locale: 'es',
        events: '../../controllers/obtener_reservaciones.php'
    });

    calendar.render();

});
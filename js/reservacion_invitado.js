(() => {
    const fecha = document.getElementById('fecha');
    const lab = document.getElementById('lab');
    const botones = [...document.querySelectorAll('.hora-btn')];
    const seleccionadas = new Set();
    botones.forEach((boton) => boton.addEventListener('click', () => {
        if (boton.disabled || boton.classList.contains('ocupada')) return;
        const hora = boton.textContent.trim();
        if (seleccionadas.has(hora)) { seleccionadas.delete(hora); boton.classList.remove('activa'); }
        else { seleccionadas.add(hora); boton.classList.add('activa'); }
    }));
    async function cargarOcupadas() {
        botones.forEach((b) => { b.disabled = false; b.classList.remove('ocupada', 'activa'); });
        seleccionadas.clear();
        if (!fecha.value || !lab.value) return;
        try {
            const response = await fetch(`../../controllers/obtener_horas_ocupadas.php?fecha=${encodeURIComponent(fecha.value)}&lab=${encodeURIComponent(lab.value)}`);
            const ocupadas = await response.json();
            if (!Array.isArray(ocupadas)) return;
            botones.forEach((boton) => {
                const inicio = boton.textContent.split('-')[0].trim();
                if (ocupadas.some((hora) => String(hora).trim() === inicio)) { boton.disabled = true; boton.classList.add('ocupada'); }
            });
        } catch (error) { console.error('No se pudieron cargar los horarios ocupados.', error); }
    }
    fecha.addEventListener('change', cargarOcupadas);
    lab.addEventListener('change', cargarOcupadas);
    cargarOcupadas();
    document.getElementById('btnGuardar').addEventListener('click', async () => {
        if (!fecha.value || !lab.value || seleccionadas.size === 0) {
            Swal.fire('Completa tu reservación', 'Selecciona fecha, laboratorio y al menos un horario.', 'warning'); return;
        }
        const datos = { fecha: fecha.value, horas: [...seleccionadas], IDLab: Number(lab.value), Practica: document.getElementById('practica').value.trim(), software: document.getElementById('software').value.trim() };
        try {
            const response = await fetch('../../controllers/guardar_reservacion.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos) });
            const result = await response.json();
            if (result.error) { Swal.fire('No se pudo reservar', result.error, 'error'); return; }
            await Swal.fire('Reservación registrada', result.mensaje || 'Tu reservación quedó guardada.', 'success');
            seleccionadas.clear();
            document.getElementById('practica').value = '';
            document.getElementById('software').value = '';
            await cargarOcupadas();
        } catch (error) { Swal.fire('Error', 'No se pudo guardar la reservación.', 'error'); }
    });
})();
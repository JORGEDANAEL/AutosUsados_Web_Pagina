// --- GUARDIA DE SEGURIDAD ---
fetch('../backend/verificar_sesion.php')
.then(response => response.json())
.then(data => {
    if (!data.activa) { window.location.replace('index.html'); }
});

// Sacamos el ID del auto de la URL (ej. pago.html?id=3)
const urlParams = new URLSearchParams(window.location.search);
const idAuto = urlParams.get('id');

if (!idAuto) {
    alert("No seleccionaste ningún auto");
    window.location.href = 'catalogo.html';
}

// Simulamos el pago y mandamos a guardar
document.getElementById('formPago').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Aquí podrías validar la tarjeta real con una API (como Stripe), pero simularemos que pasó
    const btnSubmit = this.querySelector('button[type="submit"]');
    btnSubmit.innerText = "Procesando...";
    btnSubmit.disabled = true;

    fetch('../backend/procesar_pago.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_auto: idAuto })
    })
    .then(response => response.json())
    .then(data => {
        const mensajeEl = document.getElementById('mensajePago');
        if (data.success) {
            mensajeEl.style.color = 'green';
            mensajeEl.innerText = "¡Pago exitoso! El auto ha sido apartado.";
            setTimeout(() => { window.location.href = 'catalogo.html'; }, 3000);
        } else {
            mensajeEl.style.color = 'red';
            mensajeEl.innerText = data.mensaje;
            btnSubmit.innerText = "Pagar y Reservar";
            btnSubmit.disabled = false;
        }
    })
    .catch(error => console.error('Error:', error));
});
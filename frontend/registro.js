document.getElementById('registroForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const nombre = document.getElementById('nombre').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    fetch('../backend/registro.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            nombre: nombre,
            email: email,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        const mensajeEl = document.getElementById('mensajeRegistro');
        if(data.success) {
            mensajeEl.style.color = 'green';
            mensajeEl.innerText = data.mensaje;
            
            // Si el registro fue exitoso, lo mandamos automáticamente al login después de 2 segundos
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 2000);
        } else {
            mensajeEl.style.color = 'red';
            mensajeEl.innerText = data.mensaje;
        }
    })
    .catch(error => console.error('Error al registrar:', error));
});
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault(); 

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    fetch('../backend/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email: email, password: password })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            if(data.rol === 'admin') {
                window.location.href = 'admin.html'; 
            } else {
                window.location.href = 'catalogo.html'; 
            }
        } else {
            document.getElementById('mensaje').innerText = data.mensaje;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('mensaje').innerText = "Error al conectar con el servidor.";
    });
});
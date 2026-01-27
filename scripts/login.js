
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const errorMessage = document.getElementById('errorMessage');
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            // Alternar visibilidad de contraseña
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Cambiar icono
                const icon = this.querySelector('i');
                icon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
            });
            
            // Manejar envío del formulario
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                
                // Validación básica
                if (!username || !password) {
                    showError('Por favor, complete todos los campos');
                    return;
                }
                
                // Simular autenticación (en un sistema real, esto sería una petición al servidor)
                if (authenticateUser(username, password)) {
                    // Login exitoso - redirigir al dashboard
                    showSuccess('Inicio de sesión exitoso. Redirigiendo...');
                } else {
                    showError('Usuario o contraseña incorrectos');
                }
            });
            
            // Función de autenticación simulada
            function authenticateUser(username, password) {
                // En un sistema real, esto sería una petición al servidor
                const validUsers = [
                    { username: 'admin', password: 'admin123', role: 'Administrador' },
                    { username: 'supervisor', password: 'sup123', role: 'Supervisor' },
                    { username: 'operador', password: 'op123', role: 'Operador' }
                ];
                
                return validUsers.some(user => 
                    user.username === username && user.password === password
                );
            }
            
            // Mostrar mensaje de error
            function showError(message) {
                document.getElementById('errorText').textContent = message;
                errorMessage.style.display = 'block';
                
                // Ocultar después de 5 segundos
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 5000);
            }
            
            // Mostrar mensaje de éxito
            function showSuccess(message) {
                // Crear alerta de éxito si no existe
                let successAlert = document.getElementById('successMessage');
                if (!successAlert) {
                    successAlert = document.createElement('div');
                    successAlert.id = 'successMessage';
                    successAlert.className = 'alert alert-success alert-custom';
                    successAlert.innerHTML = `<i class="bi bi-check-circle me-2"></i><span>${message}</span>`;
                    loginForm.insertBefore(successAlert, loginForm.firstChild);
                } else {
                    successAlert.querySelector('span').textContent = message;
                    successAlert.style.display = 'block';
                }
                
                // Ocultar mensaje de error si está visible
                errorMessage.style.display = 'none';
            }

            
            // Efecto de carga al enviar formulario
            loginForm.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spinner"></i> Validando...';
                submitBtn.disabled = true;
                
                // Restaurar después de 3 segundos (simulando procesamiento)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 3000);
            });
        });
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Conocimientos - Operador</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }
        
        .exam-modal .modal-content {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .exam-header {
            background: linear-gradient(135deg, var(--primary-color), #1a2530);
            color: white;
            padding: 20px;
            border-bottom: none;
        }
        
        .timer-container {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 10px 15px;
            margin-top: 10px;
        }
        
        .timer {
            font-size: 1.5rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }
        
        .timer-warning {
            color: var(--warning-color);
            animation: pulse 1s infinite;
        }
        
        .timer-danger {
            color: var(--danger-color);
            animation: pulse 0.5s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .question-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--secondary-color);
        }
        
        .question-number {
            background: var(--secondary-color);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .option-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .option-card:hover {
            border-color: var(--secondary-color);
            background: #f8f9fa;
        }
        
        .option-card.selected {
            border-color: var(--success-color);
            background: rgba(39, 174, 96, 0.1);
        }
        
        .progress-section {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .nav-btn {
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .results-section {
            text-align: center;
            padding: 30px;
        }
        
        .score-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: conic-gradient(var(--success-color) 0% 80%, #e9ecef 80% 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            position: relative;
        }
        
        .score-inner {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Botón para abrir el modal (solo para demo) -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#examModal">
                    <i class="bi bi-pencil-square me-2"></i>
                    Iniciar Prueba de Conocimientos
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Prueba de Conocimientos -->
    <div class="modal fade exam-modal" id="examModal" tabindex="-1" aria-labelledby="examModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Header con temporizador -->
                <div class="exam-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="modal-title mb-2">
                                <i class="bi bi-clipboard-check me-2"></i>
                                Prueba de Conocimientos - Operador
                            </h4>
                            <p class="mb-0 opacity-75">Estación: Ensamblaje Final | Proceso: PCM-2024</p>
                        </div>
                        <div class="timer-container text-center">
                            <small class="d-block opacity-75">Tiempo Restante</small>
                            <div class="timer" id="examTimer">01:00</div>
                        </div>
                    </div>
                </div>

                <!-- Contenido del examen -->
                <div class="modal-body p-4">
                    <!-- Pantalla de inicio -->
                    <div id="startScreen">
                        <div class="text-center py-5">
                            <i class="bi bi-clock-history display-1 text-primary mb-3"></i>
                            <h3 class="mb-3">Prueba de Conocimientos</h3>
                            <p class="text-muted mb-4">
                                Esta prueba evaluará sus conocimientos sobre el proceso de ensamblaje.<br>
                                Tiene <strong>30 minutos</strong> para completar <strong>10 preguntas</strong>.
                            </p>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                El examen se cerrará automáticamente al terminar el tiempo.
                            </div>
                            <button class="btn btn-primary btn-lg mt-3" onclick="startExam()">
                                <i class="bi bi-play-circle me-2"></i>
                                Iniciar Prueba
                            </button>
                        </div>
                    </div>

                    <!-- Pantalla de preguntas -->
                    <div id="examScreen" style="display: none;">
                        <!-- Barra de progreso -->
                        <div class="progress-section">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">Progreso: <span id="currentQuestion">1</span>/10</span>
                                <span class="fw-bold text-primary" id="progressPercent">10%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" id="progressBar" style="width: 10%;"></div>
                            </div>
                        </div>

                        <!-- Pregunta actual -->
                        <div class="question-section">
                            <div class="d-flex align-items-center mb-3">
                                <div class="question-number" id="questionNumber">1</div>
                                <h5 class="mb-0" id="questionText">¿Pregunta de evaluacion para certificacion/capacitacion?</h5>
                            </div>
                            
                            <!-- Opciones de respuesta -->
                            <div class="options-container">
                                <div class="option-card" onclick="selectOption(this)">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="question1" style="display: none;">
                                        <label class="form-check-label fw-bold">
                                            Respuesta 1
                                        </label>
                                    </div>
                                </div>
                                <div class="option-card" onclick="selectOption(this)">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="question1" style="display: none;">
                                        <label class="form-check-label fw-bold">
                                            Respusta 2
                                        </label>
                                    </div>
                                </div>
                                <div class="option-card" onclick="selectOption(this)">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="question1" style="display: none;">
                                        <label class="form-check-label fw-bold">
                                            Respuesta 3
                                        </label>
                                    </div>
                                </div>
                                <div class="option-card" onclick="selectOption(this)">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="question1" style="display: none;">
                                        <label class="form-check-label fw-bold">
                                            Respuesta 4
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navegación -->
                        <div class="d-flex justify-content-between mt-4">
                            <button class="btn btn-outline-secondary nav-btn" id="prevBtn" onclick="previousQuestion()" disabled>
                                <i class="bi bi-chevron-left me-2"></i>Anterior
                            </button>
                            <button class="btn btn-primary nav-btn" id="nextBtn" onclick="nextQuestion()">
                                Siguiente<i class="bi bi-chevron-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Pantalla de resultados -->
                    <div id="resultsScreen" style="display: none;">
                        <div class="results-section">
                            <i class="bi bi-trophy display-1 text-warning mb-3"></i>
                            <h3 class="mb-3">¡Prueba Completada!</h3>
                            
                            <div class="score-circle">
                                <div class="score-inner">
                                    <span id="finalScore">80%</span>
                                </div>
                            </div>
                            
                            <div class="row justify-content-center mb-4">
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <h5 class="text-primary" id="correctAnswers">8</h5>
                                                    <small class="text-muted">Correctas</small>
                                                </div>
                                                <div class="col-4">
                                                    <h5 class="text-warning" id="incorrectAnswers">2</h5>
                                                    <small class="text-muted">Incorrectas</small>
                                                </div>
                                                <div class="col-4">
                                                    <h5 class="text-success" id="scorePercentage">80%</h5>
                                                    <small class="text-muted">Calificación</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong id="resultMessage">Aprobado</strong> - El operador ha demostrado conocimientos suficientes.
                            </div>
                            
                            <div class="mt-4">
                                <button class="btn btn-success me-2" onclick="saveResults()">
                                    <i class="bi bi-check-lg me-2"></i>Guardar Resultados
                                </button>
                                <button class="btn btn-outline-secondary" onclick="closeModal()">
                                    <i class="bi bi-x-lg me-2"></i>Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Variables globales
        let examTime = 1 * 60; // 30 minutos en segundos
        let timerInterval;
        let currentQuestion = 1;
        let totalQuestions = 10;
        let userAnswers = new Array(totalQuestions).fill(null);
        let correctAnswers = [0, 1, 2, 0, 1, 2, 3, 0, 1, 2]; // Respuestas correctas para demo

        // Iniciar el examen
        function startExam() {
            document.getElementById('startScreen').style.display = 'none';
            document.getElementById('examScreen').style.display = 'block';
            startTimer();
            showQuestion(1);
        }

        // Temporizador
        function startTimer() {
            timerInterval = setInterval(function() {
                examTime--;
                
                let minutes = Math.floor(examTime / 60);
                let seconds = examTime % 60;
                
                // Formatear el tiempo
                let timeString = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                document.getElementById('examTimer').textContent = timeString;
                
                // Cambiar color cuando quede poco tiempo
                if (examTime <= 300) { // 5 minutos
                    document.getElementById('examTimer').className = 'timer timer-warning';
                }
                if (examTime <= 60) { // 1 minuto
                    document.getElementById('examTimer').className = 'timer timer-danger';
                }
                
                // Cerrar automáticamente cuando el tiempo se acabe
                if (examTime <= 0) {
                    clearInterval(timerInterval);
                    finishExam();
                }
            }, 1000);
        }

        // Mostrar pregunta
        function showQuestion(questionNum) {
            currentQuestion = questionNum;
            
            // Actualizar interfaz
            document.getElementById('questionNumber').textContent = questionNum;
            document.getElementById('currentQuestion').textContent = questionNum;
            
            // Actualizar progreso
            let progress = (questionNum / totalQuestions) * 100;
            document.getElementById('progressBar').style.width = `${progress}%`;
            document.getElementById('progressPercent').textContent = `${Math.round(progress)}%`;
            
            // Habilitar/deshabilitar botones de navegación
            document.getElementById('prevBtn').disabled = (questionNum === 1);
            document.getElementById('nextBtn').textContent = 
                (questionNum === totalQuestions) ? 'Finalizar' : 'Siguiente';
            
            // Restaurar respuesta seleccionada si existe
            if (userAnswers[questionNum - 1] !== null) {
                let options = document.querySelectorAll('.option-card');
                options[userAnswers[questionNum - 1]].classList.add('selected');
            }
        }

        // Seleccionar opción
        function selectOption(element) {
            // Remover selección anterior
            let options = element.parentElement.querySelectorAll('.option-card');
            options.forEach(opt => opt.classList.remove('selected'));
            
            // Marcar nueva selección
            element.classList.add('selected');
            
            // Guardar respuesta
            let optionIndex = Array.from(options).indexOf(element);
            userAnswers[currentQuestion - 1] = optionIndex;
        }

        // Navegación entre preguntas
        function previousQuestion() {
            if (currentQuestion > 1) {
                showQuestion(currentQuestion - 1);
            }
        }

        function nextQuestion() {
            if (currentQuestion < totalQuestions) {
                showQuestion(currentQuestion + 1);
            } else {
                finishExam();
            }
        }

        // Finalizar examen
        function finishExam() {
            clearInterval(timerInterval);
            document.getElementById('examScreen').style.display = 'none';
            document.getElementById('resultsScreen').style.display = 'block';
            
            // Calcular resultados
            let correct = 0;
            for (let i = 0; i < totalQuestions; i++) {
                if (userAnswers[i] === correctAnswers[i]) {
                    correct++;
                }
            }
            
            let score = (correct / totalQuestions) * 100;
            let passed = score >= 70;
            
            // Mostrar resultados
            document.getElementById('correctAnswers').textContent = correct;
            document.getElementById('incorrectAnswers').textContent = totalQuestions - correct;
            document.getElementById('scorePercentage').textContent = `${Math.round(score)}%`;
            document.getElementById('finalScore').textContent = `${Math.round(score)}%`;
            
            // Mensaje según resultado
            if (passed) {
                document.getElementById('resultMessage').textContent = 'Aprobado';
                document.querySelector('.alert').className = 'alert alert-success';
            } else {
                document.getElementById('resultMessage').textContent = 'No Aprobado';
                document.querySelector('.alert').className = 'alert alert-danger';
            }
        }

        // Guardar resultados
        function saveResults() {
            // Aquí iría la lógica para guardar en base de datos
            alert('Resultados guardados exitosamente en el sistema.');
            closeModal();
        }

        // Cerrar modal
        function closeModal() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('examModal'));
            modal.hide();
        }

        // Reiniciar cuando se cierre el modal
        document.getElementById('examModal').addEventListener('hidden.bs.modal', function() {
            // Reiniciar variables
            examTime = 1 * 60;
            currentQuestion = 1;
            userAnswers = new Array(totalQuestions).fill(null);
            
            // Reiniciar interfaz
            document.getElementById('startScreen').style.display = 'block';
            document.getElementById('examScreen').style.display = 'none';
            document.getElementById('resultsScreen').style.display = 'none';
            document.getElementById('examTimer').textContent = '1:00';
            document.getElementById('examTimer').className = 'timer';
            
            // Limpiar selecciones
            let options = document.querySelectorAll('.option-card');
            options.forEach(opt => opt.classList.remove('selected'));
        });
    </script>
</body>
</html>
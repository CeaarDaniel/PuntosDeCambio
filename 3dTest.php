<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elementos 3D Interactivos</title>
    <style>
        body {
            margin: 0;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        #info-panel {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(30, 30, 40, 0.85);
            color: white;
            padding: 20px;
            border-radius: 12px;
            backdrop-filter: blur(8px);
            border: 1px solid #444;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            width: 260px;
            pointer-events: none; /* Se activará solo cuando haya selección */
            opacity: 0.7;
            transition: opacity 0.3s;
        }
        #info-panel.active {
            pointer-events: auto;
            opacity: 1;
        }
        #info-panel h3 {
            margin: 0 0 10px;
            color: #ffaa44;
            border-bottom: 1px solid #555;
            padding-bottom: 5px;
        }
        #info-panel p {
            margin: 8px 0;
            font-size: 14px;
        }
        #info-panel .property {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        #info-panel button {
            background: #3a4a6b;
            color: white;
            border: none;
            padding: 6px 12px;
            margin: 4px 2px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s;
            min-width: 45px;
        }
        #info-panel button:hover {
            background: #5a7a9c;
        }
        #info-panel .control-group {
            margin: 15px 0;
            border-top: 1px solid #555;
            padding-top: 10px;
        }
        #info-panel .slider-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        #info-panel input[type=range] {
            flex: 1;
            cursor: pointer;
        }
        #selection-status {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: rgba(0,0,0,0.6);
            color: #0ff;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            border: 1px solid #0ff;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div id="selection-status">🔍 Ningún objeto seleccionado</div>

    <div id="info-panel">
        <h3 id="object-title">Selecciona un objeto</h3>
        <div id="object-details">
            <div class="property"><span>Tipo:</span> <span id="obj-type">-</span></div>
            <div class="property"><span>Posición:</span> <span id="obj-pos">(0, 0, 0)</span></div>
            <div class="property"><span>Rotación:</span> <span id="obj-rot">(0, 0, 0)</span></div>
            <div class="property"><span>Escala:</span> <span id="obj-scale">(1, 1, 1)</span></div>
        </div>

        <div class="control-group">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>📦 Posición</span>
                <div>
                    <button id="move-x-minus">X-</button>
                    <button id="move-x-plus">X+</button>
                    <button id="move-y-minus">Y-</button>
                    <button id="move-y-plus">Y+</button>
                    <button id="move-z-minus">Z-</button>
                    <button id="move-z-plus">Z+</button>
                </div>
            </div>
        </div>

        <div class="control-group">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>🔄 Rotación (rad)</span>
                <div>
                    <button id="rot-x-minus">X-</button>
                    <button id="rot-x-plus">X+</button>
                    <button id="rot-y-minus">Y-</button>
                    <button id="rot-y-plus">Y+</button>
                    <button id="rot-z-minus">Z-</button>
                    <button id="rot-z-plus">Z+</button>
                </div>
            </div>
        </div>

        <div class="control-group">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span>📏 Escala</span>
                <div>
                    <button id="scale-down">-</button>
                    <button id="scale-up">+</button>
                </div>
            </div>
            <div class="slider-container">
                <span>0.5</span>
                <input type="range" id="scale-slider" min="0.2" max="3" step="0.1" value="1">
                <span>3.0</span>
            </div>
        </div>
    </div>

    <!-- Importamos Three.js y los controles -->
    <script type="importmap">
        {
            "imports": {
                "three": "https://unpkg.com/three@0.128.0/build/three.module.js",
                "three/addons/": "https://unpkg.com/three@0.128.0/examples/jsm/"
            }
        }
    </script>

    <script type="module">
        import * as THREE from 'three';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

        // --- Configuración básica ---
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0x111122);

        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.set(4, 3, 6);
        camera.lookAt(0, 0, 0);

        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.shadowMap.enabled = true;
        document.body.appendChild(renderer.domElement);

        // --- Controles de cámara ---
        const controls = new OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.05;
        controls.autoRotate = true;
        controls.autoRotateSpeed = 1.5;
        controls.enableZoom = true;

        // --- Luces ---
        const ambientLight = new THREE.AmbientLight(0x404060);
        scene.add(ambientLight);

        const light1 = new THREE.PointLight(0xffaa88, 1.5, 10);
        light1.position.set(2, 3, 4);
        scene.add(light1);

        const light2 = new THREE.PointLight(0x88aaff, 1, 10);
        light2.position.set(-3, 1, 2);
        scene.add(light2);

        const light3 = new THREE.PointLight(0xffffff, 0.5);
        light3.position.set(0, 2, -5);
        scene.add(light3);

        const gridHelper = new THREE.GridHelper(10, 20, 0x888888, 0x444444);
        scene.add(gridHelper);

        // --- Crear objetos interactivos ---
        // Los guardaremos en un array para el raycaster
        const objects = [];

        // Cubo multicolor (con materiales por cara)
        const cubeMaterials = [
            new THREE.MeshStandardMaterial({ color: 0xff3333 }), // rojo
            new THREE.MeshStandardMaterial({ color: 0x33ff33 }), // verde
            new THREE.MeshStandardMaterial({ color: 0x3333ff }), // azul
            new THREE.MeshStandardMaterial({ color: 0xffff33 }), // amarillo
            new THREE.MeshStandardMaterial({ color: 0xff33ff }), // magenta
            new THREE.MeshStandardMaterial({ color: 0x33ffff })  // cian
        ];
        const cubeGeometry = new THREE.BoxGeometry(1, 1, 1);
        const cube = new THREE.Mesh(cubeGeometry, cubeMaterials);
        cube.position.set(-1.5, 0.5, 0);
        cube.userData = { type: 'Cubo', info: 'Cubo multicolor' };
        scene.add(cube);
        objects.push(cube);

        // Esfera metálica
        const sphereGeometry = new THREE.SphereGeometry(0.8, 64, 32);
        const sphereMaterial = new THREE.MeshStandardMaterial({
            color: 0x44aaff,
            roughness: 0.2,
            metalness: 0.8,
            emissive: 0x112233
        });
        const sphere = new THREE.Mesh(sphereGeometry, sphereMaterial);
        sphere.position.set(1.5, 0.8, -0.5);
        sphere.userData = { type: 'Esfera', info: 'Esfera metálica' };
        scene.add(sphere);
        objects.push(sphere);

        // Toro (donut)
        const torusGeometry = new THREE.TorusGeometry(0.9, 0.3, 32, 64);
        const torusMaterial = new THREE.MeshStandardMaterial({
            color: 0xffaa44,
            roughness: 0.3,
            metalness: 0.4,
            emissive: 0x331100
        });
        const torus = new THREE.Mesh(torusGeometry, torusMaterial);
        torus.position.set(0, 0.5, 1.2);
        torus.rotation.x = Math.PI / 2;
        torus.rotation.z = 0.3;
        torus.userData = { type: 'Toro', info: 'Donut brillante' };
        scene.add(torus);
        objects.push(torus);

        // Añadimos unas esferas pequeñas decorativas (no seleccionables o sí, para probar)
        const starMaterial = new THREE.MeshStandardMaterial({ color: 0x99aaff, emissive: 0x224466 });
        for (let i = 0; i < 8; i++) {
            const starGeo = new THREE.SphereGeometry(0.1, 8);
            const star = new THREE.Mesh(starGeo, starMaterial);
            const angle = (i / 8) * Math.PI * 2;
            star.position.set(Math.cos(angle) * 2.5, Math.sin(angle) * 1.2, Math.sin(angle) * 2);
            star.userData = { type: 'Estrella', info: 'Decoración' };
            scene.add(star);
            objects.push(star); // también seleccionables
        }

        // --- Variables para la selección ---
        let selectedObject = null;
        // Para el contorno (outline) usaremos un LineSegments que añadimos como hijo
        // Así se moverá/rotará con el objeto automáticamente.

        // Función para crear un outline (aristas) para un objeto dado
        function createOutline(obj) {
            // Usamos EdgesGeometry para extraer las aristas
            const edges = new THREE.EdgesGeometry(obj.geometry);
            const line = new THREE.LineSegments(edges, new THREE.LineBasicMaterial({ color: 0xffaa00, linewidth: 2 }));
            // line.material.linewidth no es soportado en todos los sistemas, pero lo dejamos
            line.name = 'outline'; // para identificarlo después
            // Lo añadimos como hijo para que herede las transformaciones
            obj.add(line);
            return line;
        }

        function removeOutline(obj) {
            if (obj) {
                const outline = obj.getObjectByName('outline');
                if (outline) obj.remove(outline);
            }
        }

        // Función para actualizar el panel de información con los datos del objeto seleccionado
        function updateInfoPanel(obj) {
            if (!obj) {
                document.getElementById('object-title').innerText = 'Selecciona un objeto';
                document.getElementById('obj-type').innerText = '-';
                document.getElementById('obj-pos').innerText = '(0, 0, 0)';
                document.getElementById('obj-rot').innerText = '(0, 0, 0)';
                document.getElementById('obj-scale').innerText = '(1, 1, 1)';
                document.getElementById('selection-status').innerHTML = '🔍 Ningún objeto seleccionado';
                document.getElementById('info-panel').classList.remove('active');
                return;
            }

            const type = obj.userData.type || 'Desconocido';
            const pos = obj.position;
            const rot = obj.rotation;
            const scale = obj.scale;

            document.getElementById('object-title').innerText = `✨ ${type} seleccionado`;
            document.getElementById('obj-type').innerText = type;
            document.getElementById('obj-pos').innerText = `(${pos.x.toFixed(2)}, ${pos.y.toFixed(2)}, ${pos.z.toFixed(2)})`;
            document.getElementById('obj-rot').innerText = `(${rot.x.toFixed(2)}, ${rot.y.toFixed(2)}, ${rot.z.toFixed(2)})`;
            document.getElementById('obj-scale').innerText = `(${scale.x.toFixed(2)}, ${scale.y.toFixed(2)}, ${scale.z.toFixed(2)})`;
            document.getElementById('selection-status').innerHTML = `🔍 Seleccionado: <span style="color:#ffaa44;">${type}</span>`;
            document.getElementById('info-panel').classList.add('active');

            // Actualizar slider de escala al valor actual (usamos x como referencia, asumimos uniforme)
            document.getElementById('scale-slider').value = scale.x;
        }

        // --- Raycaster para detectar clics ---
        const raycaster = new THREE.Raycaster();
        const mouse = new THREE.Vector2();

        function onClick(event) {
            // Calcular posición del mouse en coordenadas normalizadas (-1 a 1)
            mouse.x = (event.clientX / renderer.domElement.clientWidth) * 2 - 1;
            mouse.y = -(event.clientY / renderer.domElement.clientHeight) * 2 + 1;

            raycaster.setFromCamera(mouse, camera);

            // Intersectar con todos los objetos
            const intersects = raycaster.intersectObjects(objects);

            if (intersects.length > 0) {
                // Tomamos el primer objeto intersectado
                const hit = intersects[0].object;
                
                // Si ya teníamos uno seleccionado, quitamos su outline
                if (selectedObject) {
                    removeOutline(selectedObject);
                }
                
                // Asignamos nuevo seleccionado
                selectedObject = hit;
                createOutline(selectedObject);
                updateInfoPanel(selectedObject);

                // Pequeña animación o feedback: vibrar un poco? no, mejor así.
            } else {
                // Hizo clic en el fondo: deseleccionar
                if (selectedObject) {
                    removeOutline(selectedObject);
                    selectedObject = null;
                    updateInfoPanel(null);
                }
            }
        }

        // Añadir listener de clic en el canvas
        renderer.domElement.addEventListener('click', onClick);

        // --- Funciones para manipular el objeto seleccionado ---
        function moveSelected(dx, dy, dz) {
            if (!selectedObject) return;
            selectedObject.position.x += dx;
            selectedObject.position.y += dy;
            selectedObject.position.z += dz;
            updateInfoPanel(selectedObject);
        }

        function rotateSelected(dx, dy, dz) {
            if (!selectedObject) return;
            selectedObject.rotation.x += dx;
            selectedObject.rotation.y += dy;
            selectedObject.rotation.z += dz;
            updateInfoPanel(selectedObject);
        }

        function scaleSelected(factor) {
            if (!selectedObject) return;
            // Escala uniforme
            selectedObject.scale.x += factor;
            selectedObject.scale.y += factor;
            selectedObject.scale.z += factor;
            // Limitar a valores razonables
            if (selectedObject.scale.x < 0.2) selectedObject.scale.set(0.2, 0.2, 0.2);
            if (selectedObject.scale.x > 3) selectedObject.scale.set(3, 3, 3);
            updateInfoPanel(selectedObject);
            document.getElementById('scale-slider').value = selectedObject.scale.x;
        }

        function setScaleFromSlider(value) {
            if (!selectedObject) return;
            const val = parseFloat(value);
            selectedObject.scale.set(val, val, val);
            updateInfoPanel(selectedObject);
        }

        // --- Vincular botones del panel ---
        // Movimiento
        document.getElementById('move-x-minus').addEventListener('click', () => moveSelected(-0.1, 0, 0));
        document.getElementById('move-x-plus').addEventListener('click', () => moveSelected(0.1, 0, 0));
        document.getElementById('move-y-minus').addEventListener('click', () => moveSelected(0, -0.1, 0));
        document.getElementById('move-y-plus').addEventListener('click', () => moveSelected(0, 0.1, 0));
        document.getElementById('move-z-minus').addEventListener('click', () => moveSelected(0, 0, -0.1));
        document.getElementById('move-z-plus').addEventListener('click', () => moveSelected(0, 0, 0.1));

        // Rotación (en radianes, 0.1 rad ~ 5.7 grados)
        document.getElementById('rot-x-minus').addEventListener('click', () => rotateSelected(-0.1, 0, 0));
        document.getElementById('rot-x-plus').addEventListener('click', () => rotateSelected(0.1, 0, 0));
        document.getElementById('rot-y-minus').addEventListener('click', () => rotateSelected(0, -0.1, 0));
        document.getElementById('rot-y-plus').addEventListener('click', () => rotateSelected(0, 0.1, 0));
        document.getElementById('rot-z-minus').addEventListener('click', () => rotateSelected(0, 0, -0.1));
        document.getElementById('rot-z-plus').addEventListener('click', () => rotateSelected(0, 0, 0.1));

        // Escala con botones
        document.getElementById('scale-down').addEventListener('click', () => scaleSelected(-0.1));
        document.getElementById('scale-up').addEventListener('click', () => scaleSelected(0.1));

        // Escala con slider
        document.getElementById('scale-slider').addEventListener('input', (e) => setScaleFromSlider(e.target.value));

        // --- Animación ---
        function animate() {
            requestAnimationFrame(animate);

            // Pequeña rotación automática de los objetos no seleccionados (opcional)
            // Podríamos rotar solo si no hay selección, pero lo dejamos así para que se vea dinámico
            if (!selectedObject) {
                // Por ejemplo, el cubo y la esfera giran suavemente
                cube.rotation.y += 0.005;
                sphere.rotation.y += 0.01;
                torus.rotation.y += 0.007;
            }

            controls.update();
            renderer.render(scene, camera);
        }

        animate();

        // --- Manejar cambio de tamaño de ventana ---
        window.addEventListener('resize', onWindowResize, false);
        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }

        // Inicialmente ningún objeto seleccionado
        updateInfoPanel(null);
    </script>
</body>
</html>
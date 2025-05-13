<div style="padding: 20px;">
    <h4>Identifica tu problema</h4>
    <form id="form-soporte-reparacion">
        <!-- Pregunta 1 -->
        <div class="pregunta" id="pregunta-1">
            <label for="tipo-problema">¿Qué tipo de problema tienes?</label>
            <select id="tipo-problema" name="tipo-problema" required>
                <option value="">Selecciona una opción</option>
                <option value="mecanica-electronica">1. Mecánica o electrónica del vehículo</option>
                <option value="elementos-policiales">2. Elementos policiales en el vehículo</option>
            </select>
            <button type="button" class="btn-siguiente" data-siguiente="pregunta-2">Siguiente</button>
        </div>

        <!-- Pregunta 2 (solo aparece si selecciona "Elementos policiales en el vehículo") -->
        <div class="pregunta" id="pregunta-2" style="display: none;">
            <label for="categoria-elementos-policiales">¿Qué categoría específica deseas consultar?</label>
            <select id="categoria-elementos-policiales" name="categoria-elementos-policiales" required>
                <option value="">Selecciona una opción</option>
                <option value="acustico">2.1. Acústico</option>
                <option value="luminoso">2.2. Luminoso</option>
                <option value="electrico">2.3. Eléctrico</option>
                <option value="rotulacion">2.4. Rotulación</option>
                <option value="equipamiento">2.5. Equipamiento</option>
            </select>
            <button type="button" class="btn-siguiente" data-siguiente="resultado-busqueda">Buscar Solución</button>
        </div>

        <!-- Resultado de la búsqueda -->
        <div id="resultado-busqueda" style="display: none;">
            <h4>Resultados de la Búsqueda</h4>
            <p id="solucion-encontrada">Buscando en nuestra base de datos...</p>
            <button type="button" id="btn-whatsapp">Abrir Chat en WhatsApp</button>
        </div>
    </form>
</div>

<script>
    // Control del itinerario de preguntas
    document.querySelectorAll('.btn-siguiente').forEach(button => {
        button.addEventListener('click', function () {
            const siguientePregunta = this.dataset.siguiente;

            // Ocultar la pregunta actual
            this.closest('.pregunta').style.display = 'none';

            // Mostrar la siguiente pregunta o resultados
            const siguienteElemento = document.getElementById(siguientePregunta);
            if (siguienteElemento) {
                siguienteElemento.style.display = 'block';
            }
        });
    });

    // Mostrar pregunta 2 solo si selecciona "Elementos policiales en el vehículo"
    document.getElementById('tipo-problema').addEventListener('change', function () {
        const valorSeleccionado = this.value;
        const pregunta2 = document.getElementById('pregunta-2');

        if (valorSeleccionado === 'elementos-policiales') {
            pregunta2.style.display = 'block';
        } else {
            pregunta2.style.display = 'none';
        }
    });
</script>
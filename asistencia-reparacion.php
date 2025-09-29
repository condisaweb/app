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
        </div>

        <!-- Pregunta 2 -->
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
        </div>

        <!-- Resultado de la búsqueda -->
        <div id="resultado-busqueda" style="display: none;">
            <h4>Resultados de la Búsqueda</h4>
            <p id="solucion-encontrada">Buscando en nuestra base de datos...</p>
            <button type="button" id="btn-whatsapp">Abrir Chat en WhatsApp</button>
        </div>
    </form>
</div>
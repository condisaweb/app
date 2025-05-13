<!-- SCRIPT PARA MANEJAR EL MODAL -->
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("modal-asistencia");
    const modalBody = document.getElementById("modal-body");
    const btnAsistencia = document.getElementById("btn-asistencia");
    const btnBack = document.getElementById("btn-back");

    const lightboxOverlay = document.createElement("div");
    lightboxOverlay.classList.add("lightbox-overlay");
    document.body.appendChild(lightboxOverlay);

    // Manejar el lightbox al hacer clic en imágenes
    document.addEventListener("click", function (event) {
        if (event.target.classList.contains("amplificador-img")) {
            event.preventDefault();
            const imgSrc = event.target.parentElement.getAttribute("href");
            lightboxOverlay.innerHTML = `<img src="${imgSrc}" alt="Lightbox Image">`;
            lightboxOverlay.style.display = "flex";
        }
    });

    // Cerrar el lightbox al hacer clic fuera de la imagen
    lightboxOverlay.addEventListener("click", function () {
        this.style.display = "none";
        this.innerHTML = ""; // Limpia el contenido del lightbox
    });

    // Definir el itinerario
    const itinerario = [
        {
            key: "elementos-policiales",
            title: "¿Qué categoría específica deseas consultar?",
            options: [
                { value: "acustico", label: "Acústico" },
                { value: "luminoso", label: "Luminoso" },
                { value: "electrico", label: "Eléctrico" },
                { value: "rotulacion", label: "Rotulación" },
                { value: "equipamiento", label: "Equipamiento" },
            ],
        },
        {
            key: "acustico",
            title: "¿El altavoz emite sonido?",
            options: [
                { value: "no-sonido", label: "No" },
                { value: "si-sonido", label: "Sí" },
            ],
        },
        {
            key: "no-sonido",
            title: "Localizar el amplificador y escuchar si emite sonido cuando activamos la emergencia desde la botonera del vehículo",
            image: "<?php echo esc_url($image_url); ?>", // Imagen obtenida desde ACF
            options: [
                { value: "amplificador-no-sonido", label: "El amplificador no emite sonido" },
                { value: "amplificador-si-sonido", label: "El amplificador emite sonido" },
            ],
        },
        {
            key: "amplificador-no-sonido",
            title: "AVERÍA DEL AMPLIFICADOR. PÓNGASE EN CONTACTO CON NOSOTROS PARA SOLUCIONARLO.",
            message: "Por favor, contacte con nosotros para asistencia.",
            button: { id: "btn-contacto", label: "Contacto" },
        },
        {
            key: "amplificador-si-sonido",
            title: "AVERÍA DEL ALTAVOZ. PÓNGASE EN CONTACTO CON NOSOTROS PARA SOLUCIONARLO.",
            message: "Por favor, contacte con nosotros para asistencia.",
            button: { id: "btn-contacto", label: "Contacto" },
        },
    ];
    console.log("Contenido del itinerario:", itinerario);
    let currentStep = 0;
    const stepHistory = [];

    // Abrir el modal
    btnAsistencia.addEventListener("click", function () {
        currentStep = itinerario.findIndex((step) => step.key === "elementos-policiales");
        stepHistory.length = 0; // Limpiar historial al iniciar
        renderStep(itinerario[currentStep]);
        modal.style.display = "block";
    });

    // Manejar el botón "Ir hacia atrás"
    btnBack.addEventListener("click", function () {
        if (stepHistory.length > 0) {
            const previousStepKey = stepHistory.pop();
            currentStep = itinerario.findIndex((step) => step.key === previousStepKey);
            renderStep(itinerario[currentStep]);
        }
    });

    // Cerrar el modal al hacer clic fuera de él
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    // Renderizar el paso actual
    function renderStep(step) {
    console.log("Renderizando paso:", step); // Muestra todos los datos del paso actual
    console.log("URL de la imagen:", step.image); // Muestra la URL de la imagen si existe

    let html = `<h2>${step.title}</h2>`;

    if (step.image) {
        html += `
            <div class="image-container">
                <a href="${step.image}" class="lightbox-link" data-title="Imagen del Amplificador">
                    <img src="${step.image}" alt="Imagen del Amplificador" class="amplificador-img">
                </a>
            </div>
        `;
    }

    if (step.options) {
        html += `<div class="card-group">`;
        step.options.forEach((option) => {
            html += `
                <div class="card" data-value="${option.value}">
                    <h4>${option.label}</h4>
                </div>`;
        });
        html += `</div>`;
    }

    if (step.message) {
        html += `<p>${step.message}</p>`;
    }

    if (step.button) {
        html += `<button type="button" id="${step.button.id}">${step.button.label}</button>`;
    }

    modalBody.innerHTML = html;

    // Mostrar u ocultar el botón "Ir hacia atrás"
    btnBack.style.display = stepHistory.length > 0 ? "inline-block" : "none";

    // Agregar listeners a las nuevas tarjetas
    if (step.options) {
        const cards = document.querySelectorAll(".card");
        cards.forEach((card) => {
            card.addEventListener("click", function () {
                const value = card.getAttribute("data-value");
                goToNextStep(value);
            });
        });
    }

    // Agregar listener al botón (si existe)
    if (step.button) {
        const button = document.getElementById(step.button.id);
        if (button) {
            button.addEventListener("click", function () {
                alert("Redirigiendo a contacto...");
            });
        }
    }
}

    // Avanzar al siguiente paso
    function goToNextStep(selectedValue) {
        const nextStep = itinerario.find((step) => step.key === selectedValue);

        if (nextStep) {
            stepHistory.push(itinerario[currentStep].key); // Guardar el paso actual en el historial
            currentStep = itinerario.findIndex((step) => step.key === selectedValue);
            renderStep(nextStep);
        } else {
            console.error("No se encontró el siguiente paso para:", selectedValue);
        }
    }
});

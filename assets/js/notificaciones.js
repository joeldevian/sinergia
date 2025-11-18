/**
 * Muestra una notificación estilo "toast" en la pantalla.
 * 
 * @param {string} mensaje El mensaje a mostrar.
 * @param {string} tipo El tipo de notificación ('success', 'danger', 'warning', 'info'). Determina el color.
 */
function mostrarNotificacion(mensaje, tipo) {
    let backgroundColor;

    switch (tipo) {
        case 'success':
            backgroundColor = "linear-gradient(to right, #00b09b, #96c93d)";
            break;
        case 'danger':
            backgroundColor = "linear-gradient(to right, #ff5f6d, #ffc371)";
            break;
        case 'warning':
            backgroundColor = "linear-gradient(to right, #f17302, #f1c40f)";
            break;
        case 'info':
            backgroundColor = "linear-gradient(to right, #00c6ff, #0072ff)";
            break;
        default:
            backgroundColor = "#333"; // Color por defecto
    }

    Toastify({
        text: mensaje,
        duration: 3000, // 3 segundos
        close: true,
        gravity: "top", // `top` or `bottom`
        position: "right", // `left`, `center` or `right`
        stopOnFocus: true, // Previene que se cierre al pasar el mouse
        style: {
            background: backgroundColor,
        },
        onClick: function(){} // Callback after click
    }).showToast();
}

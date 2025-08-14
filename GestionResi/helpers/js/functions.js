/**
 * Obtener la URL a la cual se harán las peticiones con AJAX.
 *
 * @param   {string} path   Ruta del archivo PHP al cual se harán las peticiones.
 * @returns {object}        Objeto instancia de `URL`.
 *
 * @example getUrl("/AdmConvenio/php/AdmConvenio.php")
 */
function getUrl(path = '/') {
    const protocol = window.location.protocol
    const host = window.location.hostname
    const port = window.location.port
    const urlLocal = window.location.href.toLowerCase().split('/')
    const carpetaUrl = urlLocal.includes('Gestion De Residencias Profesionales')
        ? 'Gestion De Residencias Profesionales/bttescha'
        : urlLocal.includes('bttescha')
        ? 'bttescha'
        : ''

    // Construir la URL base
    const baseURL = `${protocol}//${host}${port ? ':' + port : ''}`
    const finalPath = carpetaUrl + path

    // Construir la URL completa para hacer peticiones
    return new URL(finalPath, baseURL)
}

/**
 * Crea una alerta de éxito o error.
 * @param {string} mensaje Mensaje que contendrá la alerta.
 * @param {string} clase Clase de Bootstrap. Por ej: `alert-success`.
 * @returns Elemento HTML listo para agregarse al DOM.
 */
function crearAlerta(mensaje, clase) {
    const alerta = document.createElement('DIV')
    alerta.classList.add('alert', clase, 'text-center', 'mt-2')
    alerta.role = 'alert'
    alerta.textContent = mensaje

    return alerta
}

/**
 * Formatea una fecha a una cadena legible para el usuario.
 * Si no se envía el segundo parámetro, este por defecto será `short`, lo cual
 * retornará el nombre del mes a 3 letras. Por ej. `dic`. Si se le envía `long`,
 * retornará el nombre del mes completo. Por ej. `diciembre`.
 *
 * @param {string} fecha Fecha con cualquier formato. Por ej.: `2023-12-17`.
 * @param {string} mes `long` (opcional).
 * @returns Fecha formateada en español.
 *
 * @example
 * formatearFecha('2023-12-17') // '17 dic 2023'
 * formatearFecha('2023-12-17', 'long') // '17 de diciembre de 2023'
 */
function formatearFecha(fecha, mes = 'short') {
    const date = new Date(fecha)

    return date.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: mes,
        day: 'numeric',
    })
}

export { getUrl, crearAlerta, formatearFecha }

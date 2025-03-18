import { getUrl, crearAlerta } from '../../helpers/js/functions.js'

/**
 * URL a la que se realizarán peticiones HTTP.
 */
const API_URL = getUrl('resi/GestionResi/auth/php/login.php')

/**
 * URL por defecto a la que redireccionará una vez que se inicie sesión.
 */
const URL_USUARIO = {
    Alumno: '/index.php',
    Profesor: '/profesor/index.php',
}

const form = $('#formLogin')
const submitButton = form.children('button[type="submit"]')

/**
 * Valida que los valores no estén vacíos.
 * Si están vacíos (ambos o solo uno) crea una alerta.
 *
 * @param {string} user     Matrícula del usuario.
 * @param {string} password Contraseña.
 * @returns                 `true` si ambos campos están llenos o `false` si uno
 *                          o ambos campos están vacíos.
 */
function validarFormulario(user, password) {
    $('.alerta-login').remove()

    if (!user || !password) {
        submitButton.text('Iniciar sesión')

        const alerta = crearAlerta(
            'Todos los campos son obligatorios',
            'alert-danger'
        )
        alerta.classList.add('mx-auto', 'alerta-login', 'mt-4')
        $('main').append(alerta)

        /**
         * Eliminar alerta después de 3s.
         */
        setTimeout(() => {
            $('.alerta-login').remove()
        }, 3000)

        return false
    }

    return true
}

form.submit(function (e) {
    e.preventDefault()

    /**
     * Obtener los valores.
     */
    const user = $('#user').val().trim()
    const password = $('#password').val().trim()

    /**
     * Detener la ejecución si los campos están vacíos.
     */
    if (!validarFormulario(user, password)) return

    /**
     * Cambiar el texto del botón a 'Iniciando sesión' cuando se dé
     * clic en él.
     */
    submitButton.text('Iniciando sesión...')

    /**
     * Información que se va a enviar en la petición.
     */
    const data = {
        case: 'login',
        data: { user, password },
    }

    $.ajax({
        url: API_URL,
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        dataType: 'json',
    })
        .done(function (res) {
            if (!res.ok) {
                alert(res.mensaje)
                submitButton.text('Iniciar sesión')
                return
            }

            const { redirect, tipo_usuario, user_info } = res.data

            /**
             * Guardar en localStorage los datos necesarios.
             */
            localStorage.setItem('user_info', JSON.stringify(user_info))

            /**
             * Redireccionar a la URL proporcionada si existe.
             */
            location.href = redirect || (getUrl() + URL_USUARIO[tipo_usuario])
        })
        .fail(function (err) {
            const error = err.responseJSON || { mensaje: 'Error desconocido' }
            alert(error.mensaje)
            submitButton.text('Iniciar sesión')
        })
})

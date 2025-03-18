$(document).ready(function () {
    $('#btnSubir').on('click', function () {
        var file = $('#archivo')[0].files[0];

        if (!file) {
            alert('Por favor, selecciona un archivo.');
            return;
        }

        var formData = new FormData();
        formData.append('archivo', file);

        $.ajax({
            url: 'procesar_carga_alumnos.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                var data = JSON.parse(response);
                $('#mensaje').html(`<div class="alert alert-${data.success ? 'success' : 'danger'}">${data.success || data.error}</div>`);
            },
            error: function () {
                $('#mensaje').html('<div class="alert alert-danger">Error al procesar el archivo.</div>');
            }
        });
    });

    $('#formAgregarAlumno').on('submit', function (e) {
        e.preventDefault();

        var formData = {
            matricula: $('#matricula').val(),
            nombre: $('#nombre').val(),
            apellidoPaterno: $('#apellidoPaterno').val(),
            apellidoMaterno: $('#apellidoMaterno').val(),
            password: $('#password').val()
        };

        $.ajax({
            url: 'procesar_registro_alumno.php',
            type: 'POST',
            data: formData,
            success: function (response) {
                var data = JSON.parse(response);
                $('#mensaje-modal').html(`<div class="alert alert-${data.success ? 'success' : 'danger'}">${data.success || data.error}</div>`);
                if (data.success) {
                    $('#formAgregarAlumno')[0].reset();
                }
            },
            error: function () {
                $('#mensaje-modal').html('<div class="alert alert-danger">Error al registrar el alumno.</div>');
            }
        });
    });
});

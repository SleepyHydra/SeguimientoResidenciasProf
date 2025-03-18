<?php
// footer.php

// Fecha actual con año
$year = date("Y");
?>

<!-- Footer -->
<footer style="background-color: #fff; color: #8a2036; padding: 15px 10px; font-size: 0.8em; border-top: 4px solid #8a2036; margin-top: 50px;">
    <div style="display: flex; flex-direction: column; align-items: center; text-align: center; gap: 8px;">
        <!-- Tercera fila (Copyright) -->
        <div style="width: 100%; text-align: center; margin-bottom: 10px;">
            <p style="margin: 3px 0;">&copy; <?php echo $year; ?> Tecnológico de Estudios Superiores de Chalco (TESCHA). Todos los derechos reservados.</p>
        </div>

        <!-- Primera fila -->
        <div style="width: 100%; display: flex; justify-content: space-between;">
            <!-- Contacto -->
            <div style="flex: 1; min-width: 220px; border-right: 1px solid rgba(138, 32, 54, 0.2); padding-right: 10px;">
                <h4 style="color: #8a2036; font-size: 1em; margin-bottom: 8px;">Contacto</h4>
                <p style="margin: 2px 0;">Carretera Federal México Cuautla s/n</p>
                <p style="margin: 2px 0;">Chalco, Estado de México</p>
                <p style="margin: 2px 0;">Teléfonos: (0155) 59820848 y 59821088</p>
                <p style="margin: 2px 0;">Correo: <a href="mailto:dir.general@tesch.edu.mx" style="color: #8a2036; text-decoration: none;">dir.general@tesch.edu.mx</a></p>
                <p style="margin: 2px 0;">Facebook: <a href="https://www.facebook.com/TESCHAOficial/" target="_blank" style="color: #8a2036; text-decoration: none;">TESCHA</a></p>
            </div>
            <!-- Acerca del sitio -->
            <div style="flex: 1; min-width: 220px; padding-left: 10px;">
                <h4 style="color: #8a2036; font-size: 1em; margin-bottom: 8px;">Acerca del Sitio</h4>
                <p style="margin: 2px 0;">Esta plataforma es para la gestión de residencias profesionales.</p>
                <p style="margin: 2px 0;">
                    <a href="https://tescha.edomex.gob.mx/legales" style="color: #8a2036; text-decoration: none;">Avisos legales</a>
                </p>
            </div>
        </div>

        <!-- Segunda fila -->
        <div style="width: 100%; text-align: center; padding: 8px 0; border-top: 1px solid rgba(138, 32, 54, 0.2); margin-top: 8px;">
            <h4 style="color: #8a2036; font-size: 1.1em; margin-bottom: 10px;">Equipo de desarrollo</h4>
            <p style="margin: 3px 0;">
                <a href="/resi/GestionResi/includes/developers.php" style="color: #8a2036; text-decoration: none;">Conoce al equipo de desarrollo</a>
            </p>
        </div>
    </div>
</footer>

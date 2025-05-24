<?php
if (!defined('ABSPATH')) {
    exit;
}

// Función para leer correos y crear reservas
 function leer_correos_ssa_y_crear_reservas() {
    $mailbox = '{imap.hostinger.com:993/imap/ssl}INBOX';
    $usuario = IMAP_USER;
    $password = IMAP_PASS;

    // Conectar al buzón de correo
    $inbox = imap_open($mailbox, $usuario, $password) or die('Error al conectar a mail: ' . imap_last_error());

    // Buscar correos de SSA
    $emails = imap_search($inbox, 'FROM "wordpress@rdtecnobelleza.net" SUBJECT "ha realizado una reserva"');

    if ($emails) {
        error_log("Se estan buscando emails...");
        foreach ($emails as $email_number) {
            $overview = imap_fetch_overview($inbox, $email_number, 0);
            $message = imap_fetchbody($inbox, $email_number, 1.2); // Intentar obtener el cuerpo en texto plano
            
             if (empty($message)) {
                $message = imap_fetchbody($inbox, $email_number, 1); // Obtenerlo en otro formato si es necesario
            }
            
            // Después de obtener $message:
            $structure = imap_fetchstructure($inbox, $email_number);

            // Verificar codificación (0=7bit, 1=8bit, 2=binary, 3=base64, 4=quoted-printable)
            if (isset($structure->parts[0]) && $structure->parts[0]->encoding == 4) {
                $message = quoted_printable_decode($message);
            } elseif (isset($structure->parts[0]) && $structure->parts[0]->encoding == 3) {
                $message = imap_base64($message);
            }
            
            // Convertir <br> en saltos de línea y eliminar otras etiquetas
            $message = str_replace(["<br />", "<br>"], "\n", $message);
            $message = strip_tags($message);
            $message = html_entity_decode($message); // Convertir entidades como &amp;


            // Extraer datos del mensaje con expresiones regulares
            preg_match('/Name:\s*(.+?)(\n|$)/i', $message, $nombre);

            // Para Email (extrae el valor del mailto o texto):
            preg_match('/Email:\s*(?:<a[^>]+>)?([a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i', $message, $email);

            // Para Phone (admite formatos internacionales):
            preg_match('/Phone:\s*(\+?\d[\d() -]+)/', $message, $telefono);
    
            preg_match('/City:\s*(.+?)(\n|$)/i', $message, $ciudad);
            $ciudad = !empty($ciudad[1]) ? trim($ciudad[1]) : 'No especificado';
            
            preg_match('/State:\s*(.+?)(\n|$)/i', $message, $provincia);
            $provincia = !empty($provincia[1]) ? trim($provincia[1]) : 'No especificado';


            // Para la fecha (captura hasta el salto de línea):
            preg_match('/para el día:\s*(.+?)(\n|$)/i', $message, $fecha);            preg_match('/Address:\s*(.+)/i', $message, $direccion);
            
            if (!empty($email[1])) {
                $cliente_email = trim($email[1]);

                if (!empty($nombre[1])) {
                    $cliente_nombre = trim($nombre[1]); // Usa el segundo elemento si existe
                } elseif (!empty($nombre[0])) {
                    $cliente_nombre = trim($nombre[0]); // Usa el primero si solo hay uno
                } else {
                    $cliente_nombre = ''; // En caso de error, asigna una cadena vacía
                }
                $telefono = trim($telefono[1]);
            if(!empty($telefono[1])){
                error_log("Teléfono: $telefono[1]");
                }    
                $direccion = trim($direccion[1]);
                $ciudad = trim($ciudad[1]);
                $provincia = trim($provincia[1]);
                $fecha_alquiler = trim($fecha[1]);
            }

            // Marcar el correo como leído
            imap_setflag_full($inbox, $email_number, "\\Seen");
            
                // Verificar si el usuario ya existe
                $user = get_user_by('email', $cliente_email);

                if (!$user) {
                    // Crear usuario con rol Cliente
                    crear_usuario_desde_reserva($cliente_nombre, $cliente_email, $telefono, $direccion, $ciudad, $provincia);
                } else {
                    $user_id = $user->ID;
                }

//Aquí se agregaba un nuevo alquiler

         } //foreach
        }  //if($emails) 

        imap_close($inbox);

       }//function
?>
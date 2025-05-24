/*
    * @package WordPress
    * @subpackage Hostinger AI Theme Child
    * @since 1.0.0
    * Este botón envía un payload a un webhook específico al hacer clic.
    * Para ejecutar una tarea específica, como un ingreso o egreso financiero.
    * Ayudado por IA en la plataforma N8N.
    * @author RD-Tecno <https://rdtecnobelleza.net>
*/

document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('btn-enviar-finanza');
    button.addEventListener('click', async () => {
      const payload = {
        tipo: 'Egreso',
        fecha: '2025-05-03',
        monto: 50000,
        categoria: 'Combustible',
        descripcion: 'Carga Nafta',
        cantidad_de_disparos: 0,
        metodo_de_pago: 'Efectivo'
      };
  
      try {
        const response = await fetch
        ('https://abac-37-19-221-228.ngrok-free.app/webhook/24fd39bd-bee0-46fd-9b14-d270d42c32d6', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload),
        });
        if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
        const data = await response.json();
        console.log('Respuesta del agente:', data);
        alert('¡Operación ejecutada con éxito!');
      } catch (error) {
        console.error('Error al invocar el webhook:', error);
        alert('Falló la conexión con el agente.');
      }
    });
  });
  
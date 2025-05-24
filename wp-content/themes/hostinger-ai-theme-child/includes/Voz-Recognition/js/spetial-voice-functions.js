/**
 * @license Copyright (c) 2025 RD-Tecno, Ltd.
 * All rights reserved.
 * Funciones especiales para el reconocimiento de voz
 * @author RD-Tecno <
 */

function interpretarComando(texto, idioma) {
    texto = texto.toLowerCase();
    console.log("Texto recibido para interpretar: " + texto);

    const comandos = [
      {
        palabras: ["ingreso", "ingresar", "entrada"],
        respuesta: "Detectado un ingreso. ¿Deseás confirmarlo?",
        accion: "ingreso"
      },
      {
        palabras: ["egreso", "egresar", "gasto", "pago"],
        respuesta: "Detectado un gasto o egreso. ¿Lo confirmamos?",
        accion: "egreso"
      },
      {
        palabras: ["editar", "modificar", "cambiar"],
        respuesta: "Se detectó intención de edición. ¿Qué querés cambiar?",
        accion: "editar"
      },
      {
        palabras: ["eliminar", "borrar", "quitar"],
        respuesta: "¿Confirmás la eliminación del registro?",
        accion: "eliminar"
      },
      {
        palabras: ["ayuda", "asistencia", "soporte"],
        respuesta: "¿Necesitás ayuda con algo específico?",
        accion: "ayuda"
      },
      {
        palabras: ["cancelar", "detener", "parar"],
        respuesta: "Acción cancelada. ¿Querés hacer algo más?",
        accion: "cancelar"
      },
      {
        palabras: ["salir", "cerrar", "terminar"],
        respuesta: "¿Querés salir de la aplicación?",
        accion: "salir"
      },
      {
        palabras: ["gracias", "ok", "perfecto", "bien"],
        respuesta: "De nada. ¿Hay algo más en lo que pueda ayudar?",
        accion: "agradecimiento"
      }
    ];

    const comando = comandos.find(cmd => cmd.palabras.some(palabra => texto.includes(palabra)));
    if (comando) {
      hablar(comando.respuesta, idioma);
  //    esperarConfirmacion(comando.accion); // Llama a la función para esperar confirmación
      return comando.accion;
    }
    hablar("No pude interpretar el comando. Probá de nuevo.", idioma);
    console.log("No pude interpretar el comando. Probá de nuevo.");
    return "No pude interpretar el comando. Probá de nuevo.";
}
window.interpretarComando = interpretarComando; // Hace la función global

function esperarConfirmacion(respuesta, idioma) {
  console.log("Esperando confirmación para: " + respuesta);
    if(!respuesta) return ""; // Si no hay respuesta, no hacemos nada
    respuesta = respuesta.toLowerCase(); // Convertimos la respuesta a minúsculas
    const afirmaciones = ["sí", "ok", "confirmo", "confirmarlo", "confirmar", "dale"];
    const negaciones = ["no", "cancelar", "detener", "parar", "nada"];

    afirmaciones.forEach(afirmacion => {
      if(respuesta.includes(afirmacion)) {
        hablar("Acción confirmada y registrada.", idioma)
        return true;
      }});
    negaciones.forEach(negacion => {
      if(respuesta.includes(negacion)) {
        hablar("Acción cancelada.", idioma)
        return false;
      }});

        hablar("No entendí tu respuesta. Por favor, confirmá o cancelá la acción.", idioma);
        return null;
    // Aquí podrías agregar lógica adicional para manejar la respuesta del usuario
}
window.esperarConfirmacion = esperarConfirmacion; // Hace la función global


function hablar(texto, idioma) {
  const synth = window.speechSynthesis;
  const utterance = new SpeechSynthesisUtterance(texto);
  utterance.lang = idioma; // Establece el idioma de la síntesis de voz
  synth.speak(utterance); // Reproduce el texto interpretado
}
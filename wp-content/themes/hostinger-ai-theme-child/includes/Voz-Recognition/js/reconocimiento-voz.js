
document.addEventListener('DOMContentLoaded', function() {

    const botones = document.querySelectorAll('.boton-reconocimiento');

    botones.forEach(function(boton) {
        
        let escuchando = false;
        let confirmando = false;
        let confirmado
        let respuesta = null;
        let comando = null;
        let confirmacion = null;
        let textoAcumulado = '';
        const textoOriginal = boton.innerText;

        const botonId = boton.id;
        const idBase = botonId.replace('btn_', '');
        const textoElemento = document.getElementById(idBase);
        const idioma = boton.getAttribute('data-idioma') || 'es-AR';

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            textoElemento.innerText = "Tu navegador no soporta Web Speech API.";
            return;
        }
        let reconocimiento = null;
        reconocimiento = new SpeechRecognition();
        reconocimiento.lang = idioma;
        reconocimiento.continuous = true;
        reconocimiento.interimResults = false;
        reconocimiento.onend = reset();

        function reset() {
            escuchando = false;
            textoAcumulado = '';
            boton.innerText = 'Hablar';
            textoElemento.innerText = '';
            console.log("Reconocimiento de voz detenido");
        }

        reconocimiento.addEventListener("audiostart", () => {
            console.log("Audio capturing started");
          });

        reconocimiento.onresult = (event) => {
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    textoAcumulado += (textoAcumulado ? ' ' : '') + event.results[i][0].transcript;
                }
            }
            textoElemento.innerText = `Texto detectado: \"${textoAcumulado}\"`;
          //  reconocimiento.end(); // Detiene el reconocimiento después de recibir el resultado
          
        };

        reconocimiento.onend = () => {
            console.log("Reconocimiento de voz detenido - texto acumulado: " + textoAcumulado);
            escuchando = false;
            if(confirmado) confirmando = false; // Cambia el estado a no confirmando
            flujoRespuestas(textoAcumulado, idioma); // Llama a la función para interpretar el comando
        };
        
        reconocimiento.onerror = (event) => {
            textoElemento.innerText = "Error: " + event.error;
            boton.innerText = textoOriginal;
            escuchando = false;
        };

        reconocimiento.onstart = () => {
            console.log("Reconocimiento de voz iniciado");
            escuchando = true;
            boton.innerText = "Detener";
            textoElemento.innerText = "Escuchando...";
        }

        reconocimiento.soundend = () => {
//Si el usuario no detuvo manualmente, pero el reconocimiento se detuvo por falta de sonido
            if (escuchando && !confirmado) {
                textoElemento.innerText = `Texto final soundend: \"${textoAcumulado}\"`;
                reconocimiento.start();
            }else {
                boton.innerText = textoOriginal;
            }
        };

        boton.addEventListener('click', function() {    

            if (!escuchando) {
                reconocimiento.start();
            } else {
                if (escuchando) {
                    escuchando = false;
                    
                    if (reconocimiento) {
                        reconocimiento.stop();
                     //   textoElemento.innerText = `Texto final escucho: \"${textoAcumulado}\"`;
                    }else {
                        textoElemento.innerText = "No se ha detectado nada.";
                    }
                    if(confirmado){
                        reset(); // Reinicia el reconocimiento si ya se ha confirmado
                    }
                    boton.innerText = textoOriginal;
                }
            }
        });

        function flujoRespuestas(respuesta, idioma) {
       //Si el texto recibido es un comando, lo interpreta y espera confirmación
            if(!confirmando && !confirmado) {
                textoElemento.innerText = "Esperando confirmación...";
                comando = interpretarComando(respuesta, idioma); // Llama a la función para interpretar el comando
                escuchando = true; // Cambia el estado a escuchando para evitar múltiples llamadas
                confirmando = true; // Cambia el estado a confirmando
                reconocimiento.start(); // Reinicia el reconocimiento para esperar la confirmación
                return;
            }
        //Si el texto recibido es una confirmación, lo interpreta y actua en consecuencia
            if(confirmando && !confirmado) {
                //reconocimiento.stop(); // Detiene el reconocimiento
                confirmacion = esperarConfirmacion(textoAcumulado, idioma); // Llama a la función para interpretar la confirmación
                confirmado = true; // Cambia el estado a confirmado
                confirmando = false;
            }
            if(confirmado){
              if(!confirmacion){
                    reset(); // Reinicia el reconocimiento si no hay confirmación
                    return;
                } 
                if(confirmacion) {
                    textoElemento.innerText = "Acción confirmada y registrada.";
                    confirmando = false; // Cambia el estado a no confirmando
                    escuchando = false; // Cambia el estado a no escuchando
                    //reset(); // Reinicia el reconocimiento
                } else if(confirmacion === false) {
                    textoElemento.innerText = "Acción cancelada.";
                    confirmando = false; // Cambia el estado a no confirmando
                    reset(); // Reinicia el reconocimiento
                } else {
                    textoElemento.innerText = "Esperando confirmación...";
                }
                confirmando = false; // Cambia el estado a no confirmando
            }            
        }
    
    });
});
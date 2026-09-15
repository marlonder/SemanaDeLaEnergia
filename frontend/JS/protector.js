const TIEMPO_INACTIVIDAD = 5 * 60 * 1000; // 5 minutos en milisegundos
const protector = document.getElementById('protector-pantalla');

let temporizadorInactividad = null;
let intervaloConsola = null;

function mostrarProtector() {
  protector.classList.add('activo');
  console.log('🖼️ Protector de pantalla ACTIVADO');
}

function ocultarProtector() {
  protector.classList.remove('activo');
}

function reiniciarTemporizador() {
  ocultarProtector();
  clearTimeout(temporizadorInactividad);
  clearInterval(intervaloConsola);

  const inicio = Date.now();

  // 👉 imprime en consola cada segundo cuánto falta para que aparezca el protector
  intervaloConsola = setInterval(() => {
    const transcurrido = Date.now() - inicio;
    const restante = Math.max(TIEMPO_INACTIVIDAD - transcurrido, 0);
    const segundosRestantes = Math.ceil(restante / 1000);
    console.log(`⏳ Inactividad: faltan ${segundosRestantes}s para mostrar el protector`);

    if (restante <= 0) {
      clearInterval(intervaloConsola);
    }
  }, 1000);

  temporizadorInactividad = setTimeout(mostrarProtector, TIEMPO_INACTIVIDAD);
}

// Cualquiera de estos eventos cuenta como "actividad"
['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'wheel']
  .forEach(evento => {
    document.addEventListener(evento, reiniciarTemporizador);
  });

// Al tocar/hacer clic en el protector, se oculta y reinicia el conteo
protector.addEventListener('click', reiniciarTemporizador);
protector.addEventListener('touchstart', reiniciarTemporizador);

// Arranca el temporizador apenas carga la página
reiniciarTemporizador();
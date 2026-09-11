const contenedor = document.getElementById('contenedor-mapa');
const wrapperMapa = document.querySelector('.mapa-wrapper');

const ANCHO = 550;
const ALTO = 750;

const CARIBE_PUNTO = [-75, 19];


let svgPrincipal = null;
let grupoMapa = null;      // 👉 NUEVO: <g> que se transforma con el zoom
let zoomMapa = null;       // 👉 NUEVO: comportamiento de zoom de d3
let proyeccionMapa = null; // 👉 NUEVO: guardamos proyección y path para
let pathMapa = null;       //    poder calcular el zoom al Caribe después
let datosCaribe = null;    // 👉 NUEVO: geojson del Caribe, para calcular límites
let datosContinental = null;



// Busca el centro de un país, ya sea continental, del Caribe (polígono) o isla-punto
function obtenerCentroidePorNombre(nombre) {
  const featCont = datosContinental.features.find(f => f.properties.name === nombre);
  if (featCont) return pathMapa.centroid(featCont);

  const featCar = datosCaribe.features.find(f => f.properties.name === nombre);
  if (featCar) return pathMapa.centroid(featCar);

  const islaPunto = (window.ISLAS_PUNTO || []).find(i => i.name === nombre);
  if (islaPunto) return proyeccionMapa(islaPunto.coords);

  return null;
}

// Dibuja un pin verde sobre cada país con proyectos
function marcarPaisesConProyectos(nombres) {
  grupoMapa.selectAll('.capa-marcadores-proyecto').remove();

  const capa = grupoMapa.append('g').attr('class', 'capa-marcadores-proyecto');

  const ESCALA_PIN = 0.4; // 👈 ajusta este número para hacerlo más grande/chico

  nombres.forEach(nombre => {
    const centro = obtenerCentroidePorNombre(nombre);
    if (!centro) {
      console.warn(`No se pudo ubicar "${nombre}" para el marcador.`);
      return;
    }
    const [x, y] = centro;

    const grupoPin = capa.append('g')
      .attr('class', 'marcador-proyecto-grupo')
      .attr('transform', `translate(${x}, ${y}) scale(${ESCALA_PIN})`)
      .style('cursor', 'pointer')
      .on('click', function (evento) {
        evento.stopPropagation();
        abrirModal(nombre);
      });

    grupoPin.append('path')
      .attr('class', 'marcador-proyecto')
      .attr('data-name', nombre)
      .attr('d', 'M0,-14 C-7,-14 -12,-9 -12,-2 C-12,7 0,14 0,14 C0,14 12,7 12,-2 C12,-9 7,-14 0,-14 Z')
      .style('fill', '#2ecc71')
      .style('stroke', '#1e8449')
      .style('stroke-width', 1);

    grupoPin.append('circle')
      .attr('cx', 0)
      .attr('cy', -5)
      .attr('r', 4)
      .style('fill', 'white')
      .style('pointer-events', 'none');
  });
}

// Pide al backend la lista de países con proyectos y los marca
async function cargarPaisesConProyectos() {
  try {
    const respuesta = await fetch(`${API_BASE_URL}/paises_con_proyectos.php`);
    const paises = await respuesta.json(); // ej: ["Ecuador", "Santa Lucía"]
    marcarPaisesConProyectos(paises);
  } catch (err) {
    console.error('No se pudo cargar la lista de países con proyectos', err);
  }
}

async function cargarMapa() {
  const [continental, caribe] = await Promise.all([
    d3.json('DATA/america_unida.geo.json'),
    d3.json('DATA/caribe.geo.json')
  ]);

  
  datosCaribe = caribe;
  datosContinental = continental;

  // le pasamos los datos del Caribe a modal.js para que ya los tenga listos
  // (evita que modal.js tenga que volver a pedirlos por red)
  if (window.inicializarDatosCaribe) {
    window.inicializarDatosCaribe(caribe);
  }

  const proyeccion = d3.geoMercator().fitSize([ANCHO, ALTO], continental);
  const path = d3.geoPath(proyeccion);
  proyeccionMapa = proyeccion;
  pathMapa = path;

  const svg = d3.select(contenedor)
    .append('svg')
    .attr('viewBox', `0 0 ${ANCHO} ${ALTO}`);

  svgPrincipal = svg;

  // 👉 NUEVO: todo el contenido del mapa va dentro de este <g>, que es
  // el que se mueve/escala cuando hacemos zoom. El <svg> nunca cambia.
  grupoMapa = svg.append('g').attr('class', 'grupo-zoom-mapa');

  // 👉 NUEVO: zoom/pan sobre el mapa principal. Lo dejamos entre 1x (normal)
  // y 8x (bien cerca), y limitamos cuánto se puede arrastrar fuera del área.
  zoomMapa = d3.zoom()
  .scaleExtent([1, 8])
  .translateExtent([[0, 0], [ANCHO, ALTO]])
  .filter(() => false) 
  .on('zoom', (evento) => {
    grupoMapa.attr('transform', evento.transform);
  });

  svg.call(zoomMapa);

  grupoMapa.selectAll('path')
    .data(continental.features)
    .join('path')
    .attr('id', d => d.properties.id)
    .attr('data-name', d => d.properties.name)
    .attr('d', path)
    .on('click', function (evento, d) {
      svg.selectAll('.seleccionado').classed('seleccionado', false);
      this.classList.add('seleccionado');
      cerrarPopoverCaribe();
      abrirModal(d.properties.name);
    });

    

    grupoMapa.selectAll('.pais-caribe')
      .data(caribe.features)
      .join('path')
      .attr('class', 'pais-caribe')
      .attr('id', d => d.properties.id)
      .attr('data-name', d => d.properties.name)
      .attr('d', path);grupoMapa.selectAll('.pais-caribe')
      .data(caribe.features)
      .join('path')
      .attr('class', 'pais-caribe')
      .attr('id', d => d.properties.id)
      .attr('data-name', d => d.properties.name)
      .attr('d', path)
      .on('click', function (evento, d) {
      evento.stopPropagation();
      svgPrincipal.selectAll('.seleccionado').classed('seleccionado', false);
      this.classList.add('seleccionado');
      cerrarPopoverCaribe();
      abrirModal(d.properties.name);
    });




  // --- Caribe: solo el texto clickeable (sin cambios) ---
  const [cx, cy] = proyeccion(CARIBE_PUNTO);

  grupoMapa.append('text')
    .attr('id', 'etiqueta-caribe')
    .attr('class', 'punto-caribe-label')
    .attr('x', cx)
    .attr('y', cy)
    //.text('Caribe')
    .on('click', function (evento) {
      evento.stopPropagation();
      const { x, y } = posicionPopoverDesdeEtiqueta();
      abrirPopoverCaribe(x, y);
    });

  // Ya con los dos geojson cargados, armamos el listado de países
  construirListaPaises(continental, caribe);
  cargarPaisesConProyectos();
}

// Calcula dónde debe aparecer el popover, tomando como referencia
// la posición real en pantalla de la etiqueta "Caribe"
function posicionPopoverDesdeEtiqueta() {
  const etiqueta = document.getElementById('etiqueta-caribe');
  const rectEtiqueta = etiqueta.getBoundingClientRect();
  const rectWrapper = wrapperMapa.getBoundingClientRect();

  return {
    x: rectEtiqueta.left - rectWrapper.left,
    y: rectEtiqueta.top - rectWrapper.top
  };
}

// =====================================================
// 👉 NUEVO: zoom del mapa principal hacia la región del Caribe
// =====================================================

// Calcula, en píxeles del mapa principal, el rectángulo que envuelve
// a todas las islas del Caribe (polígonos + puntos).
function calcularLimitesCaribe() {
  const puntos = [];

  (datosCaribe.features || []).forEach(feature => {
    const [[x0, y0], [x1, y1]] = pathMapa.bounds(feature);
    puntos.push([x0, y0], [x1, y1]);
  });

  const islasPunto = window.ISLAS_PUNTO || [];
  islasPunto.forEach(isla => {
    puntos.push(proyeccionMapa(isla.coords));
  });

  const xs = puntos.map(p => p[0]);
  const ys = puntos.map(p => p[1]);

  return {
    x0: Math.min(...xs),
    x1: Math.max(...xs),
    y0: Math.min(...ys),
    y1: Math.max(...ys)
  };
}

// Anima el zoom del <svg> principal hacia un rectángulo (en píxeles).
// "onFin" se ejecuta cuando termina la transición (útil para abrir
// el popover ya con el mapa en su posición final).
function zoomARectangulo(bounds, margen, onFin) {
  const ancho = Math.max(bounds.x1 - bounds.x0, 1);
  const alto = Math.max(bounds.y1 - bounds.y0, 1);

  const escala = Math.min(
    zoomMapa.scaleExtent()[1],
    (ANCHO - margen * 2) / ancho,
    (ALTO - margen * 2) / alto
  );

  const centroX = (bounds.x0 + bounds.x1) / 2;
  const centroY = (bounds.y0 + bounds.y1) / 2;

  const transform = d3.zoomIdentity
    .translate(ANCHO / 2, ALTO / 2)
    .scale(escala)
    .translate(-centroX, -centroY);

  svgPrincipal.transition()
    .duration(700)
    .call(zoomMapa.transform, transform)
    .on('end', () => {
      if (onFin) onFin();
    });
}

// Hace zoom hacia toda la región del Caribe.
function zoomACaribe(onFin) {
  zoomARectangulo(calcularLimitesCaribe(), 24, onFin);
}

// Regresa el mapa a la vista normal (sin zoom).
function resetZoomMapa() {
  svgPrincipal.transition()
    .duration(400)
    .call(zoomMapa.transform, d3.zoomIdentity);
}

function normalizarTexto(txt) {
  return txt
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');
}

// --- Listado / búsqueda de países ---
function construirListaPaises(continental, caribe) {
  const listaEl = document.getElementById('lista-paises');
  const inputBuscar = document.getElementById('buscar-pais');
  const btnLimpiar = document.getElementById('btn-limpiar-busqueda');
  const wrapper = document.querySelector('.panel-paises__buscar-wrapper');
  if (!listaEl) return;

  const nombresContinental = continental.features.map(d => ({
    nombre: d.properties.name,
    tipo: 'continental'
  }));

  const nombresCaribePoligonos = caribe.features.map(d => ({
    nombre: d.properties.name,
    tipo: 'caribe'
  }));

  const nombresCaribePuntos = (window.ISLAS_PUNTO || []).map(isla => ({
    nombre: isla.name,
    tipo: 'caribe'
  }));

  const todosLosPaises = [
    ...nombresContinental,
    ...nombresCaribePoligonos,
    ...nombresCaribePuntos
  ].sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'));

  // 👉 índice del item resaltado con las flechas
  let indiceActivo = -1;

  function renderItems(paises) {
    listaEl.innerHTML = paises.map(p => `
      <li class="lista-paises__item" data-nombre="${p.nombre}" data-tipo="${p.tipo}">
        ${p.nombre}
      </li>
    `).join('');

    listaEl.querySelectorAll('.lista-paises__item').forEach(item => {
      item.addEventListener('click', () => {
        seleccionarPaisDesdeLista(item.dataset.nombre, item.dataset.tipo);
      });
    });

    indiceActivo = -1;
  }

  function pintarLista(filtro = '') {
    if (!filtro.trim()) {
      listaEl.innerHTML = '';
      return;
    }
    const filtroNorm = normalizarTexto(filtro);
    const paisesFiltrados = todosLosPaises.filter(p =>
      normalizarTexto(p.nombre).includes(filtroNorm)
    );
    renderItems(paisesFiltrados);
  }

  // 👉 NUEVO: al hacer foco con el campo vacío, muestra todos los países
  function mostrarTodos() {
    renderItems(todosLosPaises);
  }

  function actualizarActivo(items) {
    items.forEach((item, i) => item.classList.toggle('activo', i === indiceActivo));
    if (items[indiceActivo]) {
      items[indiceActivo].scrollIntoView({ block: 'nearest' });
    }
  }

  pintarLista(); // arranca vacía

  if (inputBuscar) {
    inputBuscar.addEventListener('input', (e) => {
      pintarLista(e.target.value);
      if (wrapper) wrapper.classList.toggle('tiene-texto', e.target.value.trim().length > 0);
    });

    // 👉 NUEVO: mostrar todos al hacer foco si está vacío
    inputBuscar.addEventListener('focus', () => {
      if (!inputBuscar.value.trim()) {
        mostrarTodos();
      }
    });

    // 👉 NUEVO: navegación con teclado, reusando seleccionarPaisDesdeLista
    inputBuscar.addEventListener('keydown', (e) => {
      const items = listaEl.querySelectorAll('.lista-paises__item');
      if (!items.length) return;

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        indiceActivo = (indiceActivo + 1) % items.length;
        actualizarActivo(items);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        indiceActivo = (indiceActivo - 1 + items.length) % items.length;
        actualizarActivo(items);
      } else if (e.key === 'Enter') {
        e.preventDefault();
        if (indiceActivo >= 0 && items[indiceActivo]) {
          const item = items[indiceActivo];
          seleccionarPaisDesdeLista(item.dataset.nombre, item.dataset.tipo);
        }
      } else if (e.key === 'Escape') {
        listaEl.innerHTML = '';
        indiceActivo = -1;
      }
    });
  }

  if (btnLimpiar) {
    btnLimpiar.addEventListener('click', limpiarBusqueda);
  }
}

function seleccionarPaisDesdeLista(nombre, tipo) {
  const inputBuscar = document.getElementById('buscar-pais');
  const listaEl = document.getElementById('lista-paises');
  const wrapper = document.querySelector('.panel-paises__buscar-wrapper');

  if (inputBuscar) inputBuscar.value = nombre;
  if (listaEl) listaEl.innerHTML = '';               // 👈 oculta el listado
  if (wrapper) wrapper.classList.add('tiene-texto');  // 👈 mantiene visible la "X"

  if (tipo === 'caribe') {
    cerrarPopoverCaribe();
    svgPrincipal.selectAll('.seleccionado').classed('seleccionado', false);

    const seleccionado = svgPrincipal.selectAll('path.pais-caribe')
      .filter(function () {
        return this.getAttribute('data-name') === nombre;
      });

    if (!seleccionado.empty()) {
      seleccionado.classed('seleccionado', true);
    }

    zoomACaribe(() => {
      const { x, y } = posicionPopoverDesdeEtiqueta();
      abrirPopoverCaribe(x, y, nombre);
    });
  } else {
    cerrarPopoverCaribe();
    resetZoomMapa();
    svgPrincipal.selectAll('.seleccionado').classed('seleccionado', false);

    const seleccionado = svgPrincipal.selectAll('path')
      .filter(function () {
        return this.getAttribute('data-name') === nombre;
      });

    if (!seleccionado.empty()) {
      seleccionado.classed('seleccionado', true);
    } else {
      console.warn(`No se encontró "${nombre}" en el mapa continental.`);
    }
  }
}

// 👉 FIX 3: limpia el buscador y cancela selección/zoom/popover
function limpiarBusqueda() {
  const inputBuscar = document.getElementById('buscar-pais');
  const listaEl = document.getElementById('lista-paises');
  const wrapper = document.querySelector('.panel-paises__buscar-wrapper');

  if (inputBuscar) inputBuscar.value = '';
  if (listaEl) listaEl.innerHTML = '';
  if (wrapper) wrapper.classList.remove('tiene-texto');

  cerrarPopoverCaribe();
  resetZoomMapa();
  svgPrincipal.selectAll('.seleccionado').classed('seleccionado', false);
}




cargarMapa();


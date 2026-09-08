// --- Modal de info de país (sin cambios) ---
const modalOverlay = document.getElementById('modal-overlay');
const modalCerrar = document.getElementById('modal-cerrar');
const modalTitulo = document.getElementById('info-pais');
const modalContenido = document.getElementById('info-contenido');

function abrirModal(nombrePais) {
  modalTitulo.textContent = `Proyectos de ${nombrePais}`;
  modalContenido.textContent = 'Aún no hay contenido cargado para este país.';
  modalContenido.classList.add('vacio');
  modalOverlay.classList.add('activo');
}
function cerrarModal() {
  modalOverlay.classList.remove('activo');
}
modalCerrar.addEventListener('click', cerrarModal);
modalOverlay.addEventListener('click', (e) => {
  if (e.target === modalOverlay) cerrarModal();
});

// =====================================================
// 👉 NUEVO: datos inventados (luego vienen de una BD)
// =====================================================
const proyectosPorPais = {
  Ecuador: [
    {
      nombre: 'Agua Limpia Andina',
      descripcion: 'Proyecto de saneamiento y acceso a agua potable en comunidades rurales de la sierra ecuatoriana.',
      categoria: 'Ambiental',
      foto: 'https://picsum.photos/seed/agua/400/250',
      qr: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=AguaLimpiaAndina'
    },
    {
      nombre: 'Educación para Todos',
      descripcion: 'Iniciativa de becas y material escolar para niños en zonas de difícil acceso.',
      categoria: 'Educación',
      foto: 'https://picsum.photos/seed/educacion/400/250',
      qr: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=EducacionParaTodos'
    },
    {
      nombre: 'Reforestando Ecuador',
      descripcion: 'Programa de reforestación en zonas afectadas por la deforestación en la Amazonía.',
      categoria: 'Medio Ambiente',
      foto: 'https://picsum.photos/seed/bosque/400/250',
      qr: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=ReforestandoEcuador'
    }
  ]
};

// Guardamos el país actual para poder "regresar" a su lista
let paisActualProyectos = null;

// 👉 abre el modal ya reutilizado, pero mostrando la LISTA de proyectos
function abrirModalProyectos(nombrePais) {
  paisActualProyectos = nombrePais;
  mostrarListaProyectos();
  modalOverlay.classList.add('activo');
}

function mostrarListaProyectos() {
  const proyectos = proyectosPorPais[paisActualProyectos] || [];

  modalTitulo.textContent = `Proyectos en ${paisActualProyectos}`;
  modalContenido.classList.remove('vacio');

  if (proyectos.length === 0) {
    modalContenido.textContent = 'Aún no hay proyectos cargados para este país.';
    return;
  }

  modalContenido.innerHTML = `
    <ul class="lista-proyectos">
      ${proyectos.map((p, i) => `
        <li class="lista-proyectos__item" data-index="${i}">
          <span class="lista-proyectos__nombre">${p.nombre}</span>
          <span class="lista-proyectos__categoria">${p.categoria}</span>
        </li>
      `).join('')}
    </ul>
  `;

  // Click en cada proyecto -> abre el detalle
  modalContenido.querySelectorAll('.lista-proyectos__item').forEach(item => {
    item.addEventListener('click', () => {
      const idx = Number(item.dataset.index);
      mostrarDetalleProyecto(proyectos[idx]);
    });
  });
}

function mostrarDetalleProyecto(proyecto) {
  modalTitulo.textContent = proyecto.nombre;

  modalContenido.innerHTML = `
    <div class="detalle-proyecto">
      <img class="detalle-proyecto__foto" src="${proyecto.foto}" alt="${proyecto.nombre}">
      <p class="detalle-proyecto__descripcion">${proyecto.descripcion}</p>
      <p class="detalle-proyecto__categoria"><strong>Categoría:</strong> ${proyecto.categoria}</p>
      <img class="detalle-proyecto__qr" src="${proyecto.qr}" alt="Código QR del proyecto">
      <button class="btn-regresar" id="btn-regresar-proyecto">← Regresar</button>
    </div>
  `;

  document.getElementById('btn-regresar-proyecto').addEventListener('click', () => {
    mostrarListaProyectos();
  });
}

// --- Popover del Caribe ---
const popoverCaribe = document.getElementById('popover-caribe');
const popoverCaribeCerrar = document.getElementById('popover-caribe-cerrar');
const miniMapaContenedor = document.getElementById('mini-mapa-caribe');

const ANCHO_MINI = 300;
const ALTO_MINI = 200;

const ISLAS_PUNTO = [
  { id: 'TT', name: 'Trinidad y Tobago', coords: [-61.4, 10.5] },
  { id: 'LC', name: 'Santa Lucía', coords: [-60.98, 13.9] },
  { id: 'KN', name: 'San Cristóbal y Nieves', coords: [-62.75, 17.28] },
  { id: 'VC', name: 'San Vicente y las Granadinas', coords: [-61.2, 13.25] }
];

// 👉 FIX: exponemos ISLAS_PUNTO para que mapa.js pueda usar sus coordenadas
// (antes mapa.js buscaba "window.ISLAS_PUNTO_NOMBRES", que nunca existía)
window.ISLAS_PUNTO = ISLAS_PUNTO;

let miniMapaCargado = false;

// 👉 FIX: cache de los datos del Caribe para no volver a pedirlos por red.
// mapa.js ya llama a window.inicializarDatosCaribe(caribe) cuando carga el
// mapa principal, pero antes esa función no existía y se perdía la llamada.
let datosCaribeCache = null;
function inicializarDatosCaribe(geojson) {
  datosCaribeCache = geojson;
}
window.inicializarDatosCaribe = inicializarDatosCaribe;

// nombreParaResaltar (opcional): si viene, se marca esa isla como "seleccionado"
// dentro del mini-mapa una vez que está construido. Lo usa mapa.js cuando
// elegís un país del Caribe desde el buscador.
async function abrirPopoverCaribe(x, y, nombreParaResaltar = null) {
  popoverCaribe.style.left = `${x + 12}px`;
  popoverCaribe.style.top = `${y - 30}px`;
  popoverCaribe.classList.add('activo');

  if (!miniMapaCargado) {
    // Usamos el geojson que ya cargó mapa.js si está disponible,
    // así evitamos pedirlo dos veces por red.
    const caribe = datosCaribeCache || await d3.json('DATA/caribe.geo.json');

    const proyeccion = d3.geoMercator().fitSize([ANCHO_MINI, ALTO_MINI], caribe);
    const path = d3.geoPath(proyeccion);

    const svgMini = d3.select(miniMapaContenedor)
      .append('svg')
      .attr('viewBox', `0 0 ${ANCHO_MINI} ${ALTO_MINI}`);

    svgMini.selectAll('path')
      .data(caribe.features)
      .join('path')
      .attr('d', path)
      .attr('data-name', d => d.properties.name)
      .on('click', function (evento, d) {
        evento.stopPropagation();
        svgMini.selectAll('.seleccionado').classed('seleccionado', false);
        this.classList.add('seleccionado');
        abrirModal(d.properties.name);
      });

    svgMini.selectAll('circle')
      .data(ISLAS_PUNTO)
      .join('circle')
      .attr('cx', d => proyeccion(d.coords)[0])
      .attr('cy', d => proyeccion(d.coords)[1])
      .attr('r', 3)
      .attr('data-name', d => d.name)
      .on('click', function (evento, d) {
        evento.stopPropagation();
        svgMini.selectAll('.seleccionado').classed('seleccionado', false);
        this.classList.add('seleccionado');
        abrirModal(d.name);
      });

    miniMapaCargado = true;
  }

  if (nombreParaResaltar) {
    resaltarIslaCaribe(nombreParaResaltar);
  }
}

// 👉 NUEVO: resalta (sin abrir el modal de info) la isla elegida desde el buscador
function resaltarIslaCaribe(nombre) {
  const svgMini = d3.select(miniMapaContenedor).select('svg');
  if (svgMini.empty()) return;

  svgMini.selectAll('.seleccionado').classed('seleccionado', false);
  svgMini.selectAll('path, circle')
    .filter(function () {
      return this.getAttribute('data-name') === nombre;
    })
    .classed('seleccionado', true);
}

function cerrarPopoverCaribe() {
  popoverCaribe.classList.remove('activo');
}

popoverCaribeCerrar.addEventListener('click', (e) => {
  e.stopPropagation();
  cerrarPopoverCaribe();
});

document.addEventListener('click', (e) => {
  if (!popoverCaribe.contains(e.target)) {
    cerrarPopoverCaribe();
  }
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    cerrarModal();
    cerrarPopoverCaribe();
  }
});
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


const API_BASE_URL = 'http://192.168.18.22:8080/Premios_a_la_Excelencia'; 
// 👉 abre el modal ya reutilizado, pero mostrando la LISTA de proyectos
async function abrirModal(nombrePais) {
  paisActualProyectos = nombrePais;
  modalTitulo.textContent = `Proyectos de ${nombrePais}`;
  modalContenido.classList.remove('vacio');
  modalContenido.textContent = 'Cargando...';
  modalOverlay.classList.add('activo');

  try {
    const respuesta = await fetch(`${API_BASE_URL}/proyectos_mapa.php?pais=${encodeURIComponent(nombrePais)}`);
    const proyectos = await respuesta.json();
    mostrarListaProyectos(proyectos);
  } catch (err) {
    modalContenido.textContent = 'Error al cargar los proyectos.';
  }
}async function abrirModal(nombrePais) {
  paisActualProyectos = nombrePais;
  modalTitulo.textContent = `Proyectos de ${nombrePais}`;
  modalContenido.classList.remove('vacio');
  modalContenido.textContent = 'Cargando...';
  modalOverlay.classList.add('activo');

  try {
    const respuesta = await fetch(`${API_BASE_URL}/proyectos_mapa.php?pais=${encodeURIComponent(nombrePais)}`);
    const proyectos = await respuesta.json();
    mostrarListaProyectos(proyectos);
  } catch (err) {
    modalContenido.textContent = 'Error al cargar los proyectos.';
  }
}

let categoriaActiva = 'todas'; // 👈 guarda el filtro seleccionado

function mostrarListaProyectos(proyectos) {
  proyectosActuales = proyectos;
  categoriaActiva = 'todas'; // reinicia el filtro cada vez que se abre un país nuevo

  modalTitulo.textContent = `Proyectos en ${paisActualProyectos}`;
  modalContenido.classList.remove('vacio');

  if (!proyectos || proyectos.length === 0) {
    modalContenido.textContent = 'Aún no hay proyectos cargados para este país.';
    return;
  }

  renderModalListado();
}

// 👉 arma las categorías únicas presentes en los proyectos del país actual
function obtenerCategoriasUnicas(proyectos) {
  const categorias = proyectos
    .map(p => p.categoria)
    .filter(Boolean);
  return [...new Set(categorias)];
}

// 👉 dibuja filtros + lista, respetando categoriaActiva
function renderModalListado() {
  const categorias = obtenerCategoriasUnicas(proyectosActuales);

  const proyectosFiltrados = categoriaActiva === 'todas'
    ? proyectosActuales
    : proyectosActuales.filter(p => p.categoria === categoriaActiva);

  const filtrosHtml = `
    <div class="filtros-categoria">
      <button class="filtro-categoria__btn ${categoriaActiva === 'todas' ? 'activo' : ''}" data-categoria="todas">
        Todas
      </button>
      ${categorias.map(cat => `
        <button class="filtro-categoria__btn ${categoriaActiva === cat ? 'activo' : ''}" data-categoria="${cat}">
          ${cat}
        </button>
      `).join('')}
    </div>
  `;

  const listaHtml = proyectosFiltrados.length
    ? `
      <ul class="lista-proyectos">
        ${proyectosFiltrados.map((p) => {
          const idxReal = proyectosActuales.indexOf(p);
          return `
            <li class="lista-proyectos__item" data-index="${idxReal}">
              <span class="lista-proyectos__nombre">${p.nombre}</span>
              <span class="lista-proyectos__meta">
                ${p.categoria}${p.organizacion ? ` - ${p.organizacion}` : ''}
              </span>
            </li>
          `;
        }).join('')}
      </ul>
    `
    : `<p class="lista-proyectos__sin-resultados">No hay proyectos en esta categoría.</p>`;

  modalContenido.innerHTML = `
    <div class="modal-proyectos__layout">
      <div class="modal-proyectos__logo">
        <img src="http://192.168.18.22:8080/Premios_a_la_Excelencia/media/LogoPremioExcelenciaEnergticaESPColor.png" alt="Logo Premio Excelencia Energética">
      </div>
      <div class="modal-proyectos__cuerpo">
        ${filtrosHtml}
        ${listaHtml}
      </div>
    </div>
  `;

  // clic en cada botón de filtro
  modalContenido.querySelectorAll('.filtro-categoria__btn').forEach(btn => {
    btn.addEventListener('click', () => {
      categoriaActiva = btn.dataset.categoria;
      renderModalListado();
    });
  });

  // clic en cada proyecto de la lista
  modalContenido.querySelectorAll('.lista-proyectos__item').forEach(item => {
    item.addEventListener('click', () => {
      const idx = Number(item.dataset.index);
      mostrarDetalleProyecto(proyectosActuales[idx]);
    });
  });
}

// Modal de detalles con las redes
// Modal de detalles con las redes

const REDES_ICONOS = {
  linkedin:  'http://192.168.18.22:8080/Premios_a_la_Excelencia/assets/icon/linkedin.svg',
  x:         'https://cdn.simpleicons.org/x/000000',
  instagram: 'https://cdn.simpleicons.org/instagram/E4405F',
  facebook:  'https://cdn.simpleicons.org/facebook/1877F2'
};

// Convierte "linkedin:marlon/instagram:marlon.dev" en [{red, nombre}, ...]
function parseRedesSociales(valor) {
  if (!valor || !valor.trim()) return [];
  return valor.split('/').filter(Boolean).map(par => {
    const [red, ...resto] = par.split(':');
    return { red: red.trim(), nombre: resto.join(':').trim() };
  }).filter(f => REDES_ICONOS[f.red]);
}

function mostrarDetalleProyecto(proyecto) {
  modalTitulo.textContent = proyecto.nombre;

  const RUTA_IMAGEN_DEFECTO = 'http://192.168.18.22:8080/Premios_a_la_Excelencia/media/LogoPremioExcelenciaEnergticaESPColor.png';
  const fotoProyecto = proyecto.foto && proyecto.foto.trim() ? proyecto.foto : RUTA_IMAGEN_DEFECTO;

  const subcategoriaHtml = proyecto.subcategoria && proyecto.subcategoria.trim()
    ? `<span class="detalle-proyecto__subcategoria">${proyecto.subcategoria}</span>`
    : '';

  // Organización + íconos de redes sociales debajo
  const redesSociales = parseRedesSociales(proyecto.red_social);
  const redesHtml = redesSociales.length
    ? `<div class="detalle-proyecto__redes">
        ${redesSociales.map(r => `
          <span class="detalle-proyecto__red-item">
            <img class="detalle-proyecto__red-icon"
                 src="${REDES_ICONOS[r.red]}"
                 alt="${r.red}">
            <span class="detalle-proyecto__red-nombre">${r.nombre}</span>
          </span>
        `).join('')}
      </div>`
    : '';

  const organizacionHtml = proyecto.organizacion && proyecto.organizacion.trim()
    ? `
      <p class="detalle-proyecto__organizacion">
        <span class="detalle-proyecto__organizacion-label">Organización:</span>
        ${proyecto.organizacion}
      </p>
      ${redesHtml}
    `
    : '';

  // El QR ahora es una imagen ya subida por el usuario, servida directo por el backend
  const qrHtml = proyecto.qr && proyecto.qr.trim()
    ? `
      <div class="detalle-proyecto__qr-wrap" id="qr-wrap">
        <img class="detalle-proyecto__qr" id="qr-img" src="${proyecto.qr}" alt="Código QR del proyecto">
      </div>
    `
    : '';

  modalContenido.innerHTML = `
    <div class="detalle-proyecto">
      <div class="detalle-proyecto__imagen-wrap">
        <img class="detalle-proyecto__foto" src="${fotoProyecto}" alt="${proyecto.nombre}"
            onerror="this.src='${RUTA_IMAGEN_DEFECTO}'">
      </div>
      <div class="detalle-proyecto__info">
        <div class="detalle-proyecto__badges">
          <span class="detalle-proyecto__categoria">${proyecto.categoria}</span>
          ${subcategoriaHtml}
        </div>
        <p class="detalle-proyecto__descripcion">${proyecto.descripcion}</p>

        <div class="detalle-proyecto__org-qr-row">
          <div class="detalle-proyecto__organizacion-wrap">
            ${organizacionHtml}
          </div>
          ${qrHtml}
        </div>

        <button class="btn-regresar" id="btn-regresar-proyecto">← Regresar</button>
      </div>
    </div>
  `;

  document.getElementById('btn-regresar-proyecto').addEventListener('click', () => {
    mostrarListaProyectos(proyectosActuales);
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
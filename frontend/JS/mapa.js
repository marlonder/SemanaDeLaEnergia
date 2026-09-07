const contenedor = document.getElementById('contenedor-mapa');

const ANCHO = 550;
const ALTO = 750;

const CARIBE_PUNTO = [-75, 19];

// 👉 NUEVO: nombre exacto del país que va a tener proyectos por ahora
const PAIS_CON_PROYECTOS = 'Ecuador';

async function cargarMapa() {
  const continental = await d3.json('DATA/america_unida.geo.json');

  const proyeccion = d3.geoMercator().fitSize([ANCHO, ALTO], continental);
  const path = d3.geoPath(proyeccion);

  const svg = d3.select(contenedor)
    .append('svg')
    .attr('viewBox', `0 0 ${ANCHO} ${ALTO}`);

  svg.selectAll('path')
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

  // 👉 NUEVO: buscar el feature de Ecuador y ponerle una estrella encima
  const featureEcuador = continental.features.find(
    d => d.properties.name === PAIS_CON_PROYECTOS
  );

  if (featureEcuador) {
    const [ex, ey] = path.centroid(featureEcuador);

    const estrella = svg.append('text')
      .attr('class', 'punto-estrella')
      .attr('x', ex)
      .attr('y', ey)
      .attr('text-anchor', 'middle')
      .attr('dominant-baseline', 'middle')
      .text('★');

    estrella.on('click', function (evento) {
      evento.stopPropagation(); // que no dispare el click del país también
      cerrarPopoverCaribe();
      abrirModalProyectos(PAIS_CON_PROYECTOS);
    });
  } else {
    console.warn(`No se encontró el país "${PAIS_CON_PROYECTOS}" en el geojson. Revisa el nombre exacto en properties.name`);
  }

  // --- Caribe (sin cambios) ---
  const [cx, cy] = proyeccion(CARIBE_PUNTO);

  const puntoCaribe = svg.append('circle')
    .attr('class', 'punto-caribe')
    .attr('cx', cx)
    .attr('cy', cy)
    .attr('r', 5);

  svg.append('text')
    .attr('class', 'punto-caribe-label')
    .attr('x', cx)
    .attr('y', cy - 8)
    .text('Caribe');

  puntoCaribe.on('click', function (evento) {
    evento.stopPropagation();
    const svgNode = svg.node();
    const rectSvg = svgNode.getBoundingClientRect();
    const rectWrapper = document.querySelector('.mapa-wrapper').getBoundingClientRect();

    const escalaX = rectSvg.width / ANCHO;
    const escalaY = rectSvg.height / ALTO;

    const posX = (rectSvg.left - rectWrapper.left) + (cx * escalaX);
    const posY = (rectSvg.top - rectWrapper.top) + (cy * escalaY);

    abrirPopoverCaribe(posX, posY);
  });
}

cargarMapa();
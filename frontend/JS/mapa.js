const contenedor = document.getElementById('contenedor-mapa');
const infoPais = document.getElementById('info-pais');

const ANCHO = 550;
const ALTO = 750;

async function cargarMapa(archivoGeoJson) {
  const datos = await d3.json(archivoGeoJson);

  // Proyección ajustada a todos los países del archivo (estática, sin zoom)
  const proyeccion = d3.geoMercator().fitSize([ANCHO, ALTO], datos);
  const path = d3.geoPath(proyeccion);

  const svg = d3.select(contenedor)
    .append('svg')
    .attr('viewBox', `0 0 ${ANCHO} ${ALTO}`);

  svg.selectAll('path')
    .data(datos.features)
    .join('path')
    .attr('id', d => d.properties.id)
    .attr('data-name', d => d.properties.name)
    .attr('d', path)
    .on('mouseenter', (evento, d) => {
      infoPais.textContent = d.properties.name;
    })
    .on('mouseleave', () => {
      infoPais.textContent = 'Pasa el mouse sobre un país';
    })
    .on('click', function () {
      this.classList.toggle('seleccionado');
    });
}

cargarMapa('DATA/america_unida.geo.json');
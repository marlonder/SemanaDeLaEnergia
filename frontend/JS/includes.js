// includes.js

fetch('include/header.html')
  .then(res => res.text())
  .then(data => {
    document.getElementById('header').innerHTML = data;

    // 👉 Ahora sí existen los elementos del header, enganchamos los eventos aquí
    inicializarControlesHeader();
  });

fetch('include/footer.html')
  .then(res => res.text())
  .then(data => {
    document.getElementById('footer').innerHTML = data;
  });


// Boton de refrescar (y lo que necesites del buscador)
function inicializarControlesHeader() {
  const btnRefrescar = document.getElementById('btn-refrescar');
  const iconoRefrescar = btnRefrescar.querySelector('.btn-refrescar__icono');

  btnRefrescar.addEventListener('click', () => {

    limpiarBusqueda();

    d3.select(contenedor).selectAll('path').classed('seleccionado', false);
    cerrarModal();
    modalTitulo.textContent = 'Proyectos de la Excelencia';

    iconoRefrescar.classList.add('girando');
    setTimeout(() => iconoRefrescar.classList.remove('girando'), 500);
  });
}
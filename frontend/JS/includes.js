 fetch('include/header.html')
      .then(res => res.text())
      .then(data => {
        document.getElementById('header').innerHTML = data;
      });

    fetch('include/footer.html')
      .then(res => res.text())
      .then(data => {
        document.getElementById('footer').innerHTML = data;
      });


//boton de regrescar

const btnRefrescar = document.getElementById('btn-refrescar');
const iconoRefrescar = btnRefrescar.querySelector('.btn-refrescar__icono');

btnRefrescar.addEventListener('click', () => {
  d3.select(contenedor).selectAll('path').classed('seleccionado', false);
  cerrarModal();
  modalTitulo.textContent = 'Proyectos de la Excelencia';

  iconoRefrescar.classList.add('girando');
  setTimeout(() => iconoRefrescar.classList.remove('girando'), 500);
});
$(document).ready(function () {
  menuProgramas();
});

function logOut() {
  localStorage.removeItem("menu");
  localStorage.removeItem("usuario");
  $.ajax({
    type: "POST",
    url: "rest.php",
    dataType: "json",
    async: true,
    data: { modulo: "login", metodo: "logOut" },
    success: function (data) {
      document.location = "index.php";
    },
  });
}

function menuProgramas() {
  $.ajax({
    type: "POST",
    url: "rest.php",
    dataType: "json",
    async: true,
    data: { modulo: "programa", metodo: "getMenuProgramas", token: getToken() },
    success: function (data) {
      console.log(data);
      let { mainMenuHTML, arrayProgramas } = menuProgramaSub(data);
      localStorage.setItem("menu", mainMenuHTML);
      localStorage.setItem("programas", JSON.stringify(arrayProgramas));
      $("#sidebar-nav").html(mainMenuHTML);
    },
  });
}

function menuProgramaSub(data) {
  let mainMenuHTML = "";
  let arrayProgramas = [];

  mainMenuHTML += `<li class="nav-item">
                        <a class="nav-link" href="index.php?modulo=inicio">
                          <i class="bi bi-grid"></i>
                          <span>Dashboard</span>
                        </a>
                      </li>`;

  $.each(data, function (index, vmenu) {
    mainMenuHTML += `<li class="nav-item">
                          <a class="nav-link collapsed" 
                             data-bs-toggle="collapse" 
                             data-bs-target="#${vmenu.nombre}-nav" 
                             href="#">
                            <i class="bi bi-menu-button-wide"></i>
                            <span>${vmenu.nombre.toLowerCase()}</span>
                            <i class="bi bi-chevron-down ms-auto"></i>
                          </a>
                          <ul class="nav-content collapse" 
                              data-bs-parent="#sidebar-nav" 
                              id="${vmenu.nombre}-nav">`;

    $.each(vmenu.progs, function (index, vprog) {
      let link = `index.php?modulo=${vprog.programa.toLowerCase()}`;
      mainMenuHTML += `<li>
                            <a href="${link}">
                              <i class="bi bi-circle"></i>
                              <span>${vprog.descripcion.toLowerCase()}</span>
                            </a>
                          </li>`;

      arrayProgramas.push({
        programa: vprog.programa.toLowerCase(),
        descripcion: vprog.descripcion.toLowerCase(),
        nuevo: vprog.nuevo,
      });
    });

    mainMenuHTML += `</ul>
                      </li>`;
  });

  return { mainMenuHTML, arrayProgramas };
}

function getToken() {
  var userInfo = JSON.parse(localStorage.getItem("usuario"));

  if (userInfo && userInfo.token) {
    return userInfo.token;
  } else {
    return null;
  }
}

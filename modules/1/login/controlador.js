$(document).ready(function () {
  // Eliminar elementos del localStorage
  localStorage.removeItem("menu");
  localStorage.removeItem("usuario");

  // Configuración inicial del formulario
  configureForm();

  // Evento al presionar Enter en el campo de contraseña
  $("#password").keydown(function (e) {
    if (e.keyCode == "13") {
      autenticar();
    }
  });

  // Evento al hacer clic en el botón de ingreso
  $("#btn-ingreso-app").click(function (event) {
    autenticar();
  });

  // Evento al hacer clic en el botón de recuperar clave
  $("#btnRecuperar").on("click", function () {
    handleRecoveryButtonClick();
  });
});

function configureForm() {
  let usuario = localStorage.getItem("username");
  if (usuario != null) {
    $("#username").val(usuario).change();
    $("#remember").prop("checked", true);
    $("#password").focus();
  } else {
    $("#password").focus();
    $("#username").change().focus();
  }
}

function handleRecoveryButtonClick() {
  let usuario = $("#username").val();
  if (!usuario) {
    toastr.error("Por favor ingresa tu nombre de usuario.");
    return;
  }
  TraerUsuario(usuario);
}

function TraerUsuario(usuario) {
  $.ajax({
    type: "POST",
    url: "rest.php",
    dataType: "json",
    async: true,
    data: { modulo: "login", metodo: "TraerUsuario", parametros: usuario },
    success: function (data) {
      // Manipular la respuesta de la llamada AJAX
      handleUserRetrieval(data);
    },
  });
}

function handleUserRetrieval(data) {
  if (data.cel) {
    $("#cel").text(data.cel);
    $("#ob2").show(500);
    $("#mSms").attr("checked", true);
  }

  if (data.mail) {
    $("#mail").text(data.mail);
    $("#ob1").show(500);
    $("#mMail").attr("checked", true);
  }

  if (!data.cel && !data.mail) {
    $("header").hide();
    swal(
      "No hay correo o número de celular para recuperar tu clave, por favor contacta a tu jefe directo"
    );
    return;
  }

  $("#frm").hide(500);
  $("#envio").show(500);
}

function autenticar() {
  let usuario = $("#username").val();
  if (!usuario) {
    toastr.error("Por favor ingresa tu nombre de usuario.");
    return;
  }

  let password = $("#password").val();
  if (!password) {
    toastr.error("Por favor ingresa tu contraseña.");
    return;
  }

  $.ajax({
    type: "POST",
    url: "rest.php",
    dataType: "json",
    async: true,
    data: {
      modulo: "login",
      metodo: "autenticar",
      parametros: {
        usuario: usuario,
        password: password,
      },
    },
    success: function (data) {
      // Manipular la respuesta de la llamada AJAX para la autenticación
      handleAuthentication(data);
    },
  });
}

function handleAuthentication(data) {
  if (data.estado == "Exitoso") {
    localStorage.setItem("usuario", JSON.stringify(data.info));
    if ($("#remember").prop("checked")) {
      localStorage.setItem("username", $("#username").val());
    } else {
      localStorage.removeItem("username");
    }
    document.location = "index.php?modulo=inicio";
  } else {
    toastr.error(data.mensaje);
  }
}

function ValidarMultipleDB() {
  $.ajax({
    type: "POST",
    url: "rest.php",
    dataType: "json",
    async: true,
    data: { modulo: "login", metodo: "ValidarMultipleDB" },
    success: function (data) {
      // Manipular la respuesta de la llamada AJAX para validar múltiples bases de datos
      handleMultipleDBValidation(data);
    },
  });
}

function handleMultipleDBValidation(data) {
  if (data.multiple == "S") {
    $("#db").parent().show();
    $("#db").val(data.defecto);
  } else {
    $("#db").parent().remove();
  }
}

(function(document, window, $, Core) {
    (function() {

        return [[programa]] = {

            //INICIALIZACIÓN DE COMPONENTES
            Initialize: function() {
                let self = this;

                //forma de asignar un click a un boton
                $("#myBoton").on("click", function() {
                    self.funcionRest();
                });
            },

            //Petición AJAX
            ajaxRequest: function(data, type, method) {
                return $.ajax({
                    url: 'rest.php',
                    data: data,
                    type: type,
                    dataType: 'json',
                    async: true,
                    data: {
                        "modulo": "[[programa]]",
                        "metodo": method,
                        "token": getToken(),
                        "parametros": data
                    }
                })
            },

            //funcion de ejemplo
            funcionRest: function() {
                let self = this;
                let data = Core.FormToJSON("#frm");
                self.ajaxRequest(data,
                    'post', 'funcionRest')
                    .done(function(response) {
                        toastr.info("Funcion ejecutada");
                        $('#myInput2').val(response).change();
                    });
            },
        }
    })()
        [[programa]].Initialize()
})(document, window, jQuery, Core)


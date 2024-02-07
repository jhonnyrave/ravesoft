//Documentacion: http://api.qunitjs.com/
/* EJECUCION
* index.php?modulo=[[programa]]&unit_test=si
*/
QUnit.module( "Modulo Consulta", function(){

	setTimeout(function () {
		QUnit.test("Ejecucion servicio rest",function(assert){
			assert.equal(buscarPrograma('SOLICITUD_CREDITO'),undefined,"Ejecucion buscar programa");
		})
	},1000);

	QUnit.module( "Validacion", function() {
		setTimeout(function () {
			QUnit.test( "Pruebas de retorno de datos", function( assert ) {
				assert.equal( $('#descripcion').val(), 'SOLICITUD DE CREDITO', "Descripcion del programa");
				assert.equal( $('#menu-programa').val(), '54', "Validacion de seleccion de menu");
				assert.ok( $('#autenticado').prop("checked"),  "Validacion de chulo de autenticacion");
			});
		},2000);
	});
});
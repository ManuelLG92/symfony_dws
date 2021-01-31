function agregarAlCarro(usuarioId,articuloId, inputId) {
    var parametros = {
        'usuarioId' : usuarioId,
        'articuloId' : articuloId,
    }
    $(inputId).prop("disabled", true)
    $(inputId).val("Agregando..")

    $.post( "/carro", parametros, function(response) {
        var codigo = response['respuesta'];
        console.log(response['respuesta']);
        var cantidad = response['cantidad'];

        if (codigo === 4){
            console.log("Se ejecuta codigo 4")
            $(inputId).val("Agregado")
            alert("Este articulo ya esta en tu carro de compra :D !")

        } else {
            console.log("Se ejecuta codigo 0")
          $('#nroItems').text(cantidad.toString())
        $(inputId).val("Agregado")
        }


    }).fail(function(response) {
        $(inputId).prop("disabled", false);
        $(inputId).val("Añadir al carro")
            console.log(response);
        })


    console.log("se ejecuta " + usuarioId + ", art "+articuloId)
}


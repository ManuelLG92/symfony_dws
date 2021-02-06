function agregarAlCarro(usuarioId,articuloId, inputId) {
    var parametros = {
        'usuarioId' : usuarioId,
        'articuloId' : articuloId,
    }
    $(inputId).prop("disabled", true)
    $(inputId).val("Agregando..")

    $.post( "/carro", parametros, function(response) {
        var codigo = response['respuesta'];
        //console.log(response['respuesta']);
        var cantidad = response['cantidad'];

        if (codigo === 1){
            $('#nroItems').text(cantidad.toString())
            $(inputId).val("Agregado")
        }else if (codigo === 2){
            $(inputId).val("Agotado").addClass('bg-secondary')
            alert("Lo sentimos, este articulo esta agotado!")
        }else if (codigo === 3){
            $(inputId).val("Agregado")
            alert("Este articulo ya esta en tu carro de compra :D !")
        }else if (codigo === 4){
            $(inputId).val("No encontrado").addClass('bg-secondary')
            alert("Articulo no encontrado, intentalo en unos minutos!")
        }
        else {
            $(inputId).val("No encontrado").addClass('bg-secondary')
            alert("Ha ocurrido un error, intentalo en unos minutos!")
        }


    }).fail(function(response) {
        $(inputId).prop("disabled", false);
        $(inputId).val("Añadir al carro")
            console.log(response);
        })


    //console.log("se ejecuta " + usuarioId + ", art "+articuloId)
}


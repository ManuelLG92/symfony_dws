function agregarAlCarro(usuarioId,articuloId, inputId) {
    var parametros = {
        'usuarioId' : usuarioId,
        'articuloId' : articuloId,
    }
    tempInput = inputId
    $(inputId).prop("disabled", true)
    $(inputId).val("Agregando..")
    console.log(inputId)

/*
    $.ajax({
        url : '/carro',
        type : 'post',
        data : parametros,
        beforeSend: function () {
            console.log("enviando")
        },
        success:  function (response) { //una vez que el archivo recibe el request lo procesa y lo devuelve
            console.log(response['respuesta']);
        }
    })*/



    $.post( "/carro", parametros, function(response) {
        console.log(response['respuesta']);
        var cantidad = response['cantidad'];
       $('#nroItems').text(cantidad.toString())
        console.log(cantidad)

        $(inputId).val("Agregado")
    }).fail(function(response) {
        $(inputId).prop("disabled", false);
        $(inputId).val("Añadir al carro")
            console.log(response);
        })


    console.log("se ejecuta " + usuarioId + ", art "+articuloId)
}
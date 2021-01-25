$(document).ready(function() {
    //$('#btn').prop('disabled', true);
    var nombreError = true;
    var stockError = true;
    var precioError = true;
    var seccionError = true;
    var descripcionError = true;
    var imagenError = null;


    var longitudMaximaNombre = 30;
    var selectorInputNombre = '#nombre';
    longitudMaximaCampos(selectorInputNombre,longitudMaximaNombre)
    if($(selectorInputNombre).val().length > 2){
        nombreError = false;
    }

    var longitudMaximaStock = 4;
    var selectorInputStock = '#stock';
    longitudMaximaCampos(selectorInputStock,longitudMaximaStock)
    if($(selectorInputStock).val() > 0){
        stockError = false;
    }

    var longitudMaximaPrecio = 4;
    var selectorInputPrecio = '#precio';
    longitudMaximaCampos(selectorInputPrecio,longitudMaximaPrecio)
    if($(selectorInputPrecio).val() > 0){
        precioError = false;
    }

    var longitudMaximadescripcion = 255;
    var selectorInputDescripcion = '#descripcion';

    if($(selectorInputDescripcion).val().length > 9){
        descripcionError = false;
    }
    longitudMaximaCampos(selectorInputDescripcion,longitudMaximadescripcion)

    var selectorSeccion = '#secciones'
    if($(selectorSeccion).val() !== 0){
        seccionError = false;
       // console.log(seccionError)
    }

    var selectorImagen = '#imagen'


    $(selectorInputNombre).keyup(function (){
        nombre = "Nombre";
        minimo = 2;
        maximo = 50;
        tipoInput = "str";
        selectorValidacion = ".validateNombre"

        validacionGeneral(selectorInputNombre,
            tipoInput,longitudMaximaNombre,minimo,maximo) ?
            nombreError = false : nombreError = true;

        mensajeError(nombreError,tipoInput,selectorValidacion,
            nombre,minimo,maximo)

        })

    $(selectorInputStock).keyup(function (){
        nombre = "Stock";
        minimo = 1;
        maximo = 1000;
        tipoInput = "int";
        selectorValidacion = ".validateStock"

        validacionGeneral(selectorInputStock,
            tipoInput,longitudMaximaStock,minimo,maximo)
            ? stockError = false : stockError = true;

        mensajeError(stockError,tipoInput,selectorValidacion,
            nombre,minimo,maximo)

        })

    $(selectorInputPrecio).keyup(function (){
        nombre = "Precio";
        minimo = 1;
        maximo = 1000;
        tipoInput = "int";
        selectorValidacion = ".validatePrecio"
        validacionGeneral(selectorInputPrecio,
            tipoInput,longitudMaximaPrecio,minimo,maximo) ?
            precioError = false : precioError = true;

        mensajeError(precioError,tipoInput,selectorValidacion,
            nombre,minimo,maximo)

    })

    $(selectorInputDescripcion).keyup(function (){
        nombre = "Descripcion";
        minimo = 10;
        maximo = 255;
        tipoInput = "str";
        selectorValidacion = ".validateDescripcion"
        selectorContador = '.numeroCaracteres';
        contadorDescripcionFunc(selectorInputDescripcion,selectorContador )

        validacionGeneral(selectorInputDescripcion,
            tipoInput,longitudMaximadescripcion,minimo,maximo) ?
            descripcionError = false : descripcionError = true;

        mensajeError(precioError,tipoInput,selectorValidacion,
            nombre,minimo,maximo)

    })

    $(selectorSeccion).change(function () {
        valorSeccion = $(selectorSeccion).val()
        valorSeccion = parseInt(valorSeccion)
        selectorValidacionSeccion = '.validateSeccion'
        valorSeccion !== 0 ?
            seccionError = false : seccionError = true;

        if (seccionError) {
            $(selectorSeccion).addClass("is-invalid")
            $(selectorValidacionSeccion).html("Debes seleccionar una seccion.").addClass("text-danger");
        } else {
            $(selectorSeccion).addClass("is-valid").removeClass("is-invalid")
            $(selectorValidacionSeccion).html(
                "Seccion valida.").addClass("text-success").removeClass("text-danger");
        }
    })

    $(selectorImagen).change(function () {
        var imagen = $(selectorImagen)[0].files[0];
        tipoImagen = imagen.type;
        tamanhoImagen = imagen.size;
        if (tipoImagen === "image/jpeg" || tipoImagen === "image/jpg"
            || tipoImagen === "image/png" ){
            if (tamanhoImagen >= 1060000){
                $(selectorImagen).val(null);
                imagenError = true;
                alert("El tamaño maximo permitido es 1MB")
            } else {
                imagenError = false;
            }

        } else {
            $(selectorImagen).val(null);
            imagenError = true;
            alert("Solo se aceptan imagenes tipo: jpg,jpeg y png.")

        }
        var temp = "";
        temp += "<br>Filename: " + imagen.name;
        temp += "<br>Type: " + imagen.type;
        temp += "<br>Size: " + imagen.size + " bytes"
        console.log(temp)
    })


    function contadorDescripcionFunc(campo, selectorContador) {
        campoDescipcionFunc = $(campo)
        longitud = campoDescipcionFunc.val().length;
        if (longitud>150 && longitud<256){
            $(selectorContador).html(longitud +"/255").addClass("text-danger")
        } else {
            $(selectorContador).html(longitud +"/255").addClass("text-success").removeClass("text-danger")
        }

        console.log(longitud)

    }
    function validacionGeneral(campo,tipo,
                               longitudMaximaCampo, minimo,maximo) {
        var campoFunc = $(campo)
        campoValidar = campoFunc.val();

        switch (tipo) {
        case "int":
            if (campoValidar > (minimo-1) && campoValidar < (maximo+1) ) {
                campoFunc.addClass('is-valid')
                campoFunc.removeClass('is-invalid')
                return true;
            } else {
                campoFunc.addClass('is-invalid')
                campoFunc.removeClass('is-valid')
                return false;
            }

        case "str":
            if (campoValidar.length > (minimo-1) && campoValidar.length < (maximo+1) ) {
                campoFunc.addClass('is-valid')
                campoFunc.removeClass('is-invalid')
                return true;
            } else {
                campoFunc.addClass('is-invalid')
                campoFunc.removeClass('is-valid')
                return false;
            }


        }
    }

    function longitudMaximaCampos(selectorCampo, longitudMaxima) {
        selectorCampoLongitud = $(selectorCampo)
        selectorCampoLongitud.prop('maxLength',longitudMaxima);
    }
    function mensajeError(error, tipo,selectorValidacion, nombre, minimo, maximo ) {

            if (error){
                switch (tipo) {
                    case "int":
                        $(selectorValidacion).html(
                        "El " + nombre + " debe ser minimo " + minimo +
                        " y maximo " + maximo).addClass("text-danger");
                    break;
                    case "str":
                        $(selectorValidacion).html(
                        "El " + nombre + " es obligatorio, como minimo " + minimo +
                        " caracteres.").addClass("text-danger");
                    break;

                }

            } else {
                    $(selectorValidacion).html(
                        nombre + " valid@.").addClass("text-success").removeClass("text-danger");

                    }
    }
    console.log('Nombre erorr: ' + nombreError)
    console.log('stock erorr: ' + stockError)
    console.log('precio erorr: ' + precioError)
    console.log('seccion erorr: ' + seccionError)
    console.log('descripcion erorr: ' + descripcionError)
    function submitFormulario() {

        if (!nombreError && !descripcionError && !precioError
            && !stockError && !seccionError ){
            if (imagenError === null || !imagenError){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    $('#enviar').click(function () {
        console.log(seccionError)
        if(seccionError){
            alert("Debes seleccionar una seccion.")
            return
        }
        if (!nombreError && !descripcionError && !precioError
            && !stockError && !seccionError ){
            if (imagenError === null || !imagenError){
                //alert('Correctos')
                $('#formulario').submit();
            } else {
                alert('Campos e imagen Correcta')
            }
        } else {
            alert('Completa todos los campos ')
        }
    })

});

$(document).ready(()=>{
    erroresCampos = {};

    selectorErrorNombre = ".nombreError"
    selectorErrorApellido = ".apellidoError"
    selectorErrorTelefono = ".telefonoError"
    selectorErrorVia = ".viaError"
    selectorErrorNombreVia = ".nombreViaError"
    selectorErrorNumero = ".numeroError"
    selectorErrorPiso = ".pisoError"
    selectorErrorPuerta = ".puertaError"
    selectorErrorCiudad = ".ciudadError"
    selectorErrorEstado = ".estadoError"
    selectorErrorCp = ".cpError"
    selectorErrorPais = ".paisError"
    selectorErrorEmail = ".emailError"
    selectorErrorClave = ".claveError"
    selectorErrorCheck = ".checkError"


    inputNombre = "#nombre"
    compruebaCamposString(inputNombre, 2, 30)
    mensajeErrorString(erroresCampos[$(inputNombre).prop("id")],"str",selectorErrorNombre,"Nombre",2,30)

    inputApellido = "#apellido"
    compruebaCamposString(inputApellido, 2, 50)
    mensajeErrorString(erroresCampos[$(inputApellido).prop("id")],"str",selectorErrorApellido,"Apellidos",2,50)

    inputTelefono = "#telefono"
    validaTelefono(inputTelefono);
    mensajeErrorString(erroresCampos[$(inputTelefono).prop("id")],"str",selectorErrorTelefono,"Telefono",9,12)


    inputVia = "#via"
    compruebaCamposString(inputVia, 2, 30)
    mensajeErrorString(erroresCampos[$(inputVia).prop("id")],"str",selectorErrorVia,"Via",2,30)

    inputeNombreVia = "#nombre_via"
    compruebaCamposString(inputeNombreVia, 2, 50)
    mensajeErrorString(erroresCampos[$(inputeNombreVia).prop("id")],"str",selectorErrorNombreVia,"Nombre Via",2,50)

    inputNumero = "#numero"
    compruebaCamposString(inputNumero, 1, 5)
    mensajeErrorString(erroresCampos[$(inputNumero).prop("id")],"str",selectorErrorNumero,"Numero",1,2000)

    inputPiso = "#piso"
    compruebaCamposString(inputPiso, 0, 10)
    mensajeErrorString(erroresCampos[$(inputPiso).prop("id")],"nulo",selectorErrorPiso,"Piso",0,10)

    inputPuerta = "#puerta"
    compruebaCamposString(inputPuerta, 0, 10)
    mensajeErrorString(erroresCampos[$(inputPuerta).prop("id")],"nulo",selectorErrorPuerta,"Puerta",0,10)

    inputCiudad = "#ciudad"
    compruebaCamposString(inputCiudad, 2, 50)
    mensajeErrorString(erroresCampos[$(inputCiudad).prop("id")],"str",selectorErrorCiudad,"Ciudad",2,50)

    inputEstado = "#estado"
    compruebaCamposString(inputEstado, 2, 50)
    mensajeErrorString(erroresCampos[$(inputEstado).prop("id")],"str",selectorErrorEstado,"Estado",2,50)

    inputCp = "#cp"
    compruebaCamposString(inputCp, 1, 10)
    mensajeErrorString(erroresCampos[$(inputCp).prop("id")],"str",selectorErrorCp,"Codigo Postal",1,5)

    inputPais = "#pais"
    compruebaCamposString(inputPais, 2, 50)
    mensajeErrorString(erroresCampos[$(inputPais).prop("id")],"str",selectorErrorPais,"Pais",2,50)



    inputEmail = "#email"
    if ($(inputEmail).length>0){
        validarEmail(inputEmail,$(inputEmail).val())
        mensajeErrorString(erroresCampos[$(inputEmail).prop("id")],"mail",selectorErrorEmail,"Email",5,70)

    }
    //compruebaCamposString(inputPiso, 5, 70)
    inputClave = "#clave"
    if ($(inputClave).length>0){
        compruebaCamposString(inputClave, 8, 32)
        mensajeErrorString(erroresCampos[$(inputClave).prop("id")],"str",selectorErrorClave,"Clave",8,32)

    }

    inputTerminos = "#term"
    if ($(inputTerminos).length>0){
        $(inputTerminos).is(':checked') ? erroresCampos[$(inputTerminos).prop("id")] = false : erroresCampos[$(inputTerminos).prop("id")] = true
        mensajeErrorString(erroresCampos[$(inputTerminos).prop("id")],"check",selectorErrorCheck,"terminos y condiciones",0,0)

    }

    $(inputNombre).on('keyup change',()=>{
        compruebaCamposString(inputNombre, 2, 30)
        mensajeErrorString(erroresCampos[$(inputNombre).prop("id")],"str",selectorErrorNombre,"Nombre",2,30)

    })
    $(inputApellido).on('keyup change',()=>{
        compruebaCamposString(inputApellido, 2, 50)
        mensajeErrorString(erroresCampos[$(inputApellido).prop("id")],"str",selectorErrorApellido,"Apellidos",2,50)

    })

    $(inputTelefono).on('keyup change',()=>{
        validaTelefono(inputTelefono);
        mensajeErrorString(erroresCampos[$(inputTelefono).prop("id")],"str",selectorErrorTelefono,"Telefono",9,12)

    })


    $(inputVia).on('keyup change',()=>{
        compruebaCamposString(inputVia, 2, 30)
        mensajeErrorString(erroresCampos[$(inputVia).prop("id")],"str",selectorErrorVia,"Via",2,30)

    })
    $(inputeNombreVia).on('keyup change',()=>{
        compruebaCamposString(inputeNombreVia, 2, 50)
        mensajeErrorString(erroresCampos[$(inputeNombreVia).prop("id")],"str",selectorErrorNombreVia,"Nombre Via",2,50)

    })

    $(inputNumero).on('keyup change',()=>{
        compruebaCamposString(inputNumero, 1, 5)
        mensajeErrorString(erroresCampos[$(inputNumero).prop("id")],"str",selectorErrorNumero,"Numero",1,2000)

    })

    $(inputPiso).on('keyup change',()=>{
        compruebaCamposString(inputPiso, 0, 10)
        mensajeErrorString(erroresCampos[$(inputPiso).prop("id")],"nulo",selectorErrorPiso,"Piso",0,10)

    })

    $(inputPuerta).on('keyup change',()=>{

        compruebaCamposString(inputPuerta, 0, 10)
        mensajeErrorString(erroresCampos[$(inputPuerta).prop("id")],"nulo",selectorErrorPuerta,"Puerta",0,10)

    })

    $(inputCiudad).on('keyup change',()=>{
        compruebaCamposString(inputCiudad, 2, 50)
        mensajeErrorString(erroresCampos[$(inputCiudad).prop("id")],"str",selectorErrorCiudad,"Ciudad",2,50)

    })

    $(inputEstado).on('keyup change',()=>{
        compruebaCamposString(inputEstado, 2, 50)
        mensajeErrorString(erroresCampos[$(inputEstado).prop("id")],"str",selectorErrorEstado,"Estado",2,50)

    })

    $(inputCp).on('keyup change',()=>{
        compruebaCamposString(inputCp, 1, 10)
        mensajeErrorString(erroresCampos[$(inputCp).prop("id")],"str",selectorErrorCp,"Codigo Postal",1,5)

    })

    $(inputPais).on('keyup change',()=>{
        compruebaCamposString(inputPais, 2, 50)
        mensajeErrorString(erroresCampos[$(inputPais).prop("id")],"str",selectorErrorPais,"Pais",2,50)

    })
    $(inputEmail).on('keyup change',()=>{
        validarEmail(inputEmail,$(inputEmail).val())
        mensajeErrorString(erroresCampos[$(inputEmail).prop("id")],"mail",selectorErrorEmail,"Email",5,70)

    })
    $(inputClave).on('keyup change',()=>{
        compruebaCamposString(inputClave, 8, 32)
        mensajeErrorString(erroresCampos[$(inputClave).prop("id")],"str",selectorErrorClave,"Clave",8,32)

    })

    $(inputTerminos).on('click change',()=>{
        $(inputTerminos).is(':checked') ? erroresCampos[$(inputTerminos).prop("id")] = false : erroresCampos[$(inputTerminos).prop("id")] = true
        mensajeErrorString(erroresCampos[$(inputTerminos).prop("id")],"check",selectorErrorCheck,"terminos y condiciones",0,0)

    })

    $('#enviar').click(()=>{
        error = false;
        Object.values(erroresCampos).forEach(val => {
            console.log(val)
            if(val === true){
                error = true;
                return true
            }
        })
        if (error){
            alert("hay errores")
        } else {
            $('#formulario').submit()
        }
    })



    function compruebaCamposString(selectorInput, minimo, maximo ) {
        if ($(selectorInput).val().length >= minimo && $(selectorInput).val().length <= maximo ){
            erroresCampos[$(selectorInput).prop("id")] = false;
        } else {
            erroresCampos[$(selectorInput).prop("id")] = true;
        }
    }

    function validarEmail(selectorEmail,email) {
        var re = /\S+@\S+\.\S+/;

        re.test(email) ? erroresCampos[$(selectorEmail).prop("id")] = false : erroresCampos[$(selectorEmail).prop("id")] = true
        console.log(re.test(email))
        return re;
    }

    function validaTelefono(selectorTelefono) {
        telefono = $(selectorTelefono).val();
        if (telefono.length > 8 && telefono.length<13 && !isNaN(telefono)){
            erroresCampos[$(selectorTelefono).prop("id")] = false
        } else {
            erroresCampos[$(selectorTelefono).prop("id")] = true
        }
    }

    function mensajeErrorString(error, tipo ,selectorError, nombre, minimo, maximo ) {

        if (error){
            switch (tipo) {
               /**/ case "check":
                    $(selectorError).html(
                        "Debes aceptar los " + nombre +".").addClass("text-danger");
                    break;
                case "str":
                    $(selectorError).html(
                        "El " + nombre + " es obligatorio, como minimo " + minimo +
                        " caracteres y maximo " + maximo + " caracteres").addClass("text-danger");
                    break;
                case  "mail":
                    $(selectorError).html(
                        "El " + nombre + " no es valido").addClass("text-danger");
                    break;
                case  "nulo":
                    $(selectorError).html(
                        "El " + nombre + " no es valido, puede ser nulo o como maximo " + maximo +
                        " caracteres.").addClass("text-danger");
                    break;

            }
        } else {
            $(selectorError).html(
                nombre + " valid@.").addClass("text-success").removeClass("text-danger");

        }
    }







})
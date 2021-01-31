$(document).ready(function(){
    var emailError = true;
    var claveError = true;
    var selectorEmail = "#inputEmail"
    var selectorClave = "#inputPassword"
    var selectorMsgErrorClave = ".errorClave"
    var selectorMsgErrorEmail = ".errorEmail"
    var selectorSubmit = "#enviar"

    if ($(selectorEmail).val().length > 0 ){
        validarEmail($(selectorEmail).val()) ? emailError = false : emailError = true
    }
    $(selectorClave).val().length > 7 && $(selectorClave).val().length < 33 ? claveError = false : claveError = true

    $(selectorEmail).keyup(()=>{
        validarEmail($(selectorEmail).val()) ? emailError = false : emailError = true

        mostrarErrores(emailError,selectorEmail,selectorMsgErrorEmail,
            "Email valido","Introduce un email valido.")

    })

    $(selectorClave).keyup(()=>{
        $(selectorClave).val().length > 7 && $(selectorClave).val().length < 33? claveError = false : claveError = true
       mostrarErrores(claveError,selectorClave,selectorMsgErrorClave,
           "Clave valida","La clave debe tener de 8 a 32 caracteres.")
    })

    $(selectorSubmit).click(()=>{
        if (!claveError && !emailError){
            $("#formulario").submit();
        } else {
            alert("Debes tener todos los campos validos para proceder.")
        }
    })




})

function validarEmail(email) {
    var re = /\S+@\S+\.\S+/;
    return re.test(email);
}

function mostrarErrores(error,selectorInput,selectorMensaje,msgValido,mesageInvalido ) {
    if (error){
        $(selectorInput).addClass("is-invalid")
        $(selectorMensaje).html(mesageInvalido).addClass("text-danger");
    } else {
        $(selectorInput).addClass("is-valid").removeClass("is-invalid")
        $(selectorMensaje).html(msgValido).addClass("text-success").removeClass("text-danger");
    }
}


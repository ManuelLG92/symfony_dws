$(document).ready(function () {


    function mostrarErrores(error,selectorInput,selectorMensaje,msgValido,mesageInvalido) {
        if (error){
            $(selectorInput).addClass("is-invalid")
            $(selectorMensaje).html(mesageInvalido).addClass("text-danger");
        } else {
            $(selectorInput).addClass("is-valid").removeClass("is-invalid")
            $(selectorMensaje).html(msgValido).addClass("text-success").removeClass("text-danger");
        }
    }


})
var erroresCampos = {};
var errorCompra = false;

function actualizaCantidad(inputCantidad,precio,stock, inputTotal, inputHiden) {


    cantidad = $(inputCantidad).val()
    if (cantidad < 1 || isNaN(cantidad) || cantidad > stock ||
        cantidad == null || cantidad === ""){
        erroresCampos[$(inputCantidad).prop("name")] = true;
        //errorCampos = true;
       // $('#comprar').prop("disabled",true)
        $(inputTotal).html('Debes introducir un numero positivo hasta '+stock).addClass("text-danger")
       // console.log("cantidad: ",cantidad)
       // console.log($(inputHiden))
        //$(inputHiden).val(cantidad)
    } else {
        erroresCampos[$(inputCantidad).prop("name")] = false;
        //errorCampos = false;
        total = cantidad*precio;
        $(inputTotal).html('Precio total de este producto: '+total +' BC').addClass("text-success").removeClass("text-danger")
        console.log("total: ",total)
        //$('#comprar').prop("disabled",false)
        $(inputHiden).val(total)
        //console.log($('p[id^="t"]'))
        totalCompra = 0;
        //console.log($('input[name^="h"]'))
        $('input[name^="h"]').each((index, value)=>{
            totalCompra += parseInt($(value).val())
            console.log($(value).val())
        })
        $('#totalCompra').html("Total de tu compra: " + totalCompra)
       // $('#total').val(totalCompra)

    }


    //console.log($(inputCantidad).prop("name"))
}
function compruebaErrores() {
    //algunError = false;
    algunError = erroresCampos.includes(true);
    /*Object.values(erroresCampos).forEach(val => {
       // console.log(val)
        if(val === true){
            algunError = true;
            return true
        }
    });*/
    if (algunError){
        alert("Indica cantidades correctas, si no quieres algun articulo " +
            "pincha el boton eliminar")
        console.log("hay algun error")
    } else {
        $('#myFormulario').submit()
    }
}
function setTotal() {
    console.log(erroresCampos)

}







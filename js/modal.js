/*Lo de la modal, te explico como lo tengo montado yo:
Primero en la página que quieras q aparezca la modal añades un div con un id que lo identifique y que sea oculto.
*/

<div id="StatusDialog" style="display:none"></div>
/*
Entonces en el documentready de la pagina construyes la modal, con las opciones que quieres q tenga por defecto (tamaño, posicion, etc..) y defines los botones que va a tener y la acción que va a hacer cada boton:
*/
$("#StatusDialog").dialog({
        autoOpen: false, width: 400, height: 330, modal: true,
        position: ['middle', 50],
        dialogClass: 'StatusDialog',
        buttons: {
            Save: function () {
                if ($("#ContractStatusForm").validate().form()) {
                    $.post("/Contract/ChangeStatus",
                        $("#ContractStatusForm").serialize(),
                        function (data) {
                            $("#StatusDialog").html("The status has been changed")
                            disableButton('.StatusDialog', 'Save');
                        });
                }
            },
            Close: function () {
                $(this).dialog("close");
                enableButton('.StatusDialog', 'Save')
                $(location).attr('href', '/Contract/View/' + contractId);
            }
        }
    });

/*
Y tb en el documentready registras el evento click del boton que quieres q abra la modal. Esta parte quizás cambia un poco, por q esto esta hecho para MVC, llamo a una acción de un controlador y cargo la respuesta...
*/
$("#ChangeStatus").live("click", function () {
        $("#StatusDialog").html("")
                        .dialog("option", "title", "Change status")
                        .load("/Contract/ChangeStatus/" + contractId, function () {
                            $("#StatusDialog").dialog("open");
                        });
    });
/*
De esta manera, cuando hagas click en el boton con id #ChangeStatus cargara el div con id #StatusDialog con el contenido que te devuelva el load.
Despues cuando en la modal pulses un boton, llamaras al evento que hayas definido al definir la modal, ya sea, cerrar la modal, como hacer una llamada post al servidor enviando datos...
*/

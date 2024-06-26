export function observeDOMChanges(elementId,attributeToObserve) {
    const inputElement = $(`#${elementId}`)[0];
    if (!inputElement) {
        console.error(`No se encontró ningún elemento con el id ${elementId}`);
        return;
    }

    const config = { attributes: true, childList: true, subtree: true };

    const callback = function(mutationsList, observer) {
        for (let mutation of mutationsList) {
            if (mutation.type === 'attributes' && mutation.attributeName === attributeToObserve) {
                Swal.fire({
                    title: "Se han detectado cambios en el DOM",
                    text: `Se ha modificado el atributo ${attributeToObserve}`,
                    allowOutsideClick: false,
                }).then((result) => {
                    if (result.isConfirmed){
                        location.reload();
                    }
                });
            }
        }
    }

    const observer = new MutationObserver(callback);

    // Empieza a observar el input objetivo con las opciones de configuración
    observer.observe(inputElement, config);
}
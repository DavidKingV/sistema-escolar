function getCookie(nombre) {
    var nombreIgual = nombre + "=";
    var cookiesArray = document.cookie.split(';');
    for (var i = 0; i < cookiesArray.length; i++) {
        var cookie = cookiesArray[i].trim();
        if (cookie.indexOf(nombreIgual) == 0) {
            return cookie.substring(nombreIgual.length, cookie.length);
        }
    }
    return "";
}

const GetUserPhoto = async (Authorization) => {
    fetch('https://graph.microsoft.com/v1.0/me/photos/48x48/$value', {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + Authorization,
            'Content-Type': 'image/jpeg'
        }
    })
    .then(response => {
        if(response.ok) {
            return response.blob();
        } else {
            throw new Error('Error en la petición');
        }
    })
    .then(blob => {
        var url = URL.createObjectURL(blob);
        $("#profilePhoto").attr('src', url);
    })
    .catch(error => {
        console.error(error);
    });
};

$(document).ready(function() {
    GetUserPhoto(getCookie('MiAccto'));
});
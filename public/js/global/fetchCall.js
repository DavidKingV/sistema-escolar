export function sendFetch(url, method, data = null) {
  const options = {
    method,
    headers: {
      "Content-Type": "application/json",
    },
  };

  if (method === "GET" && data) {
    const params = new URLSearchParams(data);
    url += `?${params.toString()}`;
  } else if (method !== "GET" && data) {
    options.body = JSON.stringify(data);
  }

  return fetch(url, options);
}

export function sendFormData(url, method, data) {
  const options = {
    method: method,
    // El encabezado 'Content-Type' no es necesario para FormData
  };

  // Solo agregar el body si el método no es GET
  if (method !== "GET") {
    options.body = data; // Aquí simplemente pasas el FormData, no como JSON
  }

  return fetch(url, options);
}

export function enviarPeticionAjax(url, metodo, datos) {
  return $.ajax({
    url: url,
    type: metodo,
    data: datos,
  });
}

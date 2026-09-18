function isMicrosoftSession() {
  return document.getElementById("sidebar")?.dataset.authSource === "microsoft";
}

export async function confirmSensitiveAction({
  title,
  confirmButtonText = "Continuar",
}) {
  const microsoftSession = isMicrosoftSession();
  const options = {
    title,
    text: microsoftSession
      ? "Confirma la operación para continuar. Si es necesario, Microsoft solicitará nuevamente tu identidad."
      : "Ingresa tu contraseña para continuar",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    confirmButtonText,
    cancelButtonText: "Cancelar",
    allowOutsideClick: false,
  };

  if (!microsoftSession) {
    options.input = "password";
    options.inputPlaceholder = "Contraseña";
    options.inputAttributes = {
      autocapitalize: "off",
      autocorrect: "off",
    };
    options.inputValidator = (value) => {
      if (!value) {
        return "Debes ingresar tu contraseña";
      }
    };
  }

  const result = await Swal.fire(options);

  return {
    isConfirmed: result.isConfirmed,
    password: microsoftSession ? null : result.value,
  };
}

export async function handleSensitiveActionResponse(response) {
  if (response?.code !== "MICROSOFT_REAUTH_REQUIRED" || !response?.reauthUrl) {
    return false;
  }

  const result = await Swal.fire({
    icon: "info",
    title: "Confirma tu identidad",
    text: response.message,
    showCancelButton: true,
    confirmButtonText: "Continuar con Microsoft",
    cancelButtonText: "Cancelar",
    allowOutsideClick: false,
  });

  if (result.isConfirmed) {
    window.location.assign(response.reauthUrl);
  }

  return true;
}

const reauthenticationResult = new URLSearchParams(window.location.search).get(
  "reauth",
);

if (reauthenticationResult) {
  const successful = reauthenticationResult === "microsoft-success";

  Swal.fire({
    icon: successful ? "success" : "error",
    title: successful
      ? "Identidad confirmada"
      : "No fue posible confirmar tu identidad",
    text: successful
      ? "Ya puedes repetir la operación solicitada."
      : "Intenta nuevamente o cancela la operación.",
  });

  const currentUrl = new URL(window.location.href);
  currentUrl.searchParams.delete("reauth");
  window.history.replaceState({}, "", currentUrl.toString());
}

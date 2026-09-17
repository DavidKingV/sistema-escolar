export async function loadModal(url, payload, target) {
  const response = await fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "text/html, application/json",
    },
    body: JSON.stringify(payload),
  });

  if (!response.ok) {
    const contentType = response.headers.get("content-type") ?? "";
    const error = contentType.includes("application/json")
      ? await response.json()
      : { message: await response.text() };

    throw new Error(error.message || "No fue posible cargar el modal.");
  }

  const html = await response.text();
  $(target).html(html);

  return html;
}

export function registerLazyModal({ modal, target, url, payload = () => ({}) }) {
  let loaded = false;
  let loading = null;
  const targetElement = document.querySelector(target);

  // El contenido del modal vive en su vista; la página conserva únicamente
  // el contenedor y los controles externos que ya tienen eventos asociados.
  targetElement?.replaceChildren();

  document.addEventListener("click", async (event) => {
    const trigger = event.target.closest(`[data-bs-target="${modal}"]`);
    if (!trigger || loaded) return;

    event.preventDefault();
    event.stopImmediatePropagation();

    try {
      loading ??= loadModal(url, payload(trigger), target);
      await loading;
      loaded = true;
      document.querySelector(target)?.dispatchEvent(new CustomEvent("modal:loaded", { bubbles: true }));
      trigger.click();
    } catch (error) {
      loading = null;
      if (typeof Swal !== "undefined") {
        Swal.fire({ icon: "error", title: "Error", text: error.message });
      }
    }
  }, true);

  return async () => {
    if (!loaded) {
      loading ??= loadModal(url, payload(null), target);
      await loading;
      loaded = true;
      document.querySelector(target)?.dispatchEvent(new CustomEvent("modal:loaded", { bubbles: true }));
    }
  };
}

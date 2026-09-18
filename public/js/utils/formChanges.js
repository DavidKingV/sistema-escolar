// Normaliza números para comparar correctamente
const normalizeValue = (val) => {
  const value = String(val ?? "").trim();

  if (value === "") return "";

  return Number.isNaN(Number(value)) ? value : String(Number(value));
};

export function createFormObserver({ form, saveButton, getCurrentData }) {
  let originalData = {};

  const saveSnapshot = () => {
    originalData = { ...getCurrentData() };
    $(saveButton).prop("disabled", true);
  };

  const reset = () => {
    originalData = {};
    $(saveButton).prop("disabled", true);
  };

  const hasChanges = () => {
    if (!Object.keys(originalData).length) return false;

    const current = getCurrentData();

    const keys = new Set([
      ...Object.keys(originalData),
      ...Object.keys(current),
    ]);

    return [...keys].some((key) => current[key] !== originalData[key]);
  };

  const observe = () => {
    $(form)
      .find("input, textarea, select")
      .off(".formObserver")
      .on("input.formObserver change.formObserver", () => {
        $(saveButton).prop("disabled", !hasChanges());
      });
  };

  return {
    observe,
    saveSnapshot,
    hasChanges,
    reset,
  };
}

export function serializeForm(form) {
  const data = {};

  $(form)
    .find("input, textarea, select")
    .each(function () {
      const name = this.name;
      if (!name) return;

      data[name] = normalizeValue($(this).val());
    });

  return data;
}

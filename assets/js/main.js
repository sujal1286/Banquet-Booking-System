document
  .querySelector("[data-menu-toggle]")
  ?.addEventListener("click", () =>
    document.querySelector("[data-menu]")?.classList.toggle("open"),
  );
document
  .querySelectorAll("[data-autohide]")
  .forEach((el) => setTimeout(() => el.remove(), 5000));

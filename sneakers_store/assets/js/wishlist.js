document.addEventListener("DOMContentLoaded", () => {

  document.querySelectorAll("form").forEach((form) => {
    const removeInput = form.querySelector("input[name='remove_id']");
    if (removeInput) {
      form.addEventListener("submit", (e) => {
        if (!confirm("Remove this item from your wishlist?")) {
          e.preventDefault();
        }
      });
    }
  });

  document.querySelectorAll("form").forEach((form) => {
    const addInput = form.querySelector("input[name='add_to_cart_id']");
    if (addInput) {
      form.addEventListener("submit", () => {
        const btn = form.querySelector("button[type='submit']");
        if (btn) {
          btn.textContent = "Adding...";
          btn.disabled    = true;
          btn.style.opacity = "0.7";
        }
      });
    }
  });

  document.querySelectorAll(".product-card").forEach((card, i) => {
    card.style.opacity = "0";
    card.style.transform = "translateY(12px)";
    card.style.transition = `opacity 0.3s ease ${i * 0.06}s, transform 0.3s ease ${i * 0.06}s`;
    requestAnimationFrame(() => {
      card.style.opacity = "1";
      card.style.transform = "translateY(0)";
    });
  });

});
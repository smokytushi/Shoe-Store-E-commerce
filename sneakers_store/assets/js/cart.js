document.addEventListener("DOMContentLoaded", () => {

    function recalcGrandTotal() {

        let grand = 0;

        document.querySelectorAll(".qty-input").forEach(input => {

            const row = input.closest("tr");

            const price = parseFloat(
                row.querySelector(".item-price").dataset.price
            );

            let qty = parseInt(input.value);

            if (isNaN(qty))
                qty = 1;

            qty = Math.max(1, qty);
            qty = Math.min(20, qty);

            input.value = qty;

            const subtotal = price * qty;

            grand += subtotal;

            row.querySelector(".item-subtotal").textContent =
                "RM " + subtotal.toFixed(2);

        });

        const total = document.getElementById("grand-total");

        if (total) {

            total.textContent = "RM " + grand.toFixed(2);

            total.style.color = "#2a7a2a";

            setTimeout(() => {

                total.style.color = "";

            }, 500);

        }

    }

    function updateDatabase(input) {

        const cartID = input.dataset.cartId;

        const quantity = input.value;

        fetch("update_cart.php", {

            method: "POST",

            headers: {

                "Content-Type":
                    "application/x-www-form-urlencoded"

            },

            body:
                "cart_id=" +
                encodeURIComponent(cartID) +
                "&quantity=" +
                encodeURIComponent(quantity)

        })
        .then(response => response.json())
        .then(data => {

            if (!data.success) {

                alert(data.message);

            }

        })
        .catch(error => {

            console.error(error);

        });

    }

    document.querySelectorAll(".qty-input").forEach(input => {

        input.addEventListener("change", () => {

            recalcGrandTotal();

            updateDatabase(input);

        });

    });

    document.querySelectorAll("form").forEach(form => {

        const action = form.querySelector(
            "input[name='action']"
        );

        if (action && action.value === "remove") {

            form.addEventListener("submit", e => {

                if (!confirm(
                    "Remove this item from your cart?"
                )) {

                    e.preventDefault();

                }

            });

        }

    });

    const checkout = document.querySelector(
        "a[href='checkout.php']"
    );

    if (checkout) {

        let hasItems = false;

        document.querySelectorAll(".qty-input")
            .forEach(input => {

                if (parseInt(input.value) > 0)
                    hasItems = true;

            });

        if (!hasItems) {

            checkout.style.opacity = ".5";

            checkout.style.pointerEvents = "none";

        }

    }

    recalcGrandTotal();

});
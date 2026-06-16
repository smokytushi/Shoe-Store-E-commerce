<?php
session_start();

// Customer must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Customer must complete checkout first
if (!isset($_SESSION['shipping'])) {
    header("Location: checkout.php");
    exit();
}

$error_msg = "";
$payment_method = "";

$allowed_methods = [
    "Credit Card",
    "Online Banking",
    "Atome"
];

$allowed_banks = [
    "Maybank",
    "CIMB Bank",
    "Public Bank",
    "RHB Bank",
    "Hong Leong Bank",
    "Bank Islam"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $payment_method = trim($_POST["payment_method"] ?? "");

    if (!in_array($payment_method, $allowed_methods, true)) {
        $error_msg = "Please select a valid payment method.";
    } elseif ($payment_method === "Credit Card") {
        $card_name = trim($_POST["card_name"] ?? "");
        $card_number = preg_replace(
            "/\D/",
            "",
            $_POST["card_number"] ?? ""
        );
        $expiry_date = trim($_POST["expiry_date"] ?? "");
        $cvv = trim($_POST["cvv"] ?? "");

        if (
            $card_name === "" ||
            $card_number === "" ||
            $expiry_date === "" ||
            $cvv === ""
        ) {
            $error_msg = "Please complete all card information.";
        } elseif (!preg_match("/^[0-9]{13,19}$/", $card_number)) {
            $error_msg = "Please enter a valid card number.";
        } elseif (!preg_match("/^(0[1-9]|1[0-2])\/[0-9]{2}$/", $expiry_date)) {
            $error_msg = "Expiry date must use MM/YY format.";
        } elseif (!preg_match("/^[0-9]{3,4}$/", $cvv)) {
            $error_msg = "Please enter a valid CVV.";
        }
    } elseif ($payment_method === "Online Banking") {
        $bank_name = trim($_POST["bank_name"] ?? "");

        if (!in_array($bank_name, $allowed_banks, true)) {
            $error_msg = "Please select a valid bank.";
        }
    } elseif ($payment_method === "Atome") {
        $atome_phone = trim($_POST["atome_phone"] ?? "");

        if (!preg_match("/^[0-9+\-\s]{9,15}$/", $atome_phone)) {
            $error_msg = "Please enter a valid Atome phone number.";
        }
    }

    if ($error_msg === "") {
        // Mock transaction result
        $transaction_reference =
            "TXN-" .
            date("YmdHis") .
            "-" .
            random_int(1000, 9999);

        /*
         * Do not store card numbers, CVV codes,
         * or banking credentials in the session.
         */
        $_SESSION["payment"] = [
            "payment_method" => $payment_method,
            "payment_status" => "Completed",
            "transaction_reference" => $transaction_reference
        ];

        header("Location: confirmation.php");
        exit();
    }
}
?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Payment | Sneakers Store</title>

<link rel="stylesheet" href="assets/css/style.css">

<style>
    body {
        margin: 0;
        padding: 0;
        background-color: #f2f2f2;
        font-family: Arial, sans-serif;
    }

    .payment-container {
        width: 90%;
        max-width: 700px;
        margin: 40px auto;
        padding: 30px;
        background-color: #ffffff;
        border-radius: 8px;
        box-sizing: border-box;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }

    .payment-container h1 {
        margin-top: 0;
        margin-bottom: 8px;
        text-align: center;
        color: #222222;
    }

    .subtitle {
        margin-bottom: 25px;
        text-align: center;
        color: #666666;
    }

    .shipping-summary {
        margin-bottom: 25px;
        padding: 15px;
        background-color: #f7f7f7;
        border-radius: 6px;
    }

    .shipping-summary h3 {
        margin-top: 0;
        margin-bottom: 10px;
    }

    .shipping-summary p {
        margin: 5px 0;
        color: #444444;
    }

    .payment-options {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 22px;
    }

    .payment-option {
        position: relative;
    }

    .payment-option input {
        position: absolute;
        opacity: 0;
    }

    .payment-option label {
        display: block;
        padding: 15px 10px;
        border: 2px solid #dddddd;
        border-radius: 6px;
        cursor: pointer;
        text-align: center;
        font-weight: bold;
    }

    .payment-option input:checked + label {
        border-color: #222222;
        background-color: #eeeeee;
    }

    .payment-panel {
        display: none;
        margin-bottom: 20px;
        padding: 20px;
        border: 1px solid #dddddd;
        border-radius: 6px;
    }

    .payment-panel.active {
        display: block;
    }

    .form-row {
        display: flex;
        gap: 15px;
    }

    .form-group {
        flex: 1;
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        margin-bottom: 7px;
        color: #333333;
        font-size: 14px;
        font-weight: bold;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 11px;
        border: 1px solid #cccccc;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 15px;
    }

    .error-message {
        margin-bottom: 20px;
        padding: 12px;
        background-color: #ffdddd;
        border: 1px solid #ffaaaa;
        border-radius: 4px;
        color: #b00020;
        text-align: center;
    }

    .notice {
        margin-bottom: 20px;
        padding: 12px;
        background-color: #fff6d8;
        border-radius: 4px;
        color: #5f4b00;
        font-size: 14px;
    }

    .button-group {
        display: flex;
        gap: 12px;
    }

    .btn {
        flex: 1;
        padding: 13px;
        border: none;
        border-radius: 4px;
        color: #ffffff;
        cursor: pointer;
        font-size: 15px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
    }

    .btn-back {
        background-color: #666666;
    }

    .btn-back:hover {
        background-color: #777777;
    }

    .btn-pay {
        background-color: #222222;
    }

    .btn-pay:hover {
        background-color: #444444;
    }

    @media (max-width: 650px) {
        .payment-options {
            grid-template-columns: 1fr;
        }

        .form-row,
        .button-group {
            flex-direction: column;
        }

        .payment-container {
            margin: 20px auto;
            padding: 20px;
        }
    }
</style>


</head>

<body>

<div class="payment-container">
    <h1>Payment</h1>


<p class="subtitle">
    Select your preferred payment method
</p>

<?php if ($error_msg !== ""): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($error_msg); ?>
    </div>
<?php endif; ?>

<div class="shipping-summary">
    <h3>Shipping Destination</h3>

    <p>
        <strong>Name:</strong>
        <?php
        echo htmlspecialchars(
            $_SESSION["shipping"]["full_name"]
        );
        ?>
    </p>

    <p>
        <strong>Telephone:</strong>
        <?php
        echo htmlspecialchars(
            $_SESSION["shipping"]["phone_number"]
        );
        ?>
    </p>

    <p>
        <strong>Address:</strong>
        <?php
        echo htmlspecialchars(
            $_SESSION["shipping"]["address"] .
            ", " .
            $_SESSION["shipping"]["postcode"] .
            " " .
            $_SESSION["shipping"]["city"] .
            ", " .
            $_SESSION["shipping"]["state"]
        );
        ?>
    </p>
</div>

<div class="notice">
    This is a simulated payment page. Do not enter real card or banking information.
</div>

<form
    id="paymentForm"
    action="payment.php"
    method="POST"
>
    <div class="payment-options">
        <div class="payment-option">
            <input
                type="radio"
                id="credit_card"
                name="payment_method"
                value="Credit Card"
                <?php
                echo $payment_method === "Credit Card"
                    ? "checked"
                    : "";
                ?>
            >

            <label for="credit_card">
                Credit Card
            </label>
        </div>

        <div class="payment-option">
            <input
                type="radio"
                id="online_banking"
                name="payment_method"
                value="Online Banking"
                <?php
                echo $payment_method === "Online Banking"
                    ? "checked"
                    : "";
                ?>
            >

            <label for="online_banking">
                Online Banking
            </label>
        </div>

        <div class="payment-option">
            <input
                type="radio"
                id="atome"
                name="payment_method"
                value="Atome"
                <?php
                echo $payment_method === "Atome"
                    ? "checked"
                    : "";
                ?>
            >

            <label for="atome">
                Atome
            </label>
        </div>
    </div>

    <div
        id="creditCardPanel"
        class="payment-panel"
    >
        <div class="form-group">
            <label for="card_name">
                Cardholder Name
            </label>

            <input
                type="text"
                id="card_name"
                name="card_name"
                placeholder="Test User"
            >
        </div>

        <div class="form-group">
            <label for="card_number">
                Card Number
            </label>

            <input
                type="text"
                id="card_number"
                name="card_number"
                placeholder="1111 2222 3333 4444"
                maxlength="23"
                inputmode="numeric"
            >
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="expiry_date">
                    Expiry Date
                </label>

                <input
                    type="text"
                    id="expiry_date"
                    name="expiry_date"
                    placeholder="MM/YY"
                    maxlength="5"
                >
            </div>

            <div class="form-group">
                <label for="cvv">
                    CVV
                </label>

                <input
                    type="password"
                    id="cvv"
                    name="cvv"
                    placeholder="123"
                    maxlength="4"
                    inputmode="numeric"
                >
            </div>
        </div>
    </div>

    <div
        id="onlineBankingPanel"
        class="payment-panel"
    >
        <div class="form-group">
            <label for="bank_name">
                Select Bank
            </label>

            <select
                id="bank_name"
                name="bank_name"
            >
                <option value="">
                    Select a bank
                </option>

                <?php foreach ($allowed_banks as $bank): ?>
                    <option
                        value="<?php echo htmlspecialchars($bank); ?>"
                    >
                        <?php echo htmlspecialchars($bank); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <p>
            You will be redirected to a simulated banking verification page.
        </p>
    </div>

    <div
        id="atomePanel"
        class="payment-panel"
    >
        <div class="form-group">
            <label for="atome_phone">
                Atome Telephone Number
            </label>

            <input
                type="tel"
                id="atome_phone"
                name="atome_phone"
                placeholder="0123456789"
                maxlength="15"
            >
        </div>

        <p>
            A simulated verification request will be processed.
        </p>
    </div>

    <div class="button-group">
        <a
            href="checkout.php"
            class="btn btn-back"
        >
            Back
        </a>

        <button
            type="submit"
            class="btn btn-pay"
        >
            Confirm Payment
        </button>
    </div>
</form>

</div>

<script>
const paymentMethodInputs =
    document.querySelectorAll(
        'input[name="payment_method"]'
    );

const creditCardPanel =
    document.getElementById("creditCardPanel");

const onlineBankingPanel =
    document.getElementById("onlineBankingPanel");

const atomePanel =
    document.getElementById("atomePanel");

function displayPaymentPanel() {
    creditCardPanel.classList.remove("active");
    onlineBankingPanel.classList.remove("active");
    atomePanel.classList.remove("active");

    const selectedMethod =
        document.querySelector(
            'input[name="payment_method"]:checked'
        );

    if (!selectedMethod) {
        return;
    }

    if (selectedMethod.value === "Credit Card") {
        creditCardPanel.classList.add("active");
    } else if (
        selectedMethod.value === "Online Banking"
    ) {
        onlineBankingPanel.classList.add("active");
    } else if (selectedMethod.value === "Atome") {
        atomePanel.classList.add("active");
    }
}

paymentMethodInputs.forEach(function (input) {
    input.addEventListener(
        "change",
        displayPaymentPanel
    );
});

document
    .getElementById("card_number")
    .addEventListener("input", function () {
        let digits = this.value.replace(/\D/g, "");

        this.value = digits
            .replace(/(.{4})/g, "$1 ")
            .trim();
    });

document
    .getElementById("expiry_date")
    .addEventListener("input", function () {
        let digits = this.value.replace(/\D/g, "");

        if (digits.length > 2) {
            digits =
                digits.substring(0, 2) +
                "/" +
                digits.substring(2, 4);
        }

        this.value = digits;
    });

document
    .getElementById("paymentForm")
    .addEventListener("submit", function (event) {
        const selectedMethod =
            document.querySelector(
                'input[name="payment_method"]:checked'
            );

        if (!selectedMethod) {
            alert("Please select a payment method.");
            event.preventDefault();
            return;
        }

        if (selectedMethod.value === "Credit Card") {
            const cardName =
                document
                    .getElementById("card_name")
                    .value
                    .trim();

            const cardNumber =
                document
                    .getElementById("card_number")
                    .value
                    .replace(/\D/g, "");

            const expiryDate =
                document
                    .getElementById("expiry_date")
                    .value
                    .trim();

            const cvv =
                document
                    .getElementById("cvv")
                    .value
                    .trim();

            if (
                cardName === "" ||
                !/^[0-9]{13,19}$/.test(cardNumber) ||
                !/^(0[1-9]|1[0-2])\/[0-9]{2}$/.test(
                    expiryDate
                ) ||
                !/^[0-9]{3,4}$/.test(cvv)
            ) {
                alert(
                    "Please enter valid mock card information."
                );

                event.preventDefault();
            }
        } else if (
            selectedMethod.value === "Online Banking"
        ) {
            const bankName =
                document
                    .getElementById("bank_name")
                    .value;

            if (bankName === "") {
                alert("Please select a bank.");
                event.preventDefault();
            }
        } else if (
            selectedMethod.value === "Atome"
        ) {
            const atomePhone =
                document
                    .getElementById("atome_phone")
                    .value
                    .trim();

            if (
                !/^[0-9+\-\s]{9,15}$/.test(
                    atomePhone
                )
            ) {
                alert(
                    "Please enter a valid Atome telephone number."
                );

                event.preventDefault();
            }
        }
    });

displayPaymentPanel();
</script>

</body>
</html>

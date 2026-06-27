<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db_connect.php';

// Customer must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error_msg = "";

// Keep entered values if validation fails
$full_name = "";
$phone_number = "";
$address = "";
$city = "";
$state = "";
$postcode = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $phone_number = trim($_POST["phone_number"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $city = trim($_POST["city"] ?? "");
    $state = trim($_POST["state"] ?? "");
    $postcode = trim($_POST["postcode"] ?? "");

    // Server-side validation
    if (
        $full_name === "" ||
        $phone_number === "" ||
        $address === "" ||
        $city === "" ||
        $state === "" ||
        $postcode === ""
    ) {
        $error_msg = "Please complete all shipping information.";
    } elseif (strlen($full_name) < 3) {
        $error_msg = "Please enter a valid full name.";
    } elseif (!preg_match("/^[0-9+\-\s]{9,15}$/", $phone_number)) {
        $error_msg = "Please enter a valid telephone number.";
    } elseif (!preg_match("/^[0-9]{5}$/", $postcode)) {
        $error_msg = "Please enter a valid 5-digit postcode.";
    } elseif (strlen($address) < 10) {
        $error_msg = "Please enter a complete shipping address.";
    } else {
        // Store shipping information for the payment and confirmation pages
        $_SESSION["shipping"] = [
            "full_name" => $full_name,
            "phone_number" => $phone_number,
            "address" => $address,
            "city" => $city,
            "state" => $state,
            "postcode" => $postcode
        ];

        header("Location: payment.php");
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

    <title>Checkout | Sneakers Store</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f2f2f2;
            font-family: Arial, sans-serif;
        }

        .checkout-container {
            width: 90%;
            max-width: 650px;
            margin: 40px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-sizing: border-box;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .checkout-container h1 {
            margin-top: 0;
            margin-bottom: 8px;
            text-align: center;
            color: #222222;
        }

        .checkout-container .subtitle {
            margin-bottom: 25px;
            text-align: center;
            color: #666666;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-group {
            flex: 1;
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #333333;
            font-size: 14px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 11px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            font-size: 15px;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #222222;
            outline: none;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
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

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 10px;
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

        .btn-continue {
            background-color: #222222;
        }

        .btn-continue:hover {
            background-color: #444444;
        }

        .field-error {
            margin-top: 5px;
            color: #cc0000;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .form-row,
            .button-group {
                flex-direction: column;
            }

            .checkout-container {
                margin: 20px auto;
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="checkout-container">
    <h1>Checkout</h1>
    <p class="subtitle">Enter your shipping information</p>

    <?php if ($error_msg !== ""): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>

    <form
        id="checkoutForm"
        action="checkout.php"
        method="POST"
    >
        <div class="form-group">
            <label for="full_name">Full Name</label>

            <input
                type="text"
                id="full_name"
                name="full_name"
                value="<?php echo htmlspecialchars($full_name); ?>"
                minlength="3"
                maxlength="100"
                required
            >

            <div
                class="field-error"
                id="fullNameError"
            ></div>
        </div>

        <div class="form-group">
            <label for="phone_number">Telephone Number</label>

            <input
                type="tel"
                id="phone_number"
                name="phone_number"
                value="<?php echo htmlspecialchars($phone_number); ?>"
                placeholder="Example: 0123456789"
                maxlength="15"
                required
            >

            <div
                class="field-error"
                id="phoneError"
            ></div>
        </div>

        <div class="form-group">
            <label for="address">Shipping Address</label>

            <textarea
                id="address"
                name="address"
                minlength="10"
                maxlength="255"
                required
            ><?php echo htmlspecialchars($address); ?></textarea>

            <div
                class="field-error"
                id="addressError"
            ></div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="city">City</label>

                <input
                    type="text"
                    id="city"
                    name="city"
                    value="<?php echo htmlspecialchars($city); ?>"
                    maxlength="100"
                    required
                >
            </div>

            <div class="form-group">
                <label for="postcode">Postcode</label>

                <input
                    type="text"
                    id="postcode"
                    name="postcode"
                    value="<?php echo htmlspecialchars($postcode); ?>"
                    placeholder="63000"
                    maxlength="5"
                    inputmode="numeric"
                    required
                >

                <div
                    class="field-error"
                    id="postcodeError"
                ></div>
            </div>
        </div>

        <div class="form-group">
            <label for="state">State or Federal Territory</label>

            <select
                id="state"
                name="state"
                required
            >
                <option value="">Select a state</option>

                <?php
                $states = [
                    "Johor",
                    "Kedah",
                    "Kelantan",
                    "Melaka",
                    "Negeri Sembilan",
                    "Pahang",
                    "Penang",
                    "Perak",
                    "Perlis",
                    "Sabah",
                    "Sarawak",
                    "Selangor",
                    "Terengganu",
                    "Kuala Lumpur",
                    "Labuan",
                    "Putrajaya"
                ];

                foreach ($states as $state_option) {
                    $selected = (
                        $state === $state_option
                    ) ? "selected" : "";

                    echo '<option value="' .
                        htmlspecialchars($state_option) .
                        '" ' .
                        $selected .
                        '>' .
                        htmlspecialchars($state_option) .
                        '</option>';
                }
                ?>
            </select>
        </div>

        <div class="button-group">
            <a
                href="browse.php"
                class="btn btn-back"
            >
                Back
            </a>

            <button
                type="submit"
                class="btn btn-continue"
            >
                Continue to Payment
            </button>
        </div>
    </form>
</div>

<script>
const checkoutForm = document.getElementById("checkoutForm");

checkoutForm.addEventListener("submit", function (event) {
    const fullName = document
        .getElementById("full_name")
        .value
        .trim();

    const phoneNumber = document
        .getElementById("phone_number")
        .value
        .trim();

    const address = document
        .getElementById("address")
        .value
        .trim();

    const postcode = document
        .getElementById("postcode")
        .value
        .trim();

    const fullNameError =
        document.getElementById("fullNameError");

    const phoneError =
        document.getElementById("phoneError");

    const addressError =
        document.getElementById("addressError");

    const postcodeError =
        document.getElementById("postcodeError");

    fullNameError.textContent = "";
    phoneError.textContent = "";
    addressError.textContent = "";
    postcodeError.textContent = "";

    let isValid = true;

    const phonePattern = /^[0-9+\-\s]{9,15}$/;
    const postcodePattern = /^[0-9]{5}$/;

    if (fullName.length < 3) {
        fullNameError.textContent =
            "Please enter your full name.";

        isValid = false;
    }

    if (!phonePattern.test(phoneNumber)) {
        phoneError.textContent =
            "Enter a valid telephone number.";

        isValid = false;
    }

    if (address.length < 10) {
        addressError.textContent =
            "Please enter a complete shipping address.";

        isValid = false;
    }

    if (!postcodePattern.test(postcode)) {
        postcodeError.textContent =
            "Postcode must contain exactly 5 digits.";

        isValid = false;
    }

    if (!isValid) {
        event.preventDefault();
    }
});
</script>

</body>
</html>
```
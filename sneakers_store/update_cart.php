<?php

require_once 'includes/db_connect.php';

header('Content-Type: application/json');

if (
    !isset($_POST['cart_id']) ||
    !isset($_POST['quantity'])
) {

    echo json_encode([
        "success" => false,
        "message" => "Missing data"
    ]);

    exit;

}

$cart_id = (int) $_POST['cart_id'];

$quantity = max(
    1,
    min(
        20,
        (int) $_POST['quantity']
    )
);

$stmt = $conn->prepare(
    "UPDATE cart
     SET quantity=?
     WHERE cart_id=?"
);

$stmt->bind_param(
    "ii",
    $quantity,
    $cart_id
);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true
    ]);

}
else {

    echo json_encode([
        "success" => false,
        "message" => "Database error"
    ]);

}
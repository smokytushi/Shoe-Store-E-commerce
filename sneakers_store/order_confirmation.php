<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db_connect.php';

// ── Guards ────────────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_order_id'])) {
    header("Location: index.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$order_id = intval($_SESSION['last_order_id']);

// ── Fetch order header ────────────────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.order_status,
           o.total_amount, o.shipping_address,
           p.payment_method, p.payment_status,
           d.expected_delivery
    FROM orders o
    LEFT JOIN payments p       ON p.order_id  = o.order_id
    LEFT JOIN delivery_status d ON d.order_id = o.order_id
    WHERE o.order_id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: index.php");
    exit();
}

// ── Fetch order line items ────────────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT od.quantity, od.price_at_purchase,
           p.product_name, p.image_url
    FROM order_details od
    JOIN products p ON p.product_id = od.product_id
    WHERE od.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
$stmt->close();

// Clear last_order_id from session so refreshing can't re-show stale data
unset($_SESSION['last_order_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed | Sneakers Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            margin: 0; padding: 0;
            background: #f2f2f2;
            font-family: Arial, sans-serif;
        }
        .confirm-wrap {
            width: 90%;
            max-width: 720px;
            margin: 50px auto 80px;
        }

        /* ── Success banner ── */
        .success-banner {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,.08);
            padding: 40px 30px 30px;
            text-align: center;
            margin-bottom: 24px;
        }
        .checkmark {
            font-size: 56px;
            line-height: 1;
            margin-bottom: 12px;
        }
        .success-banner h1 {
            margin: 0 0 8px;
            font-size: 28px;
            color: #1a7a1a;
        }
        .success-banner p {
            color: #555;
            margin: 0;
        }
        .order-ref {
            display: inline-block;
            margin-top: 14px;
            padding: 6px 16px;
            background: #f0f0f0;
            border-radius: 20px;
            font-size: 14px;
            color: #333;
            font-weight: bold;
        }

        /* ── Info panels ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .panel {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            padding: 20px 22px;
        }
        .panel h3 {
            margin: 0 0 12px;
            font-size: 14px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 8px;
        }
        .panel p {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }
        .panel strong { color: #111; }

        /* ── Status badge ── */
        .badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }
        .badge-pending    { background:#fff3cd; color:#856404; }
        .badge-processing { background:#cce5ff; color:#004085; }
        .badge-shipped    { background:#ffe0b2; color:#e65100; }
        .badge-delivered  { background:#d4edda; color:#155724; }
        .badge-completed  { background:#d4edda; color:#155724; }
        .badge-failed     { background:#f8d7da; color:#721c24; }

        /* ── Order items table ── */
        .panel.full { grid-column: 1 / -1; }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            padding: 11px 10px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        th { color: #888; font-size: 13px; font-weight: 600; }
        td img {
            width: 52px; height: 52px;
            object-fit: cover; border-radius: 6px;
            vertical-align: middle;
        }
        .item-name { font-weight: bold; color: #222; }
        .total-row td {
            border-top: 2px solid #eee;
            border-bottom: none;
            font-size: 15px;
            font-weight: bold;
            padding-top: 14px;
        }

        /* ── Actions ── */
        .actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }
        .btn {
            flex: 1;
            display: block;
            padding: 14px;
            text-align: center;
            border-radius: 6px;
            text-decoration: none;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }
        .btn-primary   { background: #222; color: #fff; }
        .btn-secondary { background: #fff; color: #333; border: 1px solid #ccc; }
        .btn:hover { opacity: .85; }

        @media (max-width: 600px) {
            .info-grid { grid-template-columns: 1fr; }
            .actions   { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="confirm-wrap">

    <!-- ── Success banner ── -->
    <div class="success-banner">
        <div class="checkmark">✅</div>
        <h1>Order Confirmed!</h1>
        <p>Thank you for your purchase. Your order has been received and is being processed.</p>
        <span class="order-ref">Order #<?= $order['order_id'] ?></span>
    </div>

    <!-- ── Info grid ── -->
    <div class="info-grid">

        <!-- Shipping -->
        <div class="panel">
            <h3>Shipping To</h3>
            <p><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
        </div>

        <!-- Payment & Delivery -->
        <div class="panel">
            <h3>Payment & Delivery</h3>
            <p>
                <strong>Method:</strong>
                <?= htmlspecialchars($order['payment_method']) ?>
            </p>
            <p>
                <strong>Payment:</strong>
                <span class="badge badge-<?= strtolower($order['payment_status']) ?>">
                    <?= htmlspecialchars($order['payment_status']) ?>
                </span>
            </p>
            <p>
                <strong>Order Status:</strong>
                <span class="badge badge-<?= strtolower($order['order_status']) ?>">
                    <?= htmlspecialchars($order['order_status']) ?>
                </span>
            </p>
            <p>
                <strong>Est. Delivery:</strong>
                <?= date('d M Y', strtotime($order['expected_delivery'])) ?>
            </p>
            <p>
                <strong>Order Date:</strong>
                <?= date('d M Y, g:i A', strtotime($order['order_date'])) ?>
            </p>
        </div>

        <!-- Order items — full width -->
        <div class="panel full">
            <h3>Items Ordered</h3>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items->fetch_assoc()):
                        $img = !empty($item['image_url'])
                             ? 'assets/images/' . htmlspecialchars($item['image_url'])
                             : 'assets/images/placeholder.jpg';
                        $sub = $item['price_at_purchase'] * $item['quantity'];
                    ?>
                    <tr>
                        <td><img src="<?= $img ?>" alt="<?= htmlspecialchars($item['product_name']) ?>"></td>
                        <td class="item-name"><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>RM <?= number_format($item['price_at_purchase'], 2) ?></td>
                        <td>RM <?= number_format($sub, 2) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="4" style="text-align:right;">Total Paid</td>
                        <td>RM <?= number_format($order['total_amount'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

    <!-- ── Actions ── -->
    <div class="actions">
        <a href="browse.php" class="btn btn-primary">Continue Shopping</a>
        <a href="user_dashboard.php" class="btn btn-secondary">My Dashboard</a>
    </div>

</div>

</body>
</html>
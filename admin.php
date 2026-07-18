<?php
session_start();

// Simple Hardcoded Admin Password
$admin_password = "admin"; // CHANGE THIS BEFORE DEPLOYING!

if (isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['logged_in'] = true;
    } else {
        $error = "Incorrect password.";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// If logged in, fetch orders
if ($logged_in) {
    require_once 'api/db_connect.php';
    
    // Handle status update
    if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
        $order_id = intval($_POST['order_id']);
        $new_status = $conn->real_escape_string($_POST['status']);
        $conn->query("UPDATE orders SET status='$new_status' WHERE id=$order_id");
    }

    $result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
    $orders = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Heritage Palaharam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans min-h-screen">

<?php if (!$logged_in): ?>
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-sm text-center">
            <h1 class="text-2xl font-bold text-amber-800 mb-6">Admin Login</h1>
            <?php if(isset($error)) echo "<p class='text-red-500 mb-4'>$error</p>"; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Enter Password" class="w-full px-4 py-2 border rounded-lg mb-4 focus:outline-none focus:border-amber-600" required>
                <button type="submit" class="w-full bg-amber-700 hover:bg-amber-800 text-white font-bold py-2 px-4 rounded-lg transition-colors">Login</button>
            </form>
        </div>
    </div>
<?php else: ?>
    <nav class="bg-amber-800 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="font-bold text-xl">Heritage Admin</span>
                </div>
                <div>
                    <a href="?logout=true" class="text-amber-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Recent Orders</h2>
            
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <?php if(count($orders) === 0): ?>
                        <li class="px-6 py-8 text-center text-gray-500">No orders yet.</li>
                    <?php endif; ?>
                    <?php foreach($orders as $order): ?>
                        <?php $cart = json_decode($order['cart_data'], true); ?>
                        <li class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-amber-600">Order #<?php echo $order['id']; ?></span>
                                    <span class="text-xs text-gray-500"><?php echo date('M d, Y - h:i A', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $order['status'] === 'New' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                    <form method="POST" class="flex items-center gap-2">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="text-xs border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500">
                                            <option value="New" <?php if($order['status']=='New') echo 'selected'; ?>>New</option>
                                            <option value="Processing" <?php if($order['status']=='Processing') echo 'selected'; ?>>Processing</option>
                                            <option value="Shipped" <?php if($order['status']=='Shipped') echo 'selected'; ?>>Shipped</option>
                                            <option value="Delivered" <?php if($order['status']=='Delivered') echo 'selected'; ?>>Delivered</option>
                                        </select>
                                        <button type="submit" name="update_status" class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs py-1 px-2 rounded transition-colors">Update</button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2 border-b pb-1">Customer Details</h4>
                                    <p class="text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                                    <p class="text-sm text-gray-600 flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[16px]">call</span> <?php echo htmlspecialchars($order['phone']); ?></p>
                                    <p class="text-sm text-gray-600 flex items-start gap-1 mt-1"><span class="material-symbols-outlined text-[16px] mt-0.5">location_on</span> <?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2 border-b pb-1">Order Summary</h4>
                                    <ul class="space-y-2 mb-3">
                                        <?php if(is_array($cart)): ?>
                                            <?php foreach($cart as $item): ?>
                                                <li class="flex justify-between text-sm">
                                                    <span class="text-gray-800"><?php echo htmlspecialchars($item['quantity']); ?>x <?php echo htmlspecialchars($item['name']); ?></span>
                                                    <span class="text-gray-600">₹<?php echo htmlspecialchars($item['price'] * $item['quantity']); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                    <div class="flex justify-between items-center border-t border-gray-200 pt-2">
                                        <span class="font-bold text-gray-800">Total</span>
                                        <span class="font-bold text-amber-600 text-lg">₹<?php echo htmlspecialchars($order['total_price']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </main>
<?php endif; ?>

</body>
</html>

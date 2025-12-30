<?php
require 'auth_check.php';
$title = "Tambah Transaksi";

// Fetch barang for JS
$barang = query("SELECT * FROM barang WHERE stok > 0");

// Handle Checkout
if (isset($_POST['checkout'])) {
    $cart = json_decode($_POST['cart_data'], true);
    $total_all = $_POST['total_bayar_input'];
    $user_id = $_SESSION['user_id'];
    $no_transaksi = 'TRX-' . time();
    $tanggal = date('Y-m-d H:i:s');
    
    // Begin Transaction
    mysqli_begin_transaction($conn);
    try {
        // Insert Parent
        mysqli_query($conn, "INSERT INTO penjualan (no_transaksi, user_id, total_bayar, tanggal) VALUES ('$no_transaksi', '$user_id', '$total_all', '$tanggal')");
        $penjualan_id = mysqli_insert_id($conn);
        
        foreach ($cart as $item) {
            $barang_id = $item['id'];
            $qty = $item['qty'];
            $price = $item['harga'];
            $subtotal = $qty * $price;
            
            // Insert Detail
            mysqli_query($conn, "INSERT INTO penjualan_detail (penjualan_id, barang_id, jumlah, harga_satuan, subtotal) VALUES ('$penjualan_id', '$barang_id', '$qty', '$price', '$subtotal')");
            
            // Update Stock
            mysqli_query($conn, "UPDATE barang SET stok = stok - $qty WHERE id = $barang_id");
        }
        
        mysqli_commit($conn);
        header("Location: transaksi.php");
        exit;
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}

require 'includes/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-140px)]">
    <!-- Product List -->
    <div class="lg:col-span-2 flex flex-col gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm sticky top-0 z-20">
            <input type="text" id="searchItem" placeholder="Cari barang..." class="form-input w-full rounded-lg border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-primary focus:border-primary">
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 overflow-y-auto pr-2" id="productList">
            <?php foreach($barang as $b): ?>
            <div class="product-item relative bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm cursor-pointer hover:border-primary transition group"
                 data-id="<?= $b['id'] ?>" 
                 data-name="<?= $b['nama_barang'] ?>" 
                 data-price="<?= $b['harga'] ?>" 
                 data-stok="<?= $b['stok'] ?>"
                 onclick='addToCart(<?= htmlspecialchars(json_encode($b), ENT_QUOTES) ?>)'>
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                             <div class="w-full aspect-[4/3] rounded-lg overflow-hidden bg-white mb-2 flex items-center justify-center border border-slate-100">
                                <?php if(!empty($b['gambar']) && file_exists('assets/products/'.$b['gambar'])): ?>
                                <img src="assets/products/<?= $b['gambar'] ?>" alt="<?= $b['nama_barang'] ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <?php else: ?>
                                <div class="p-4 bg-blue-50 text-blue-600 rounded-full">
                                    <span class="material-symbols-outlined text-3xl">view_in_ar</span>
                                </div>
                                <?php endif; ?>
                             </div>
                             <span class="absolute top-3 right-3 text-xs font-bold bg-white/90 backdrop-blur text-slate-700 px-2 py-1 rounded shadow-sm">Stok: <?= $b['stok'] ?></span>
                        </div>
                        <h4 class="font-bold text-slate-800 dark:text-white line-clamp-2"><?= $b['nama_barang'] ?></h4>
                        <p class="text-sm text-slate-500 mb-2"><?= $b['kode_barang'] ?></p>
                    </div>
                    <p class="font-bold text-primary text-lg">Rp <?= number_format($b['harga'], 0, ',', '.') ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Cart -->
    <div class="flex flex-col bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xl h-full">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined">shopping_cart</span> Keranjang
            </h3>
        </div>
        
        <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-3" id="cartItems">
            <!-- JS populated -->
            <div class="text-center text-slate-400 mt-10">Keranjang masih kosong</div>
        </div>
        
        <div class="p-4 bg-slate-50 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 rounded-b-xl">
            <div class="flex justify-between items-center mb-4">
                <span class="text-slate-600 dark:text-slate-300 font-medium">Total</span>
                <span class="text-2xl font-bold text-slate-900 dark:text-white" id="cartTotal">Rp 0</span>
            </div>
            
            <form method="post" id="checkoutForm">
                <input type="hidden" name="cart_data" id="cartDataInput">
                <input type="hidden" name="total_bayar_input" id="totalBayarInput">
                <button type="submit" name="checkout" id="btnCheckout" disabled class="w-full py-4 bg-primary disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold rounded-xl hover:bg-blue-600 transition shadow-lg">
                    BAYAR SEKARANG
                </button>
            </form>
        </div>
    </div>
</div>

<script>
let cart = [];

function addToCart(item) {
    // Check stock
    let existing = cart.find(c => c.id == item.id);
    if (existing) {
        if (existing.qty >= item.stok) {
            alert('Stok tidak mencukupi!');
            return;
        }
        existing.qty++;
    } else {
        cart.push({...item, qty: 1});
    }
    renderCart();
}

function updateQty(id, change) {
    let item = cart.find(c => c.id == id);
    if (!item) return;
    
    let newQty = item.qty + change;
    if (newQty > item.stok) {
        alert('Stok maksimul tercapai');
        return;
    }
    if (newQty < 1) {
        cart = cart.filter(c => c.id != id);
    } else {
        item.qty = newQty;
    }
    renderCart();
}

function removeFromCart(id) {
    cart = cart.filter(c => c.id != id);
    renderCart();
}

function renderCart() {
    let html = '';
    let total = 0;
    
    if (cart.length === 0) {
        $('#cartItems').html('<div class="text-center text-slate-400 mt-10">Keranjang masih kosong</div>');
        $('#btnCheckout').prop('disabled', true);
    } else {
        cart.forEach(item => {
            let subtotal = item.price * item.qty; // Note: PHP passed 'harga' but data attribute might be different or accessed differently. Let's ensure consistency.
            // Actually in addToCart(<?= json_encode($b) ?>), keys are database columns: harga, stok, id, nama_barang.
            subtotal = item.harga * item.qty;
            total += subtotal;
            
            html += `
            <div class="flex justify-between items-center bg-white border border-slate-100 p-3 rounded-lg shadow-sm">
                <div class="flex-1">
                    <p class="font-bold text-slate-800 text-sm">${item.nama_barang}</p>
                    <p class="text-xs text-slate-500">Rp ${parseInt(item.harga).toLocaleString()}</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="updateQty(${item.id}, -1)" class="w-6 h-6 flex items-center justify-center bg-slate-200 rounded text-slate-600 hover:bg-slate-300 font-bold">-</button>
                    <span class="font-medium text-sm w-4 text-center">${item.qty}</span>
                    <button onclick="updateQty(${item.id}, 1)" class="w-6 h-6 flex items-center justify-center bg-primary text-white rounded hover:bg-blue-600 font-bold">+</button>
                </div>
            </div>
            `;
        });
        $('#cartItems').html(html);
        $('#btnCheckout').prop('disabled', false);
    }
    
    $('#cartTotal').text('Rp ' + total.toLocaleString('id-ID'));
    $('#totalBayarInput').val(total);
    $('#cartDataInput').val(JSON.stringify(cart));
}

// Search Filter
$('#searchItem').on('keyup', function() {
    let value = $(this).val().toLowerCase();
    $('.product-item').filter(function() {
        $(this).toggle($(this).data('name').toLowerCase().indexOf(value) > -1)
    });
});
</script>

<?php require 'includes/footer.php'; ?>

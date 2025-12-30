<?php
// Ensure $title is set
if (!isset($title)) {
    $title = "Inventory System";
}
// Get current page name for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= $title ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
</head>
<body class="font-display">
<div class="relative flex h-auto min-h-screen w-full flex-col bg-background-light dark:bg-background-dark overflow-x-hidden">
    <div class="flex min-h-screen">
        <!-- SideNavBar -->
        <aside class="flex h-screen w-64 flex-col justify-between border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sticky top-0">
            <div class="flex flex-col gap-8">
                <div class="flex items-center gap-3 px-3 py-2 text-[#0d141b] dark:text-white">
                    <svg class="size-6 text-primary" fill="none" viewbox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 4H17.3334V17.3334H30.6666V30.6666H44V44H4V4Z" fill="currentColor"></path>
                    </svg>
                    <h2 class="text-xl font-bold">Inventory System</h2>
                </div>
                <div class="flex flex-col gap-2">
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?= $current_page == 'dashboard.php' ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' ?>" href="dashboard.php">
                        <span class="material-symbols-outlined">dashboard</span>
                        <p class="text-sm font-medium">Dashboard</p>
                    </a>
                    
                    <?php if($_SESSION['role'] == 'admin'): ?>
                    <div class="px-3 pt-2 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Master Data</div>
                    
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?= $current_page == 'kategori.php' ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' ?>" href="kategori.php">
                        <span class="material-symbols-outlined">category</span>
                        <p class="text-sm font-medium">Kategori</p>
                    </a>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?= $current_page == 'barang.php' ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' ?>" href="barang.php">
                        <span class="material-symbols-outlined">inventory_2</span>
                        <p class="text-sm font-medium">Data Barang</p>
                    </a>
                    <?php else: ?>
                    <div class="px-3 pt-2 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Product Info</div>
                     <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?= $current_page == 'barang.php' ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' ?>" href="barang.php">
                        <span class="material-symbols-outlined">inventory_2</span>
                        <p class="text-sm font-medium">List Barang</p>
                    </a>
                    <?php endif; ?>

                    <div class="px-3 pt-2 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Management</div>

                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?= $current_page == 'transaksi.php' || $current_page == 'transaksi_tambah.php' ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' ?>" href="transaksi.php">
                        <span class="material-symbols-outlined">receipt_long</span>
                        <p class="text-sm font-medium">Transaksi</p>
                    </a>
                    
                    <?php if($_SESSION['role'] == 'admin'): ?>
                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?= $current_page == 'users.php' ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' ?>" href="users.php">
                        <span class="material-symbols-outlined">group</span>
                        <p class="text-sm font-medium">Users</p>
                    </a>
                    <?php endif; ?>

                    <a class="flex items-center gap-3 rounded-lg px-3 py-2 <?= $current_page == 'laporan.php' ? 'bg-primary/10 text-primary' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' ?>" href="laporan.php">
                        <span class="material-symbols-outlined">lab_profile</span>
                        <p class="text-sm font-medium">Laporan</p>
                    </a>
                </div>
            </div>
            <div class="flex flex-col gap-4 border-t border-slate-200 dark:border-slate-800 pt-4">
                <div class="flex gap-3 items-center">
                    <div class="h-10 w-10 rounded-full bg-slate-200 overflow-hidden flex-shrink-0 border border-slate-200 dark:border-slate-700">
                         <?php if(isset($_SESSION['avatar']) && !empty($_SESSION['avatar']) && file_exists('assets/avatars/'.$_SESSION['avatar'])): ?>
                            <img src="assets/avatars/<?= $_SESSION['avatar'] ?>" alt="Profile" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-slate-500 font-bold">
                                <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-slate-800 dark:text-slate-100 text-sm font-medium leading-normal"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></h1>
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-normal leading-normal"><?= ucfirst($_SESSION['role']) ?></p>
                    </div>
                </div>
                <a href="logout.php" class="flex items-center gap-2 text-red-500 hover:text-red-700 text-sm px-3">
                    <span class="material-symbols-outlined text-lg">logout</span> Sign Out
                </a>
            </div>
        </aside>
        
        <main class="flex flex-1 flex-col h-screen overflow-y-auto w-full">
            <!-- TopNavBar -->
            <header class="flex items-center justify-between whitespace-nowrap border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-8 py-3 sticky top-0 z-10">
                <h1 class="text-slate-900 dark:text-slate-50 text-2xl font-bold leading-tight tracking-tight"><?= $title ?></h1>
                <div class="flex items-center gap-4">
                     <!-- Minimal header controls -->
                     <button onclick="window.print()" class="text-slate-500 hover:text-slate-700">
                        <span class="material-symbols-outlined">print</span>
                     </button>
                </div>
            </header>
            
            <div class="p-8 flex flex-col gap-6">
                <!-- Content Starts Here -->

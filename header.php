<?php
if (!isset($page_title)) $page_title = "Dapur Ina Aina";
if (!isset($active_tab)) $active_tab = "dashboard";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Dapur Ina Aina Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        @media print {
            body * { visibility: hidden; }
            #printable-receipt, #printable-receipt * { visibility: visible; }
            #printable-receipt { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col md:flex-row">

    <!-- Sidebar Navigation -->
    <aside class="w-full md:w-64 bg-slate-900 text-slate-300 flex-shrink-0 flex flex-col justify-between border-r border-slate-800">
        <div>
            <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-600 to-amber-500 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-amber-500/20">
                        <i class="fa-solid fa-mug-hot"></i>
                    </div>
                    <div>
                        <h1 class="font-extrabold text-slate-100 text-lg leading-tight">Dapur Ina Aina</h1>
                        <p class="text-[11px] text-amber-400/90 font-medium tracking-wide">CAFE MANAGEMENT</p>
                    </div>
                </div>
            </div>

            <nav class="p-3 space-y-1.5">
                <p class="px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Main Menu</p>
                
                <a href="index.php" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 <?= $active_tab == 'dashboard' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60' ?>">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-base"></i>
                    <span>Dashboard</span>
                </a>

                <a href="pesanan.php" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 <?= $active_tab == 'pesanan' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60' ?>">
                    <i class="fa-solid fa-receipt w-5 text-center text-base"></i>
                    <span>Data Pesanan</span>
                </a>

                <a href="stok.php" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 <?= $active_tab == 'stok' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60' ?>">
                    <i class="fa-solid fa-boxes-stacked w-5 text-center text-base"></i>
                    <span>Stok & Produk</span>
                </a>

                <a href="billing.php" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 <?= $active_tab == 'billing' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60' ?>">
                    <i class="fa-solid fa-credit-card w-5 text-center text-base"></i>
                    <span>Billing & Bayar</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            <div class="p-3 rounded-lg bg-slate-800/50 border border-slate-700/50">
                <p class="text-xs font-semibold text-slate-300">Associate Programmer</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Skema Okupasi — PHP Native</p>
                <div class="mt-2 pt-2 border-t border-slate-700/40 flex items-center justify-between text-[11px]">
                    <span class="text-emerald-400 font-medium flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> DB Connected
                    </span>
                    <span class="text-slate-500">v2.4</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30 px-6 py-4 flex items-center justify-between shadow-xs">
            <h2 class="text-xl font-bold text-slate-800 tracking-tight"><?= $page_title ?></h2>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-amber-100 border border-amber-300 flex items-center justify-center text-amber-700 font-bold text-sm">M</div>
                    <div>
                        <p class="text-xs font-bold text-slate-800 leading-none">Marcho</p>
                        <p class="text-[10px] text-slate-500 mt-0.5">Kasir Cafe</p>
                    </div>
                </div>
            </div>
        </header>
        <div class="p-6 md:p-8 space-y-8 flex-1">
<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$products = \App\Models\Product::all();
foreach ($products as $p) {
    $lastPurchase = \App\Models\PurchaseItem::where('product_id', $p->id)->orderBy('id', 'desc')->first();
    if ($lastPurchase) {
        $p->update(['purchase_price' => $lastPurchase->purchase_price]);
    } else {
        $p->update(['purchase_price' => 50000]); // Dummy price for testing
    }
}
echo "Done";

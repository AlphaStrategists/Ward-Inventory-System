<?php
$files = ['GeneralStockAdjustment.php', 'GeneralTransaction.php', 'MedicineBatch.php', 'Order.php', 'StockReceipt.php', 'Ward.php'];
foreach ($files as $f) {
    $c = file_get_contents('app/Models/'.$f);
    preg_match('/=======\r?\n(.*?)\r?\n>>>>>>>/s', $c, $m);
    if ($m) {
        echo "\n--- $f dev-five block ---\n";
        echo $m[1];
    }
}

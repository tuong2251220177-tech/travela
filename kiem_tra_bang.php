<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$db = app('db');

echo "=== Cấu trúc TBL_USERS ===\n";
$columns = $db->select("DESCRIBE tbl_users");
foreach ($columns as $col) {
    echo $col->Field . " (" . $col->Type . ", " . ($col->Null === 'YES' ? 'nullable' : 'not null') . ")\n";
}

echo "\n=== Cấu trúc TBL_BOOKING ===\n";
$columns = $db->select("DESCRIBE tbl_booking");
foreach ($columns as $col) {
    echo $col->Field . " (" . $col->Type . ", " . ($col->Null === 'YES' ? 'nullable' : 'not null') . ")\n";
}
?>

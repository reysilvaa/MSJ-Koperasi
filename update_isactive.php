<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Updating isactive for KOP menus...\n";

// Update sys_gmenu
$updated_gmenu = DB::table('sys_gmenu')
    ->where('gmenu', 'like', 'KOP%')
    ->update(['isactive' => '1']);

// Update sys_dmenu
$updated_dmenu = DB::table('sys_dmenu')
    ->where('gmenu', 'like', 'KOP%')
    ->update(['isactive' => '1']);

echo "Updated {$updated_gmenu} rows in sys_gmenu\n";
echo "Updated {$updated_dmenu} rows in sys_dmenu\n";

// Verify
echo "\nVerifying sys_gmenu:\n";
$gmenus = DB::table('sys_gmenu')->where('gmenu', 'like', 'KOP%')->get(['gmenu', 'name', 'isactive']);
foreach($gmenus as $menu) {
    echo "{$menu->gmenu} - {$menu->name} - isactive: {$menu->isactive}\n";
}

echo "\nVerifying sys_dmenu:\n";
$dmenus = DB::table('sys_dmenu')->where('gmenu', 'like', 'KOP%')->get(['dmenu', 'name', 'isactive']);
foreach($dmenus as $menu) {
    echo "{$menu->dmenu} - {$menu->name} - isactive: {$menu->isactive}\n";
}
?>

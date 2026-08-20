<?php
$perms = [
  'view_promotions',
  'create_promotions',
  'edit_promotions',
  'delete_promotions'
];

$permIds = \App\Models\Permission::whereIn('name', $perms)->pluck('id');

$admin = \App\Models\Role::where('name', 'admin')->first();
if ($admin) {
    $admin->permissions()->syncWithoutDetaching($permIds);
    echo "Added to admin.\n";
}

$owner = \App\Models\Role::where('name', 'owner')->first();
if ($owner) {
    $owner->permissions()->syncWithoutDetaching($permIds);
    echo "Added to owner.\n";
}

echo "Done.\n";

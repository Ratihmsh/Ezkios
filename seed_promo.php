<?php
$perms = [
  ['name' => 'view_promotions', 'display_name' => 'Lihat Promosi', 'group' => 'Promosi'],
  ['name' => 'create_promotions', 'display_name' => 'Tambah Promosi', 'group' => 'Promosi'],
  ['name' => 'edit_promotions', 'display_name' => 'Edit Promosi', 'group' => 'Promosi'],
  ['name' => 'delete_promotions', 'display_name' => 'Hapus Promosi', 'group' => 'Promosi'],
];
foreach ($perms as $p) {
    App\Models\Permission::firstOrCreate(['name' => $p['name']], $p);
}
$owner = App\Models\Role::where('name', 'owner')->first();
$owner->permissions()->syncWithoutDetaching(App\Models\Permission::whereIn('name', array_column($perms, 'name'))->pluck('id'));
echo 'Permissions added!';

<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('permission_role')->truncate();
        DB::table('role_user')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ========== PERMISSIONS ==========
        $permissions = [
            // Dashboard
            ['name' => 'view_dashboard', 'display_name' => 'View Dashboard', 'group' => 'Dashboard'],

            // Products
            ['name' => 'view_products', 'display_name' => 'View Products', 'group' => 'Products'],
            ['name' => 'create_products', 'display_name' => 'Create Products', 'group' => 'Products'],
            ['name' => 'edit_products', 'display_name' => 'Edit Products', 'group' => 'Products'],

            // Suppliers
            ['name' => 'view_suppliers', 'display_name' => 'View Suppliers', 'group' => 'Suppliers'],
            ['name' => 'create_suppliers', 'display_name' => 'Create Suppliers', 'group' => 'Suppliers'],
            ['name' => 'edit_suppliers', 'display_name' => 'Edit Suppliers', 'group' => 'Suppliers'],

            // Purchases
            ['name' => 'view_purchases', 'display_name' => 'View Purchases', 'group' => 'Purchases'],
            ['name' => 'create_purchases', 'display_name' => 'Create Purchases', 'group' => 'Purchases'],
            ['name' => 'print_purchases', 'display_name' => 'Print Purchases', 'group' => 'Purchases'],
            ['name' => 'settle_purchases', 'display_name' => 'Pelunasan Pembelian', 'group' => 'Purchases'],

            // Sales
            ['name' => 'view_sales', 'display_name' => 'View Sales', 'group' => 'Sales'],
            ['name' => 'create_sales', 'display_name' => 'Create Sales', 'group' => 'Sales'],
            ['name' => 'print_sales', 'display_name' => 'Print Sales', 'group' => 'Sales'],
            ['name' => 'settle_sales', 'display_name' => 'Pelunasan Penjualan', 'group' => 'Sales'],
            ['name' => 'close_cashier', 'display_name' => 'Tutup Buku (Kasir)', 'group' => 'Sales'],
            ['name' => 'adjust_stock', 'display_name' => 'Koreksi Stok', 'group' => 'Sales'],

            // Laporan
            ['name' => 'view_reports', 'display_name' => 'View Reports', 'group' => 'Laporan'],
            ['name' => 'create_cash_transactions', 'display_name' => 'Tambah Transaksi Kas', 'group' => 'Laporan'],
            ['name' => 'export_reports', 'display_name' => 'Ekspor Excel', 'group' => 'Laporan'],

            // Log Aktivitas
            ['name' => 'view_activity_logs', 'display_name' => 'View Activity Logs', 'group' => 'Log Aktivitas'],

            // Pengumuman
            ['name' => 'view_news_events', 'display_name' => 'View Pengumuman', 'group' => 'Pengumuman'],
            ['name' => 'create_news_events', 'display_name' => 'Create Pengumuman', 'group' => 'Pengumuman'],
            ['name' => 'edit_news_events', 'display_name' => 'Edit Pengumuman', 'group' => 'Pengumuman'],
            ['name' => 'delete_news_events', 'display_name' => 'Delete Pengumuman', 'group' => 'Pengumuman'],

            // Manajemen User
            ['name' => 'view_users', 'display_name' => 'View Users', 'group' => 'Manajemen User'],
            ['name' => 'create_users', 'display_name' => 'Create Users', 'group' => 'Manajemen User'],
            ['name' => 'edit_users', 'display_name' => 'Edit Users', 'group' => 'Manajemen User'],

            // Role Permission
            ['name' => 'view_roles', 'display_name' => 'View Roles', 'group' => 'Role Permission'],
            ['name' => 'create_roles', 'display_name' => 'Create Roles', 'group' => 'Role Permission'],
            ['name' => 'edit_roles', 'display_name' => 'Edit Roles', 'group' => 'Role Permission'],
            ['name' => 'delete_roles', 'display_name' => 'Delete Roles', 'group' => 'Role Permission'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // ========== ROLE OWNER ==========
        $owner = Role::create([
            'name' => 'owner',
            'display_name' => 'Owner',
            'description' => 'Pemilik toko dengan akses penuh',
            'is_active' => true,
        ]);
        $owner->permissions()->attach(Permission::all());

        // ========== ROLE ADMIN ==========
        $admin = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Admin dengan akses hampir penuh',
            'is_active' => true,
        ]);
        // Admin dapat SEMUA PERMISSION kecuali role permission dan user management (create, edit, delete, tapi boleh view)
        // Aturan ini bisa disesuaikan, untuk amannya kita attach manual
        $adminPermissions = Permission::whereNotIn('name', [
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
            'create_users', 'edit_users', 'delete_users' // mungkin admin boleh view_users
        ])->get();
        $admin->permissions()->attach($adminPermissions);

        // ========== ROLE KASIR ==========
        $kasir = Role::create([
            'name' => 'kasir',
            'display_name' => 'Kasir',
            'description' => 'Kasir dengan akses terbatas',
            'is_active' => true,
        ]);
        $kasir->permissions()->attach(Permission::whereIn('name', [
            'view_dashboard',
            'view_products',
            'view_sales', 'create_sales', 'print_sales', 'close_cashier',
            'view_reports'
        ])->get());

        // ========== ROLE MANAGER ==========
        $managerRole = Role::create([
            'name' => 'manager',
            'display_name' => 'Manager',
            'description' => 'Manager dengan akses sedang',
            'is_active' => true,
        ]);
        $managerRole->permissions()->attach(Permission::whereIn('name', [
            'view_dashboard',
            'view_products', 'create_products', 'edit_products',
            'view_suppliers', 'create_suppliers', 'edit_suppliers',
            'view_purchases', 'create_purchases', 'print_purchases', 'settle_purchases',
            'view_sales', 'create_sales', 'print_sales', 'settle_sales', 'close_cashier', 'adjust_stock',
            'view_reports', 'export_reports',
            'view_news_events'
        ])->get());
    }
}

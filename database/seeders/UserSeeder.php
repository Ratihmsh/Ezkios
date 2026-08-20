<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Cek apakah roles sudah ada
        $roles = Role::all();
        if ($roles->isEmpty()) {
            $this->command->error('❌ No roles found! Please run RolePermissionSeeder first.');
            $this->command->info('Run: php artisan db:seed --class=RolePermissionSeeder');
            return;
        }

        $this->command->info('✅ Roles found: ' . $roles->pluck('name')->join(', '));

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('role_user')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Ambil roles dengan pengecekan
        $ownerRole = Role::where('name', 'owner')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $kasirRole = Role::where('name', 'kasir')->first();

        if (!$ownerRole || !$adminRole || !$kasirRole) {
            $this->command->error('❌ Required roles not found!');
            return;
        }

        $this->command->info('Creating users...');

        // ========== OWNER ==========
        $owner = User::create([
            'name' => 'Owner EZKIOS',
            'username' => 'owner',
            'email' => 'owner@ezkios.com',
            'password' => Hash::make('password123'),
            'role_id' => $ownerRole->id,
            'phone' => '081234567895',
            'address' => 'Jl. EZKIOS No. 1, Jakarta',
            'is_active' => true,
        ]);
        // Attach ke pivot
        $owner->roles()->attach($ownerRole->id);
        $this->command->info('✅ Owner created: owner@ezkios.com (role_id: ' . $ownerRole->id . ')');

        // ========== ADMIN ==========
        $admin = User::create([
            'name' => 'Admin EZKIOS',
            'username' => 'admin',
            'email' => 'admin@ezkios.com',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'phone' => '081234567891',
            'address' => 'Jl. EZKIOS No. 2, Jakarta',
            'is_active' => true,
        ]);
        $admin->roles()->attach($adminRole->id);
        $this->command->info('✅ Admin created: admin@ezkios.com (role_id: ' . $adminRole->id . ')');

        // ========== KASIR ==========
        $kasir = User::create([
            'name' => 'Kasir EZKIOS',
            'username' => 'kasir',
            'email' => 'kasir@ezkios.com',
            'password' => Hash::make('password123'),
            'role_id' => $kasirRole->id,
            'phone' => '081234567892',
            'address' => 'Jl. EZKIOS No. 3, Jakarta',
            'is_active' => true,
        ]);
        $kasir->roles()->attach($kasirRole->id);
        $this->command->info('✅ Kasir created: kasir@ezkios.com (role_id: ' . $kasirRole->id . ')');

        // ========== SUPER USER (Multiple Roles) ==========
        $super = User::create([
            'name' => 'Super User',
            'username' => 'superuser',
            'email' => 'super@ezkios.com',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'phone' => '081234567893',
            'address' => 'Jl. Super No. 1, Jakarta',
            'is_active' => true,
        ]);
        $super->roles()->attach([$adminRole->id, $kasirRole->id]);
        $this->command->info('✅ Super User created: super@ezkios.com (roles: admin, kasir)');

        // ========== USER TIDAK AKTIF ==========
        $inactive = User::create([
            'name' => 'Andi Wijaya',
            'username' => 'andiwijaya',
            'email' => 'andi@ezkios.com',
            'password' => Hash::make('password123'),
            'role_id' => $kasirRole->id,
            'phone' => '081234567896',
            'address' => 'Jl. Mawar No. 10, Jakarta',
            'is_active' => false,
        ]);
        $inactive->roles()->attach($kasirRole->id);
        $this->command->info('✅ Inactive user created: andi@ezkios.com (role: kasir, inactive)');

        // ========== VERIFY ==========
        $this->command->info('====================================');
        $this->command->info('📊 Verifikasi Data:');
        $this->command->info('====================================');

        $users = User::with('role')->get();
        foreach ($users as $u) {
            $roleName = $u->role ? $u->role->name : 'NO ROLE';
            $this->command->info("{$u->email} | role_id: {$u->role_id} | role: {$roleName}");
        }

        $this->command->info('====================================');
        $this->command->info('✅ User Seeder Completed!');
        $this->command->info('====================================');
        $this->command->info('Login credentials:');
        $this->command->info('Owner:   owner@ezkios.com / password123');
        $this->command->info('Admin:   admin@ezkios.com / password123');
        $this->command->info('Kasir:   kasir@ezkios.com / password123');
        $this->command->info('Super:   super@ezkios.com / password123');
        $this->command->info('Inactive: andi@ezkios.com / password123');
        $this->command->info('====================================');
    }
}

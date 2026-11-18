<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder {
    public function run(): void {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $employee = Role::firstOrCreate(['name' => 'employee']);
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@lasac.com'],
            [
                'name' => 'LASAC Admin',
                'password' => Hash::make('admin123'),
            ]
        );
        $adminUser->assignRole('admin');
    }
}

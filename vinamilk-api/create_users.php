<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Delete existing users
\App\Models\User::where('email', 'admin@vinamilk.com.vn')->delete();
\App\Models\User::where('email', 'vyquy633@gmail.com')->delete();

// Create tenant
$tenant = \App\Models\Tenant::firstOrCreate(
    ['name' => 'Default'],
    [
        'name' => 'Default',
        'slug' => 'default',
    ]
);

// Create admin user
$admin = new \App\Models\User();
$admin->name = 'Admin';
$admin->email = 'admin@vinamilk.com.vn';
$admin->password = Hash::make('password');
$admin->tenant_id = $tenant->id;
$admin->save();

// Create regular user
$user = new \App\Models\User();
$user->name = 'Vy Trong Quy';
$user->email = 'vyquy633@gmail.com';
$user->password = Hash::make('Vytrongquy2003.');
$user->tenant_id = $tenant->id;
$user->save();

// Verify password
$checkAdmin = \Illuminate\Support\Facades\Hash::check('password', $admin->password);
echo "Admin password verification: " . ($checkAdmin ? 'SUCCESS' : 'FAILED') . "\n";

echo "Users created successfully!\n";
echo "Admin: admin@vinamilk.com.vn / password\n";
echo "User: vyquy633@gmail.com / Vytrongquy2003.\n";

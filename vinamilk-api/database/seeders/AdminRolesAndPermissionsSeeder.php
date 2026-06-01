<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all resources permissions
        $resources = [
            'activity-logs',
            'admin-users',
            'role-permissions',
            'products',
            'categories',
            'brands',
            'product-lines',
            'flavors',
            'volumes',
            'sugar-levels',
            'age-groups',
            'nutritional-needs',
            'certificates',
            'care-products',
            'orders',
            'users',
            'logistics',
            'shipping-methods',
            'packing',
            'packaging-types',
            'care-delivery-options',
            'promotion-campaigns',
            'promotion-flash-sales',
            'vouchers',
            'coupons',
            'marketing-rules',
            'marketing-gifts',
            'promotion-banners',
            'promotions-page-banners',
            'special-highlights',
            'trending-searches',
            'promotion-page-settings',
            'promotion-terms',
            'blog-posts',
            'blog-categories',
            'banners',
            'mega-menus',
            'support-pages',
            'care-page-settings',
            'chat-settings',
            'chat-knowledge',
            'chat-messages',
            'payments',
            'payment-logs',
            'vat-orders',
            'rewards',
            'reward-redemptions',
            'stores',
            'tenants',
            'care-subscriptions',
            'care-greeting-cards',
        ];

        $actions = ['view', 'create', 'edit', 'delete', 'export'];

        // Create permissions for each resource and action
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action} {$resource}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // Create roles
        $roles = [
            'Super Admin',
            'System Admin',
            'Shop Manager',
            'Logistics Manager',
            'Product Manager',
            'Marketing Manager',
            'Content Manager',
            'Order Processor',
            'Customer Support Manager',
            'Finance Manager',
            'Store Manager',
            'Care Manager',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        // Assign permissions to each role based on the summary
        $this->assignSuperAdminPermissions();
        $this->assignSystemAdminPermissions();
        $this->assignShopManagerPermissions();
        $this->assignLogisticsManagerPermissions();
        $this->assignProductManagerPermissions();
        $this->assignMarketingManagerPermissions();
        $this->assignContentManagerPermissions();
        $this->assignOrderProcessorPermissions();
        $this->assignCustomerSupportManagerPermissions();
        $this->assignFinanceManagerPermissions();
        $this->assignStoreManagerPermissions();
        $this->assignCareManagerPermissions();
    }

    private function assignSuperAdminPermissions()
    {
        $role = Role::findByName('Super Admin');
        $role->givePermissionTo(Permission::all());
    }

    private function assignSystemAdminPermissions()
    {
        $role = Role::findByName('System Admin');
        
        // System management
        $role->givePermissionTo([
            'view activity-logs', 'create activity-logs', 'edit activity-logs', 'delete activity-logs',
            'view admin-users', 'create admin-users', 'edit admin-users', 'delete admin-users',
            'view role-permissions', 'create role-permissions', 'edit role-permissions', 'delete role-permissions',
            'view chat-settings', 'create chat-settings', 'edit chat-settings', 'delete chat-settings',
            'view chat-knowledge', 'create chat-knowledge', 'edit chat-knowledge', 'delete chat-knowledge',
        ]);

        // Business data
        $role->givePermissionTo([
            'view products', 'create products', 'edit products', 'delete products', 'export products',
            'view categories', 'create categories', 'edit categories', 'delete categories', 'export categories',
            'view brands', 'create brands', 'edit brands', 'delete brands', 'export brands',
            'view orders', 'create orders', 'edit orders', 'delete orders', 'export orders',
            'view users', 'create users', 'edit users', 'delete users', 'export users',
            'view blog-posts', 'create blog-posts', 'edit blog-posts', 'delete blog-posts', 'export blog-posts',
            'view banners', 'create banners', 'edit banners', 'delete banners', 'export banners',
        ]);
    }

    private function assignShopManagerPermissions()
    {
        $role = Role::findByName('Shop Manager');
        
        $role->givePermissionTo([
            'view products', 'create products', 'edit products', 'delete products', 'export products',
            'view categories', 'create categories', 'edit categories', 'delete categories', 'export categories',
            'view brands', 'create brands', 'edit brands', 'delete brands', 'export brands',
            'view orders', 'create orders', 'edit orders', 'delete orders', 'export orders',
            'view users', 'create users', 'edit users', 'export users',
            'view logistics', 'create logistics', 'edit logistics', 'delete logistics', 'export logistics',
            'view shipping-methods', 'create shipping-methods', 'edit shipping-methods', 'delete shipping-methods', 'export shipping-methods',
            'view promotion-campaigns', 'create promotion-campaigns', 'edit promotion-campaigns', 'delete promotion-campaigns', 'export promotion-campaigns',
            'view promotion-flash-sales', 'create promotion-flash-sales', 'edit promotion-flash-sales', 'delete promotion-flash-sales', 'export promotion-flash-sales',
            'view vouchers', 'create vouchers', 'edit vouchers', 'delete vouchers', 'export vouchers',
            'view coupons', 'create coupons', 'edit coupons', 'delete coupons', 'export coupons',
            'view marketing-rules', 'create marketing-rules', 'edit marketing-rules', 'delete marketing-rules', 'export marketing-rules',
            'view stores', 'create stores', 'edit stores', 'delete stores', 'export stores',
        ]);
    }

    private function assignLogisticsManagerPermissions()
    {
        $role = Role::findByName('Logistics Manager');
        
        $role->givePermissionTo([
            'view logistics', 'create logistics', 'edit logistics', 'delete logistics', 'export logistics',
            'view shipping-methods', 'create shipping-methods', 'edit shipping-methods', 'delete shipping-methods', 'export shipping-methods',
            'view packing', 'create packing', 'edit packing', 'delete packing', 'export packing',
            'view packaging-types', 'create packaging-types', 'edit packaging-types', 'delete packaging-types', 'export packaging-types',
            'view care-delivery-options', 'create care-delivery-options', 'edit care-delivery-options', 'delete care-delivery-options', 'export care-delivery-options',
            'view orders', 'edit orders', // Only view and update status
        ]);
    }

    private function assignProductManagerPermissions()
    {
        $role = Role::findByName('Product Manager');
        
        $role->givePermissionTo([
            'view products', 'create products', 'edit products', 'delete products', 'export products',
            'view categories', 'create categories', 'edit categories', 'delete categories', 'export categories',
            'view brands', 'create brands', 'edit brands', 'delete brands', 'export brands',
            'view product-lines', 'create product-lines', 'edit product-lines', 'delete product-lines', 'export product-lines',
            'view flavors', 'create flavors', 'edit flavors', 'delete flavors', 'export flavors',
            'view volumes', 'create volumes', 'edit volumes', 'delete volumes', 'export volumes',
            'view sugar-levels', 'create sugar-levels', 'edit sugar-levels', 'delete sugar-levels', 'export sugar-levels',
            'view age-groups', 'create age-groups', 'edit age-groups', 'delete age-groups', 'export age-groups',
            'view nutritional-needs', 'create nutritional-needs', 'edit nutritional-needs', 'delete nutritional-needs', 'export nutritional-needs',
            'view certificates', 'create certificates', 'edit certificates', 'delete certificates', 'export certificates',
            'view care-products', 'create care-products', 'edit care-products', 'delete care-products', 'export care-products',
        ]);
    }

    private function assignMarketingManagerPermissions()
    {
        $role = Role::findByName('Marketing Manager');
        
        $role->givePermissionTo([
            'view promotion-campaigns', 'create promotion-campaigns', 'edit promotion-campaigns', 'delete promotion-campaigns', 'export promotion-campaigns',
            'view promotion-flash-sales', 'create promotion-flash-sales', 'edit promotion-flash-sales', 'delete promotion-flash-sales', 'export promotion-flash-sales',
            'view vouchers', 'create vouchers', 'edit vouchers', 'delete vouchers', 'export vouchers',
            'view coupons', 'create coupons', 'edit coupons', 'delete coupons', 'export coupons',
            'view marketing-rules', 'create marketing-rules', 'edit marketing-rules', 'delete marketing-rules', 'export marketing-rules',
            'view marketing-gifts', 'create marketing-gifts', 'edit marketing-gifts', 'delete marketing-gifts', 'export marketing-gifts',
            'view promotion-banners', 'create promotion-banners', 'edit promotion-banners', 'delete promotion-banners', 'export promotion-banners',
            'view promotions-page-banners', 'create promotions-page-banners', 'edit promotions-page-banners', 'delete promotions-page-banners', 'export promotions-page-banners',
            'view special-highlights', 'create special-highlights', 'edit special-highlights', 'delete special-highlights', 'export special-highlights',
            'view trending-searches', 'create trending-searches', 'edit trending-searches', 'delete trending-searches', 'export trending-searches',
            'view promotion-page-settings', 'create promotion-page-settings', 'edit promotion-page-settings', 'delete promotion-page-settings', 'export promotion-page-settings',
            'view promotion-terms', 'create promotion-terms', 'edit promotion-terms', 'delete promotion-terms', 'export promotion-terms',
        ]);
    }

    private function assignContentManagerPermissions()
    {
        $role = Role::findByName('Content Manager');
        
        $role->givePermissionTo([
            'view blog-posts', 'create blog-posts', 'edit blog-posts', 'delete blog-posts', 'export blog-posts',
            'view blog-categories', 'create blog-categories', 'edit blog-categories', 'delete blog-categories', 'export blog-categories',
            'view banners', 'create banners', 'edit banners', 'delete banners', 'export banners',
            'view mega-menus', 'create mega-menus', 'edit mega-menus', 'delete mega-menus', 'export mega-menus',
            'view support-pages', 'create support-pages', 'edit support-pages', 'delete support-pages', 'export support-pages',
            'view care-page-settings', 'create care-page-settings', 'edit care-page-settings', 'delete care-page-settings', 'export care-page-settings',
            'view promotions-page-banners', 'create promotions-page-banners', 'edit promotions-page-banners', 'delete promotions-page-banners', 'export promotions-page-banners',
        ]);
    }

    private function assignOrderProcessorPermissions()
    {
        $role = Role::findByName('Order Processor');
        
        $role->givePermissionTo([
            'view orders', 'edit orders', // Only view and update status
            'view users', // Only view basic info
        ]);
    }

    private function assignCustomerSupportManagerPermissions()
    {
        $role = Role::findByName('Customer Support Manager');
        
        $role->givePermissionTo([
            'view chat-settings', 'create chat-settings', 'edit chat-settings', 'delete chat-settings', 'export chat-settings',
            'view chat-knowledge', 'create chat-knowledge', 'edit chat-knowledge', 'delete chat-knowledge', 'export chat-knowledge',
            'view chat-messages',
            'view users',
            'view orders', // Only view for support
            'view rewards', 'create rewards', 'edit rewards', 'delete rewards', 'export rewards',
            'view reward-redemptions', 'create reward-redemptions', 'edit reward-redemptions', 'delete reward-redemptions', 'export reward-redemptions',
        ]);
    }

    private function assignFinanceManagerPermissions()
    {
        $role = Role::findByName('Finance Manager');
        
        $role->givePermissionTo([
            'view payments', 'create payments', 'edit payments', 'delete payments', 'export payments',
            'view payment-logs',
            'view vat-orders', 'create vat-orders', 'edit vat-orders', 'delete vat-orders', 'export vat-orders',
        ]);
    }

    private function assignStoreManagerPermissions()
    {
        $role = Role::findByName('Store Manager');
        
        $role->givePermissionTo([
            'view stores', 'edit stores', // Only their own store
            'view tenants', 'edit tenants', // Only their own tenant
            'view orders', 'edit orders', // Only their store's orders
            'view products', // Only their store's inventory
        ]);
    }

    private function assignCareManagerPermissions()
    {
        $role = Role::findByName('Care Manager');
        
        $role->givePermissionTo([
            'view care-products', 'create care-products', 'edit care-products', 'delete care-products', 'export care-products',
            'view care-subscriptions', 'create care-subscriptions', 'edit care-subscriptions', 'delete care-subscriptions', 'export care-subscriptions',
            'view care-delivery-options', 'create care-delivery-options', 'edit care-delivery-options', 'delete care-delivery-options', 'export care-delivery-options',
            'view care-greeting-cards', 'create care-greeting-cards', 'edit care-greeting-cards', 'delete care-greeting-cards', 'export care-greeting-cards',
            'view care-page-settings', 'create care-page-settings', 'edit care-page-settings', 'delete care-page-settings', 'export care-page-settings',
        ]);
    }
}

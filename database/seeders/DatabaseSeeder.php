<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        DB::table('users')->insertOrIgnore([
            'name'       => 'Super Admin',
            'email'      => 'admin@store.com',
            'password'   => Hash::make('Admin@1234'),
            'role'       => 'super_admin',
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Default Settings
        $settings = [
            // General
            ['key' => 'store_name',          'value' => 'MyStore',                'type' => 'string',  'group' => 'general',    'label' => 'Store Name'],
            ['key' => 'store_tagline',        'value' => 'Quality You Can Trust',  'type' => 'string',  'group' => 'general',    'label' => 'Tagline'],
            ['key' => 'store_email',          'value' => 'info@mystore.com',       'type' => 'string',  'group' => 'general',    'label' => 'Store Email'],
            ['key' => 'store_phone',          'value' => '+92 300 0000000',        'type' => 'string',  'group' => 'general',    'label' => 'Phone'],
            ['key' => 'store_address',        'value' => 'Islamabad, Pakistan',    'type' => 'string',  'group' => 'general',    'label' => 'Address'],
            ['key' => 'currency',             'value' => 'PKR',                    'type' => 'string',  'group' => 'general',    'label' => 'Currency'],
            ['key' => 'currency_symbol',      'value' => '₨',                      'type' => 'string',  'group' => 'general',    'label' => 'Currency Symbol'],
            ['key' => 'timezone',             'value' => 'Asia/Karachi',           'type' => 'string',  'group' => 'general',    'label' => 'Timezone'],
            // Branding
            ['key' => 'logo',                 'value' => '',                       'type' => 'file',    'group' => 'branding',   'label' => 'Logo'],
            ['key' => 'favicon',              'value' => '',                       'type' => 'file',    'group' => 'branding',   'label' => 'Favicon'],
            ['key' => 'color_primary',        'value' => '#6366f1',               'type' => 'string',  'group' => 'branding',   'label' => 'Primary Color'],
            ['key' => 'color_secondary',      'value' => '#f59e0b',               'type' => 'string',  'group' => 'branding',   'label' => 'Secondary Color'],
            ['key' => 'color_accent',         'value' => '#10b981',               'type' => 'string',  'group' => 'branding',   'label' => 'Accent Color'],
            ['key' => 'color_text',           'value' => '#1f2937',               'type' => 'string',  'group' => 'branding',   'label' => 'Text Color'],
            ['key' => 'theme_style',          'value' => 'modern',                'type' => 'string',  'group' => 'branding',   'label' => 'Theme Style'],
            ['key' => 'background_image',     'value' => '',                       'type' => 'file',    'group' => 'branding',   'label' => 'Background Image'],
            ['key' => 'background_animated',  'value' => '0',                      'type' => 'boolean', 'group' => 'branding',   'label' => 'Animated Background'],
            // Shipping
            ['key' => 'free_shipping_min',    'value' => '2000',                   'type' => 'string',  'group' => 'shipping',   'label' => 'Free Shipping Minimum (PKR)'],
            ['key' => 'default_shipping_cost','value' => '200',                    'type' => 'string',  'group' => 'shipping',   'label' => 'Default Shipping Cost (PKR)'],
            // Social
            ['key' => 'facebook_url',         'value' => '',                       'type' => 'string',  'group' => 'social',     'label' => 'Facebook URL'],
            ['key' => 'instagram_url',        'value' => '',                       'type' => 'string',  'group' => 'social',     'label' => 'Instagram URL'],
            ['key' => 'whatsapp_number',      'value' => '',                       'type' => 'string',  'group' => 'social',     'label' => 'WhatsApp Number'],
            // SEO
            ['key' => 'meta_title',           'value' => 'MyStore - Online Shop',  'type' => 'string',  'group' => 'seo',        'label' => 'Default Meta Title'],
            ['key' => 'meta_description',     'value' => 'Shop quality products online', 'type' => 'string', 'group' => 'seo', 'label' => 'Default Meta Description'],
            // Maintenance
            ['key' => 'maintenance_mode',     'value' => '0',                      'type' => 'boolean', 'group' => 'general',    'label' => 'Maintenance Mode'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insertOrIgnore(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Payment Configs
        $gateways = [
            ['gateway' => 'cod',        'is_enabled' => true,  'is_test_mode' => false],
            ['gateway' => 'easypaisa',  'is_enabled' => false, 'is_test_mode' => true,
             'extra_config' => json_encode(['hash_key' => '', 'return_url' => '', 'account_number' => ''])],
            ['gateway' => 'jazzcash',   'is_enabled' => false, 'is_test_mode' => true,
             'extra_config' => json_encode(['hash_key' => '', 'return_url' => '', 'account_number' => ''])],
            ['gateway' => 'card',       'is_enabled' => false, 'is_test_mode' => true,
             'extra_config' => json_encode(['publishable_key' => '', 'webhook_secret' => ''])],
        ];

        foreach ($gateways as $gateway) {
            DB::table('payment_configs')->insertOrIgnore(array_merge($gateway, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Homepage Sections
        $sections = [
            ['section_key' => 'hero',          'title' => 'Hero Slider',         'is_enabled' => true,  'sort_order' => 1],
            ['section_key' => 'categories',    'title' => 'Shop by Category',    'is_enabled' => true,  'sort_order' => 2],
            ['section_key' => 'featured',      'title' => 'Featured Products',   'is_enabled' => true,  'sort_order' => 3],
            ['section_key' => 'promo_banner',  'title' => 'Promotional Banner',  'is_enabled' => true,  'sort_order' => 4],
            ['section_key' => 'new_arrivals',  'title' => 'New Arrivals',        'is_enabled' => true,  'sort_order' => 5],
            ['section_key' => 'testimonials',  'title' => 'Testimonials',        'is_enabled' => false, 'sort_order' => 6],
            ['section_key' => 'trust_badges',  'title' => 'Trust Badges',        'is_enabled' => true,  'sort_order' => 7],
        ];

        foreach ($sections as $section) {
            DB::table('homepage_sections')->insertOrIgnore(array_merge($section, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Default Pages
        DB::table('pages')->insertOrIgnore([
            ['slug' => 'about',   'title' => 'About Us',      'content' => '<p>Welcome to our store. We are committed to providing you the best quality products.</p>', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'contact', 'title' => 'Contact Us',    'content' => '<p>Get in touch with us.</p>', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'privacy', 'title' => 'Privacy Policy', 'content' => '<p>Your privacy is important to us.</p>', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'terms',   'title' => 'Terms of Service', 'content' => '<p>Please read these terms carefully.</p>', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}

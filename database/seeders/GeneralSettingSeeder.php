<?php

namespace Database\Seeders;

use App\Models\GeneralSetting;
use Illuminate\Database\Seeder;

class GeneralSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['general', 'site_name', 'text', 'Site name', true, 'PetMart'],
            ['general', 'site_tagline', 'text', 'Tagline', true, "Bangladesh's moderated pet marketplace"],

            ['homepage', 'home_hero_banner', 'image', 'Homepage banner image', true, null],
            ['homepage', 'home_hero_badge', 'text', 'Banner badge text', true, 'Verified pet & goods marketplace in Bangladesh'],
            ['homepage', 'home_hero_title', 'text', 'Banner title', true, 'Find & Sell Pets, Animals & Supplies Safely'],
            ['homepage', 'home_hero_subtitle', 'textarea', 'Banner subtitle', true, 'Browse verified breeders, adoption posts, stud services and quality pet accessories across Bangladesh.'],

            ['contact', 'contact_phone', 'text', 'Support phone', true, null],
            ['contact', 'contact_email', 'email', 'Support email', true, null],

            ['social', 'facebook_url', 'url', 'Facebook page URL', true, null],

            ['footer', 'footer_about', 'textarea', 'Footer: about text', true, "Bangladesh's dedicated, moderated marketplace for verified pet breeders, adoption listings, veterinary supplies and secondhand pet essentials."],
            ['footer', 'footer_help_title', 'text', 'Footer: help section heading', true, 'Help & Verification'],
            ['footer', 'footer_notice_title', 'text', 'Footer: notice box title', true, 'Verified Breeder Program'],
            ['footer', 'footer_notice_text', 'textarea', 'Footer: notice box text', true, 'Protect yourself from animal scams: never wire advance payments without physical verification.'],
            ['footer', 'footer_copyright', 'text', 'Footer: copyright name', true, 'PetMart Bangladesh'],
        ];

        foreach ($settings as [$group, $key, $type, $label, $public, $default]) {
            $row = GeneralSetting::firstOrNew(['key' => $key]);
            if (!$row->exists) {
                $row->value = $default;
            }
            $row->fill([
                'group'        => $group,
                'type'         => $type,
                'display_name' => $label,
                'is_public'    => $public,
            ])->save();
        }
    }
}

<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Duplicate the file
        $sourceFile = resource_path('lang/en.json');
        $destinationFile = resource_path('lang/en-new.json');
        File::copy($sourceFile, $destinationFile);

        $sourceFile = public_path('languages/en.json');
        $destinationFile = public_path('languages/en-new.json');
        File::copy($sourceFile, $destinationFile);

        $sourceFile = public_path('web_languages/en.json');
        $destinationFile = public_path('web_languages/en-new.json');
        File::copy($sourceFile, $destinationFile);

        DB::table('languages')->insert(
            [
                'name' => 'English',
                'code' => 'en-new',
                'file_name' => 'en-new.json',
                'status' => '1',
            ],
        );


        DB::table('settings')->insert(
            [
                [
                    'type' => 'company_name',
                    'data' => 'eBroker'
                ],
                [
                    'type' => 'currency_symbol',
                    'data' => '$'
                ],
                [
                    'type' => 'ios_version',
                    'data' => '1.0.0'
                ],
                [
                    'type' => 'default_language',
                    'data' => 'en-new'
                ],
                [
                    'type' => 'force_update',
                    'data' => '0'
                ],
                [
                    'type' => 'android_version',
                    'data' => '1.0.0'
                ],
                [
                    'type' => 'number_with_suffix',
                    'data' => '0'
                ],
                [
                    'type' => 'maintenance_mode',
                    'data' => 0,
                ],
                [
                    'type' => 'privacy_policy',
                    'data' => '',
                ],
                [
                    'type' => 'terms_conditions',
                    'data' => '',
                ],
                [
                    'type' => 'company_tel1',
                    'data' => '',
                ],
                [
                    'type' => 'company_tel2',
                    'data' => '',
                ],
                [
                    'type' => 'razorpay_gateway',
                    'data' => '0',
                ],
                [
                    'type' => 'paystack_gateway',
                    'data' => '0',
                ],
                [
                    'type' => 'paypal_gateway',
                    'data' => '0',
                ],
                [
                    'type' => 'system_version',
                    'data' => '1.2.4',
                ],
                [
                    'type' => 'company_logo',
                    'data' => 'logo.png',
                ],
                [
                    'type' => 'web_logo',
                    'data' => 'web_logo.png',
                ],
                [
                    'type' => 'favicon_icon',
                    'data' => 'favicon.png',
                ],
                [
                    'type' => 'web_favicon',
                    'data' => 'favicon.png',
                ],
                [
                    'type' => 'web_footer_logo',
                    'data' => 'Logo_white.svg',
                ],
                [
                    'type' => 'web_placeholder_logo',
                    'data' => 'placeholder.svg',
                ],
                [
                    'type' => 'app_home_screen',
                    'data' => 'homeLogo.png',
                ],
                [
                    'type' => 'placeholder_logo',
                    'data' => 'placeholder.png',
                ],
                [
                    'type' => 'system_color',
                    'data' => '#087c7c',
                ],
            ]
        );
    }
}


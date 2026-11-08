<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            ['name' => 'Dhaka', 'bn_name' => 'ঢাকা'],
            ['name' => 'Chittagong', 'bn_name' => 'চট্টগ্রাম'],
            ['name' => 'Sylhet', 'bn_name' => 'সিলেট'],
            ['name' => 'Rajshahi', 'bn_name' => 'রাজশাহী'],
        ];

        foreach ($divisions as $divData) {
            $div = Location::firstOrCreate(
                ['slug' => Str::slug($divData['name'])],
                [
                    'name' => $divData['name'],
                    'bn_name' => $divData['bn_name'],
                    'type' => 'division',
                    'status' => true,
                ]
            );

            if ($divData['name'] === 'Dhaka') {
                $districts = ['Dhaka City', 'Gazipur', 'Narayanganj'];
                foreach ($districts as $distName) {
                    $dist = Location::firstOrCreate(
                        ['slug' => Str::slug($distName)],
                        [
                            'name' => $distName,
                            'type' => 'district',
                            'parent_id' => $div->id,
                            'status' => true,
                        ]
                    );

                    if ($distName === 'Dhaka City') {
                        $areas = ['Dhanmondi', 'Uttara', 'Gulshan', 'Mirpur', 'Banani'];
                        foreach ($areas as $areaName) {
                            Location::firstOrCreate(
                                ['slug' => Str::slug($areaName)],
                                [
                                    'name' => $areaName,
                                    'type' => 'area',
                                    'parent_id' => $dist->id,
                                    'status' => true,
                                ]
                            );
                        }
                    }
                }
            }
        }
    }
}

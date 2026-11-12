<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * All 8 divisions and 64 districts of Bangladesh (official English spellings, 2018 onwards),
 * plus a starter list of well-known neighbourhoods. More areas are added from Admin › Settings › Locations.
 */
class LocationSeeder extends Seeder
{
    private const DIVISIONS = [
        'Dhaka' => ['ঢাকা', ['Dhaka', 'Faridpur', 'Gazipur', 'Gopalganj', 'Kishoreganj', 'Madaripur', 'Manikganj', 'Munshiganj', 'Narayanganj', 'Narsingdi', 'Rajbari', 'Shariatpur', 'Tangail']],
        'Chattogram' => ['চট্টগ্রাম', ['Bandarban', 'Brahmanbaria', 'Chandpur', 'Chattogram', "Cox's Bazar", 'Cumilla', 'Feni', 'Khagrachhari', 'Lakshmipur', 'Noakhali', 'Rangamati']],
        'Rajshahi' => ['রাজশাহী', ['Bogura', 'Chapainawabganj', 'Joypurhat', 'Naogaon', 'Natore', 'Pabna', 'Rajshahi', 'Sirajganj']],
        'Khulna' => ['খুলনা', ['Bagerhat', 'Chuadanga', 'Jashore', 'Jhenaidah', 'Khulna', 'Kushtia', 'Magura', 'Meherpur', 'Narail', 'Satkhira']],
        'Barishal' => ['বরিশাল', ['Barguna', 'Barishal', 'Bhola', 'Jhalokati', 'Patuakhali', 'Pirojpur']],
        'Sylhet' => ['সিলেট', ['Habiganj', 'Moulvibazar', 'Sunamganj', 'Sylhet']],
        'Rangpur' => ['রংপুর', ['Dinajpur', 'Gaibandha', 'Kurigram', 'Lalmonirhat', 'Nilphamari', 'Panchagarh', 'Rangpur', 'Thakurgaon']],
        'Mymensingh' => ['ময়মনসিংহ', ['Jamalpur', 'Mymensingh', 'Netrokona', 'Sherpur']],
    ];

    private const AREAS = [
        'Dhaka' => ['Badda', 'Banani', 'Baridhara', 'Bashundhara', 'Dhanmondi', 'Farmgate', 'Gulshan', 'Jatrabari', 'Keraniganj', 'Khilgaon', 'Lalbagh', 'Malibagh', 'Mirpur', 'Mohakhali', 'Mohammadpur', 'Motijheel', 'Old Dhaka', 'Rampura', 'Savar', 'Shantinagar', 'Tejgaon', 'Uttara', 'Wari'],
        'Chattogram' => ['Agrabad', 'Bahaddarhat', 'Chawkbazar', 'Halishahar', 'Khulshi', 'Kotwali', 'Nasirabad', 'Panchlaish', 'Patenga'],
        'Sylhet' => ['Amberkhana', 'Shibganj', 'Uposhohor', 'Zindabazar'],
    ];

    public function run(): void
    {
        $order = 0;
        foreach (self::DIVISIONS as $division => [$bn, $districts]) {
            $div = $this->upsert(null, 'division', $division, $bn, $order++);

            foreach ($districts as $i => $district) {
                $dist = $this->upsert($div->id, 'district', $district, null, $i);

                foreach (self::AREAS[$district] ?? [] as $j => $area) {
                    $this->upsert($dist->id, 'area', $area, null, $j);
                }
            }
        }
    }

    private function upsert(?int $parentId, string $type, string $name, ?string $bn, int $order): Location
    {
        return Location::updateOrCreate(
            ['type' => $type, 'parent_id' => $parentId, 'slug' => Str::slug($name)],
            ['name' => $name, 'bn_name' => $bn, 'sort_order' => $order, 'status' => true]
        );
    }
}

<?php

use App\Support\PhoneNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Store every phone number as +8801XXXXXXXXX so the unique index really is unique.
 * Rows whose canonical form already belongs to another user are left unchanged and logged
 * for manual review.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->select('id', 'phone')->orderBy('id')->chunkById(500, function ($users) {
            foreach ($users as $user) {
                $canonical = PhoneNumber::normalize($user->phone);
                if ($canonical === $user->phone || ! PhoneNumber::isValid($canonical)) {
                    continue;
                }
                if (DB::table('users')->where('phone', $canonical)->where('id', '!=', $user->id)->exists()) {
                    Log::warning('Phone normalisation skipped: duplicate account', ['user_id' => $user->id]);
                    continue;
                }
                DB::table('users')->where('id', $user->id)->update(['phone' => $canonical]);
            }
        });
    }

    public function down(): void
    {
    }
};

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Role extends Model
{
    protected $table = 'roles';


    use HasFactory;

    protected $fillable = ['name', 'display_name', 'status'];

    public function validate($input = [])
    {
        $validate = Validator::make($input, [
            'name' => 'required',
        ]);

        return $validate;
    }
}

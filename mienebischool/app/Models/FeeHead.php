<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeHead extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function classFees()
    {
        return $this->hasMany(ClassFee::class);
    }
}

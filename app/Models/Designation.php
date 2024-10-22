<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $fillable = [
        'department_id','name','created_by'
    ];

        public function employees()
    {
        return $this->hasMany(Employee::class, 'designation_id');
    }
}

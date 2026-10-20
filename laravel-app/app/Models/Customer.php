<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $primaryKey = 'customer_id';

    protected $fillable = [
        'fullname',
        'email',
        'contact_number',
        'address',
        'customer_type',
    ];

    public $timestamps = false; // walang updated_at column, "created_at" lang meron

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'customer_id', 'customer_id');
    }
}
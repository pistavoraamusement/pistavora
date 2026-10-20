<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';
    protected $primaryKey = 'attendance_id';
    public $timestamps = false;

    // `remarks` at `source` ay isinama na rin. Ginagamit ng QR check-in
    // at ng bio import ang mga ito. (Ang `area` ay nasa Employees table,
    // hindi dito, dahil bawat empleyado ang may area, hindi bawat araw.)
    protected $fillable = [
        'employee_id', 'employee_name', 'category',
        'attendance_date', 'time_in', 'time_out', 'status', 'total_hours',
        'remarks', 'source',
    ];
}
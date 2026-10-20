<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $table = 'payroll';
    protected $primaryKey = 'payroll_id';
    public $timestamps = false;

    protected $fillable = [
        'employee_id', 'name', 'category',
        'payroll_period_start', 'payroll_period_end', 'cutoff_type',
        'total_days', 'present_days', 'daily_rate', 'total_hours',
        'basic_salary', 'holiday_pay', 'overtime_pay', 'holiday_ot_pay',
        'sss_deduction', 'philhealth_deduction',
        'pagibig_deduction', 'withholding_tax', 'deduction',
        'gross_pay', 'total_deductions', 'net_pay', 'net_salary',
        'payroll_status', 'generated_at',
    ];

    // Para gumana yung $sal->id sa view (route('payslip.show', $sal->id))
    public function getIdAttribute()
    {
        return $this->payroll_id;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicineOutgoing extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "medicine_outgoing";

    protected $fillable = [
        'id_medicine',
        'id_unit',
        'batch_no',
        'exp_date',
        'quantity',
        'date'
    ];

    public function medicine() {
        return $this->belongsTo(Medicine::class, 'id_medicine');
    }

    public function unit() {
        return $this->belongsTo(Unit::class,'id_unit');
    }
}

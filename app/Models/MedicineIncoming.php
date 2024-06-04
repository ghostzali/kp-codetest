<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicineIncoming extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "medicine_incoming";

    protected $fillable = [
        'id_medicine',
        'id_unit',
        'batch_no',
        'exp_date',
        'quantity',
        'date'
    ];
}

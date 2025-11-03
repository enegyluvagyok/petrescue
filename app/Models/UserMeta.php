<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model
{
    protected $table = 'user_meta';

    protected $fillable = [
        'user_id',
        'birth_place',
        'phone',
        'mother_name',
        'birth_date',
        'postal_code',
        'city',
        'street_name',
        'street_type',
        'house_number',
        'floor',
        'door',
        'id_card_number',
        'taj_number',
        'tax_id',
        'avatar_path',
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

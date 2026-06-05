<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;
    protected $table = 'room_types';
    public $timestamps = false;
    protected $fillable = ['type_name', 'price', 'max_guests', 'description'];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'room_type_id');
    }
}

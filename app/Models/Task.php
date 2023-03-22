<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model {

    use HasFactory;

    public $table = 'tasks';

    public function users(){
        return $this->hasOne(User::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title', 'description', 'status', 'created_by', 'assign_to'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}

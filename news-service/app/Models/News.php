<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';

    protected $fillable = [
        'title',
        'image',
        'description',
        'date',
        'created_by',
        'likes',
        'views',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'news_user', 'news_id', 'user_id')->withTimestamps();
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserve extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function scopeSection($query, $section)
    {
        $query->where('section', $section);
    }
    public function scopeDate($query, $date)
    {
        $dateTime = Carbon::parse($date)->toDateString();

        $query->whereRaw("DATE(time) = ?", [$dateTime]);
    }
    public function office()
    {
        return $this->belongsTo(Office::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

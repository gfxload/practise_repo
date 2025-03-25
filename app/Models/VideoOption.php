<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Service;

class VideoOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'parameter_name',
        'parameter_value',
        'display_name',
        'points_cost',
    ];

    protected $casts = [
        'points_cost' => 'integer',
        'service_id' => 'integer',
    ];

    /**
     * Set the points_cost attribute.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setPointsCostAttribute($value)
    {
        $this->attributes['points_cost'] = (int) $value;
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}


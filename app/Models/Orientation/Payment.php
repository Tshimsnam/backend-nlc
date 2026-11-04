<?php

namespace App\Models\Orientation;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'orientation_payments';

    protected $guarded = [];

    protected $hidden = ['redirect_url', 'review_note', 'verified_by'];

    protected $casts = ['paid_at' => 'datetime', 'amount_cents' => 'integer'];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}

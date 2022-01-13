<?php

namespace IWasHereFirst2\LaravelMultiMail\Models;

use Illuminate\Database\Eloquent\Model;

class EmailAccount extends Model
{
    protected $guarded = [];

    public function provider()
    {
        return $this->belongsTo(EmailProvider::class);
    }
}

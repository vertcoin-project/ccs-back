<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'payment_id', 'payment_id');
    }

    public function getAmountReceivedAttribute() {
        return $this->deposits->sum('amount');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Monero\Wallet;

/**
 * App\Project
 *
 * @property int $id
 * @property string $payment_id
 * @property string $target_amount
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Deposit[] $deposits
 * @property-read mixed $amount_received
 * @property-read string $uri
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project whereTargetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

    public function getUriAttribute() {
        return 'monero:'.env('WALLET_ADDRESS').'tx_payment_id='.$this->payment_id;
    }
}

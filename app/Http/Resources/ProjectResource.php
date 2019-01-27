<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'subaddr_index' => $this->subaddr_index,
            'status' => $this->status,
            'amount_received' => $this->amount_received,
            'target_amount' => $this->target_amount,
            'percentage_funded' => $this->percentage_funded,
            'qrcode' => ['base64' => base64_encode($this->qrcode)],
            'contributions' => $this->contributions,
        ];
    }
}

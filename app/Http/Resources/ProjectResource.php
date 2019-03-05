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
            'raised_amount' => $this->raised_amount,
            'target_amount' => $this->target_amount,
            'percentage_funded' => $this->percentage_funded,
            'qrcode' => ['base64' => base64_encode($this->qrcode)],
            'contributions' => $this->contributions,
        ];
    }
}

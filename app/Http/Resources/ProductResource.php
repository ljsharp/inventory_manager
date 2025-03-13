<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        info(json_encode($this->attributes));
        return [
            ...parent::toArray($request),
            "attributes" => $this->attributes->isNotEmpty() ?
                $this->attributes->map(function ($attribute) {
                    return [
                        $attribute->name => $attribute->values
                    ];
                })->flatMap(fn($attribute) => $attribute) : [],
            "variants" => $this->variants
        ];
    }
}

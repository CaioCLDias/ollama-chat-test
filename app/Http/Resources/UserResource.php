<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="UserResource",
     *     type="object",
     *     title="User Resource",
     *     required={"id", "name", "email"},
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="email", type="string", example="john@example.com"),
     *     @OA\Property(property="email_verified_at", type="string", format="date-time", example="2025-05-16T01:00:00Z"),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-16T01:00:00Z")
     * )
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
        ];
    }
}

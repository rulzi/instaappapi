<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'permissions',
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        'permissions',
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->getTimestamp(),
            'updated_at' => $user->updated_at->getTimestamp(),
        ];
    }

    /**
     * Include permissions data
     *
     * @param User $user
     * @return \League\Fractal\Resource\Item
     */
    public function includePermissions(User $user)
    {
        return $this->item($user->permissions, new PermissionTransformer());
    }
}

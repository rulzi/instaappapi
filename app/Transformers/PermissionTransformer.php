<?php

namespace App\Transformers;

use App\Models\Permission;
use League\Fractal\TransformerAbstract;

class PermissionTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Permission $permission)
    {
        return [
            'user_id' => $permission->user_id,
            'can_create_post' => $permission->can_create_post,
            'can_update_post' => $permission->can_update_post,
            'can_delete_post' => $permission->can_delete_post,
            'can_create_comment' => $permission->can_create_comment,
            'can_update_comment' => $permission->can_update_comment,
            'can_delete_comment' => $permission->can_delete_comment,
            'can_like_post' => $permission->can_like_post,
            'can_unlike_post' => $permission->can_unlike_post,
        ];
    }
}

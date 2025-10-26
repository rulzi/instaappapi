<?php

namespace App\Transformers;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'user',
        'comments',
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        'user',
        'comments'
    ];
    
    /**
     * A Fractal transformer.
     *
     * @param Post $post
     * @return array
     */
    public function transform(Post $post)
    {
        return [
            'id' => $post->id,
            'content' => $post->content,
            'image_url' => url($post->image_url),
            'created_at' => $post->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $post->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Include user data
     *
     * @param Post $post
     * @return \League\Fractal\Resource\Item
     */
    public function includeUser(Post $post)
    {
        return $this->item($post->user, new UserTransformer());
    }

    /**
     * Include comments data
     *
     * @param Post $post
     * @return \League\Fractal\Resource\Collection
     */
    public function includeComments(Post $post)
    {
        return $this->collection($post->comments, new CommentTransformer());
    }
}

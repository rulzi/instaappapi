<?php

namespace App\Transformers;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user();
        $isLiked = $user ? $post->isLikedBy($user->id) : false;
        
        return [
            'id' => $post->id,
            'content' => $post->content,
            'image_url' => url($post->image_url),
            'likes_count' => $post->likes_count,
            'is_liked' => $isLiked,
            'created_at' => $post->created_at->getTimestamp(),
            'updated_at' => $post->updated_at->getTimestamp(),
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

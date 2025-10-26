<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Services\ApiResponseService;
use App\Transformers\CommentTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Fractal\Facades\Fractal;

class CommentController extends Controller
{
    protected $apiResponseService;

    public function __construct(ApiResponseService $apiResponseService)
    {
        $this->apiResponseService = $apiResponseService;
    }

    /**
     * Display a listing of comments for a specific post.
     */
    public function index(Request $request, $postId)
    {
        try {
            $post = Post::findOrFail($postId);
            $comments = Comment::with('user')
                ->where('post_id', $postId)
                ->latest()
                ->get();

            $data = [
                'comments' => Fractal::collection($comments, new CommentTransformer())->toArray(),
            ];
            
            return $this->apiResponseService->success(
                $data,
                'Comments retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponseService->error(
                'Post not found',
                404
            );
        } catch (\Exception $e) {
            return $this->apiResponseService->error(
                'Failed to retrieve comments',
                500
            );
        }
    }

    /**
     * Store a newly created comment.
     */
    public function store(Request $request, $postId)
    {
        try {
            $request->validate([
                'content' => 'required|string|max:1000'
            ]);

            $post = Post::findOrFail($postId);
            $user = Auth::user();

            $comment = new Comment();
            $comment->user_id = $user->id;
            $comment->post_id = $postId;
            $comment->content = $request->content;
            $comment->save();

            $comment->load('user');

            $data = [
                'comment' => Fractal::item($comment, new CommentTransformer())->toArray(),
            ];

            return $this->apiResponseService->success(
                $data,
                'Comment created successfully',
                201
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponseService->error(
                'Post not found',
                404
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiResponseService->error(
                'Validation failed',
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->apiResponseService->error(
                'Failed to create comment',
                500
            );
        }
    }

    /**
     * Display the specified comment.
     */
    public function show($postId, $commentId)
    {
        try {
            $comment = Comment::with('user')
                ->where('post_id', $postId)
                ->findOrFail($commentId);

            $data = [
                'comment' => Fractal::item($comment, new CommentTransformer())->toArray(),
            ];
            
            return $this->apiResponseService->success(
                $data,
                'Comment retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponseService->error(
                'Comment not found',
                404
            );
        } catch (\Exception $e) {
            return $this->apiResponseService->error(
                'Failed to retrieve comment',
                500
            );
        }
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, $postId, $commentId)
    {
        try {
            $comment = Comment::where('post_id', $postId)->findOrFail($commentId);
            
            // Check if user owns the comment
            if ($comment->user_id !== Auth::id()) {
                return $this->apiResponseService->error(
                    'Unauthorized to update this comment',
                    403
                );
            }

            $request->validate([
                'content' => 'required|string|max:1000'
            ]);

            $comment->content = $request->content;
            $comment->save();

            $comment->load('user');

            $data = [
                'comment' => Fractal::item($comment, new CommentTransformer())->toArray(),
            ];

            return $this->apiResponseService->success(
                $data,
                'Comment updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponseService->error(
                'Comment not found',
                404
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiResponseService->error(
                'Validation failed',
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->apiResponseService->error(
                'Failed to update comment',
                500
            );
        }
    }

    /**
     * Remove the specified comment.
     */
    public function destroy($postId, $commentId)
    {
        try {
            $comment = Comment::where('post_id', $postId)->findOrFail($commentId);
            
            // Check if user owns the comment
            if ($comment->user_id !== Auth::id()) {
                return $this->apiResponseService->error(
                    'Unauthorized to delete this comment',
                    403
                );
            }

            $comment->delete();

            return $this->apiResponseService->success(
                null,
                'Comment deleted successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponseService->error(
                'Comment not found',
                404
            );
        } catch (\Exception $e) {
            return $this->apiResponseService->error(
                'Failed to delete comment',
                500
            );
        }
    }
}

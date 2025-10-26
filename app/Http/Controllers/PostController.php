<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\ApiResponseService;
use App\Transformers\PostTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Fractal\Facades\Fractal;

class PostController extends Controller
{
    protected $apiResponseService;

    public function __construct(ApiResponseService $apiResponseService)
    {
        $this->apiResponseService = $apiResponseService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $posts = Post::with('user')->latest()->paginate(10);

            $data = [
                'posts' => Fractal::collection($posts, new PostTransformer())->toArray(),
                'pagination' => [
                    'total' => $posts->total(),
                    'per_page' => $posts->perPage(),
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                ],
            ];
            
            return $this->apiResponseService->success(
                $data,
                'Posts retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->apiResponseService->error(
                'Failed to retrieve posts',
                500
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'content' => 'nullable|string|max:2000',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $user = Auth::user();
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('posts', $imageName, 'public');
                $imageUrl = Storage::url($imagePath);
            }

            $post = new Post();
            $post->user_id = $user->id;
            $post->content = $request->content;
            $post->image_url = $imageUrl;
            $post->save();

            $post->load('user');

            $data = [
                'post' => Fractal::item($post, new PostTransformer())->toArray(),
            ];

            return $this->apiResponseService->success(
                $data,
                'Post created successfully',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiResponseService->error(
                'Validation failed',
                422,
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->apiResponseService->error(
                'Failed to create post',
                500
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $post = Post::with('user')->findOrFail($id);

            $data = [
                'post' => Fractal::item($post, new PostTransformer())->toArray(),
            ];
            
            return $this->apiResponseService->success(
                $data,
                'Post retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponseService->error(
                'Post not found',
                404
            );
        } catch (\Exception $e) {
            return $this->apiResponseService->error(
                'Failed to retrieve post',
                500
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $post = Post::findOrFail($id);
            
            // Check if user owns the post
            if ($post->user_id !== Auth::id()) {
                return $this->apiResponseService->error(
                    'Unauthorized to update this post',
                    403
                );
            }

            $request->validate([
                'content' => 'nullable|string|max:2000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $post->content = $request->input('content');
            
            $post->save();

            $post->load('user');

            $data = [
                'post' => Fractal::item($post, new PostTransformer())->toArray(),
                'message' => $request->content,
            ];

            return $this->apiResponseService->success(
                $data,
                'Post updated successfully'
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
                'Failed to update post',
                500
            );
        }
    }

    public function updateImage(Request $request, string $id)
    {
        try {
            $post = Post::findOrFail($id);
        
            // Delete old image if it exists
            if ($post->image_url) {
                $oldImagePath = str_replace('/storage/', '', $post->image_url);
                Storage::disk('public')->delete($oldImagePath);
            }

            // Upload new image
            $image = $request->file('image');
            $imageName = Str::uuid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('posts', $imageName, 'public');
            $post->image_url = Storage::url($imagePath);

            $post->save();

            $post->load('user');

            $data = [
                'post' => Fractal::item($post, new PostTransformer())->toArray(),
            ];

            return $this->apiResponseService->success(
                $data,
                'Image updated successfully'
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
                'Failed to update image',
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $post = Post::findOrFail($id);
            
            // Check if user owns the post
            if ($post->user_id !== Auth::id()) {
                return $this->apiResponseService->error(
                    'Unauthorized to delete this post',
                    403
                );
            }

            // Delete image file
            if ($post->image_url) {
                $imagePath = str_replace('/storage/', '', $post->image_url);
                Storage::disk('public')->delete($imagePath);
            }

            $post->delete();

            return $this->apiResponseService->success(
                null,
                'Post deleted successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponseService->error(
                'Post not found',
                404
            );
        } catch (\Exception $e) {
            return $this->apiResponseService->error(
                'Failed to delete post',
                500
            );
        }
    }
}

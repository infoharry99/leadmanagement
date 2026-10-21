<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Services\BlogService;
use Illuminate\Http\Response;

class PublicBlogController extends Controller
{
    public function __construct(protected BlogService $blogService)
    {
    }

    public function index()
    {
        $blogs = Blog::with(['category', 'author', 'tags'])
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->paginate(9);

        $categories = BlogCategory::all();

        return view('public.blog_index', compact('blogs', 'categories'));
    }

    public function show(string $slug)
    {
        $blog = Blog::with(['category', 'author', 'tags'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $relatedBlogs = Blog::where('category_id', $blog->category_id)
            ->where('id', '!=', $blog->id)
            ->where('status', 'published')
            ->take(3)
            ->get();

        return view('public.blog_show', compact('blog', 'relatedBlogs'));
    }

    public function sitemap(): Response
    {
        $xml = $this->blogService->generateSitemapXml();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    public function llmsTxt(): Response
    {
        $text = $this->blogService->generateLlmsTxt();

        return response($text, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Services\BlogService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(protected BlogService $blogService)
    {
    }

    public function index(Request $request)
    {
        $status = $request->input('status');
        $categoryId = $request->input('category_id');

        $blogs = Blog::with(['category', 'author', 'tags'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->latest('updated_at')
            ->paginate(15);

        $categories = BlogCategory::all();

        return view('blogs.index', compact('blogs', 'categories', 'status', 'categoryId'));
    }

    public function create()
    {
        $categories = BlogCategory::all();
        $tags = BlogTag::all();
        return view('blogs.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug',
            'summary' => 'nullable|string',
            'body' => 'required|string',
            'category_id' => 'nullable|exists:blog_categories,id',
            'status' => 'required|in:draft,review,scheduled,published',
            'primary_keyword' => 'nullable|string|max:255',
            'secondary_keywords' => 'nullable|string',
            'search_intent' => 'required|in:informational,commercial,transactional,navigational',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|url|max:255',
            'tag_ids' => 'nullable|array',
            'aeo_faq_questions' => 'nullable|array',
            'aeo_faq_answers' => 'nullable|array',
        ]);

        // Build FAQ Q&A structure for AEO / GEO
        $faq = [];
        if (!empty($validated['aeo_faq_questions']) && !empty($validated['aeo_faq_answers'])) {
            foreach ($validated['aeo_faq_questions'] as $idx => $q) {
                if (!empty($q) && !empty($validated['aeo_faq_answers'][$idx])) {
                    $faq[] = [
                        'question' => trim($q),
                        'answer' => trim($validated['aeo_faq_answers'][$idx]),
                    ];
                }
            }
        }
        $validated['aeo_geo_faq'] = $faq;

        $blog = $this->blogService->saveBlog($validated);

        return redirect()->route('blogs.show', $blog)->with('success', "Blog '{$blog->title}' created with SEO score: {$blog->seo_score}/100");
    }

    public function show(Blog $blog)
    {
        $blog->load(['category', 'author', 'tags', 'revisions.user']);
        return view('blogs.show', compact('blog'));
    }

    public function edit(Blog $blog)
    {
        $categories = BlogCategory::all();
        $tags = BlogTag::all();
        return view('blogs.edit', compact('blog', 'categories', 'tags'));
    }

    public function update(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:blogs,slug,' . $blog->id,
            'summary' => 'nullable|string',
            'body' => 'required|string',
            'category_id' => 'nullable|exists:blog_categories,id',
            'status' => 'required|in:draft,review,scheduled,published',
            'primary_keyword' => 'nullable|string|max:255',
            'secondary_keywords' => 'nullable|string',
            'search_intent' => 'required|in:informational,commercial,transactional,navigational',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|url|max:255',
            'tag_ids' => 'nullable|array',
            'aeo_faq_questions' => 'nullable|array',
            'aeo_faq_answers' => 'nullable|array',
        ]);

        $faq = [];
        if (!empty($validated['aeo_faq_questions']) && !empty($validated['aeo_faq_answers'])) {
            foreach ($validated['aeo_faq_questions'] as $idx => $q) {
                if (!empty($q) && !empty($validated['aeo_faq_answers'][$idx])) {
                    $faq[] = [
                        'question' => trim($q),
                        'answer' => trim($validated['aeo_faq_answers'][$idx]),
                    ];
                }
            }
        }
        $validated['aeo_geo_faq'] = $faq;

        $this->blogService->saveBlog($validated, $blog);

        return redirect()->route('blogs.show', $blog)->with('success', "Blog updated. New SEO score: {$blog->seo_score}/100");
    }

    public function destroy(Blog $blog)
    {
        $blog->delete();
        return redirect()->route('blogs.index')->with('success', 'Blog article deleted.');
    }
}

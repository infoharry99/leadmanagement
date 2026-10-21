<?php

namespace App\Http\Controllers;

use App\Models\AiContentGeneration;
use App\Models\ContentCalendar;
use App\Models\SocialPost;
use App\Services\AiContentService;
use Illuminate\Http\Request;

class AiStudioController extends Controller
{
    public function __construct(protected AiContentService $aiService)
    {
    }

    public function index()
    {
        $recentGenerations = AiContentGeneration::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('ai.index', compact('recentGenerations'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|in:linkedin,instagram,facebook,tiktok,blog',
            'topic' => 'required|string|max:255',
            'audience' => 'nullable|string|max:255',
            'tone' => 'required|string',
            'service_focus' => 'required|string',
            'call_to_action' => 'nullable|string',
            'keywords' => 'nullable|string',
            'prohibited_claims' => 'nullable|string',
        ]);

        $result = $this->aiService->generateContent($validated);

        $generation = AiContentGeneration::create([
            'user_id' => auth()->id(),
            'topic' => $validated['topic'],
            'platform' => $validated['platform'],
            'audience' => $validated['audience'] ?? null,
            'tone' => $validated['tone'],
            'service_focus' => $validated['service_focus'],
            'call_to_action' => $validated['call_to_action'] ?? null,
            'keywords' => $validated['keywords'] ?? null,
            'prohibited_claims' => $validated['prohibited_claims'] ?? null,
            'output_variations' => $result['variations'],
            'provider' => $result['provider'],
        ]);

        return redirect()->route('ai.show', $generation)->with('success', 'Generated multiple variations successfully.');
    }

    public function show(AiContentGeneration $generation)
    {
        return view('ai.show', compact('generation'));
    }

    public function saveToCalendar(Request $request, AiContentGeneration $generation)
    {
        $request->validate([
            'content' => 'required|string',
            'scheduled_date' => 'required|date',
            'title' => 'required|string',
        ]);

        $item = ContentCalendar::create([
            'title' => $request->input('title'),
            'scheduled_date' => $request->input('scheduled_date'),
            'scheduled_time' => '10:00:00',
            'platform' => $generation->platform,
            'content_type' => $generation->platform === 'tiktok' ? 'video_script' : ($generation->platform === 'instagram' ? 'carousel' : 'post'),
            'content_draft' => $request->input('content'),
            'target_audience' => $generation->audience,
            'industry' => 'Tech',
            'campaign_goal' => 'Lead generation & Awareness',
            'status' => 'draft',
            'assigned_user_id' => auth()->id(),
        ]);

        return redirect()->route('calendar.index')->with('success', 'AI copy saved to Content Calendar as a draft.');
    }

    public function saveToSocial(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'title' => 'required|string',
            'platform' => 'required|string',
        ]);

        $post = SocialPost::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'target_platforms' => [$request->input('platform')],
            'status' => 'draft',
            'created_by_id' => auth()->id(),
        ]);

        return redirect()->route('social.index')->with('success', 'Created new social media draft post.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::with('createdBy')->latest()->paginate(10);
        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        return view('templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'body_html' => 'required|string',
            'body_plain' => 'nullable|string',
        ]);

        $template = EmailTemplate::create(array_merge($validated, [
            'variables_schema' => ['first_name', 'company_name', 'industry', 'service_name', 'unsubscribe_url'],
            'created_by_id' => auth()->id(),
        ]));

        return redirect()->route('templates.index')->with('success', "Template '{$template->name}' created.");
    }

    public function edit(EmailTemplate $template)
    {
        return view('templates.edit', compact('template'));
    }

    public function update(Request $request, EmailTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'body_html' => 'required|string',
            'body_plain' => 'nullable|string',
        ]);

        $template->update($validated);

        return redirect()->route('templates.index')->with('success', "Template updated.");
    }

    public function destroy(EmailTemplate $template)
    {
        if ($template->campaigns()->exists()) {
            return back()->with('error', 'Cannot delete template linked to existing campaigns.');
        }

        $template->delete();
        return redirect()->route('templates.index')->with('success', 'Template removed.');
    }

    public function preview(EmailTemplate $template)
    {
        $sample = [
            'first_name' => 'Sarah',
            'company_name' => 'Apex Technologies',
            'industry' => 'Cloud Infrastructure',
            'service_name' => 'Laravel Enterprise Application Development',
            'unsubscribe_url' => url('/unsubscribe/sample-token'),
        ];

        $rendered = $template->render($sample, $sample['unsubscribe_url']);

        return response()->json($rendered);
    }
}

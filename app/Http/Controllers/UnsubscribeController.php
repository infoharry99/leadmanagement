<?php

namespace App\Http\Controllers;

use App\Models\SuppressionList;
use App\Services\EmailService;
use Illuminate\Http\Request;

class UnsubscribeController extends Controller
{
    public function __construct(protected EmailService $emailService)
    {
    }

    public function show(Request $request, string $token)
    {
        $email = $request->input('email');
        $isSuppressed = $email ? SuppressionList::isSuppressed($email) : false;

        return view('public.unsubscribe', compact('token', 'email', 'isSuppressed'));
    }

    public function process(Request $request, string $token)
    {
        $request->validate([
            'email' => 'required|email',
            'reason' => 'nullable|string',
            'campaign_id' => 'nullable|integer',
        ]);

        $email = $request->input('email');
        $reason = $request->input('reason', 'User requested unsubscribe via web link');
        $campaignId = $request->input('campaign_id');

        $this->emailService->processUnsubscribe($email, $reason, $campaignId ? (int) $campaignId : null);

        return view('public.unsubscribe_success', [
            'email' => $email,
            'serviceInfo' => "Thank you for updating your email preferences. Your address has been removed from our marketing list. For future reference, Nexteck provides web development, custom software, mobile applications, and business automation services. No further marketing emails will be sent unless you separately opt in again.",
        ]);
    }
}

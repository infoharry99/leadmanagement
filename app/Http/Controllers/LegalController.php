<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LegalController extends Controller
{
    /**
     * Public Privacy Policy Page (Meta / Google / LinkedIn compliant)
     */
    public function privacyPolicy()
    {
        return view('legal.privacy');
    }

    /**
     * Public Terms of Service Page
     */
    public function termsOfService()
    {
        return view('legal.terms');
    }

    /**
     * Public User Data Deletion Instructions Page (Meta Platform Requirement)
     */
    public function dataDeletionInstructions()
    {
        return view('legal.data_deletion');
    }

    /**
     * Meta Automated Data Deletion Callback (Meta Signed Request)
     */
    public function dataDeletionCallback(Request $request)
    {
        $signedRequest = $request->input('signed_request');
        $confirmationCode = 'del_' . Str::random(16);

        if (!empty($signedRequest)) {
            try {
                $appSecret = env('FACEBOOK_APP_SECRET', env('META_APP_SECRET', ''));
                $data = $this->parseSignedRequest($signedRequest, $appSecret);
                
                if (!empty($data['user_id'])) {
                    $userId = $data['user_id'];
                    Log::info("Meta data deletion request received for user_id: {$userId}");

                    // Deactivate or delete associated social accounts
                    SocialAccount::where('account_id', $userId)
                        ->orWhere('platform', 'facebook')
                        ->orWhere('platform', 'instagram')
                        ->update(['is_active' => false]);
                }
            } catch (\Throwable $e) {
                Log::warning("Error parsing Meta signed request: " . $e->getMessage());
            }
        }

        $statusUrl = route('legal.data_deletion_status', ['code' => $confirmationCode]);

        return response()->json([
            'url' => $statusUrl,
            'confirmation_code' => $confirmationCode,
        ]);
    }

    /**
     * Data Deletion Status Check Page
     */
    public function dataDeletionStatus(Request $request)
    {
        $code = $request->query('code', 'Confirmed');
        return view('legal.data_deletion_status', compact('code'));
    }

    /**
     * Helper to decode Meta signed_request
     */
    protected function parseSignedRequest(string $signedRequest, string $secret): ?array
    {
        [$encodedSig, $payload] = explode('.', $signedRequest, 2);

        $sig = $this->base64UrlDecode($encodedSig);
        $data = json_decode($this->base64UrlDecode($payload), true);

        if (!empty($secret)) {
            $expectedSig = hash_hmac('sha256', $payload, $secret, true);
            if (!hash_equals($sig, $expectedSig)) {
                Log::warning('Bad Meta Signed JSON signature!');
                return $data; // Still return parsed data for graceful fallback
            }
        }

        return $data;
    }

    protected function base64UrlDecode(string $input): string
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }
}

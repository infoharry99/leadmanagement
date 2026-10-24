<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SocialPostSchedulerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
        Storage::fake('public');
    }

    public function test_authenticated_user_can_view_social_dashboard(): void
    {
        // Seed a sample scheduled and published post
        SocialPost::create([
            'title' => 'Scheduled Launch',
            'content' => 'Launching tomorrow on LinkedIn!',
            'target_platforms' => ['linkedin'],
            'status' => 'scheduled',
            'scheduled_at' => now()->addDay(),
            'created_by_id' => $this->user->id,
        ]);

        SocialPost::create([
            'title' => 'Published Announcement',
            'content' => 'We are live on Facebook!',
            'target_platforms' => ['facebook'],
            'status' => 'published',
            'published_at' => now()->subHour(),
            'created_by_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('social.posts.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Social Media Post Scheduler');
        $response->assertSee('Create Post');
        $response->assertSee('Scheduled');
        $response->assertSee('Published');
        $response->assertSee('Failed');
        $response->assertSee('Launching tomorrow on LinkedIn!');
        $response->assertSee('We are live on Facebook!');
    }

    public function test_user_can_view_create_post_form_with_platforms_and_restrictions(): void
    {
        $response = $this->actingAs($this->user)->get(route('social.posts.create'));

        $response->assertStatus(200);
        $response->assertSee('Create New Social Post');
        $response->assertSee('Upload Media');
        $response->assertSee('name="media"', false);
        $response->assertSee('Facebook Pages');
        $response->assertSee('Instagram');
        $response->assertSee('LinkedIn');
        $response->assertSee('TikTok');
        $response->assertSee('Requires an image or video file');
        $response->assertSee('Requires a video file (.mp4, .mov, 3s-10m)');

        // Also verify legacy /social/create path renders the new form with image upload
        $responseLegacy = $this->actingAs($this->user)->get('/social/create');
        $responseLegacy->assertStatus(200);
        $responseLegacy->assertSee('Upload Media');
        $responseLegacy->assertSee('name="media"', false);
    }

    public function test_user_can_create_and_publish_immediate_post(): void
    {
        // Setup connected account
        SocialAccount::create([
            'platform' => 'facebook',
            'account_name' => 'Nexteck Page',
            'account_id' => '123456789',
            'access_token' => 'mock_token',
            'is_active' => true,
        ]);

        $image = UploadedFile::fake()->image('banner.png', 800, 600);

        $response = $this->actingAs($this->user)->post(route('social.posts.store'), [
            'content' => 'Exciting updates are coming to our platform! Check out our new scheduler.',
            'hashtags' => 'marketing, tech, startup',
            'platforms' => ['facebook'],
            'action_type' => 'now',
            'media' => $image,
        ]);

        $response->assertRedirect(route('social.posts.index'));
        $this->assertDatabaseHas('social_posts', [
            'content' => 'Exciting updates are coming to our platform! Check out our new scheduler.',
            'status' => 'published',
        ]);

        $post = SocialPost::where('content', 'like', '%Exciting updates%')->first();
        $this->assertNotNull($post);
        $this->assertContains('#marketing', $post->hashtags);
        $this->assertContains('#startup', $post->hashtags);
        $this->assertNotEmpty($post->media_urls);
    }

    public function test_user_can_schedule_post_for_future_datetime(): void
    {
        SocialAccount::create([
            'platform' => 'linkedin',
            'account_name' => 'Nexteck LinkedIn',
            'account_id' => 'urn:li:person:99999',
            'access_token' => 'mock_token',
            'is_active' => true,
        ]);

        $tomorrow = Carbon::now()->addDays(2)->format('Y-m-d');

        $response = $this->actingAs($this->user)->post(route('social.posts.store'), [
            'content' => 'Scheduled announcement for next week.',
            'hashtags' => '#innovation #future',
            'platforms' => ['linkedin'],
            'action_type' => 'schedule',
            'scheduled_date' => $tomorrow,
            'scheduled_time' => '14:30',
        ]);

        $response->assertRedirect(route('social.posts.index', ['status' => 'scheduled']));
        $this->assertDatabaseHas('social_posts', [
            'content' => 'Scheduled announcement for next week.',
            'status' => 'scheduled',
        ]);

        $post = SocialPost::where('content', 'Scheduled announcement for next week.')->first();
        $this->assertNotNull($post->scheduled_at);
        $this->assertEquals($tomorrow, $post->scheduled_at->format('Y-m-d'));
        $this->assertEquals('14:30', $post->scheduled_at->format('H:i'));
    }

    public function test_instagram_restriction_prevents_text_only_post(): void
    {
        SocialAccount::create([
            'platform' => 'instagram',
            'account_name' => 'nexteck_ig',
            'account_id' => 'ig_123',
            'access_token' => 'mock_token',
            'is_active' => true,
        ]);

        // Attempting text-only post to Instagram
        $response = $this->actingAs($this->user)->from(route('social.posts.create'))->post(route('social.posts.store'), [
            'content' => 'Text only post on Instagram without any image or video.',
            'platforms' => ['instagram'],
            'action_type' => 'now',
        ]);

        $response->assertRedirect(route('social.posts.create'));
        $response->assertSessionHasErrors('instagram');
        $this->assertDatabaseMissing('social_posts', [
            'content' => 'Text only post on Instagram without any image or video.',
        ]);
    }

    public function test_tiktok_restriction_prevents_image_only_post(): void
    {
        SocialAccount::create([
            'platform' => 'tiktok',
            'account_name' => 'nexteck_tiktok',
            'account_id' => 'tt_123',
            'access_token' => 'mock_token',
            'is_active' => true,
        ]);

        $image = UploadedFile::fake()->image('photo.jpg');

        // TikTok does not accept photos/images; it strictly requires video
        $response = $this->actingAs($this->user)->from(route('social.posts.create'))->post(route('social.posts.store'), [
            'content' => 'TikTok video test with image file.',
            'platforms' => ['tiktok'],
            'action_type' => 'now',
            'media' => $image,
        ]);

        $response->assertRedirect(route('social.posts.create'));
        $response->assertSessionHasErrors('tiktok');
        $this->assertDatabaseMissing('social_posts', [
            'content' => 'TikTok video test with image file.',
        ]);
    }

    public function test_scheduled_posts_command_publishes_due_posts(): void
    {
        SocialAccount::create([
            'platform' => 'facebook',
            'account_name' => 'Nexteck Page',
            'account_id' => '123456789',
            'access_token' => 'mock_token',
            'is_active' => true,
        ]);

        // Create a scheduled post that is due
        $post = SocialPost::create([
            'title' => 'Due post',
            'content' => 'This post was scheduled and is now due to publish.',
            'target_platforms' => ['facebook'],
            'status' => 'scheduled',
            'scheduled_at' => now()->subMinutes(5),
            'created_by_id' => $this->user->id,
        ]);

        Artisan::call('nexteck:publish-social');

        $post->refresh();
        $this->assertEquals('published', $post->status);
        $this->assertNotNull($post->published_at);
    }

    public function test_user_can_connect_and_disconnect_social_account(): void
    {
        // 1. Manual Connect
        $response = $this->actingAs($this->user)->post(route('social.connections.manual'), [
            'platform' => 'linkedin',
            'account_name' => 'Nexteck Corporate',
            'account_id' => 'urn:li:organization:12345',
            'access_token' => 'AQVxxxx_test_token_string',
        ]);

        $response->assertRedirect(route('social.connections.index'));
        $account = SocialAccount::where('platform', 'linkedin')->first();
        $this->assertNotNull($account);
        $this->assertEquals('Nexteck Corporate', $account->account_name);
        $this->assertTrue($account->is_active);

        // 2. Disconnect
        $response = $this->actingAs($this->user)->post(route('social.connections.disconnect', $account));
        $response->assertRedirect(route('social.connections.index'));

        $account->refresh();
        $this->assertFalse($account->is_active);
    }

    public function test_meta_webhook_verification_handshake_succeeds(): void
    {
        // Unauthenticated Meta crawler verifies the webhook with challenge
        $response = $this->get('/social/callback/instagram?hub.mode=subscribe&hub.challenge=1158201444&hub.verify_token=test');

        $response->assertStatus(200);
        $this->assertEquals('1158201444', $response->getContent());

        // Dedicated webhook endpoint
        $responseDedicated = $this->get('/webhooks/meta?hub.mode=subscribe&hub.challenge=99887766&hub.verify_token=test');
        $responseDedicated->assertStatus(200);
        $this->assertEquals('99887766', $responseDedicated->getContent());

        // POST webhook event
        $responsePost = $this->postJson('/social/callback/instagram', [
            'object' => 'instagram',
            'entry' => [],
        ]);
        $responsePost->assertStatus(200);
        $responsePost->assertJson(['status' => 'EVENT_RECEIVED']);
    }

    public function test_user_can_view_api_configuration_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('social.config.index'));

        $response->assertStatus(200);
        $response->assertSee('Social API Credentials');
        $response->assertSee('Authorized OAuth Redirect URLs');
        $response->assertSee('Meta (Facebook Pages');
        $response->assertSee('LinkedIn Developer Setup');
        $response->assertSee('TikTok Content Posting API');
        $response->assertSee(route('social.oauth.callback', ['platform' => 'facebook']));
        $response->assertSee(route('social.oauth.callback', ['platform' => 'linkedin']));
        $response->assertSee(route('social.oauth.callback', ['platform' => 'tiktok']));
    }
}

<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\SystemSettings;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\InfobipClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear system settings cache before each test
        Cache::forget('system_setting.sms_enabled');
    }

    /** @test */
    public function system_setting_can_store_and_retrieve_values()
    {
        $this->assertTrue(SystemSetting::smsEnabled(), 'SMS should be enabled by default when no setting exists');

        SystemSetting::setValue('sms_enabled', '0');
        $this->assertFalse(SystemSetting::smsEnabled());

        SystemSetting::setValue('sms_enabled', '1');
        $this->assertTrue(SystemSetting::smsEnabled());
    }

    /** @test */
    public function system_settings_uses_cache()
    {
        SystemSetting::setValue('sms_enabled', '1');
        
        // Cache should now hold the value
        $this->assertTrue(Cache::has('system_setting.sms_enabled'));
        $this->assertEquals('1', Cache::get('system_setting.sms_enabled'));

        // Changing via setValue should bust the cache
        SystemSetting::setValue('sms_enabled', '0');
        $this->assertEquals('0', Cache::get('system_setting.sms_enabled'));
    }

    /** @test */
    public function admin_can_access_system_settings_page_and_toggle_sms()
    {
        // Setup user with super_admin role to access admin panel
        $adminRole = Role::create(['name' => 'super_admin']);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        // Verify initial state
        SystemSetting::setValue('sms_enabled', '1');

        Livewire::actingAs($admin)
            ->test(SystemSettings::class)
            ->assertSet('smsEnabled', true)
            ->call('toggleSms')
            ->assertSet('smsEnabled', false)
            ->assertNotificationSent();

        // Verify db was updated
        $this->assertFalse(SystemSetting::smsEnabled());

        // Toggle it back
        Livewire::actingAs($admin)
            ->test(SystemSettings::class)
            ->assertSet('smsEnabled', false)
            ->call('toggleSms')
            ->assertSet('smsEnabled', true)
            ->assertNotificationSent();

        $this->assertTrue(SystemSetting::smsEnabled());
    }

    /** @test */
    public function infobip_client_respects_sms_kill_switch()
    {
        Http::fake([
            '*/sms/2/text/advanced' => Http::response(['messages' => [['messageId' => 'test-id']]], 200),
        ]);

        $client = new InfobipClient();

        // 1. When SMS is enabled
        SystemSetting::setValue('sms_enabled', '1');
        $response = $client->sendSms('+263771234567', 'Hello World');

        $this->assertEquals('test-id', $response['messages'][0]['messageId']);
        Http::assertSentCount(1);

        // Reset fake and switch SMS off
        Http::fake([
            '*/sms/2/text/advanced' => Http::response(['messages' => [['messageId' => 'test-id-2']]], 200),
        ]);
        SystemSetting::setValue('sms_enabled', '0');

        // 2. When SMS is disabled
        $response = $client->sendSms('+263771234567', 'Hello World');
        
        // Assert we returned early with the disabled placeholder
        $this->assertEquals('sms-disabled', $response['messages'][0]['messageId']);
        
        // Assert no external HTTP request was made when disabled
        Http::assertSentCount(0);
    }
}

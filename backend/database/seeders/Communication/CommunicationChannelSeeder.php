<?php

namespace Database\Seeders\Communication;

use App\Models\Communication\CommunicationChannel;
use App\Models\School;
use Illuminate\Database\Seeder;

class CommunicationChannelSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();

        $channels = [
            [
                'name' => 'Email Channel',
                'code' => 'EMAIL-DEFAULT',
                'channel_type' => 'email',
                'provider' => 'log-email',
                'configuration' => ['from' => 'noreply@greenwood.local'],
                'status' => 'active',
            ],
            [
                'name' => 'SMS Channel',
                'code' => 'SMS-DEFAULT',
                'channel_type' => 'sms',
                'provider' => 'log-sms',
                'configuration' => ['sender_id' => 'GREENW'],
                'status' => 'active',
            ],
            [
                'name' => 'Push Channel',
                'code' => 'PUSH-DEFAULT',
                'channel_type' => 'push',
                'provider' => 'log-push',
                'configuration' => ['app' => 'school-erp'],
                'status' => 'active',
            ],
            [
                'name' => 'In-App Channel',
                'code' => 'INAPP-DEFAULT',
                'channel_type' => 'in_app',
                'provider' => 'in-app',
                'configuration' => ['badge' => true],
                'status' => 'active',
            ],
        ];

        foreach ($channels as $channel) {
            CommunicationChannel::withoutGlobalScopes()->updateOrCreate(
                ['school_id' => $school->id, 'code' => $channel['code']],
                $channel
            );
        }
    }
}

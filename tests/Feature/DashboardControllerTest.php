<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Enums\UserType;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\PayloadHistory;
use App\Models\MessageHistory;
use App\Models\Webhook;
use App\Models\User;
use App\Models\Bot;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
    * test Feature Dashboard.
    *
    * @return void
    */
    public function testDashboardFeature()
    {
        $user = factory(User::class)->create(['role' => UserType::USER]);

        $chatbot = factory(Bot::class)->create(['user_id' => $user->id]);
        $webhook = factory(Webhook::class)->create(['user_id' => $user->id, 'bot_id' => $chatbot->id]);
        $payloadHistory = factory(PayloadHistory::class)->create(['webhook_id' => $webhook->id, 'status' => 1]);

        factory(PayloadHistory::class)->create(['webhook_id' => $webhook->id, 'status' => 0]);
        $messageHistory = factory(MessageHistory::class)->create(['payload_history_id' => $payloadHistory->id, 'status' => 1]);
        factory(MessageHistory::class)->create(['payload_history_id' => $payloadHistory->id, 'status' => 0]);

        $this->actingAs($user);
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        
        $response->assertViewHas('countData');
        $countDataResponse = $response->getOriginalContent()->getData()['countData'];
        $this->assertEquals(1, $countDataResponse['webhook']);
        $this->assertEquals(2, $countDataResponse['payloadHistory']); 
        $this->assertEquals(2, $countDataResponse['messageHistory']); 
        $this->assertEquals(1, $countDataResponse['bot']);

        $response->assertViewHas('payloadHistory');
        $payloadHistoryResponse = $response->getOriginalContent()->getData()['payloadHistory'];
        $this->assertEquals(1, $payloadHistoryResponse['failedCases']); 
        $this->assertEquals(1, $payloadHistoryResponse['successCases']);
        $this->assertTrue(is_array($payloadHistoryResponse['dateChart']));
        $this->assertTrue(is_array($payloadHistoryResponse['payloadFailedChart']));
        $this->assertEquals(1, $payloadHistoryResponse['payloadFailedChart'][22][1]);
        $this->assertTrue(is_array($payloadHistoryResponse['payloadSuccessChart']));
        $this->assertEquals(1, $payloadHistoryResponse['payloadSuccessChart'][22][1]);

        $response->assertViewHas('messageHistory');
        $messageHistoryResponse = $response->getOriginalContent()->getData()['messageHistory'];
        $this->assertEquals(1, $messageHistoryResponse['failedCases']); 
        $this->assertEquals(1, $messageHistoryResponse['successCases']);
        $this->assertTrue(is_array($messageHistoryResponse['dateChart']));
        $this->assertTrue(is_array($messageHistoryResponse['messageFailedChart']));
        $this->assertEquals(1, $messageHistoryResponse['messageFailedChart'][22][1]);
        $this->assertTrue(is_array($messageHistoryResponse['messageSuccessChart']));
        $this->assertEquals(1, $messageHistoryResponse['messageSuccessChart'][22][1]);
    }

    /**
    * test Feature Dashboard Unauthenticated.
    *
    * @return void
    */
    public function testUnauthenticatedDashboardFeature()
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(302);
        $response->assertLocation('/');
    }
}

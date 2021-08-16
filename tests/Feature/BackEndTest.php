<?php

namespace Tests\Feature;

use App\Http\Controllers\SubscriberController;
use App\Models\Settings;
use MailerLiteApi\MailerLite;
use Tests\TestCase;

class BackEndTest extends TestCase
{
    public function testGetAllSubscribers()
    {
        $response = $this->json('GET', '/subscribers', ['start' => 0, 'length' => 10, 'search' => ['value' => ''], 'draw' => 1], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'draw',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'country',
                            'subscribe_date',
                            'subscribe_time',
                            'date_subscribe',
                        ],
                    ],
                    'recordsTotal',
                    'recordsFiltered',
                ]
            );
    }

    public function testSearchSubscribersShouldHaveAllDataIfSubsciberEmailExists()
    {
        $subscribersApi = (new MailerLite(Settings::where('key', 'MAILERLITE_API_KEY')->first()->value))->subscribers();
        $subscribers = $subscribersApi->get();
        $firstSubcriber = $subscribers->first();
        $response = $this->json('GET', '/subscribers', ['start' => 0, 'length' => 10, 'search' => ['value' => $firstSubcriber->email], 'draw' => 1], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $content = json_decode($response->getContent());

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'draw',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'country',
                            'subscribe_date',
                            'subscribe_time',
                            'date_subscribe',
                        ],
                    ],
                    'recordsTotal',
                    'recordsFiltered',
                ]
            );

        $firstSubcriberFields = $firstSubcriber->fields;
        $firstSubcriberCountry = app(SubscriberController::class)->getFieldValue($firstSubcriberFields, 'country');

        $this->assertEquals($firstSubcriber->email, $content->data[0]->email);
        $this->assertEquals($firstSubcriber->name, $content->data[0]->name);
        $this->assertEquals($firstSubcriber->id, $content->data[0]->id);
        $this->assertEquals($firstSubcriberCountry, $content->data[0]->country);
        $this->assertEquals($firstSubcriber->date_created, $content->data[0]->date_subscribe);
    }

    public function testSearchSubscribersShouldHaveEmptyDataIfSubsciberEmailDoesNotExist()
    {
        $response = $this->json('GET', '/subscribers', ['start' => 0, 'length' => 10, 'search' => ['value' => 'test@test.com'], 'draw' => 1], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'draw',
                    'data' => [],
                    'recordsTotal',
                    'recordsFiltered',
                ]
            );
    }

    public function testGetSubscriberShouldShowUserDetailsIfSubsciberEmailExists()
    {
        $subscribersApi = (new MailerLite(Settings::where('key', 'MAILERLITE_API_KEY')->first()->value))->subscribers();
        $subscribers = $subscribersApi->get();
        $firstSubcriberEmail = $subscribers->first()->email;
        $response = $this->get('/subscribers/' . $firstSubcriberEmail);

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'name',
                    'email',
                    'country',
                ]
            );
    }

    public function testGetSubscriberShouldShowErrorIfSubsciberEmailDoesNotExist()
    {
        $response = $this->get('/subscribers/test@test.com');

        $response->assertStatus(404)
            ->assertJsonStructure(['errors' => ['message']]);
    }

    public function testStoreSubscriberShouldShowErrorIfNameIsMissing()
    {
        $response = $this->postJson('/subscribers', ['email' => 'test@barihossain.com', 'country' => 'Bangladesh']);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['name']]);
    }

    public function testStoreSubscriberShouldShowErrorIfEmailIsMissing()
    {
        $response = $this->postJson('/subscribers', ['name' => 'Test User', 'country' => 'Bangladesh']);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['email']]);
    }

    public function testStoreSubscriberShouldShowErrorIfEmailIsInvalid()
    {
        $response = $this->postJson('/subscribers', ['name' => 'Test User', 'email' => 'test@test', 'country' => 'Bangladesh']);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['email']]);
    }

    public function testStoreSubscriberShouldShowErrorIfCountryIsMissing()
    {
        $response = $this->postJson('/subscribers', ['name' => 'Test User', 'email' => 'test@barihossain.com']);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['country']]);
    }

    public function testStoreSubscriberShouldShowSuccessIfAllGoesWell()
    {
        $response = $this->postJson('/subscribers', ['name' => 'Test User', 'email' => 'test@barihossain.com', 'country' => 'Bangladesh']);

        $response->assertOk()
            ->assertJsonStructure(['success']);
    }

    public function testUpdateSubscriberShouldShowErrorIfNameIsMissing()
    {
        $response = $this->putJson('/subscribers/test@barihossain.com', ['country' => 'Bangladesh']);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['name']]);
    }

    public function testUpdateSubscriberShouldShowErrorIfCountryIsMissing()
    {
        $response = $this->putJson('/subscribers/test@barihossain.com', ['name' => 'Test User']);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['country']]);
    }

    public function testUpdateSubscriberShouldShowSuccessIfAllGoesWell()
    {
        $response = $this->putJson('/subscribers/test@barihossain.com', ['name' => 'Test User', 'country' => 'Bangladesh']);

        $response->assertOk()
            ->assertJsonStructure(['success']);
    }

    public function testDeleteSubscriberShouldShowErrorIfSubsciberEmailDoesNotExist()
    {
        $response = $this->deleteJson('/subscribers/test@test.com');

        $response->assertStatus(404)
            ->assertJsonStructure(['errors' => ['message']]);
    }

    public function testDeleteSubscriberShouldSuccessIfAllGoesWell()
    {
        $response = $this->deleteJson('/subscribers/test@barihossain.com');

        $response->assertOk()
            ->assertJsonStructure(['success']);
    }

    public function testStoreApiKeyShouldShowErrorIfKeyDoesNotExist()
    {
        $response = $this->post('/', ['api_key' => '']);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['api_key']);
    }

    public function testStoreApiKeyShouldShowErrorIfKeyIsInvalid()
    {
        $validApiKey = Settings::where('key', 'MAILERLITE_API_KEY')->first()->value;
        $invalidApiKey = $validApiKey . '123';

        $response = $this->post('/', ['api_key' => $invalidApiKey]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['api_key']);
    }

    public function testUpdateApiKeyShouldShowErrorIfKeyDoesNotExist()
    {
        $response = $this->put('/', ['api_key' => '']);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['api_key']);
    }

    public function testUpdateApiKeyShouldShowErrorIfKeyIsInvalid()
    {
        $validApiKey = Settings::where('key', 'MAILERLITE_API_KEY')->first()->value;
        $invalidApiKey = $validApiKey . '123';

        $response = $this->put('/', ['api_key' => $invalidApiKey]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['api_key']);
    }

    public function testUpdateApiKeyShouldShowSuccessIfKeyIsValid()
    {
        $validApiKey = Settings::where('key', 'MAILERLITE_API_KEY')->first()->value;

        $response = $this->put('/', ['api_key' => $validApiKey]);

        $response->assertRedirect(route('home'))
            ->assertSessionHas('message', ['type' => 'success', 'body' => 'MailerLite API Key successfully updated.']);
    }
}

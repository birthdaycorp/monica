<?php

namespace Tests\Api\Account;

use Tests\ApiTestCase;
use App\Models\Contact\Call;
use App\Models\Account\Account;
use App\Models\Account\Place;
use App\Models\Contact\Contact;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiPlaceControllerTest extends ApiTestCase
{
    use DatabaseTransactions;

    protected $jsonPlace = [
        'id',
        'object',
        'street',
        'city',
        'province',
        'postal_code',
        'latitude',
        'longitude',
        'country',
        'account' => [
            'id',
        ],
        'created_at',
        'updated_at',
    ];

    public function test_it_gets_a_list_of_places()
    {
        $user = $this->signin();

        factory(Place::class, 3)->create([
            'account_id' => $user->account->id,
        ]);

        $response = $this->json('GET', '/api/places');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['*' => $this->jsonPlace],
        ]);
    }

    public function test_it_applies_the_limit_parameter_in_search()
    {
        $user = $this->signin();

        factory(Place::class, 10)->create([
            'account_id' => $user->account_id,
        ]);

        $response = $this->json('GET', '/api/places?limit=1');

        $response->assertJsonFragment([
            'total' => 10,
            'current_page' => 1,
            'per_page' => '1',
            'last_page' => 10,
        ]);

        $response = $this->json('GET', '/api/places?limit=2');

        $response->assertJsonFragment([
            'total' => 10,
            'current_page' => 1,
            'per_page' => '2',
            'last_page' => 5,
        ]);
    }

    public function test_it_gets_one_place()
    {
        $user = $this->signin();

        $place = factory(Place::class)->create([
            'account_id' => $user->account->id,
        ]);

        $response = $this->json('get', '/api/places/' . $place->id);

        $response->assertstatus(200);
        $response->assertjsonstructure([
            'data' => $this->jsonPlace,
        ]);
        $response->assertjsonfragment([
            'object' => 'place',
            'id' => $place->id,
        ]);
    }

    public function test_it_cant_get_a_call_with_unexistent_id()
    {
        $user = $this->signin();

        $response = $this->json('get', '/api/places/0');

        $this->expectnotfound($response);
    }

    public function test_it_create_a_place()
    {
        $user = $this->signin();

        $response = $this->json('post', '/api/places', [
            'city' => 'New York',
        ]);

        $response->assertstatus(201);
        $response->assertjsonstructure([
            'data' => $this->jsonPlace,
        ]);

        $placeId = $response->json('data.id');

        $response->assertjsonfragment([
            'object' => 'place',
            'id' => $placeId,
        ]);

        $this->assertdatabasehas('places', [
            'account_id' => $user->account->id,
            'id' => $placeId,
            'city' => 'New York',
            'latitude' => null,
        ]);
    }

    public function test_it_updates_a_place()
    {
        $user = $this->signin();

        $place = factory(Place::class)->create([
            'account_id' => $user->account->id,
        ]);

        $response = $this->json('put', '/api/places/' . $place->id, [
            'city' => 'New York',
        ]);

        $response->assertstatus(200);

        $response->assertjsonstructure([
            'data' => $this->jsonPlace,
        ]);

        $placeId = $response->json('data.id');

        $this->assertequals($place->id, $placeId);

        $response->assertjsonfragment([
            'object' => 'call',
            'id' => $placeId,
        ]);

        $this->assertDatabaseHas('places', [
            'account_id' => $user->account->id,
            'id' => $placeId,
            'city' => 'New York',
            'latitude' => null,
        ]);
    }

    // public function test_updating_call_generates_an_error()
    // {
    //     $user = $this->signin();
    //     $place = factory(Place::class)->create([
    //         'account_id' => $user->account->id,
    //     ]);

    //     $response = $this->json('put', '/api/places/' . $place->id, [
    //         'contact_id' => $place->contact_id,
    //     ]);

    //     $this->expectinvalidparameter($response, [
    //         'the called at field is required.',
    //     ]);
    // }

    // public function test_it_cant_update_a_call_if_account_is_not_linked_to_call()
    // {
    //     $user = $this->signin();

    //     $contact = factory(contact::class)->create([]);
    //     $place = factory(Place::class)->create([
    //         'account_id' => $contact->account->id,
    //
    //     ]);

    //     $response = $this->json('put', '/api/places/' . $place->id, [
    //         'city' => 'New York',
    //         'called_at' => '2018-05-01',
    //     ]);

    //     $this->expectnotfound($response);
    // }

    // public function test_it_deletes_a_call()
    // {
    //     $user = $this->signin();
    //     $contact = factory(contact::class)->create([
    //         'account_id' => $user->account->id,
    //     ]);
    //     $place = factory(Place::class)->create([
    //         'account_id' => $user->account->id,
    //
    //     ]);
    //     $this->assertdatabasehas('places', [
    //         'account_id' => $user->account->id,
    //
    //         'id' => $place->id,
    //     ]);

    //     $response = $this->json('delete', '/api/places/' . $place->id);

    //     $response->assertstatus(200);
    //     $this->assertdatabasemissing('places', [
    //         'account_id' => $user->account->id,
    //
    //         'id' => $place->id,
    //     ]);
    // }

    // public function test_it_cant_delete_a_call_if_call_doesnt_exist()
    // {
    //     $user = $this->signin();

    //     $response = $this->json('delete', '/api/places/0');

    //     $this->expectnotfound($response);
    // }

    // public function test_it_cant_delete_a_call_if_account_is_not_linked()
    // {
    //     $user = $this->signin();
    //     $contact = factory(contact::class)->create([]);
    //     $place = factory(Place::class)->create([
    //         'account_id' => $contact->account->id,
    //
    //     ]);

    //     $response = $this->json('delete', '/api/places/' . $place->id);

    //     $this->expectnotfound($response);
    // }
}

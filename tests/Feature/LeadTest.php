<?php

use App\Actions\CreateAmoLead;
use App\Exceptions\AmoAuth\AmoCrmNotAuthorizedException;
use App\Http\Requests\StoreLeadRequest;
use Illuminate\Support\Facades\Log;
use RuntimeException;

function validLeadPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Иван Иванов',
        'email' => 'ivan@example.com',
        'phone' => '+79991234567',
        'price' => '1500.50',
        'spent_more_than_30_seconds' => true,
    ], $overrides);
}

test('store creates lead and redirects back with success flash', function () {
    $action = Mockery::mock(CreateAmoLead::class);
    $action->shouldReceive('handle')
        ->once()
        ->with(Mockery::on(function (StoreLeadRequest $request) {
            return $request->input('price') === '1500.50'
                && $request->boolean('spent_more_than_30_seconds') === true;
        }));

    $this->instance(CreateAmoLead::class, $action);

    $response = $this->from(route('home'))->post(route('lead.store'), validLeadPayload());

    $response->assertRedirect(route('home'));
    $response->assertSessionHas('success', 'Заявка успешно отправлена!');
    $response->assertSessionMissing('error');
});

test('store validates form fields', function (array $payload, array $failedFields) {
    $action = Mockery::mock(CreateAmoLead::class);
    $action->shouldNotReceive('handle');

    $this->instance(CreateAmoLead::class, $action);

    $response = $this->from(route('home'))->post(route('lead.store'), $payload);

    $response->assertSessionHasErrors($failedFields);
})->with([
    'invalid email' => [validLeadPayload(['email' => 'not-an-email']), ['email']],
    'short name' => [validLeadPayload(['name' => 'Я']), ['name']],
    'short phone' => [validLeadPayload(['phone' => '123']), ['phone']],
    'non-numeric price' => [validLeadPayload(['price' => 'abc']), ['price']],
    'non-positive price' => [validLeadPayload(['price' => '0']), ['price']],
    'missing 30s flag' => [validLeadPayload(['spent_more_than_30_seconds' => null]), ['spent_more_than_30_seconds']],
]);

test('store shows error flash when amoCRM is not connected', function () {
    Log::spy();

    $action = Mockery::mock(CreateAmoLead::class);
    $action->shouldReceive('handle')
        ->once()
        ->andThrow(new AmoCrmNotAuthorizedException);

    $this->instance(CreateAmoLead::class, $action);

    $response = $this->from(route('home'))->post(route('lead.store'), validLeadPayload());

    $response->assertSessionHas('error', 'Не удалось отправить заявку. Попробуйте позже.');
    $response->assertSessionMissing('success');
    Log::shouldHaveReceived('warning')->once();
});

test('store shows error flash when amoCRM api throws', function () {
    Log::spy();

    $action = Mockery::mock(CreateAmoLead::class);
    $action->shouldReceive('handle')
        ->once()
        ->andThrow(new RuntimeException('api down'));

    $this->instance(CreateAmoLead::class, $action);

    $response = $this->from(route('home'))->post(route('lead.store'), validLeadPayload());

    $response->assertSessionHas('error', 'Не удалось отправить заявку. Попробуйте позже.');
    $response->assertSessionMissing('success');
    Log::shouldHaveReceived('error')->once();
});

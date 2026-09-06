<?php

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\EntitiesServices\Leads;
use AmoCRM\Models\LeadModel;
use App\Exceptions\AmoAuth\AmoCrmNotAuthorizedException;
use App\Services\AmoCrm\AmoCrmService;
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

function mockedAmoCrmService(callable $assertLead): AmoCrmService
{
    $leads = Mockery::mock(Leads::class);
    $leads->shouldReceive('addOneComplex')
        ->once()
        ->with(Mockery::on(fn (LeadModel $lead) => $assertLead($lead)))
        ->andReturnUsing(fn (LeadModel $lead) => $lead);

    $apiClient = Mockery::mock(AmoCRMApiClient::class);
    $apiClient->shouldReceive('leads')->once()->andReturn($leads);

    $amoCrm = Mockery::mock(AmoCrmService::class);
    $amoCrm->shouldReceive('getClient')->once()->andReturn($apiClient);

    return $amoCrm;
}

test('store creates lead and redirects back with success flash', function () {
    $amoCrm = mockedAmoCrmService(function (LeadModel $lead) {
        expect($lead->getName())->toBe('Иван Иванов')
            ->and($lead->getPriceWithMinorUnits())->toBe(1500.5);

        $contactFields = $lead->getContacts()?->first()?->getCustomFieldsValues();
        expect($contactFields?->getBy('fieldCode', 'EMAIL')?->getValues()?->first()?->getValue())
            ->toBe('ivan@example.com')
            ->and($contactFields?->getBy('fieldCode', 'PHONE')?->getValues()?->first()?->getValue())
            ->toBe('+79991234567');

        return true;
    });

    $this->instance(AmoCrmService::class, $amoCrm);

    $response = $this->from(route('home'))->post(route('lead.store'), validLeadPayload());

    $response->assertRedirect(route('home'));
    $response->assertSessionHas('success', 'Заявка успешно отправлена!');
    $response->assertSessionMissing('error');
});

test('store validates form fields', function (array $payload, array $failedFields) {
    $amoCrm = Mockery::mock(AmoCrmService::class);
    $amoCrm->shouldNotReceive('getClient');

    $this->instance(AmoCrmService::class, $amoCrm);

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

    $amoCrm = Mockery::mock(AmoCrmService::class);
    $amoCrm->shouldReceive('getClient')
        ->once()
        ->andThrow(new AmoCrmNotAuthorizedException);

    $this->instance(AmoCrmService::class, $amoCrm);

    $response = $this->from(route('home'))->post(route('lead.store'), validLeadPayload());

    $response->assertSessionHas('error', 'Не удалось отправить заявку. Попробуйте позже.');
    $response->assertSessionMissing('success');
    Log::shouldHaveReceived('warning')->once();
});

test('store shows error flash when amoCRM api throws', function () {
    Log::spy();

    $amoCrm = Mockery::mock(AmoCrmService::class);
    $amoCrm->shouldReceive('getClient')
        ->once()
        ->andThrow(new RuntimeException('api down'));

    $this->instance(AmoCrmService::class, $amoCrm);

    $response = $this->from(route('home'))->post(route('lead.store'), validLeadPayload());

    $response->assertSessionHas('error', 'Не удалось отправить заявку. Попробуйте позже.');
    $response->assertSessionMissing('success');
    Log::shouldHaveReceived('error')->once();
});

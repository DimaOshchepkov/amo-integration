<?php

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\EntitiesServices\Leads;
use AmoCRM\Models\LeadModel;
use App\Actions\CreateAmoLead;
use App\Http\Requests\StoreLeadRequest;
use App\Services\AmoCrm\AmoCrmService;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config()->set('services.amocrm.lead_30s_field_id', null);
});

function leadRequest(array $overrides = []): StoreLeadRequest
{
    $request = StoreLeadRequest::create(route('lead.store'), 'POST', array_merge([
        'name' => 'Иван Иванов',
        'email' => 'ivan@example.com',
        'phone' => '+79991234567',
        'price' => '1500.50',
        'spent_more_than_30_seconds' => true,
    ], $overrides));

    $request->setContainer(app());

    return $request;
}

function createAmoLeadWith(callable $assertLead): CreateAmoLead
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

    return new CreateAmoLead($amoCrm);
}

test('handle maps form data to lead with contact and checkbox', function () {
    config()->set('services.amocrm.lead_30s_field_id', 777);

    $action = createAmoLeadWith(function (LeadModel $lead) {
        expect($lead->getName())->toBe('Иван Иванов')
            ->and($lead->getPriceWithMinorUnits())->toBe(1500.5);

        $contact = $lead->getContacts()?->first();
        expect($contact)->not->toBeNull()
            ->and($contact->getName())->toBe('Иван Иванов');

        $contactFields = $contact->getCustomFieldsValues();
        expect($contactFields->getBy('fieldCode', 'EMAIL')?->getValues()?->first()?->getValue())
            ->toBe('ivan@example.com')
            ->and($contactFields->getBy('fieldCode', 'PHONE')?->getValues()?->first()?->getValue())
            ->toBe('+79991234567');

        $leadFields = $lead->getCustomFieldsValues();
        expect($leadFields->getBy('fieldId', 777)?->getValues()?->first()?->getValue())
            ->toBe(true);

        return true;
    });

    $createdLead = $action->handle(leadRequest());

    expect($createdLead)->toBeInstanceOf(LeadModel::class);
});

test('handle omits checkbox when flag is false', function () {
    config()->set('services.amocrm.lead_30s_field_id', 777);

    $action = createAmoLeadWith(function (LeadModel $lead) {
        expect($lead->getCustomFieldsValues()?->getBy('fieldId', 777))->toBeNull();

        return true;
    });

    $action->handle(leadRequest(['spent_more_than_30_seconds' => false]));
});

test('handle omits checkbox when field id is not configured', function () {
    $action = createAmoLeadWith(function (LeadModel $lead) {
        expect($lead->getCustomFieldsValues()?->getBy('fieldId', 777))->toBeNull();

        return true;
    });

    $action->handle(leadRequest());
});

<?php

namespace App\Actions;

use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\InvalidArgumentException;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\CheckboxCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\CheckboxCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\CheckboxCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Models\LeadModel;
use App\Exceptions\AmoAuth\AmoCrmNotAuthorizedException;
use App\Http\Requests\StoreLeadRequest;
use App\Services\AmoCrm\AmoCrmService;
use Illuminate\Support\Facades\Log;

readonly class CreateAmoLead
{
    public function __construct(
        private AmoCrmService $amoCrm,
    ) {}

    /**
     * Создаёт сделку из заявки формы с прикреплённым контактом.
     *
     * @throws AmoCrmNotAuthorizedException
     * @throws AmoCRMMissedTokenException
     * @throws InvalidArgumentException
     */
    public function handle(StoreLeadRequest $request): LeadModel
    {
        $lead = (new LeadModel)
            ->setName((string) $request->string('name'))
            ->setPrice((float) $request->input('price'));

        $leadCustomFields = new CustomFieldsValuesCollection;

        $field30sId = config('services.amocrm.lead_30s_field_id');

        if ($request->boolean('spent_more_than_30_seconds') && filled($field30sId) && (int) $field30sId > 0) {
            $leadCustomFields->add(
                (new CheckboxCustomFieldValuesModel)
                    ->setFieldId((int) $field30sId)
                    ->setValues(
                        (new CheckboxCustomFieldValueCollection)
                            ->add((new CheckboxCustomFieldValueModel)->setValue(true))
                    )
            );
        }

        if (! $leadCustomFields->isEmpty()) {
            $lead->setCustomFieldsValues($leadCustomFields);
        }

        $contact = (new ContactModel)
            ->setName((string) $request->string('name'))
            ->setCustomFieldsValues(
                (new CustomFieldsValuesCollection)
                    ->add($this->multitextField('EMAIL', (string) $request->string('email')))
                    ->add($this->multitextField('PHONE', (string) $request->string('phone')))
            );

        $lead->setContacts((new ContactsCollection)->add($contact));

        $createdLead = $this->amoCrm->getClient()->leads()->addOneComplex($lead);

        Log::info('Сделка создана в amoCRM', [
            'lead_id' => $createdLead->getId(),
            'contact_id' => $createdLead->getContacts()?->first()?->getId(),
        ]);

        return $createdLead;
    }

    /**
     * Встроенное поле контакта (PHONE/EMAIL) — multitext по field_code.
     */
    private function multitextField(string $fieldCode, string $value): MultitextCustomFieldValuesModel
    {
        $field = new MultitextCustomFieldValuesModel;

        $field->setFieldCode($fieldCode);
        $field->setValues(
            (new MultitextCustomFieldValueCollection)
                ->add((new MultitextCustomFieldValueModel)->setValue($value))
        );

        return $field;
    }
}

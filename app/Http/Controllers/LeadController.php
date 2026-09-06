<?php

namespace App\Http\Controllers;

use App\Actions\CreateAmoLead;
use App\Exceptions\AmoAuth\AmoCrmNotAuthorizedException;
use App\Http\Requests\StoreLeadRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class LeadController extends Controller
{
    public function __construct(
        private readonly CreateAmoLead $createAmoLead,
    ) {}

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        try {
            $this->createAmoLead->handle($request);
        } catch (AmoCrmNotAuthorizedException $e) {
            Log::warning('Заявка не отправлена в amoCRM: интеграция не подключена', ['reason' => $e->getMessage()]);

            return back()->with('error', 'Не удалось отправить заявку. Попробуйте позже.');
        } catch (Throwable $e) {
            Log::error('Ошибка создания сделки в amoCRM', ['exception' => $e]);

            return back()->with('error', 'Не удалось отправить заявку. Попробуйте позже.');
        }

        return back()->with('success', 'Заявка успешно отправлена!');
    }
}

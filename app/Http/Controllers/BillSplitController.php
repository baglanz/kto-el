<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BillSplitController extends Controller
{
    public function index(): View
    {
        return view('bill', [
            'data' => [
                'service_percent' => 10,
                'tip_percent' => 0,
                'people' => [''],
                'items' => [
                    ['name' => '', 'price' => '', 'quantity' => 1, 'eaters' => []],
                ],
            ],
            'result' => null,
            'errorsBag' => null,
        ]);
    }

    public function calculate(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'service_percent' => ['required','numeric','min:0','max:100'],
            'tip_percent' => ['required','numeric','min:0','max:100'],
            'people' => ['required','array','min:1'],
            'people.*' => ['nullable','string','max:50'],
            'items' => ['required','array','min:1'],
            'items.*.name' => ['nullable','string','max:120'],
            'items.*.price' => ['required','numeric','min:0'],
            'items.*.quantity' => ['nullable','integer','min:1'],
            'items.*.eaters' => ['nullable','array'],
            'items.*.eaters.*' => ['required','integer','min:0'],
        ], [
            'service_percent.required' => 'Поле "Обслуживание" обязательно для заполнения',
            'service_percent.numeric' => 'Поле "Обслуживание" должно быть числом',
            'service_percent.min' => 'Поле "Обслуживание" не может быть меньше 0',
            'service_percent.max' => 'Поле "Обслуживание" не может быть больше 100',
            'tip_percent.required' => 'Поле "Чаевые" обязательно для заполнения',
            'tip_percent.numeric' => 'Поле "Чаевые" должно быть числом',
            'tip_percent.min' => 'Поле "Чаевые" не может быть меньше 0',
            'tip_percent.max' => 'Поле "Чаевые" не может быть больше 100',
            'people.required' => 'Необходимо добавить хотя бы одного участника',
            'people.array' => 'Поле "Участники" должно быть массивом',
            'people.min' => 'Необходимо добавить хотя бы одного участника',
            'people.*.string' => 'Имя участника должно быть строкой',
            'people.*.max' => 'Имя участника не может быть длиннее 50 символов',
            'items.required' => 'Необходимо добавить хотя бы одну позицию',
            'items.array' => 'Поле "Позиции" должно быть массивом',
            'items.min' => 'Необходимо добавить хотя бы одну позицию',
            'items.*.name.string' => 'Название позиции должно быть строкой',
            'items.*.name.max' => 'Название позиции не может быть длиннее 120 символов',
            'items.*.price.required' => 'Цена позиции обязательна для заполнения',
            'items.*.price.numeric' => 'Цена позиции должна быть числом',
            'items.*.price.min' => 'Цена позиции не может быть меньше 0',
            'items.*.quantity.integer' => 'Количество должно быть целым числом',
            'items.*.quantity.min' => 'Количество не может быть меньше 1',
            'items.*.eaters.array' => 'Поле "Кто ел" должно быть массивом',
            'items.*.eaters.*.required' => 'Неверный индекс участника',
            'items.*.eaters.*.integer' => 'Индекс участника должен быть числом',
            'items.*.eaters.*.min' => 'Индекс участника не может быть меньше 0',
        ], [
            'service_percent' => 'Обслуживание',
            'tip_percent' => 'Чаевые',
            'people' => 'Участники',
            'people.*' => 'Участник',
            'items' => 'Позиции',
            'items.*.name' => 'Название позиции',
            'items.*.price' => 'Цена позиции',
            'items.*.quantity' => 'Количество',
            'items.*.eaters' => 'Кто ел',
            'items.*.eaters.*' => 'Участник',
        ]);

        // Нормализуем людей: убираем пустые, переиндексируем
        $rawPeople = $validated['people'];
        $people = [];
        foreach ($rawPeople as $p) {
            $p = trim((string)$p);
            if ($p !== '') $people[] = $p;
        }
        if (count($people) === 0) {
            return back()->withErrors(['people' => 'Добавь хотя бы одно имя'])->withInput();
        }

        // Расчет
        $subtotals = array_fill(0, count($people), 0.0);
        $items = $validated['items'];

        // Нормализуем quantity для всех items
        foreach ($items as &$item) {
            if (!isset($item['quantity']) || $item['quantity'] < 1) {
                $item['quantity'] = 1;
            } else {
                $item['quantity'] = (int) $item['quantity'];
            }
        }
        unset($item); // снимаем ссылку

        foreach ($items as $item) {
            $price = (float) $item['price'];
            $quantity = (int) $item['quantity'];
            $totalPrice = $price * $quantity;

            $eaters = $item['eaters'] ?? [];
            $eaters = array_values(array_unique(array_map('intval', $eaters)));

            // Если никого не выбрали — делим на всех
            if (count($eaters) === 0) {
                $eaters = range(0, count($people) - 1);
            }

            // На всякий: фильтр границ
            $eaters = array_values(array_filter($eaters, fn($i) => $i >= 0 && $i < count($people)));
            $eatersCount = count($eaters);

            if ($eatersCount === 0) {
                // теоретически возможно только если people пустой
                continue;
            }

            $share = $totalPrice / $eatersCount;
            foreach ($eaters as $personIndex) {
                $subtotals[$personIndex] += $share;
            }
        }


        $totalSubtotal = array_sum($subtotals);

        $servicePercent = (float)$validated['service_percent'];
        $tipPercent = (float)$validated['tip_percent'];

        $serviceTotal = $totalSubtotal * ($servicePercent / 100);
        $tipTotal = $totalSubtotal * ($tipPercent / 100);

        $result = [];
        foreach ($people as $i => $name) {
            $base = $subtotals[$i];
            $ratio = $totalSubtotal > 0 ? ($base / $totalSubtotal) : 0;

            $serviceShare = $serviceTotal * $ratio;
            $tipShare = $tipTotal * $ratio;

            $result[] = [
                'name' => $name,
                'subtotal' => $base,
                'service' => $serviceShare,
                'tip' => $tipShare,
                'total' => $base + $serviceShare + $tipShare,
            ];
        }

        $resultData = [
            'rows' => $result,
            'subtotal_total' => $totalSubtotal,
            'service_total' => $serviceTotal,
            'tip_total' => $tipTotal,
            'grand_total' => $totalSubtotal + $serviceTotal + $tipTotal,
        ];

        // Сохраняем расчет в базу данных
        Bill::create([
            'phone_number' => $request->input('phone_number'),
            'service_percent' => $validated['service_percent'],
            'tip_percent' => $validated['tip_percent'],
            'people' => $people,
            'items' => $items,
            'result' => $resultData,
        ]);

        return view('bill', [
            'data' => [
                'service_percent' => $validated['service_percent'],
                'tip_percent' => $validated['tip_percent'],
                'people' => $rawPeople, // оставляем как вводили, чтобы не прыгали индексы в UI
                'items' => $items,
            ],
            'result' => $resultData,
        ]);
    }
}

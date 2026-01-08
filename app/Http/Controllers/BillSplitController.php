<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class BillSplitController extends Controller
{
    public function index()
    {
        return view('bill', [
            'data' => [
                'service_percent' => 10,
                'tip_percent' => 0,
                'people' => [''],
                'items' => [
                    ['name' => '', 'price' => '', 'eaters' => []],
                ],
            ],
            'result' => null,
            'errorsBag' => null,
        ]);
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'service_percent' => ['required','numeric','min:0','max:100'],
            'tip_percent' => ['required','numeric','min:0','max:100'],
            'people' => ['required','array','min:1'],
            'people.*' => ['nullable','string','max:50'],
            'items' => ['required','array','min:1'],
            'items.*.name' => ['nullable','string','max:120'],
            'items.*.price' => ['required','numeric','min:0'],
            'items.*.eaters' => ['nullable','array'],
            'items.*.eaters.*' => ['required','integer','min:0'],
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

        foreach ($items as $idx => $item) {
            $price = (float) $item['price'];

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

            $share = $price / $eatersCount;
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

        return view('bill', [
            'data' => [
                'service_percent' => $validated['service_percent'],
                'tip_percent' => $validated['tip_percent'],
                'people' => $rawPeople, // оставляем как вводили, чтобы не прыгали индексы в UI
                'items' => $items,
            ],
            'result' => [
                'rows' => $result,
                'subtotal_total' => $totalSubtotal,
                'service_total' => $serviceTotal,
                'tip_total' => $tipTotal,
                'grand_total' => $totalSubtotal + $serviceTotal + $tipTotal,
            ],
        ]);
    }
}

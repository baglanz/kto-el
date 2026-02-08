<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Счет - Разделение</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            color: #111;
        }
        .info-section {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .info-section h2 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #111;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f1f1f1;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
            white-space: nowrap;
        }
        .text-center {
            text-align: center;
            white-space: nowrap;
        }
        .price-cell {
            white-space: nowrap;
        }
        .total-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .total-row.final {
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #333;
        }
        .items-table {
            margin-bottom: 30px;
        }
        .result-table {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <h1>Разделение счета</h1>

    <div class="info-section">
        <h2>Параметры расчета</h2>
        <p><strong>Обслуживание:</strong> {{ rtrim(rtrim(number_format($service_percent, 2, '.', ' '), '0'), '.') }}%</p>
        <p><strong>Чаевые:</strong> {{ rtrim(rtrim(number_format($tip_percent, 2, '.', ' '), '0'), '.') }}%</p>
        <p><strong>Дата:</strong> {{ $bill->created_at->format('d.m.Y H:i') }}</p>
    </div>

    <div class="info-section">
        <h2>Позиции</h2>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Название</th>
                    <th class="text-center">Цена</th>
                    <th class="text-center">Кол-во</th>
                    <th>Участники</th>
                    <th class="text-right">Сумма</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $peopleCount = count($people ?? []);
                @endphp
                @foreach($items as $item)
                    @php
                        $itemPrice = (float)($item['price'] ?? 0);
                        $itemQuantity = (int)($item['quantity'] ?? 1);
                        $itemTotalPrice = $itemPrice * $itemQuantity;
                        $itemEaters = $item['eaters'] ?? [];
                        $itemEaters = array_values(array_unique(array_map('intval', $itemEaters)));
                        
                        // Если никого не выбрали — делим на всех
                        if (count($itemEaters) === 0 && $peopleCount > 0) {
                            $itemEaters = range(0, $peopleCount - 1);
                        }
                        
                        // Собираем имена участников
                        $eaterNames = [];
                        foreach ($itemEaters as $eaterIndex) {
                            if (isset($people[$eaterIndex])) {
                                $eaterNames[] = $people[$eaterIndex];
                            }
                        }
                        $eatersList = implode(', ', $eaterNames);
                    @endphp
                    <tr>
                        <td>{{ $item['name'] ?: 'Без названия' }}</td>
                        <td class="text-center price-cell">{{ rtrim(rtrim(number_format($itemPrice, 2, '.', ' '), '0'), '.') }}</td>
                        <td class="text-center">{{ $itemQuantity }}</td>
                        <td>{{ $eatersList }}</td>
                        <td class="text-right price-cell">{{ rtrim(rtrim(number_format($itemTotalPrice, 2, '.', ' '), '0'), '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="info-section">
        <h2>Результат расчета</h2>
        <table class="result-table">
            <thead>
                <tr>
                    <th>Участник</th>
                    <th class="text-right">Еда</th>
                    <th class="text-right">Обслуживание</th>
                    <th class="text-right">Чаевые</th>
                    <th class="text-right">Итого</th>
                </tr>
            </thead>
            <tbody>
                @foreach($result['rows'] as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td class="text-right price-cell">{{ rtrim(rtrim(number_format($row['subtotal'], 2, '.', ' '), '0'), '.') }}</td>
                        <td class="text-right price-cell">{{ rtrim(rtrim(number_format($row['service'], 2, '.', ' '), '0'), '.') }}</td>
                        <td class="text-right price-cell">{{ rtrim(rtrim(number_format($row['tip'], 2, '.', ' '), '0'), '.') }}</td>
                        <td class="text-right price-cell"><strong>{{ rtrim(rtrim(number_format($row['total'], 2, '.', ' '), '0'), '.') }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="total-section">
        <div class="total-row">
            <span>Сумма позиций:</span>
            <span class="price-cell"><strong>{{ rtrim(rtrim(number_format($result['subtotal_total'], 2, '.', ' '), '0'), '.') }}</strong></span>
        </div>
        <div class="total-row">
            <span>Обслуживание:</span>
            <span class="price-cell"><strong>{{ rtrim(rtrim(number_format($result['service_total'], 2, '.', ' '), '0'), '.') }}</strong></span>
        </div>
        <div class="total-row">
            <span>Чаевые:</span>
            <span class="price-cell"><strong>{{ rtrim(rtrim(number_format($result['tip_total'], 2, '.', ' '), '0'), '.') }}</strong></span>
        </div>
        <div class="total-row final">
            <span>Общая сумма:</span>
            <span class="price-cell">{{ rtrim(rtrim(number_format($result['grand_total'], 2, '.', ' '), '0'), '.') }}</span>
        </div>
    </div>
</body>
</html>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Разделение счета</title>
    <style>
        /* Базовые переменные и сброс */
        :root {
            --primary: #0066cc;
            --primary-hover: #0052a3;
            --bg: #f4f6f8;
            --surface: #ffffff;
            --text: #333333;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --danger: #ef4444;
            --danger-bg: #fef2f2;
            --radius: 12px;
        }

        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 16px;
        }

        .container {
            max-width: 768px;
            margin: 0 auto;
        }

        /* Карточки */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        /* Типографика */
        h2 { margin-top: 0; margin-bottom: 8px; font-size: 24px; }
        .muted { color: var(--text-muted); font-size: 14px; margin-bottom: 16px; display: block; }
        .font-bold { font-weight: 600; }

        /* Сетки и выравнивание */
        .flex-between { display: flex; justify-content: space-between; align-items: center; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .gap-2 { display: flex; gap: 8px; flex-direction: column; }

        /* Элементы форм */
        label { font-size: 14px; font-weight: 500; color: var(--text-muted); display: block; margin-bottom: 4px; }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 15px;
            transition: border-color 0.2s;
        }
        input:focus { border-color: var(--primary); outline: none; }

        /* Кнопки */
        button { font-family: inherit; font-size: 14px; font-weight: 500; padding: 10px 16px; border: 0; border-radius: 8px; cursor: pointer; transition: 0.2s; }
        .btn-primary { background: var(--primary); color: #fff; width: 100%; font-size: 16px; padding: 14px; margin-top: 8px;}
        .btn-primary:hover { background: var(--primary-hover); }

        .btn-secondary { background: var(--border); color: var(--text); }
        .btn-secondary:hover { background: #d1d5db; }

        .btn-icon { background: transparent; color: var(--danger); padding: 8px; font-size: 14px;}
        .btn-icon:hover { background: var(--danger-bg); }

        /* Новая кнопка добавления элемента (широкая, с пунктиром) */
        .btn-add {
            width: 100%;
            background: transparent;
            border: 2px dashed var(--border);
            color: var(--text-muted);
            padding: 14px;
            font-size: 15px;
            margin-top: 12px;
        }
        .btn-add:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(0, 102, 204, 0.05);
        }

        /* Блок позиции */
        .item-card { border: 1px solid var(--border); border-radius: 10px; padding: 16px; margin-top: 12px; }
        .item-grid { display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 12px; align-items: end; margin-bottom: 12px; }

        @media (max-width: 540px) {
            .item-grid { grid-template-columns: 1fr 1fr; }
            .item-grid > div:first-child { grid-column: span 2; }
            .item-grid > div:last-child { grid-column: span 2; display: flex; justify-content: flex-end; }
        }

        /* Чипсы (кто ел) */
        .chips { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px; }
        .chip { display: inline-block; cursor: pointer; }
        .chip input { display: none; }
        .chip span {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid var(--border);
            color: var(--text);
            font-size: 14px;
            user-select: none;
            transition: all 0.2s;
        }
        .chip input:checked + span {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        /* Таблица результатов */
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; white-space: nowrap; }
        th, td { border-bottom: 1px solid var(--border); padding: 12px 8px; text-align: left; }
        th { color: var(--text-muted); font-weight: 500; font-size: 14px; }
        .price-cell { font-variant-numeric: tabular-nums; text-align: right; }
        th.price-cell { text-align: right; }

        /* Ошибки */
        .error-box { background: var(--danger-bg); border: 1px solid #fecaca; color: var(--danger); padding: 12px 20px; border-radius: var(--radius); margin-bottom: 16px; }
        .error-box ul { margin: 8px 0 0; padding-left: 20px; }

        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border-width: 0; }
    </style>
</head>
<body>

<div class="container">
    <h2>Разделение счета</h2>
    <span class="muted">Вводите людей и позиции, отмечайте кто ел — получаете итог.</span>

    @if ($errors->any())
        <div class="error-box">
            <b>Найдены ошибки:</b>
            <ul>
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('bill.calculate') }}">
        @csrf

        <div class="card">
            <div class="grid-2">
                <div>
                    <label for="service_percent">Обслуживание, %</label>
                    <input type="number" step="0.1" id="service_percent" name="service_percent" value="{{ old('service_percent', $data['service_percent'] ?? 10) }}">
                </div>
                <div>
                    <label for="tip_percent">Чаевые, %</label>
                    <input type="number" step="0.1" id="tip_percent" name="tip_percent" value="{{ old('tip_percent', $data['tip_percent'] ?? 0) }}">
                </div>
            </div>
        </div>

        <div class="card" id="peopleCard">
            <div class="font-bold" style="font-size: 18px; margin-bottom: 16px;">Участники</div>

            <div id="peopleList" class="gap-2">
                @foreach (old('people', $data['people'] ?? ['']) as $i => $p)
                    <div class="flex-between person-row" data-person-index="{{ $i }}" style="gap: 12px;">
                        <label for="people_{{ $i }}" class="sr-only">Имя участника {{ $i + 1 }}</label>
                        <input type="text" id="people_{{ $i }}" name="people[{{ $i }}]" placeholder="Имя участника (Напр. Алексей)" value="{{ $p }}">
                        <button type="button" class="btn-icon remove-person">Удалить</button>
                    </div>
                @endforeach
            </div>

            <button type="button" class="btn-add" id="addPersonBtn">+ Добавить участника</button>
        </div>

        <div class="card" id="itemsCard">
            <div class="font-bold" style="font-size: 18px; margin-bottom: 16px;">Позиции в чеке</div>

            @php
                $peopleOld = old('people', $data['people'] ?? []);
            @endphp

            <div id="itemsContainer">
                @foreach (old('items', $data['items'] ?? []) as $idx => $item)
                    <div class="item-card item-row" data-item-index="{{ $idx }}" style="margin-top: 0; margin-bottom: 12px;">
                        <div class="item-grid">
                            <div>
                                <label for="item_name_{{ $idx }}">Название</label>
                                <input type="text" id="item_name_{{ $idx }}" name="items[{{ $idx }}][name]" placeholder="Напр. Паста Карбонара" value="{{ $item['name'] ?? '' }}">
                            </div>
                            <div>
                                <label for="item_price_{{ $idx }}">Цена</label>
                                <input type="number" step="0.01" id="item_price_{{ $idx }}" name="items[{{ $idx }}][price]" value="{{ $item['price'] ?? '' }}">
                            </div>
                            <div>
                                <label for="item_quantity_{{ $idx }}">Кол-во</label>
                                <input type="number" step="1" min="1" id="item_quantity_{{ $idx }}" name="items[{{ $idx }}][quantity]" value="{{ $item['quantity'] ?? 1 }}">
                            </div>
                            <div>
                                <button type="button" class="btn-icon remove-item">Удалить</button>
                            </div>
                        </div>

                        <div>
                            <label>Кто ел:</label>
                            <div class="chips eater-chips" data-item-index="{{ $idx }}">
                                @foreach ($peopleOld as $pi => $pn)
                                    <label class="chip">
                                        <input type="checkbox"
                                               name="items[{{ $idx }}][eaters][]"
                                               value="{{ $pi }}"
                                               @if(in_array($pi, $item['eaters'] ?? [])) checked @endif
                                        >
                                        <span class="person-label">{{ $pn !== '' ? $pn : ('#'.($pi+1)) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" class="btn-add" id="addItemBtn">+ Добавить позицию</button>
        </div>

        <button class="btn-primary" type="submit">Рассчитать счет</button>
    </form>

    @if (!empty($result))
        <div class="card" style="margin-top:24px;">
            <div class="flex-between">
                <span class="font-bold" style="font-size: 18px;">Итог расчета</span>
                @if(isset($billId))
                    <a href="{{ route('bill.download-pdf', $billId) }}" class="btn-secondary" style="text-decoration:none; display: inline-block;">📥 Скачать PDF</a>
                @endif
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                    <tr>
                        <th>Участник</th>
                        <th class="price-cell">Еда</th>
                        <th class="price-cell">Обслуж.</th>
                        <th class="price-cell">Чаевые</th>
                        <th class="price-cell">Итого</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($result['rows'] as $r)
                        <tr>
                            <td class="font-bold">{{ $r['name'] }}</td>
                            <td class="price-cell">{{ rtrim(rtrim(number_format($r['subtotal'], 2, '.', ' '), '0'), '.') }}</td>
                            <td class="price-cell">{{ rtrim(rtrim(number_format($r['service'], 2, '.', ' '), '0'), '.') }}</td>
                            <td class="price-cell">{{ rtrim(rtrim(number_format($r['tip'], 2, '.', ' '), '0'), '.') }}</td>
                            <td class="price-cell"><b style="color: var(--primary);">{{ rtrim(rtrim(number_format($r['total'], 2, '.', ' '), '0'), '.') }}</b></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:20px; border-top: 1px solid var(--border); padding-top: 16px; text-align: right;">
                <div class="muted" style="margin-bottom: 4px;">Сумма позиций: <span class="price-cell" style="color:var(--text);">{{ rtrim(rtrim(number_format($result['subtotal_total'], 2, '.', ' '), '0'), '.') }}</span></div>
                <div class="muted" style="margin-bottom: 4px;">Обслуживание: <span class="price-cell" style="color:var(--text);">{{ rtrim(rtrim(number_format($result['service_total'], 2, '.', ' '), '0'), '.') }}</span></div>
                <div class="muted" style="margin-bottom: 8px;">Чаевые: <span class="price-cell" style="color:var(--text);">{{ rtrim(rtrim(number_format($result['tip_total'], 2, '.', ' '), '0'), '.') }}</span></div>
                <div style="font-size: 18px; font-weight: bold;">Общая сумма: <span class="price-cell" style="color: var(--primary);">{{ rtrim(rtrim(number_format($result['grand_total'], 2, '.', ' '), '0'), '.') }}</span></div>
            </div>
        </div>
    @endif
</div>

<script>
    (() => {
        const peopleList = document.getElementById('peopleList');
        const addPersonBtn = document.getElementById('addPersonBtn');
        const itemsContainer = document.getElementById('itemsContainer');
        const addItemBtn = document.getElementById('addItemBtn');

        function getPeopleNames() {
            const inputs = peopleList.querySelectorAll('input[name^="people["]');
            return Array.from(inputs).map(i => i.value.trim());
        }

        function rebuildEaterChipsForAllItems() {
            const names = getPeopleNames();
            const itemRows = itemsContainer.querySelectorAll('.item-row');
            itemRows.forEach(row => {
                const idx = row.getAttribute('data-item-index');
                const chips = row.querySelector('.eater-chips');
                const checked = new Set(
                    Array.from(chips.querySelectorAll('input[type="checkbox"]:checked')).map(c => c.value)
                );

                chips.innerHTML = '';
                names.forEach((n, pi) => {
                    const label = n !== '' ? n : `#${pi+1}`;
                    const wrap = document.createElement('label');
                    wrap.className = 'chip';
                    wrap.innerHTML = `
                    <input type="checkbox" name="items[${idx}][eaters][]" value="${pi}" ${checked.has(String(pi)) ? 'checked' : ''}>
                    <span class="person-label">${label}</span>
                `;
                    chips.appendChild(wrap);
                });
            });
        }

        function addPersonRow() {
            const i = peopleList.querySelectorAll('.person-row').length;
            const div = document.createElement('div');
            div.className = 'flex-between person-row';
            div.style.gap = '12px';
            div.setAttribute('data-person-index', String(i));
            div.innerHTML = `
            <label for="people_${i}" class="sr-only">Имя участника ${i + 1}</label>
            <input type="text" id="people_${i}" name="people[${i}]" placeholder="Имя участника">
            <button type="button" class="btn-icon remove-person">Удалить</button>
        `;
            peopleList.appendChild(div);
            rebuildEaterChipsForAllItems();
        }

        function addItemRow() {
            const idx = itemsContainer.querySelectorAll('.item-row').length;
            const names = getPeopleNames();

            const div = document.createElement('div');
            div.className = 'item-card item-row';
            div.style.marginTop = '0';
            div.style.marginBottom = '12px';
            div.setAttribute('data-item-index', String(idx));

            const chips = names.map((n, pi) => {
                const label = n !== '' ? n : `#${pi+1}`;
                return `
                <label class="chip">
                    <input type="checkbox" name="items[${idx}][eaters][]" value="${pi}">
                    <span class="person-label">${label}</span>
                </label>
            `;
            }).join('');

            div.innerHTML = `
            <div class="item-grid">
                <div>
                    <label for="item_name_${idx}">Название</label>
                    <input type="text" id="item_name_${idx}" name="items[${idx}][name]" placeholder="Напр. Паста">
                </div>
                <div>
                    <label for="item_price_${idx}">Цена</label>
                    <input type="number" step="0.01" id="item_price_${idx}" name="items[${idx}][price]" value="">
                </div>
                <div>
                    <label for="item_quantity_${idx}">Кол-во</label>
                    <input type="number" step="1" min="1" id="item_quantity_${idx}" name="items[${idx}][quantity]" value="1">
                </div>
                <div>
                    <button type="button" class="btn-icon remove-item">Удалить</button>
                </div>
            </div>
            <div>
                <label>Кто ел:</label>
                <div class="chips eater-chips" data-item-index="${idx}">${chips}</div>
            </div>
        `;
            itemsContainer.appendChild(div);
        }

        addPersonBtn?.addEventListener('click', addPersonRow);
        addItemBtn?.addEventListener('click', addItemRow);

        peopleList?.addEventListener('input', (e) => {
            if (e.target && e.target.matches('input[name^="people["]')) {
                rebuildEaterChipsForAllItems();
            }
        });

        document.addEventListener('click', (e) => {
            if (e.target?.classList.contains('remove-person')) {
                e.target.closest('.person-row')?.remove();
                rebuildEaterChipsForAllItems();
            }
            if (e.target?.classList.contains('remove-item')) {
                e.target.closest('.item-row')?.remove();
            }
        });
    })();
</script>

</body>
</html>

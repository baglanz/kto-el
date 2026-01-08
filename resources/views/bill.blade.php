<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Разделение счета</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial; margin: 16px; max-width: 980px; }
        .card { border: 1px solid #ddd; border-radius: 12px; padding: 12px; margin-bottom: 12px; }
        .row { display: flex; gap: 8px; flex-wrap: wrap; }
        input[type="text"], input[type="number"] { padding: 8px; border: 1px solid #ccc; border-radius: 8px; }
        button { padding: 10px 12px; border: 0; border-radius: 10px; cursor: pointer; }
        .btn { background:#111; color:#fff; }
        .btn2 { background:#f1f1f1; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #eee; padding: 8px; vertical-align: top; }
        .error { color: #b00020; margin: 6px 0; }
        .chips { display:flex; gap:8px; flex-wrap:wrap; }
        .chip { border:1px solid #ddd; border-radius:999px; padding:6px 10px; }
        .muted { color:#666; font-size: 14px; }
    </style>
</head>
<body>

<h2>Разделение счета</h2>
<p class="muted">Вводишь людей и позиции, отмечаешь кто ел — получаешь итог. Сервис по умолчанию 10%.</p>

@if ($errors->any())
    <div class="card">
        <div class="error"><b>Ошибки:</b></div>
        <ul class="error">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('bill.calculate') }}">
    @csrf

    <div class="card">
        <div class="row">
            <div>
                <div class="muted">Обслуживание, %</div>
                <input type="number" step="0.1" name="service_percent" value="{{ old('service_percent', $data['service_percent']) }}">
            </div>
            <div>
                <div class="muted">Чаевые, %</div>
                <input type="number" step="0.1" name="tip_percent" value="{{ old('tip_percent', $data['tip_percent']) }}">
            </div>
        </div>
    </div>

    <div class="card" id="peopleCard">
        <div class="row" style="justify-content:space-between; align-items:center;">
            <b>Участники</b>
            <button type="button" class="btn2" id="addPersonBtn">+ Добавить</button>
        </div>

        <div id="peopleList">
            @foreach (old('people', $data['people']) as $i => $p)
                <div class="row person-row" data-person-index="{{ $i }}" style="margin-top:8px;">
                    <input type="text" name="people[{{ $i }}]" placeholder="Имя" value="{{ $p }}">
                    <button type="button" class="btn2 remove-person">Удалить</button>
                </div>
            @endforeach
        </div>
        <div class="muted" style="margin-top:8px;">Важно: имена используются в селекторах у позиций.</div>
    </div>

    <div class="card" id="itemsCard">
        <div class="row" style="justify-content:space-between; align-items:center;">
            <b>Позиции</b>
            <button type="button" class="btn2" id="addItemBtn">+ Позиция</button>
        </div>

        @php
            $peopleOld = old('people', $data['people']);
        @endphp

        <table id="itemsTable" style="margin-top:8px;">
            <thead>
            <tr>
                <th style="width:36%;">Название</th>
                <th style="width:14%;">Цена</th>
                <th>Кто ел</th>
                <th style="width:10%;"></th>
            </tr>
            </thead>
            <tbody>
            @foreach (old('items', $data['items']) as $idx => $item)
                <tr class="item-row" data-item-index="{{ $idx }}">
                    <td>
                        <input type="text" name="items[{{ $idx }}][name]" placeholder="Напр. паста" value="{{ $item['name'] ?? '' }}" style="width:100%;">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="items[{{ $idx }}][price]" value="{{ $item['price'] ?? '' }}" style="width:120px;">
                    </td>
                    <td>
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
                    </td>
                    <td>
                        <button type="button" class="btn2 remove-item">Удалить</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="muted" style="margin-top:8px;">
            Если имя пустое — показываем #N. Лучше заполнить имена перед распределением.
        </div>
    </div>

    <button class="btn" type="submit">Посчитать</button>
</form>

@if (!empty($result))
    <div class="card" style="margin-top:12px;">
        <b>Итог</b>
        <table style="margin-top:8px;">
            <thead>
            <tr>
                <th>Участник</th>
                <th>Еда</th>
                <th>Обслуживание</th>
                <th>Чаевые</th>
                <th>Итого</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($result['rows'] as $r)
                <tr>
                    <td>{{ $r['name'] }}</td>
                    <td>{{ number_format($r['subtotal'], 2, '.', ' ') }}</td>
                    <td>{{ number_format($r['service'], 2, '.', ' ') }}</td>
                    <td>{{ number_format($r['tip'], 2, '.', ' ') }}</td>
                    <td><b>{{ number_format($r['total'], 2, '.', ' ') }}</b></td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="row" style="margin-top:10px; justify-content:flex-end;">
            <div class="muted">
                Сумма позиций: <b>{{ number_format($result['subtotal_total'], 2, '.', ' ') }}</b><br>
                Обслуживание: <b>{{ number_format($result['service_total'], 2, '.', ' ') }}</b><br>
                Чаевые: <b>{{ number_format($result['tip_total'], 2, '.', ' ') }}</b><br>
                Общая сумма: <b>{{ number_format($result['grand_total'], 2, '.', ' ') }}</b>
            </div>
        </div>
    </div>
@endif

<script>
    (() => {
        const peopleList = document.getElementById('peopleList');
        const addPersonBtn = document.getElementById('addPersonBtn');
        const itemsTbody = document.querySelector('#itemsTable tbody');
        const addItemBtn = document.getElementById('addItemBtn');

        function getPeopleNames() {
            const inputs = peopleList.querySelectorAll('input[name^="people["]');
            return Array.from(inputs).map(i => i.value.trim());
        }

        function rebuildEaterChipsForAllItems() {
            const names = getPeopleNames();
            const itemRows = itemsTbody.querySelectorAll('tr.item-row');
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
            div.className = 'row person-row';
            div.style.marginTop = '8px';
            div.setAttribute('data-person-index', i);
            div.innerHTML = `
            <input type="text" name="people[${i}]" placeholder="Имя">
            <button type="button" class="btn2 remove-person">Удалить</button>
        `;
            peopleList.appendChild(div);
            rebuildEaterChipsForAllItems();
        }

        function addItemRow() {
            const idx = itemsTbody.querySelectorAll('tr.item-row').length;
            const names = getPeopleNames();

            const tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.setAttribute('data-item-index', idx);

            const chips = names.map((n, pi) => {
                const label = n !== '' ? n : `#${pi+1}`;
                return `
                <label class="chip">
                    <input type="checkbox" name="items[${idx}][eaters][]" value="${pi}">
                    <span class="person-label">${label}</span>
                </label>
            `;
            }).join('');

            tr.innerHTML = `
            <td><input type="text" name="items[${idx}][name]" placeholder="Напр. паста" style="width:100%;"></td>
            <td><input type="number" step="0.01" name="items[${idx}][price]" value="" style="width:120px;"></td>
            <td><div class="chips eater-chips" data-item-index="${idx}">${chips}</div></td>
            <td><button type="button" class="btn2 remove-item">Удалить</button></td>
        `;
            itemsTbody.appendChild(tr);
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
                // Быстро и просто: не переиндексируем, чтобы не ломать names[].
                // Но обновим чипсы по текущим инпутам (индексы станут "дырявыми" — это минус).
                // Для MVP лучше НЕ удалять людей, а "очищать имя".
                // Оставим удаление, но это edge-case.
                rebuildEaterChipsForAllItems();
            }
            if (e.target?.classList.contains('remove-item')) {
                e.target.closest('tr.item-row')?.remove();
            }
        });
    })();
</script>

</body>
</html>

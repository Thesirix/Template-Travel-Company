@php($name = fn ($field) => "passagers[$index][$field]")
<tr>
    <td>
        <select name="{{ $name('type') }}" class="form-control form-control-sm" required>
            @foreach(\App\Models\BlockPowerPassenger::TYPES as $value => $label)
                <option value="{{ $value }}" @selected(($passenger['type'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </td>
    <td><input type="text" name="{{ $name('last_name') }}" class="form-control form-control-sm" required maxlength="150" value="{{ $passenger['last_name'] ?? '' }}"></td>
    <td><input type="text" name="{{ $name('first_name') }}" class="form-control form-control-sm" required maxlength="150" value="{{ $passenger['first_name'] ?? '' }}"></td>
    <td><input type="date" name="{{ $name('birth_date') }}" class="form-control form-control-sm" max="{{ date('Y-m-d') }}" value="{{ $passenger['birth_date'] ?? '' }}"></td>
    <td><input type="number" name="{{ $name('sale_price') }}" class="form-control form-control-sm" min="0" step="0.01" value="{{ $passenger['sale_price'] ?? '' }}"></td>
    <td><input type="number" name="{{ $name('deposit') }}" class="form-control form-control-sm" min="0" step="0.01" value="{{ $passenger['deposit'] ?? '' }}"></td>
    <td><input type="text" name="{{ $name('deposit_number') }}" class="form-control form-control-sm" maxlength="50" value="{{ $passenger['deposit_number'] ?? '' }}"></td>
    <td><button type="button" class="btn btn-sm btn-outline-secondary remove-row" title="Retirer"><i class="fa-solid fa-xmark"></i></button></td>
</tr>

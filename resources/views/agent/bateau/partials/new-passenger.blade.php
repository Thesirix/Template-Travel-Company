{{-- Ligne « nouveau passager » du formulaire bateau ; sert aussi de modèle JavaScript (__INDEX__, __TYPE__) --}}
@php
    $name = fn ($field) => "nouveaux[$index][$field]";
    $type = $passenger['type'] ?? 'adulte';
@endphp
<tr>
    <td><input type="radio" name="responsable" value="new:{{ $index }}" @checked(old('responsable') === 'new:' . $index)></td>
    <td>
        <input type="hidden" name="{{ $name('type') }}" value="{{ $type }}">
        <span class="passenger-type">{{ \App\Models\BoatBooking::PASSENGER_TYPES[$type][0] ?? '' }}</span>
    </td>
    <td><input type="text" name="{{ $name('nom') }}" class="form-control form-control-sm" required value="{{ $passenger['nom'] ?? '' }}"></td>
    <td><input type="text" name="{{ $name('prenom') }}" class="form-control form-control-sm" required value="{{ $passenger['prenom'] ?? '' }}"></td>
    <td><input type="date" name="{{ $name('date_naissance') }}" class="form-control form-control-sm" required max="{{ date('Y-m-d') }}" value="{{ $passenger['date_naissance'] ?? '' }}"></td>
    <td class="text-gray-500">calculé</td>
    <td><input type="text" name="{{ $name('tele') }}" class="form-control form-control-sm" maxlength="15" value="{{ $passenger['tele'] ?? '' }}"></td>
    <td><input type="email" name="{{ $name('mail') }}" class="form-control form-control-sm" value="{{ $passenger['mail'] ?? '' }}"></td>
    <td><input type="text" name="{{ $name('passport_num') }}" class="form-control form-control-sm" required maxlength="15" value="{{ $passenger['passport_num'] ?? '' }}"></td>
    <td><input type="file" name="{{ $name('passport') }}" class="form-control-file" style="width: 190px" accept=".jpg,.jpeg,.png,.webp,.pdf"></td>
    <td><button type="button" class="btn btn-sm btn-outline-secondary remove-new-passenger" title="Retirer"><i class="fa-solid fa-xmark"></i></button></td>
</tr>

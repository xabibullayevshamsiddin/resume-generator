@php($rowIndex = $rowIndex ?? 0)
<tr class="relative-row" data-row="{{ $rowIndex }}">
    <td class="cell-relationship" data-label="Qarindoshligi">
        <label class="field__label--sr-only" for="relatives-{{ $rowIndex }}-relationship">Qarindoshligi</label>
        <select
            id="relatives-{{ $rowIndex }}-relationship"
            name="relatives[{{ $rowIndex }}][relationship]"
            class="input select js-relative-relationship"
        >
            @foreach(['Otasi', 'Onasi', 'Akasi', 'Ukasi', 'Opasi', 'Singlisi', 'Turmush o\'rtog\'i', 'O\'g\'li', 'Qizi', 'Boshqa'] as $option)
                <option value="{{ $option }}" @selected(old('relatives.'.$rowIndex.'.relationship') === $option)>{{ $option }}</option>
            @endforeach
        </select>
        <input
            type="text"
            id="relatives-{{ $rowIndex }}-relationship-other"
            name="relatives[{{ $rowIndex }}][relationship_other]"
            value="{{ old('relatives.'.$rowIndex.'.relationship_other') }}"
            placeholder="Qarindoshlik darajasi"
            class="input input--other js-relationship-other {{ old('relatives.'.$rowIndex.'.relationship') === 'Boshqa' ? '' : 'is-hidden' }}"
            aria-label="Qarindoshlik darajasi (boshqa)"
        >
    </td>
    <td class="cell-fullname" data-label="F.I.Sh.">
        <label class="field__label--sr-only" for="relatives-{{ $rowIndex }}-full-name">F.I.Sh.</label>
        <input
            type="text"
            id="relatives-{{ $rowIndex }}-full-name"
            name="relatives[{{ $rowIndex }}][full_name]"
            value="{{ old('relatives.'.$rowIndex.'.full_name') }}"
            placeholder="F.I.Sh."
            class="input"
        >
    </td>
    <td class="cell-birth-year" data-label="Tug'ilgan yili">
        <label class="field__label--sr-only" for="relatives-{{ $rowIndex }}-birth-year">Tug'ilgan yili</label>
        <input
            type="text"
            id="relatives-{{ $rowIndex }}-birth-year"
            name="relatives[{{ $rowIndex }}][birth_year]"
            value="{{ old('relatives.'.$rowIndex.'.birth_year') }}"
            placeholder="1975"
            class="input"
        >
    </td>
    <td class="cell-birth-place" data-label="Tug'ilgan joyi">
        <label class="field__label--sr-only" for="relatives-{{ $rowIndex }}-birth-place">Tug'ilgan joyi</label>
        <input
            type="text"
            id="relatives-{{ $rowIndex }}-birth-place"
            name="relatives[{{ $rowIndex }}][birth_place]"
            value="{{ old('relatives.'.$rowIndex.'.birth_place') }}"
            placeholder="Tug'ilgan joyi"
            class="input"
        >
    </td>
    <td class="cell-workplace" data-label="Ish joyi">
        <label class="field__label--sr-only" for="relatives-{{ $rowIndex }}-workplace">Ish joyi</label>
        <input
            type="text"
            id="relatives-{{ $rowIndex }}-workplace"
            name="relatives[{{ $rowIndex }}][workplace]"
            value="{{ old('relatives.'.$rowIndex.'.workplace') }}"
            placeholder="Ish joyi"
            class="input"
        >
    </td>
    <td class="cell-position" data-label="Lavozimi">
        <label class="field__label--sr-only" for="relatives-{{ $rowIndex }}-position">Lavozimi</label>
        <input
            type="text"
            id="relatives-{{ $rowIndex }}-position"
            name="relatives[{{ $rowIndex }}][position]"
            value="{{ old('relatives.'.$rowIndex.'.position') }}"
            placeholder="Lavozimi"
            class="input"
        >
    </td>
    <td class="cell-address" data-label="Turar joyi">
        <label class="field__label--sr-only" for="relatives-{{ $rowIndex }}-address">Turar joyi</label>
        <input
            type="text"
            id="relatives-{{ $rowIndex }}-address"
            name="relatives[{{ $rowIndex }}][address]"
            value="{{ old('relatives.'.$rowIndex.'.address') }}"
            placeholder="Turar joy manzili"
            class="input"
        >
    </td>
    <td class="cell-phone" data-label="Telefon">
        <label class="field__label--sr-only" for="relatives-{{ $rowIndex }}-phone">Telefon raqami</label>
        <input
            type="text"
            id="relatives-{{ $rowIndex }}-phone"
            name="relatives[{{ $rowIndex }}][phone]"
            value="{{ old('relatives.'.$rowIndex.'.phone') }}"
            placeholder="+998 90 123 45 67"
            class="input"
        >
    </td>
    <td class="cell-actions">
        <button type="button" class="btn btn--danger btn--remove-row" title="Qatorni o'chirish" aria-label="Qarindosh qatorini o'chirish">✕</button>
    </td>
</tr>

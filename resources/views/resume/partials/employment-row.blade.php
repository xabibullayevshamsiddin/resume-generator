@php($rowIndex = $rowIndex ?? 0)
<tr class="employment-row" data-row="{{ $rowIndex }}">
    <td class="cell-period">
        <label class="field__label--sr-only" for="employment-{{ $rowIndex }}-period">Davri</label>
        <input
            type="text"
            id="employment-{{ $rowIndex }}-period"
            name="employment[{{ $rowIndex }}][period]"
            value="{{ old('employment.'.$rowIndex.'.period') }}"
            placeholder="2020-yil mart — 2024-yil avgust"
            class="input"
        >
    </td>
    <td class="cell-organization">
        <label class="field__label--sr-only" for="employment-{{ $rowIndex }}-organization">Tashkilot nomi</label>
        <input
            type="text"
            id="employment-{{ $rowIndex }}-organization"
            name="employment[{{ $rowIndex }}][organization]"
            value="{{ old('employment.'.$rowIndex.'.organization') }}"
            placeholder="Tashkilot nomi"
            class="input"
        >
    </td>
    <td class="cell-position">
        <label class="field__label--sr-only" for="employment-{{ $rowIndex }}-position">Lavozim</label>
        <input
            type="text"
            id="employment-{{ $rowIndex }}-position"
            name="employment[{{ $rowIndex }}][position]"
            value="{{ old('employment.'.$rowIndex.'.position') }}"
            placeholder="Lavozim / yo'nalish"
            class="input"
        >
    </td>
    <td class="cell-actions">
        <button type="button" class="btn btn--danger btn--remove-row" title="Qatorni o'chirish" aria-label="Mehnat faoliyati qatorini o'chirish">✕</button>
    </td>
</tr>

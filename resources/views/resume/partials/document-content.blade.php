<div class="doc {{ $docClass ?? 'doc--pdf' }}">
    <div class="doc__page doc__page--1">
        {{-- ===== 1-sahifa ===== --}}
        <table class="doc__header-table">
            <tr>
                <td class="doc__header-name">
                    <div class="doc__fullname">{{ $fullName }}</div>
                    <div class="doc__status">
                        {{ $currentStatus }}@if($currentStatus !== '—' && $currentOrganization !== '—'){{ ', ' }}@endif{{ $currentOrganization }}@if($currentPosition !== '—'){{ ', ' }}@endif{{ $currentPosition }}
                    </div>
                </td>
                <td class="doc__header-photo" rowspan="2">
                    @if($photoDataUri)
                        <img class="doc__photo" src="{{ $photoDataUri }}" alt="Profil rasmi">
                    @endif
                </td>
            </tr>
        </table>

        <div class="doc__section-title">Shaxsiy ma'lumotlar</div>
        <table class="doc__personal-table">
            <tr>
                <td class="doc__label">Tug'ilgan sanasi:</td>
                <td class="doc__value">{{ $birthDate }}</td>
                <td class="doc__label">Millati:</td>
                <td class="doc__value">{{ $nationality }}</td>
            </tr>
            <tr>
                <td class="doc__label">Tug'ilgan joyi:</td>
                <td class="doc__value">{{ $birthPlace }}</td>
                <td class="doc__label">Partiyaviyligi:</td>
                <td class="doc__value">{{ $partyAffiliation }}</td>
            </tr>
            <tr>
                <td class="doc__label">Ma'lumoti:</td>
                <td class="doc__value">{{ $education }}</td>
                <td class="doc__label">Harbiy unvoni:</td>
                <td class="doc__value">{{ $militaryRank }}</td>
            </tr>
            <tr>
                <td class="doc__label">Tamomlagan:</td>
                <td class="doc__value">{{ $institution }}@if($institutionYear !== '—'){{ ', ' }}@endif{{ $institutionYear }}</td>
                <td class="doc__label">Ilmiy darajasi:</td>
                <td class="doc__value">{{ $academicDegree }}</td>
            </tr>
            <tr>
                <td class="doc__label">Mutaxassisligi:</td>
                <td class="doc__value">{{ $specialty }}</td>
                <td class="doc__label">Ilmiy unvoni:</td>
                <td class="doc__value">{{ $academicTitle }}</td>
            </tr>
            <tr>
                <td class="doc__label">Chet tillari:</td>
                <td class="doc__value">{{ $languages }}</td>
                <td class="doc__label">Mukofotlari:</td>
                <td class="doc__value">{{ $awards }}</td>
            </tr>
            <tr>
                <td class="doc__label">Deputatlik:</td>
                <td class="doc__value" colspan="3">{{ $electedBodies }}</td>
            </tr>
        </table>

        <div class="doc__section-title">MEHNAT FAOLIYATI</div>
        <table class="doc__employment-table">
            <thead>
                <tr class="doc__th-row">
                    <th class="doc__th" style="width: 30%;">Davri</th>
                    <th class="doc__th">Tashkilot, lavozim</th>
                </tr>
            </thead>
            <tbody>
            @forelse($employmentRows as $row)
                <tr>
                    <td class="doc__td doc__nowrap">{{ $row['period'] }}</td>
                    <td class="doc__td">
                        {{ $row['organization'] }}@if($row['position'] !== ''){{ ', ' }}{{ $row['position'] }}@endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="doc__td" colspan="2">—</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===== 2-sahifa ===== --}}
    <div class="doc__page doc__page--2">
        <div class="doc__page2-title">{{ $fullName }}ning yaqin qarindoshlari haqida MA'LUMOT</div>

        <table class="doc__relatives-table">
            <thead>
                <tr class="doc__th-row">
                    <th class="doc__th" style="width: 14%;">Qarindoshligi</th>
                    <th class="doc__th" style="width: 22%;">F.I.Sh.</th>
                    <th class="doc__th" style="width: 20%;">Tug'ilgan yili, joyi</th>
                    <th class="doc__th" style="width: 22%;">Ish joyi, lavozimi</th>
                    <th class="doc__th" style="width: 22%;">Turar joyi, telefon</th>
                </tr>
            </thead>
            <tbody>
            @forelse($relativeRows as $row)
                <tr>
                    <td class="doc__td">{{ $row['relationship'] }}</td>
                    <td class="doc__td">{{ $row['fullName'] }}</td>
                    <td class="doc__td">
                        {{ $row['birth']['main'] }}
                        <span class="doc__sub">{{ $row['birth']['sub'] }}</span>
                    </td>
                    <td class="doc__td">
                        {{ $row['work']['main'] }}
                        <span class="doc__sub">{{ $row['work']['sub'] }}</span>
                    </td>
                    <td class="doc__td">
                        {{ $row['address']['main'] }}
                        <span class="doc__sub">{{ $row['address']['sub'] }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="doc__td" colspan="5">—</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <table class="doc__final-table">
            <tr>
                <td class="doc__label">Mobil raqami:</td>
                <td class="doc__value">{{ $phone }}</td>
            </tr>
            <tr>
                <td class="doc__label">Uy manzili:</td>
                <td class="doc__value">{{ $homeAddress }}</td>
            </tr>
            <tr>
                <td class="doc__label">Pasport ma'lumotlari:</td>
                <td class="doc__value">{{ $passportInfo }}</td>
            </tr>
        </table>
    </div>
</div>

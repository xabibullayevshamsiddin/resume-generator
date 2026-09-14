<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    protected $fillable = [
        'full_name',
        'current_status',
        'current_organization',
        'current_position',
        'birth_date',
        'birth_place',
        'nationality',
        'party_affiliation',
        'party_affiliation_other',
        'military_rank',
        'education',
        'institution',
        'institution_year',
        'specialty',
        'academic_degree',
        'academic_degree_other',
        'academic_title',
        'academic_title_other',
        'languages',
        'awards',
        'elected_bodies',
        'employment',
        'relatives',
        'phone',
        'home_address',
        'passport_info',
        'photo_path',
    ];

    protected $casts = [
        'birth_date' => 'date:Y-m-d',
        // Maxfiy maydonlar bazada shifrlangan holda saqlanadi:
        // mehnat faoliyati, qarindoshlar (telefon/manzillar), aloqa ma'lumotlari
        'employment' => 'encrypted:array',
        'relatives' => 'encrypted:array',
        'home_address' => 'encrypted',
        'passport_info' => 'encrypted',
        'phone' => 'encrypted',
    ];

    /**
     * Model ma'lumotlarini PDF service kutgan formatga (validated massiv
     * strukturasiga) o'tkazadi.
     */
    public function toFormData(): array
    {
        return [
            'full_name' => $this->full_name,
            'current_status' => $this->current_status,
            'current_organization' => $this->current_organization,
            'current_position' => $this->current_position,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'birth_place' => $this->birth_place,
            'nationality' => $this->nationality,
            'party_affiliation' => $this->party_affiliation,
            'party_affiliation_other' => $this->party_affiliation_other,
            'military_rank' => $this->military_rank,
            'education' => $this->education,
            'institution' => $this->institution,
            'institution_year' => $this->institution_year,
            'specialty' => $this->specialty,
            'academic_degree' => $this->academic_degree,
            'academic_degree_other' => $this->academic_degree_other,
            'academic_title' => $this->academic_title,
            'academic_title_other' => $this->academic_title_other,
            'languages' => $this->languages,
            'awards' => $this->awards,
            'elected_bodies' => $this->elected_bodies,
            'employment' => $this->employment ?? [],
            'relatives' => $this->relatives ?? [],
            'phone' => $this->phone,
            'home_address' => $this->home_address,
            'passport_info' => $this->passport_info,
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateResumeRequest extends FormRequest
{
    /**
     * Foydalanuvchi hujjat yuklab olishga ruxsat beriladi.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * "Boshqa" tanlangan select maydonlar uchun shartli qoidalar.
     */
    public function rules(): array
    {
        return [
            // Asosiy ma'lumotlar
            'full_name' => ['required', 'string', 'max:255'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'current_status' => ['nullable', 'string', 'max:255'],
            'current_organization' => ['nullable', 'string', 'max:255'],
            'current_position' => ['nullable', 'string', 'max:255'],

            // Shaxsiy ma'lumotlar
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'birth_place' => ['required', 'string', 'max:255'],
            'nationality' => ['required', 'string', 'max:100'],
            'party_affiliation' => ['required', 'in:yo\'q,boshqa'],
            'party_affiliation_other' => ['required_if:party_affiliation,boshqa', 'nullable', 'string', 'max:255'],
            'military_rank' => ['nullable', 'string', 'max:100'],

            // Ta'lim
            'education' => ['required', 'string', 'max:255'],
            'institution' => ['required', 'string', 'max:255'],
            'institution_year' => ['nullable', 'string', 'max:30'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'academic_degree' => ['required', 'in:yo\'q,bakalavr,magistr,PhD,DSc,boshqa'],
            'academic_degree_other' => ['required_if:academic_degree,boshqa', 'nullable', 'string', 'max:255'],
            'academic_title' => ['required', 'in:yo\'q,dotsent,professor,boshqa'],
            'academic_title_other' => ['required_if:academic_title,boshqa', 'nullable', 'string', 'max:255'],

            // Qo'shimcha
            'languages' => ['nullable', 'string', 'max:500'],
            'awards' => ['nullable', 'string', 'max:1000'],
            'elected_bodies' => ['nullable', 'string', 'max:1500'],

            // Mehnat faoliyati
            'employment' => ['required', 'array', 'min:1', 'max:20'],
            'employment.*.period' => ['required', 'string', 'max:255'],
            'employment.*.organization' => ['required', 'string', 'max:500'],
            'employment.*.position' => ['nullable', 'string', 'max:500'],

            // Yaqin qarindoshlar
            'relatives' => ['required', 'array', 'min:1', 'max:10'],
            'relatives.*.relationship' => ['required', 'string', 'max:100'],
            'relatives.*.relationship_other' => ['required_if:relatives.*.relationship,Boshqa', 'nullable', 'string', 'max:255'],
            'relatives.*.full_name' => ['required', 'string', 'max:255'],
            'relatives.*.birth_year' => ['nullable', 'string', 'max:30'],
            'relatives.*.birth_place' => ['nullable', 'string', 'max:255'],
            'relatives.*.workplace' => ['nullable', 'string', 'max:500'],
            'relatives.*.position' => ['nullable', 'string', 'max:500'],
            'relatives.*.address' => ['nullable', 'string', 'max:1000'],
            'relatives.*.phone' => ['nullable', 'string', 'max:50'],

            // Yakuniy ma'lumotlar
            'phone' => ['nullable', 'string', 'max:50'],
            'home_address' => ['required', 'string', 'max:1000'],
            'passport_info' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Barcha xato xabarlari o'zbek tilida.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'F.I.Sh. kiritilishi shart.',
            'full_name.max' => 'F.I.Sh. 255 belgidan oshmasligi kerak.',

            'photo.required' => 'Profil rasmi yuklanishi shart.',
            'photo.image' => 'Profil rasmi rasm fayl bo\'lishi kerak (jpg, jpeg, png, webp).',
            'photo.mimes' => 'Profil rasmi faqat jpg, jpeg, png yoki webp formatida bo\'lishi kerak.',
            'photo.max' => 'Profil rasmi 3 MB dan oshmasligi kerak.',

            'current_status.max' => 'Hozirgi holat 255 belgidan oshmasligi kerak.',
            'current_organization.max' => 'Tashkilot nomi 255 belgidan oshmasligi kerak.',
            'current_position.max' => 'Fakultet, yo\'nalish yoki lavozim 255 belgidan oshmasligi kerak.',

            'birth_date.date' => 'Tug\'ilgan sana to\'g\'ri sana formatida bo\'lishi kerak.',
            'birth_date.before_or_equal' => 'Tug\'ilgan sana bugungi kundan keyin bo\'lmasligi kerak.',

            'birth_place.required' => 'Tug\'ilgan joy kiritilishi shart.',
            'birth_place.max' => 'Tug\'ilgan joy 255 belgidan oshmasligi kerak.',
            'nationality.required' => 'Millati kiritilishi shart.',
            'nationality.max' => 'Millati 100 belgidan oshmasligi kerak.',

            'party_affiliation.required' => 'Partiyaviyligi tanlanishi shart.',
            'party_affiliation.in' => 'Partiyaviyligi uchun noto\'g\'ri qiymat tanlangan.',
            'party_affiliation_other.required_if' => 'Partiyaviyligi "boshqa" tanlanganda izoh kiritilishi shart.',
            'party_affiliation_other.max' => 'Partiyaviylik izohi 255 belgidan oshmasligi kerak.',
            'military_rank.max' => 'Harbiy unvon 100 belgidan oshmasligi kerak.',

            'education.required' => 'Ma\'lumoti tanlanishi shart.',
            'education.max' => 'Ma\'lumoti 255 belgidan oshmasligi kerak.',
            'institution.required' => 'Tamomlagan ta\'lim muassasasi kiritilishi shart.',
            'institution.max' => 'Ta\'lim muassasasi nomi 255 belgidan oshmasligi kerak.',
            'institution_year.max' => 'Tamomlagan yili 30 belgidan oshmasligi kerak.',
            'specialty.max' => 'Mutaxassislik 255 belgidan oshmasligi kerak.',

            'academic_degree.required' => 'Ilmiy darajasi tanlanishi shart.',
            'academic_degree.in' => 'Ilmiy darajasi uchun noto\'g\'ri qiymat tanlangan.',
            'academic_degree_other.required_if' => 'Ilmiy darajasi "boshqa" tanlanganda izoh kiritilishi shart.',
            'academic_degree_other.max' => 'Ilmiy daraja izohi 255 belgidan oshmasligi kerak.',
            'academic_title.required' => 'Ilmiy unvoni tanlanishi shart.',
            'academic_title.in' => 'Ilmiy unvoni uchun noto\'g\'ri qiymat tanlangan.',
            'academic_title_other.required_if' => 'Ilmiy unvon "boshqa" tanlanganda izoh kiritilishi shart.',
            'academic_title_other.max' => 'Ilmiy unvon izohi 255 belgidan oshmasligi kerak.',

            'languages.max' => 'Chet tillari 500 belgidan oshmasligi kerak.',
            'awards.max' => 'Davlat mukofotlari 1000 belgidan oshmasligi kerak.',
            'elected_bodies.max' => 'Deputatlik ma\'lumoti 1500 belgidan oshmasligi kerak.',

            'employment.required' => 'Mehnat faoliyati kamida bitta qatordan iborat bo\'lishi kerak.',
            'employment.array' => 'Mehnat faoliyati ma\'lumotlari noto\'g\'ri formatda.',
            'employment.min' => 'Mehnat faoliyati kamida bitta qatordan iborat bo\'lishi kerak.',
            'employment.max' => 'Mehnat faoliyati ko\'pi bilan 20 qatordan iborat bo\'lishi mumkin.',
            'employment.*.period.required' => 'Mehnat faoliyati qatorida davri kiritilishi shart.',
            'employment.*.period.max' => 'Mehnat faoliyati davri 255 belgidan oshmasligi kerak.',
            'employment.*.organization.required' => 'Mehnat faoliyati qatorida tashkilot nomi kiritilishi shart.',
            'employment.*.organization.max' => 'Tashkilot nomi 500 belgidan oshmasligi kerak.',
            'employment.*.position.max' => 'Lavozim 500 belgidan oshmasligi kerak.',

            'relatives.required' => 'Yaqin qarindoshlar kamida bitta qatordan iborat bo\'lishi kerak.',
            'relatives.array' => 'Qarindoshlar ma\'lumotlari noto\'g\'ri formatda.',
            'relatives.min' => 'Yaqin qarindoshlar kamida bitta qatordan iborat bo\'lishi kerak.',
            'relatives.max' => 'Yaqin qarindoshlar ko\'pi bilan 10 tadan iborat bo\'lishi mumkin.',
            'relatives.*.relationship.required' => 'Qarindoshlik darajasi tanlanishi shart.',
            'relatives.*.relationship.max' => 'Qarindoshlik darajasi 100 belgidan oshmasligi kerak.',
            'relatives.*.relationship_other.required_if' => 'Qarindoshlik "Boshqa" tanlanganda izoh kiritilishi shart.',
            'relatives.*.relationship_other.max' => 'Qarindoshlik izohi 255 belgidan oshmasligi kerak.',
            'relatives.*.full_name.required' => 'Qarindoshning F.I.Sh. kiritilishi shart.',
            'relatives.*.full_name.max' => 'Qarindoshning F.I.Sh. 255 belgidan oshmasligi kerak.',
            'relatives.*.birth_year.max' => 'Tug\'ilgan yili 30 belgidan oshmasligi kerak.',
            'relatives.*.birth_place.max' => 'Qarindoshning tug\'ilgan joyi 255 belgidan oshmasligi kerak.',
            'relatives.*.workplace.max' => 'Ish joyi 500 belgidan oshmasligi kerak.',
            'relatives.*.position.max' => 'Qarindoshning lavozimi 500 belgidan oshmasligi kerak.',
            'relatives.*.address.max' => 'Turar joy manzili 1000 belgidan oshmasligi kerak.',
            'relatives.*.phone.max' => 'Qarindoshning telefon raqami 50 belgidan oshmasligi kerak.',

            'phone.max' => 'Telefon raqam 50 belgidan oshmasligi kerak.',
            'home_address.required' => 'Uy manzili kiritilishi shart.',
            'home_address.max' => 'Uy manzili 1000 belgidan oshmasligi kerak.',
            'passport_info.max' => 'Pasport ma\'lumotlari 255 belgidan oshmasligi kerak.',
        ];
    }

    /**
     * Maydon nomlarini o'zbekcha o'qiladigan qilib ko'rsatish.
     */
    public function attributes(): array
    {
        return [
            'full_name' => 'F.I.Sh.',
            'photo' => 'Profil rasmi',
            'birth_date' => 'Tug\'ilgan sana',
            'birth_place' => 'Tug\'ilgan joy',
            'nationality' => 'Millati',
            'education' => 'Ma\'lumoti',
            'institution' => 'Ta\'lim muassasasi',
            'home_address' => 'Uy manzili',
            'phone' => 'Mobil raqam',
        ];
    }
}

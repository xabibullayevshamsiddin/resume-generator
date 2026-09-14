<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');

            // Shaxsiy ma'lumotlar
            $table->text('current_status')->nullable();
            $table->text('current_organization')->nullable();
            $table->text('current_position')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_place');
            $table->string('nationality', 100);
            $table->string('party_affiliation', 100);
            $table->string('party_affiliation_other')->nullable();
            $table->string('military_rank', 100)->nullable();

            // Ta'lim
            $table->string('education', 100);
            $table->string('institution');
            $table->string('institution_year', 30)->nullable();
            $table->string('specialty')->nullable();
            $table->string('academic_degree', 100);
            $table->string('academic_degree_other')->nullable();
            $table->string('academic_title', 100);
            $table->string('academic_title_other')->nullable();

            // Qo'shimcha
            $table->text('languages')->nullable();
            $table->text('awards')->nullable();
            $table->text('elected_bodies')->nullable();

            // JSON massivlar (mehnat faoliyati, qarindoshlar)
            // Shifrlangan holda saqlanadi (encrypted:array cast) — ustun faqat matn
            $table->text('employment');
            $table->text('relatives');

            // Aloqa (sensitive — encrypted cast bilan)
            $table->text('phone')->nullable();
            $table->text('home_address');          // encrypted
            $table->text('passport_info')->nullable(); // encrypted

            // Rasmni ham saqlaymiz (private disk, tashqaridan ochilmaydi)
            $table->string('photo_path')->nullable();

            $table->timestamps();

            $table->index('full_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};

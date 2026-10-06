<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mail_logs', function (Blueprint $table) {
            $table->text('to')->change();
            $table->text('subject')->change();
        });
    }

    public function down(): void
    {
        Schema::table('mail_logs', function (Blueprint $table) {
            $table->string('to')->change();
            $table->string('subject')->change();
        });
    }
};

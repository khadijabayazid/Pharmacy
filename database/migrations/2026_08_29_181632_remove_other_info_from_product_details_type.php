<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE product_details MODIFY type ENUM(
            'warnings',
            'usage_method',
            'indications',
            'side_effects'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE product_details MODIFY type ENUM(
            'warnings',
            'usage_method',
            'indications',
            'side_effects',
            'other_info'
        )");
    }
};

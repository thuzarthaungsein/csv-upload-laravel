<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csv_entries', function (Blueprint $table) {
            $table->id();
            $table->string('unique_key');
            $table->longText('product_title')->nullable();
            $table->longText('product_description')->nullable();
            $table->longText('style_hash')->nullable();
            $table->longText('sanmar_mainframe_color')->nullable();
            $table->longText('size')->nullable();
            $table->longText('color_name')->nullable();
            $table->integer('piece_price')->default(0); // will save cent values
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('csv_entries');
    }
};

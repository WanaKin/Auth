<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePasswordResetTokensTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create( 'password_reset_tokens', function ( Blueprint $table ) {
            $table->id();
            $table->morphs( 'authenticatable', 'password_reset_authenticatable_index' );
            $table->string( 'token' )->unique();
            $table->timestamps();
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists( 'password_reset_tokens' );
    }
}

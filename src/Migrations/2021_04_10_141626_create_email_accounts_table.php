<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email');
            $table->string('pass');
            $table->string('username')->nullable();
            $table->string('from_name')->nullable();
            $table->string('reply_to_mail')->nullable();
            $table->string('reply_to_name')->nullable();
            $table->bigInteger('provider_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('provider_id')->references('id')->on('email_providers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_accounts');
    }
}

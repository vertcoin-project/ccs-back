<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('payment_id')->nullable();
            $table->string('address')->nullable();
            $table->string('address_uri')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('target_amount')->nullable();
            $table->string('raised_amount')->nullable();
            $table->string('state')->default('OPENED');
            $table->string('filename')->nullable();
            $table->unsignedInteger('merge_request_id')->unique();
            $table->string('gitlab_username');
            $table->string('gitlab_url');
            $table->string('gitlab_state')->default('opened');
            $table->timestamp('gitlab_created_at');
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
        Schema::dropIfExists('projects');
    }
}

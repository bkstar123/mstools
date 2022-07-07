<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrackingGoliveDxpSites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracking_golive_dxp_sites', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sites');
            $table->bigInteger('admin_id')->unsigned()->index();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tracking_golive_dxp_sites');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexToSalaries extends Migration
{
    public function up()
    {
        Schema::table('salaries', function (Blueprint $table) {
            // prevent duplicate salary records per employee per period
            $table->unique(['employee_id', 'period_start', 'period_end'], 'salaries_employee_period_unique');
        });
    }

    public function down()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropUnique('salaries_employee_period_unique');
        });
    }
}

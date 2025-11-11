<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAbsenceFieldsToSalariesTable extends Migration
{
    public function up()
    {
        Schema::table('salaries', function (Blueprint $table) {
            if (! Schema::hasColumn('salaries', 'absent_days_without_reason')) {
                $table->integer('absent_days_without_reason')->default(0)->after('base_salary');
            }
            if (! Schema::hasColumn('salaries', 'absent_days_with_reason')) {
                $table->integer('absent_days_with_reason')->default(0)->after('absent_days_without_reason');
            }
            if (! Schema::hasColumn('salaries', 'final_salary')) {
                $table->decimal('final_salary', 12, 2)->default(0)->after('net_pay');
            }
        });
    }

    public function down()
    {
        Schema::table('salaries', function (Blueprint $table) {
            if (Schema::hasColumn('salaries', 'final_salary')) {
                $table->dropColumn('final_salary');
            }
            if (Schema::hasColumn('salaries', 'absent_days_with_reason')) {
                $table->dropColumn('absent_days_with_reason');
            }
            if (Schema::hasColumn('salaries', 'absent_days_without_reason')) {
                $table->dropColumn('absent_days_without_reason');
            }
        });
    }
}

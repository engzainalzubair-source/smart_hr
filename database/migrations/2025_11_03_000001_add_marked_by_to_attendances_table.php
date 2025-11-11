<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMarkedByToAttendancesTable extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete()->after('notes');
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('marked_by');
        });
    }
}

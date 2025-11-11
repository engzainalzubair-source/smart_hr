<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToRewardsPenaltiesTable extends Migration
{
    public function up()
    {
        Schema::table('rewards_penalties', function (Blueprint $table) {
            if (!Schema::hasColumn('rewards_penalties', 'value_type')) {
                $table->enum('value_type', ['amount','percentage'])->default('amount')->after('type');
            }
            if (!Schema::hasColumn('rewards_penalties', 'status')) {
                $table->enum('status', ['pending','approved','rejected','applied'])->default('approved')->after('issued_at');
            }
            if (!Schema::hasColumn('rewards_penalties', 'policy_rule')) {
                $table->string('policy_rule')->nullable()->after('reason');
            }
            if (!Schema::hasColumn('rewards_penalties', 'metadata')) {
                $table->json('metadata')->nullable()->after('policy_rule');
            }
            if (!Schema::hasColumn('rewards_penalties', 'applied_to_payroll')) {
                $table->boolean('applied_to_payroll')->default(false)->after('metadata');
            }
        });
    }

    public function down()
    {
        Schema::table('rewards_penalties', function (Blueprint $table) {
            if (Schema::hasColumn('rewards_penalties', 'applied_to_payroll')) {
                $table->dropColumn('applied_to_payroll');
            }
            if (Schema::hasColumn('rewards_penalties', 'metadata')) {
                $table->dropColumn('metadata');
            }
            if (Schema::hasColumn('rewards_penalties', 'policy_rule')) {
                $table->dropColumn('policy_rule');
            }
            if (Schema::hasColumn('rewards_penalties', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('rewards_penalties', 'value_type')) {
                $table->dropColumn('value_type');
            }
        });
    }
}

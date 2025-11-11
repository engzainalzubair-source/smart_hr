<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRewardsPenaltiesTable extends Migration
{
    public function up()
    {
        Schema::create('rewards_penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['reward','penalty']);
            $table->decimal('amount', 12, 2)->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('issued_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rewards_penalties');
    }
}

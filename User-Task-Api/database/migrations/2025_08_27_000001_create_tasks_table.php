<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            // 👤 Assigned user (task belongs to this user)
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->index('user_id');

            // 👨‍💼 Created by (admin who assigned the task)
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->index('created_by');

            // 📌 Task details
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('status', ['todo','in_progress','done'])->default('todo');
            $table->enum('priority', ['low','medium','high'])->default('medium');
            $table->dateTime('due_date')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};

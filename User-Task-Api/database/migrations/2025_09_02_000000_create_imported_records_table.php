<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void
{
Schema::create('imported_records', function (Blueprint $table) {
$table->id();
$table->string('filename');
$table->string('name')->nullable();
$table->string('email')->nullable();
$table->string('phone')->nullable();
$table->text('address')->nullable();
$table->string('city')->nullable();
$table->string('state')->nullable();
$table->string('zip')->nullable();
$table->date('dob')->nullable();
$table->string('gender')->nullable();
$table->string('occupation')->nullable();
$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
$table->timestamps();
});
}


public function down(): void
{
Schema::dropIfExists('imported_records');
}
};
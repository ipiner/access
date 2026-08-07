<?php

use Illuminate\Database\Schema\Blueprint;
use Pin\Database\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->schema()->create('users', function (Blueprint $table) {
            $this->useTable($table);
            $this->id();
            $this->string('username', '', 30);
            $this->string('password', '密码', 120, true);
            $this->string('realname', '', 30, true);
            $this->version();
            $this->blameable();
            $this->timestamps();
            $this->deleted();
            $table->unique('username', 'deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema()->dropIfExists('users');
    }
};

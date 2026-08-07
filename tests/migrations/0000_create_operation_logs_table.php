<?php

use Illuminate\Database\Schema\Blueprint;
use Pin\Modules\Log\Migration\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->schema()->create('operation_logs', function (Blueprint $table) {
            $this->useTable($table);

            $this->base();
            $this->user();
            $this->subject();
            $this->json('changes', '变更内容');
            $this->request();

            $table->comment($this->makeComment('操作日志表', 'pin'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema()->dropIfExists('operation_logs');
    }
};

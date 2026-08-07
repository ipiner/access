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
        $this->schema()->create('menus', function (Blueprint $table) {
            $this->useTable($table);
            $this->id(false);
            $this->unsignedInteger('pid', '父id|menus.id')->default(0)->index();
            $this->string('name', '菜单名称', 30);
            $this->string('code', '菜单编码', 45);
            $this->string('path', '菜单路径|...父父id,父id,id', 100)->unique();
            $this->unsignedTinyInteger('level', '层级')->default(1);
            $this->unsignedInteger('sort', '排序');
            $this->string('type', '类型|menu: 菜单; button: 按钮', 15)->default('menu');
            $this->unsignedTinyInteger('enabled', '启用|0: 否; 1: 是; 2: 是且不能禁用')->default(1);
            $this->unsignedTinyInteger('visible', '是否显示|0: 否; 1: 是')->default(1);
            $this->string('icon', 'icon图标', 45, true);
            $this->string('route', '前端路由地址', 45, true);
            $this->version();
            $this->timestamps();
            $this->deleted();

            $table->unique(['name', 'pid', 'deleted_at']);
            $table->unique(['code', 'deleted_at']);
            $table->index(['level', 'sort']);
            $table->comment($this->makeComment('菜单表', 'pin'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema()->dropIfExists('menus');
    }
};

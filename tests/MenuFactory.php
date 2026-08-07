<?php

namespace Pin\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Pin\Access\Models\Menu;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => uniqid(),
            'code' => uniqid(),
            'type' => Menu::MENU,
            'enabled' => Menu::ENABLED,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Nube;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Nube>
 */
class NubeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $password = Str::random(10);

        return [
            'nombre' => fake()->unique()->company(),
            'host' => fake()->domainName(),
            'puerto' => 21,
            'usuario' => fake()->userName(),
            'password' => encrypt($password),
            'ruta_raiz' => '/',
            'tipo_conexion' => 'ftp',
            'ssl_pasv' => false,
            'timeout' => 30,
            'activo' => true,
            'descripcion' => fake()->sentence(),
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the nube is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }

    /**
     * Indicate that the nube uses SFTP.
     */
    public function sftp(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_conexion' => 'sftp',
            'puerto' => 22,
        ]);
    }

    /**
     * Indicate that the nube uses FTPS.
     */
    public function ftps(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_conexion' => 'ftps',
            'ssl_pasv' => true,
        ]);
    }
}

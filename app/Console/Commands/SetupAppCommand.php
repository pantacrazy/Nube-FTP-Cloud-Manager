<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetupAppCommand extends Command
{
    protected $signature = 'app:setup';

    protected $description = 'Configurar la base de datos y preparar la aplicación para su primer uso';

    public function handle(): int
    {
        $this->info('===========================================');
        $this->info('       CONFIGURACIÓN DE LA APLICACIÓN');
        $this->info('===========================================');
        $this->newLine();

        if (! $this->verificarConexionBD()) {
            return 1;
        }

        $this->verificarTablasExistentes();

        if ($this->confirmarMigrateFresh()) {
            $this->ejecutarMigrateFresh();
        }

        $this->verificarEstadoUsuarios();

        return 0;
    }

    private function verificarConexionBD(): bool
    {
        $this->info('1. Verificando conexión a la base de datos...');

        try {
            DB::connection()->getPdo();
            $this->info('   ✓ Conexión exitosa');
            $this->newLine();

            return true;
        } catch (\Exception $e) {
            $this->error('   ✗ Error de conexión: '.$e->getMessage());
            $this->newLine();
            $this->error('Verificá que la base de datos "fuentes" exista en MySQL.');
            $this->error('Podés crearla con: CREATE DATABASE fuentes;');

            return false;
        }
    }

    private function verificarTablasExistentes(): void
    {
        $this->info('2. Verificando tablas existentes...');

        $tablasEsperadas = ['users', 'nubes', 'migrations', 'sessions', 'cache', 'jobs'];
        $tablasExistentes = Schema::getTableListing();
        $encontradas = array_intersect($tablasEsperadas, $tablasExistentes);

        if (count($encontradas) > 0) {
            $this->warn('   Tablas encontradas: '.implode(', ', $encontradas));
        } else {
            $this->info('   No se encontraron tablas. Se crearán desde cero.');
        }
        $this->newLine();
    }

    private function confirmarMigrateFresh(): bool
    {
        $this->info('3. Preparación de migraciones');
        $this->newLine();
        $this->error('   ⚠️  ADVERTENCIA: migrate:fresh borrará TODOS los datos existentes');
        $this->newLine();

        return $this->confirm('   ¿Deseas ejecutar migrate:fresh? (Recomendado para instalación inicial)', false);
    }

    private function ejecutarMigrateFresh(): void
    {
        $this->info('   Ejecutando migrate:fresh...');
        $this->newLine();

        try {
            $this->call('migrate:fresh', ['--force' => true]);
            $this->info('   ✓ Migraciones ejecutadas correctamente');
        } catch (\Exception $e) {
            $this->error('   ✗ Error en migraciones: '.$e->getMessage());
        }
        $this->newLine();
    }

    private function verificarEstadoUsuarios(): void
    {
        $this->info('4. Verificando estado de usuarios...');

        try {
            $cantidadUsuarios = DB::table('users')->count();

            if ($cantidadUsuarios === 0) {
                $this->warn('   ⚠️  No existen usuarios en la base de datos');
                $this->newLine();
                $this->info('   ===========================================');
                $this->info('   ¡CONFIGURACIÓN COMPLETA!');
                $this->info('   ===========================================');
                $this->newLine();
                $this->info('   Pasos siguientes:');
                $this->info('   1. Ejecutá: php artisan serve');
                $this->info('   2. Abrí tu navegador en: http://localhost:8000/setup');
                $this->info('   3. Creá tu primer usuario administrador');
                $this->newLine();
            } else {
                $this->info('   ✓ Existen '.$cantidadUsuarios.' usuario(s) en la base de datos');
                $this->newLine();
                $this->info('   La aplicación está lista para usar.');
            }
        } catch (\Exception $e) {
            $this->error('   ✗ Error al verificar usuarios: '.$e->getMessage());
        }
        $this->newLine();
    }
}

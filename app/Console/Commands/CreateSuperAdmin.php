<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**CREAR SUPERADMIN:
 * php artisan admin:create
 * Ingresa nombre: Superadmin(ejemplo)
 * Ingresa contraseña: ********
 * Confirma: yes
 * ✅ Superadmin creado exitosamente
 */


class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear un superadmin de emergencia (email: admin@admin.com, rol: superadmin, aprobado: true)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚨 Creando Superadmin de Emergencia');
        $this->warn('Este comando creará un usuario con email admin@admin.com y rol superadmin.');
        $this->warn('Solo usar en emergencias cuando no exista ningún admin aprobado.');

        // Verificar si ya existe algún admin aprobado (distinto de admin@admin.com)
        $existingApprovedAdmin = User::where('role', 'superadmin')
            ->where('is_approved', true)
            ->where('email', '!=', 'admin@admin.com')
            ->first();

        if ($existingApprovedAdmin) {
            $this->error('❌ Ya existe un admin aprobado en el sistema: ' . $existingApprovedAdmin->email);
            $this->info('Este comando es solo para emergencias cuando no existe ningún admin aprobado.');
            $this->info('Para gestionar usuarios, usa el panel: /admin/users');
            return 1;
        }

        // Verificar si ya existe admin@admin.com
        $existingAdmin = User::where('email', 'admin@admin.com')->first();
        if ($existingAdmin) {
            if ($existingAdmin->is_approved) {
                $this->error('❌ Ya existe un superadmin aprobado: admin@admin.com');
                $this->info('Para gestionar usuarios, usa el panel: /admin/users');
                return 1;
            } else {
                // admin@admin.com existe pero no está aprobado → aprobarlo
                $this->warn('⚠️  El usuario admin@admin.com existe pero no está aprobado.');
                if ($this->confirm('¿Aprobar el superadmin existente?', true)) {
                    $existingAdmin->update(['is_approved' => true]);
                    $this->info('✅ Superadmin aprobado exitosamente.');
                    $this->info('📧 Email: admin@admin.com');
                    $this->info('👤 Nombre: ' . $existingAdmin->name);
                    $this->info('🔑 Rol: superadmin');
                    $this->info('✅ Aprobado: true');
                    return 0;
                } else {
                    $this->info('❌ Operación cancelada.');
                    return 1;
                }
            }
        }

        // Solicitar nombre
        $name = $this->ask('Nombre del superadmin:', 'Superadmin');

        // Solicitar contraseña
        $password = $this->secret('Contraseña (no se mostrará):');
        if (empty($password) || strlen($password) < 8) {
            $this->error('❌ La contraseña debe tener al menos 8 caracteres.');
            return 1;
        }

        // Confirmar
        if (!$this->confirm("¿Crear superadmin '{$name}' con email admin@admin.com?", true)) {
            $this->info('❌ Operación cancelada.');
            return 1;
        }

        // Crear superadmin
        try {
            $user = User::create([
                'name' => $name,
                'email' => 'admin@admin.com',
                'password' => Hash::make($password),
                'role' => 'superadmin',
                'is_approved' => true,
            ]);

            $this->info('✅ Superadmin creado exitosamente.');
            $this->info('📧 Email: admin@admin.com');
            $this->info('👤 Nombre: ' . $user->name);
            $this->info('🔑 Rol: superadmin');
            $this->info('✅ Aprobado: true');
            $this->warn('⚠️  Guarda la contraseña de forma segura.');
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error creando superadmin: ' . $e->getMessage());
            return 1;
        }
    }
}

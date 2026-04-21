<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_fillable(): void
    {
        $user = new User([
            'name' => 'TestUser',
            'password' => 'hashed',
            'role' => 'admin',
        ]);

        $this->assertEquals('TestUser', $user->name);
        $this->assertEquals('admin', $user->role);
    }

    public function test_user_hidden(): void
    {
        $user = new User([
            'name' => 'TestUser',
            'password' => 'secret',
        ]);

        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    public function test_is_admin_devuelve_true_cuando_es_admin(): void
    {
        $user = new User(['role' => 'admin']);

        $this->assertTrue($user->isAdmin());
    }

    public function test_is_admin_devuelve_false_cuando_no_es_admin(): void
    {
        $user = new User(['role' => 'user']);

        $this->assertFalse($user->isAdmin());
    }

    public function test_is_user_devuelve_true_cuando_es_user(): void
    {
        $user = new User(['role' => 'user']);

        $this->assertTrue($user->isUser());
    }

    public function test_is_user_devuelve_false_cuando_no_es_user(): void
    {
        $user = new User(['role' => 'admin']);

        $this->assertFalse($user->isUser());
    }

    public function test_password_es_cast_como_hashed(): void
    {
        $user = new User();

        $casts = $user->getCasts();

        $this->assertArrayHasKey('password', $casts);
        $this->assertEquals('hashed', $casts['password']);
    }

    public function test_user_factory_creaUsuarioValido(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotEmpty($user->name);
        $this->assertNotEmpty($user->password);
    }

    public function test_user_factory_admin(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertEquals('admin', $user->role);
    }

    public function test_user_factory_user(): void
    {
        $user = User::factory()->user()->create();

        $this->assertEquals('user', $user->role);
    }
}

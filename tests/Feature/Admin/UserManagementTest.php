<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_user_management_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['name' => 'Cinema Member']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Quản lý người dùng')
            ->assertSee('Cinema Member');
    }

    public function test_non_admin_cannot_manage_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_a_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'New Member',
                'email' => 'new-member@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'user',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasNoErrors();

        $user = User::where('email', 'new-member@example.com')->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_admin_can_open_create_and_edit_forms(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Thêm người dùng')
            ->assertSee(route('admin.users.store'));

        $this->get(route('admin.users.edit', $user))
            ->assertOk()
            ->assertSee('Sửa người dùng')
            ->assertSee(route('admin.users.update', $user));
    }

    public function test_admin_can_promote_and_demote_another_user_from_the_edit_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'admin',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame('admin', $user->fresh()->role);

        $this->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'user',
        ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame('user', $user->fresh()->role);
    }

    public function test_admin_cannot_change_or_delete_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'user',
            ])
            ->assertSessionHasErrors('role');

        $this->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('user');

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_admin_can_demote_and_delete_another_account_while_retaining_admin_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $otherAdmin), [
                'name' => $otherAdmin->name,
                'email' => $otherAdmin->email,
                'role' => 'user',
            ])
            ->assertRedirect(route('admin.users.index'));
        $this->assertSame('user', $otherAdmin->fresh()->role);

        $this->delete(route('admin.users.destroy', $otherAdmin))
            ->assertRedirect(route('admin.users.index'));
        $this->assertNull($otherAdmin->fresh());

        $this->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('user');

        $this->assertSame('admin', $admin->fresh()->role);
        $this->assertSame('user', $member->fresh()->role);
    }

    public function test_admin_can_delete_another_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertNull($user->fresh());
    }
}

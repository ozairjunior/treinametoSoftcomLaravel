<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function testRootRedirectsToDashboard()
    {
        $this->get('/')->assertRedirect('/home');
    }

    public function testGuestIsRedirectedToLogin()
    {
        $this->get('/home')->assertRedirect('/login');
    }

    public function testAuthenticationPagesRenderWithAdminLte()
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('vendor/adminlte/dist/css/adminlte.min.css', false)
            ->assertSee('vendor/icheck-bootstrap/icheck-bootstrap.min.css', false);

        $this->get('/register')->assertOk();
        $this->get('/password/reset')->assertOk();
    }

    public function testAuthenticatedUserCanRenderAdminLteDashboard()
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->actingAs($user)
            ->get('/home')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('vendor/adminlte/dist/css/adminlte.min.css', false)
            ->assertDontSee('/css/app.css', false)
            ->assertDontSee('/js/app.js', false);
    }

    public function testAdminLteAssetsArePublished()
    {
        $this->assertFileExists(public_path('vendor/adminlte/dist/css/adminlte.min.css'));
        $this->assertFileExists(public_path('vendor/adminlte/dist/js/adminlte.min.js'));
        $this->assertFileExists(public_path('vendor/bootstrap/js/bootstrap.bundle.min.js'));
        $this->assertFileExists(public_path('vendor/jquery/jquery.min.js'));
        $this->assertFileExists(public_path('vendor/icheck-bootstrap/icheck-bootstrap.min.css'));
    }

    public function testLaravelCollectiveHtmlIsConfigured()
    {
        $this->assertInstanceOf(\Collective\Html\FormBuilder::class, app('form'));
        $this->assertInstanceOf(\Collective\Html\HtmlBuilder::class, app('html'));
        $this->assertTrue(class_exists('Form'));
        $this->assertTrue(class_exists('Html'));
    }
}

<?php

namespace App\Tests;

use App\Core\Auth;
use App\Core\Session;
use PHPUnit\Framework\TestCase;

final class SessionAuthTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            session_write_close();
        }
    }

    public function testSessionStoresValues(): void
    {
        Session::start([
            'session_name' => 'fastphunzira_test',
            'session_lifetime' => 1200,
            'require_https' => false,
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
        ]);

        Session::set('demo_key', 'demo_value');

        $this->assertSame('demo_value', Session::get('demo_key'));
        $this->assertTrue(Session::has('demo_key'));
    }

    public function testAuthTracksLoginState(): void
    {
        Session::start([
            'session_name' => 'fastphunzira_test',
            'session_lifetime' => 1200,
            'require_https' => false,
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
        ]);

        $this->assertFalse(Auth::check());

        Auth::login([
            'id' => 42,
            'email' => 'student@example.com',
            'role' => 'student',
            'password_hash' => 'secretHash',
        ]);

        $this->assertTrue(Auth::check());
        $this->assertSame(42, Auth::userId());
        $this->assertSame('student@example.com', Auth::user()['email']);
        $this->assertArrayNotHasKey('password_hash', Auth::user());

        Auth::logout();

        $this->assertFalse(Auth::check());
        $this->assertNull(Auth::user());
    }
}

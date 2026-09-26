<?php

namespace App\Tests;

use App\Core\Env;
use PHPUnit\Framework\TestCase;

final class StagingEnvironmentValidationTest extends TestCase
{
    public function testStagingRequiresRealDatabaseCredentials(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('placeholder');

        Env::validateForRuntime('staging', [
            'DB_HOST' => 'sql205.infinityfree.com',
            'DB_DATABASE' => 'if0_43013735_stagingfastphunzira',
            'DB_USERNAME' => 'if0_43013735',
            'DB_PASSWORD' => 'YOUR_STAGING_DATABASE_PASSWORD',
            'APP_URL' => 'https://staging-fastphunzira.lovestoblog.com',
        ]);
    }
}

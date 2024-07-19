<?php

abstract class TestCase extends CBSTestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    public function getAdminHeader(): array
    {
        return $this->getHeader('ADMIN');
    }
}



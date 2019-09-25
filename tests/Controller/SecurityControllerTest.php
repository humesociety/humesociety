<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLogin()
    {
        $client = static::createClient();

        // check the login page exists
        $crawler = $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('main h1', 'Sign In');

        // submit the login form with bad credentials
        $loginForm = $crawler->selectButton('Sign In')->form();
        $loginForm['username'] = 'tech';
        $loginForm['password'] = 'wrongpassword';
        $client->submit($loginForm);

        // follow the redirect and check we haven't been logged in
        $this->assertTrue($client->getResponse()->isRedirect('/login'));
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.error', 'Invalid credentials.');

        // submit the form with good credentials
        $loginForm = $crawler->selectButton('Sign In')->form();
        $loginForm['username'] = 'tech';
        $loginForm['password'] = 'password';
        $client->submit($loginForm);

        // follow the redirect and check we have been logged in
        $this->assertTrue($client->getResponse()->isRedirect('/account'));
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRegister()
    {
        $client = static::createClient();

        // check the register page exists
        $crawler = $client->request('GET', '/register');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('main h1', 'Sign Up');
    }

    public function testForgot()
    {
        $client = static::createClient();

        // check the forgot details page exists
        $crawler = $client->request('GET', '/forgot');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('main h1', 'Forgot Details');

        // submit the forgot details form with bad details
        $forgotForm = $crawler->selectButton('Email Me')->form();
        $forgotForm['user_forgot_password[email]'] = 'wrong@humesociety.org';
        $crawler = $client->submit($forgotForm);

        // check for the error message
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('form li', 'Email address not found');
    }
}

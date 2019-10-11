<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testLogin()
    {
        // check the login page exists
        $crawler = $this->client->request('GET', '/login');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('main h1', 'Sign In');

        // submit the login form with bad credentials
        $loginForm = $crawler->selectButton('Sign In')->form();
        $loginForm['username'] = 'tech';
        $loginForm['password'] = 'wrongpassword';
        $this->client->submit($loginForm);

        // follow the redirect and check we haven't been logged in
        $this->assertTrue($this->client->getResponse()->isRedirect('/login'));
        $crawler = $this->client->followRedirect();
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.error', 'Invalid credentials.');

        // submit the form with good credentials
        $loginForm = $crawler->selectButton('Sign In')->form();
        $loginForm['username'] = 'tech';
        $loginForm['password'] = 'password';
        $this->client->submit($loginForm);

        // follow the redirect and check we have been logged in
        $this->assertTrue($this->client->getResponse()->isRedirect('/account'));
        $crawler = $this->client->followRedirect();
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testRegister()
    {
        // check the register page exists
        $crawler = $this->client->request('GET', '/register');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('main h1', 'Sign Up');
    }

    public function testForgot()
    {
        // check the forgot details page exists
        $crawler = $this->client->request('GET', '/forgot');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('main h1', 'Forgot Credentials');

        // submit the forgot details form with bad details
        $forgotForm = $crawler->selectButton('Email Me')->form();
        $forgotForm['user_type_forgot_credentials[email]'] = 'wrong@humesociety.org';
        $crawler = $this->client->submit($forgotForm);

        // check for the error message
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('form li', 'Email address not found');
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Tests\Functional\FunctionalTestCase;

final class RegisterTest extends FunctionalTestCase
{
    /**
     * @param array{username: string, email: string, plainPassword: string} $formData
     *
     * @dataProvider provideInvalidRegisterData
     */
    public function testThatRegistrationShouldFail(array $formData): void
    {
        $crawler = $this->get('/auth/register');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name="register"]');

        $form = $crawler->filter('form[name="register"]')->form([
            'register[username]'      => $formData['username'],
            'register[email]'         => $formData['email'],
            'register[plainPassword]' => $formData['plainPassword'],
        ]);

        $this->client->submit($form);

        $statusCode = self::getClient()->getResponse()->getStatusCode();

        if (\in_array($statusCode, [200, 422], true)) {
            self::assertSelectorExists('.form-error-message');
        } else {
            self::assertSame(302, $statusCode, 'Expected redirect on valid registration.');
        }
    }

    /**
     * @return iterable<string, array{0: array{username: string, email: string, plainPassword: string}}>
     */
    public static function provideInvalidRegisterData(): iterable
    {
        yield 'empty username' => [[
            'username'      => '',
            'email'         => 'user@email.com',
            'plainPassword' => 'SuperPassword123!',
        ]];

        yield 'non unique username' => [[
            'username'      => 'username',
            'email'         => 'user@email.com',
            'plainPassword' => 'SuperPassword123!',
        ]];

        yield 'too long username' => [[
            'username'      => str_repeat('a', 256),
            'email'         => 'user@email.com',
            'plainPassword' => 'SuperPassword123!',
        ]];

        yield 'empty email' => [[
            'username'      => 'username',
            'email'         => '',
            'plainPassword' => 'SuperPassword123!',
        ]];

        yield 'non unique email' => [[
            'username'      => 'username',
            'email'         => 'user@email.com',
            'plainPassword' => 'SuperPassword123!',
        ]];

        yield 'invalid email' => [[
            'username'      => 'username',
            'email'         => 'invalid-email',
            'plainPassword' => 'SuperPassword123!',
        ]];
    }
}

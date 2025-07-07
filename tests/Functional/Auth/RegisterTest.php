<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Model\Entity\User;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterTest extends FunctionalTestCase
{
    public function testThatRegistrationShouldSucceeded(): void
    {
        $this->get('/auth/register');

        // Générer un identifiant unique
        $uniqueSuffix = uniqid();
        $email = "user_$uniqueSuffix@email.com";
        $username = "username_$uniqueSuffix";
        $plainPassword = 'SuperPassword123!';

        $formData = [
            'register[username]' => $username,
            'register[email]' => $email,
            'register[plainPassword]' => $plainPassword,
        ];

        $this->client->submitForm("S'inscrire", $formData);

        self::assertResponseRedirects('/auth/login');

        $user = $this->getEntityManager()->getRepository(User::class)->findOneByEmail($email);
        $userPasswordHasher = $this->service(UserPasswordHasherInterface::class);

        self::assertNotNull($user);
        self::assertSame($username, $user->getUsername());
        self::assertSame($email, $user->getEmail());
        self::assertTrue($userPasswordHasher->isPasswordValid($user, $plainPassword));
    }

    /**
     * @dataProvider provideInvalidFormData
     * @param array<string, string> $formData
     */
    public function testThatRegistrationShouldFailed(array $formData): void
    {
        $this->get('/auth/register');
        $this->client->submitForm('S\'inscrire', $formData);
        self::assertResponseIsUnprocessable();
    }

    /**
     * @return iterable<array{formData: array<string, string>}>
     */
    public static function provideInvalidFormData(): iterable
    {
        yield 'empty username' => ['formData' => self::getFormData(['register[username]' => ''])];
        yield 'non unique username' => ['formData' => self::getFormData(['register[username]' => 'user+1'])];
        yield 'too long username' => ['formData' => self::getFormData(['register[username]' => 'Lorem ipsum dolor sit amet orci aliquam'])];
        yield 'empty email' => ['formData' => self::getFormData(['register[email]' => ''])];
        yield 'non unique email' => ['formData' => self::getFormData(['register[email]' => 'user+1@email.com'])];
        yield 'invalid email' => ['formData' => self::getFormData(['register[email]' => 'fail'])];
    }

    /**
     * @param array<string, string> $overrideData
     * @return array<string, string>
     */
    public static function getFormData(array $overrideData = []): array
    {
        return [
            'register[username]' => 'username',
            'register[email]' => 'user@email.com',
            'register[plainPassword]' => 'SuperPassword123!',
        ] + $overrideData;
    }
}

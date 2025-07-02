<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'role' => UserRole::ADMIN,
                'password' => 'admin123',
                'isVerified' => true,
            ],
            [
                'name' => 'John',
                'email' => 'john.doe@example.com',
                'role' => UserRole::ADMIN,
                'password' => 'password123',
                'isVerified' => true,
            ],
            [
                'name' => 'Jane',
                'email' => 'jane.smith@example.com',
                'role' => UserRole::USER,
                'password' => 'password123',
                'isVerified' => true,
            ],
            [
                'name' => 'Alice',
                'email' => 'alice.wonder@example.com',
                'role' => UserRole::USER,
                'password' => 'wonderland',
                'isVerified' => true,
            ],
            [
                'name' => 'Bob',
                'email' => 'bob.builder@example.com',
                'role' => UserRole::USER,
                'password' => 'buildit',
                'isVerified' => true,
            ],
            [
                'name' => 'Charlie',
                'email' => 'charlie.chaplin@example.com',
                'role' => UserRole::USER,
                'password' => 'silentfilm',
                'isVerified' => false,
            ],
            [
                'name' => 'Diana',
                'email' => 'diana.prince@example.com',
                'role' => UserRole::ADMIN,
                'password' => 'amazongal',
                'isVerified' => true,
            ],
            [
                'name' => 'Eve',
                'email' => 'eve.harrington@example.com',
                'role' => UserRole::USER,
                'password' => 'secretagent',
                'isVerified' => true,
            ],
            [
                'name' => 'Frank',
                'email' => 'frank.sinatra@example.com',
                'role' => UserRole::USER,
                'password' => 'myway',
                'isVerified' => true,
            ],
            [
                'name' => 'Grace',
                'email' => 'grace.hopper@example.com',
                'role' => UserRole::ADMIN,
                'password' => 'debugme',
                'isVerified' => true,
            ],
            [
                'name' => 'Harry',
                'email' => 'harry.potter@example.com',
                'role' => UserRole::USER,
                'password' => 'hogwarts',
                'isVerified' => true,
            ],
            [
                'name' => 'Igor',
                'email' => 'igor.tech@example.com',
                'role' => UserRole::USER,
                'password' => 'moreusers',
                'isVerified' => true,
            ]
        ];


        $createdUsers = [];

        foreach ($users as $index => $userData) {
            $user = new User();
            $user->setName($userData['name']);
            $user->setEmail($userData['email']);
            $user->setRole($userData['role']);
            $user->setIsVerified($userData['isVerified']);

            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);

            $manager->persist($user);
            $createdUsers[] = $user;

            $this->addReference('user_' . $index, $user);
        }

        $this->createFollowingRelationships($createdUsers);

        $manager->flush();
    }

    private function createFollowingRelationships(array $users): void
    {
        $admin = $users[0];
        for ($i = 1; $i < count($users); $i++) {
            $admin->addFollowing($users[$i]);
        }

        $followingPairs = [
            [1, 2],
            [1, 3],
            [2, 4],
            [3, 5],
            [4, 6],
            [5, 7],
            [6, 8],
            [7, 9],
            [8, 10],
        ];

        foreach ($followingPairs as $pair) {
            $users[$pair[0]]->addFollowing($users[$pair[1]]);
            $users[$pair[1]]->addFollowing($users[$pair[0]]);
        }

        $oneWayFollowing = [
            [2, 3],
            [4, 1],
            [6, 3],
            [8, 2],
            [10, 4],
        ];

        foreach ($oneWayFollowing as $pair) {
            $users[$pair[0]]->addFollowing($users[$pair[1]]);
        }
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\User;
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
                'email' => 'admin@example.com',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'admin123',
                'isVerified' => true,
            ],
            [
                'email' => 'john.doe@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'password123',
                'isVerified' => true,
            ],
            [
                'email' => 'jane.smith@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'password123',
                'isVerified' => true,
            ],
            [
                'email' => 'mike.wilson@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'password123',
                'isVerified' => true,
            ],
            [
                'email' => 'sarah.johnson@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'password123',
                'isVerified' => true,
            ],
            [
                'email' => 'david.brown@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'password123',
                'isVerified' => true,
            ],
            [
                'email' => 'lisa.davis@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'password123',
                'isVerified' => true,
            ],
            [
                'email' => 'robert.taylor@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'password123',
                'isVerified' => true,
            ],
            [
                'email' => 'emily.white@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'password123',
                'isVerified' => true,
            ],
            [
                'email' => 'alex.garcia@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'password123',
                'isVerified' => true,
            ],
            [
                'email' => 'chris.martin@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'password123',
                'isVerified' => true,
            ],
            [
                'email' => 'newuser@example.com',
                'roles' => ['ROLE_USER'],
                'password' => 'password123',
                'isVerified' => false,
            ],
        ];

        $createdUsers = [];

        foreach ($users as $index => $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setRoles($userData['roles']);
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

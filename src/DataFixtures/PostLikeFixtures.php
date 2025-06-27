<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PostLikeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $users = [];
        $posts = [];

        for ($i = 0; $i < 12; $i++) {
            $users[] = $this->getReference('user_' . $i, User::class);
        }

        for ($i = 0; $i < 12; $i++) {
            $posts[] = $this->getReference('post_' . $i, Post::class);
        }

        for ($i = 0; $i < 3; $i++) {
            $posts[] = $this->getReference('short_post_' . $i, Post::class);
        }

        foreach ($posts as $postIndex => $post) {
            $this->createLikesForPost($manager, $post, $users, $postIndex);
        }

        $this->updatePostCounters($manager, $posts);

        $manager->flush();
    }

    private function createLikesForPost(ObjectManager $manager, Post $post, array $users, int $postIndex): void
    {
        $createdAt = $post->getCreatedAt();
        $daysSinceCreation = (new \DateTimeImmutable())->diff($createdAt)->days;

        $baseEngagement = max(1, 30 - $daysSinceCreation);
        $likeChance = min(80, $baseEngagement * 2); // Max 80% chance of like
        $dislikeChance = min(15, $baseEngagement / 2); // Max 15% chance of dislike

        if ($postIndex < 3) {
            $likeChance += 10;
            $dislikeChance += 5;
        }

        $usersWhoReacted = [];

        foreach ($users as $userIndex => $user) {
            if ($post->getAuthor()->getId() === $user->getId()) {
                continue;
            }

            if (rand(1, 100) > 70) {
                continue;
            }

            $random = rand(1, 100);
            $reactionType = null;

            if ($random <= $likeChance) {
                $reactionType = 'like';
            } elseif ($random <= $likeChance + $dislikeChance) {
                $reactionType = 'dislike';
            }

            if ($reactionType) {
                $postLike = new PostLike();
                $postLike->setUser($user);
                $postLike->setPost($post);
                $postLike->setType($reactionType);

                $manager->persist($postLike);
                $usersWhoReacted[] = $user->getId();
            }
        }

        if ($postIndex < 5 && empty($usersWhoReacted)) {
            $randomUser = $users[array_rand($users)];
            if ($randomUser->getId() !== $post->getAuthor()->getId()) {
                $postLike = new PostLike();
                $postLike->setUser($randomUser);
                $postLike->setPost($post);
                $postLike->setType('like');
                $manager->persist($postLike);
            }
        }
    }

    private function updatePostCounters(ObjectManager $manager, array $posts): void
    {
        foreach ($posts as $post) {
            $likesCount = $manager->getRepository(PostLike::class)->count([
                'post' => $post,
                'type' => 'like'
            ]);

            $dislikesCount = $manager->getRepository(PostLike::class)->count([
                'post' => $post,
                'type' => 'dislike'
            ]);

            $post->setLikesCount($likesCount);
            $post->setDislikesCount($dislikesCount);
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PostFixtures::class,
        ];
    }
}

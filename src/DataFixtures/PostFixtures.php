<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PostFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $posts = [
            [
                'title' => 'Welcome to Our Community!',
                'content' => 'Hello everyone! Welcome to our amazing community platform. We\'re excited to have you here and can\'t wait to see what conversations and connections will emerge. Feel free to share your thoughts, ask questions, and engage with fellow members.',
                'image' => 'welcome-banner.jpg',
                'createdAt' => new \DateTimeImmutable('-30 days'),
            ],
            [
                'title' => 'The Future of Web Development',
                'content' => 'Web development is evolving at an incredible pace. From the rise of AI-assisted coding to the emergence of new frameworks and tools, developers today have more opportunities than ever before. What trends are you most excited about? Share your thoughts on where you think the industry is heading.',
                'image' => 'web-dev-future.jpg',
                'createdAt' => new \DateTimeImmutable('-25 days'),
            ],
            [
                'title' => 'Symfony 7: New Features Overview',
                'content' => 'Symfony 7 brings some fantastic new features that will make our development experience even better. The improved performance, enhanced security features, and developer experience improvements are game-changers. Let\'s discuss what features you\'re most looking forward to implementing in your projects.',
                'image' => 'symfony7-features.jpg',
                'createdAt' => new \DateTimeImmutable('-20 days'),
            ],
            [
                'title' => 'Best Practices for Database Design',
                'content' => 'Proper database design is crucial for application performance and scalability. Whether you\'re working with MySQL, PostgreSQL, or other database systems, following established patterns and principles will save you countless hours down the road. What database design patterns do you swear by?',
                'image' => 'database-design.jpg',
                'createdAt' => new \DateTimeImmutable('-18 days'),
            ],
            [
                'title' => 'Remote Work: Tips and Tricks',
                'content' => 'Working remotely has become the new normal for many developers. From setting up the perfect home office to maintaining work-life balance, there are many factors that contribute to remote work success. What strategies have worked best for you in your remote work journey?',
                'image' => 'remote-work-setup.jpg',
                'createdAt' => new \DateTimeImmutable('-15 days'),
            ],
            [
                'title' => 'Open Source Contribution Guide',
                'content' => 'Contributing to open source projects is one of the best ways to improve your skills and give back to the community. Whether you\'re fixing bugs, adding features, or improving documentation, every contribution matters. Here\'s a comprehensive guide to get you started on your open source journey.',
                'image' => 'open-source-guide.jpg',
                'createdAt' => new \DateTimeImmutable('-12 days'),
            ],
            [
                'title' => 'CSS Grid vs Flexbox: When to Use What',
                'content' => 'Both CSS Grid and Flexbox are powerful layout tools, but they serve different purposes. Understanding when to use each one can significantly improve your front-end development workflow. Let\'s break down the use cases and help you choose the right tool for each situation.',
                'image' => 'css-grid-flexbox.jpg',
                'createdAt' => new \DateTimeImmutable('-10 days'),
            ],
            [
                'title' => 'API Security Best Practices',
                'content' => 'Securing your APIs is more important than ever. From authentication and authorization to rate limiting and input validation, there are many layers to consider. This post covers essential security practices that every developer should implement when building APIs.',
                'image' => 'api-security.jpg',
                'createdAt' => new \DateTimeImmutable('-8 days'),
            ],
            [
                'title' => 'Docker for PHP Developers',
                'content' => 'Docker has revolutionized how we develop and deploy applications. For PHP developers, Docker provides consistent development environments and simplified deployment processes. Let\'s explore how to set up Docker for your PHP projects and the benefits it brings to your workflow.',
                'image' => 'docker-php.jpg',
                'createdAt' => new \DateTimeImmutable('-6 days'),
            ],
            [
                'title' => 'Testing Strategies for Modern Applications',
                'content' => 'Comprehensive testing is essential for maintaining code quality and preventing regressions. From unit tests to integration tests and end-to-end testing, each level serves a specific purpose. What testing strategies have been most effective in your projects?',
                'image' => 'testing-strategies.jpg',
                'createdAt' => new \DateTimeImmutable('-4 days'),
            ],
            [
                'title' => 'Performance Optimization Techniques',
                'content' => 'Application performance can make or break user experience. From database query optimization to caching strategies and code profiling, there are many techniques to improve performance. Share your favorite performance optimization tips and tricks!',
                'image' => 'performance-optimization.jpg',
                'createdAt' => new \DateTimeImmutable('-2 days'),
            ],
            [
                'title' => 'The Art of Code Review',
                'content' => 'Code reviews are an essential part of the development process. They help maintain code quality, share knowledge, and catch potential issues before they reach production. What makes a great code review? Let\'s discuss best practices and common pitfalls to avoid.',
                'image' => 'code-review.jpg',
                'createdAt' => new \DateTimeImmutable('-1 day'),
            ],
        ];

        // Get users for post authors
        $users = $manager->getRepository(User::class)->findAll();

        if (empty($users)) {
            throw new \Exception('No users found. Please load UserFixtures first.');
        }

        foreach ($posts as $index => $postData) {
            $post = new Post();
            $post->setTitle($postData['title']);
            $post->setContent($postData['content']);
            $post->setImage($postData['image']);
            $post->setCreatedAt($postData['createdAt']);

            // Assign random author from available users
            $randomUser = $users[array_rand($users)];
            $post->setAuthor($randomUser);

            // Set random like/dislike counts to make posts more realistic
            $likesCount = rand(0, 50);
            $dislikesCount = rand(0, 10);
            $post->setLikesCount($likesCount);
            $post->setDislikesCount($dislikesCount);

            $manager->persist($post);

            // Create reference for potential use in other fixtures
            $this->addReference('post_' . $index, $post);
        }

        // Create a few additional posts with shorter content
        $shortPosts = [
            [
                'title' => 'Quick Tip: Git Aliases',
                'content' => 'Save time with Git aliases! Add these to your .gitconfig: git config --global alias.co checkout, git config --global alias.br branch, git config --global alias.ci commit',
                'image' => 'git-tips.jpg',
            ],
            [
                'title' => 'Monday Motivation',
                'content' => 'Every expert was once a beginner. Keep learning, keep growing, and don\'t be afraid to make mistakes. They\'re all part of the journey! 💪',
                'image' => 'motivation.jpg',
            ],
            [
                'title' => 'Debugging Like a Pro',
                'content' => 'Debugging tip: When stuck, try explaining the problem to a rubber duck (or colleague). Often, the act of articulating the issue helps you find the solution!',
                'image' => 'debugging-tips.jpg',
            ],
        ];

        foreach ($shortPosts as $index => $postData) {
            $post = new Post();
            $post->setTitle($postData['title']);
            $post->setContent($postData['content']);
            $post->setImage($postData['image']);

            // These are recent posts
            $post->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 3) . ' hours'));

            $randomUser = $users[array_rand($users)];
            $post->setAuthor($randomUser);

            // Lower engagement for shorter posts
            $likesCount = rand(0, 20);
            $dislikesCount = rand(0, 5);
            $post->setLikesCount($likesCount);
            $post->setDislikesCount($dislikesCount);

            $manager->persist($post);

            $this->addReference('short_post_' . $index, $post);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}

<?php

declare(strict_types=1);

namespace CSRPlatform\Shared\Database;

use CSRPlatform\Shared\Entity\ServiceCategories;
use CSRPlatform\Shared\Entity\UserAccount;
use CSRPlatform\Shared\Entity\UserProfiles;
use Faker\Factory;

final class DataGenerator
{
    public function seed(): void
    {
        $faker = Factory::create();
        $profiles = new UserProfiles();
        $accounts = new UserAccount();
        $categories = new ServiceCategories();

        $existing = $profiles->listProfiles();
        if (count($existing) === 0) {
            foreach ([
                ['role' => 'admin', 'description' => 'System administrator'],
                ['role' => 'csr', 'description' => 'CSR representative'],
                ['role' => 'pin', 'description' => 'Person in need'],
                ['role' => 'pm', 'description' => 'Project manager'],
            ] as $profile) {
                $profiles->createProfile($profile['role'], $profile['description']);
            }
        }

        $categoriesList = $categories->listCategories();
        if (count($categoriesList) === 0) {
            foreach (['Food assistance', 'Medical support', 'Education', 'Community outreach'] as $name) {
                $categories->createCategory($name);
            }
        }

        $profilesMap = [];
        foreach ($profiles->listProfiles() as $profile) {
            $profilesMap[$profile['role']] = (int)$profile['id'];
        }

        foreach (['admin', 'csr', 'pin', 'pm'] as $role) {
            $accounts->registerUA($role, ucfirst($role) . ' User', strtolower($role) . '@example.com', 'Password1');
        }

        for ($i = 0; $i < 5; $i++) {
            $accounts->registerUA('csr', $faker->name(), $faker->unique()->safeEmail(), 'Password1');
            $accounts->registerUA('pin', $faker->name(), $faker->unique()->safeEmail(), 'Password1');
        }
    }
}

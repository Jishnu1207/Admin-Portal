<?php

namespace App\DataFixtures;

use App\Entity\Users;
use App\Entity\Customer;
use App\Entity\Invoice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create a default admin user
        $adminUser = new Users();
        $adminUser->setEmail('admin@example.com');
        $adminUser->setRoles(['ROLE_USER']);
        $adminUser->setPassword($this->passwordHasher->hashPassword(
            $adminUser,
            'admin123'
        ));

        $manager->persist($adminUser);

        // Create 10 sample customers
        for ($i = 1; $i <= 10; $i++) {
            $customer = new Customer();
            $customer->setName('Customer ' . $i);
            $customer->setEmail('customer' . $i . '@example.com');
            $customer->setPhone('123-456-78' . sprintf('%02d', $i));
            $customer->setAddress('Address for Customer ' . $i);
            $manager->persist($customer);

            // Create 3 invoices for each customer
            for ($j = 1; $j <= 3; $j++) {
                $invoice = new Invoice();
                $invoice->setCustomer($customer);
                $invoice->setAmount(mt_rand(100, 1000) / 10);
                $invoice->setDescription('Invoice ' . $j . ' for Customer ' . $i);
                $invoice->setDate(new \DateTime(sprintf('-%d days', mt_rand(1, 30))));
                $invoice->setStatus(mt_rand(0, 1)); // Status is an integer (0 or 1)
                $manager->persist($invoice);
            }
        }

        $manager->flush();
    }
}

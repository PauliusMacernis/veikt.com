<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Job;
use Nelmio\Alice\Fixtures;
use Faker\Factory as FakerFactory;

class LoadFixtures implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
//        $job = new Job();
//        $job->setStep1Html('Test' . rand(1,100));
//        $job->setStep1Id('S' . rand(1,100000));
//        $job->setStep1Statistics('Stats' . rand(1,100000));
//
//        $manager->persist($job);
//        $manager->flush();

        Fixtures::load(
            __DIR__ . '/fixtures.yml',
            $manager,
            [
                'providers' => [$this]
            ]
        );

    }

    public function step1_html()
    {
        $faker = FakerFactory::create();
        $textBig = $faker->realText(2000, 2);
        $textSmall = $faker->company();


        $html = [
            '<div>' . $textBig . '</div>',
            '<div>' . $textBig . ' <a>' . $textSmall . '</a></div>',
            '<div>' . $textSmall . '</div><span>' . $textBig . '</span>',
            $textBig
        ];
        $key = array_rand($html);

        return $html[$key];

    }
}
<?php

namespace AppBundle\Form;

use AppBundle\Entity\SubFamily;
use AppBundle\Repository\SubFamilyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // http://symfony.com/doc/current/reference/forms/types.html
        $builder
            ->add('name')
            ->add('description')
            ->add('subFamily', EntityType::class, [
                'placeholder' => 'Choose a Sub Family',
                'class' => SubFamily::class,
                'query_builder' => function(SubFamilyRepository $repo) {
                    return $repo->createAlphabeticalQueryBuilder();
                }
            ])
            ->add('isPublished', ChoiceType::class, [
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ]
            ])
            ->add('datePosted', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'choice',
                'attr' => [
                    'class' => 'js-datepicker'
                ],
                'html5' => false, // if HTML5 == true then some browsers bring two datepickers (our JavaScript + defaul HTML5)
                                  //   instead of one (our JavaScript)
            ])
            ->add('step1_id')
            ->add('step1_statistics')
            ->add('step1_downloadedTime', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'choice',
                'attr' => [
                    'class' => 'js-datepicker'
                ],
                'html5' => false, // if HTML5 == true then some browsers bring two datepickers (our JavaScript + defaul HTML5)
                //   instead of one (our JavaScript)
            ])
            ->add('step1_html')
            ->add('step1_project')
            ->add('step1_url')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Job',
        ]);
    }

    public function getName()
    {
        return 'app_bundle_job_form_type';
    }
}

<?php

namespace PlatformBundle\Form;

use PlatformBundle\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
// use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvertType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cat_pattern = 'D%';

		$builder
			->add('date', DateType::class)
			->add('title', textType::class)
			->add('content', TextareaType::class)
			->add('author', TextType::class)
			->add('published', CheckboxType::class, array('required' => false))
			->add('image', ImageType::class)
			/*->add('categories', CollectionType::class, array
			(
				'entry_type'	=>	CategoryType::class,
				'allow_add'		=>	true,
				'allow_delete'	=>	true,
			))*/
			->add('categories', EntityType::class, array
            (
                'class'         =>  'PlatformBundle\Entity\Category',
                'choice_label'  =>  'name',
                'multiple'      =>  true,
                'query_builder' =>  function(CategoryRepository $repository) use (&$cat_pattern)
                {
                    return $repository->getLikeQueryBuilder($cat_pattern);
                },
            ))
			->add('save', SubmitType::class);

    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PlatformBundle\Entity\Advert'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'platformbundle_advert';
    }


}

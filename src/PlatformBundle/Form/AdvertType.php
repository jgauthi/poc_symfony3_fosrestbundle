<?php

namespace PlatformBundle\Form;

use PlatformBundle\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\{
    AbstractType,
    FormBuilderInterface,
    Extension\Core\Type\CheckboxType,
    Extension\Core\Type\DateType,
    Extension\Core\Type\SubmitType,
    Extension\Core\Type\TextType,
    FormEvent,
    FormEvents,
};
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvertType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $cat_pattern = 'D%';

		$builder
			->add('date', DateType::class)
			->add('title', textType::class)
			->add('content', MarkdownType::class)
			->add('author', TextType::class)
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

        // We add a function that will listen to an event
        $builder->addEventListener
        (
            FormEvents::POST_SET_DATA,
            function(FormEvent $event)
            {
                $advert = $event->getData();
                if(null === $advert)
                    return;

                // If the ad is not published, or if it does not exist in base (id is null)
                if(!$advert->getPublished() || null === $advert->getId())
                     $event->getForm()->add('published', CheckboxType::class, array('required' => false));
                else $event->getForm()->remove('published');
            }
        );

    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => 'PlatformBundle\Entity\Advert'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'platformbundle_advert';
    }


}

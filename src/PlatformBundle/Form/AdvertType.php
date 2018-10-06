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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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

        // On ajoute une fonction qui va écouter un évènement
        $builder->addEventListener
        (
            FormEvents::POST_SET_DATA,
            function(FormEvent $event)
            {
                $advert = $event->getData();
                if(null === $advert)
                    return;

                // Si l'annonce n'est pas publiée, ou si elle n'existe pas encore en base (id est null)
                if(!$advert->getPublished() || null === $advert->getId())
                     $event->getForm()->add('published', CheckboxType::class, array('required' => false));
                else $event->getForm()->remove('published');
            }
        );

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

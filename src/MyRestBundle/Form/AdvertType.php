<?php
namespace MyRestBundle\Form;

use PlatformBundle\Form\CategoryType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title')
            ->add('content')
            ->add('author')
            ->add('categories', CollectionType::class, [
                'entry_type'        => CategoryType::class,
                'allow_add'         => true,
                'error_bubbling'    => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'PlatformBundle\Entity\Advert',
            'csrf_protection' => false,
        ]);
    }
}
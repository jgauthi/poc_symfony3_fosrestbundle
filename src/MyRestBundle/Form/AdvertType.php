<?php
namespace MyRestBundle\Form;

use PlatformBundle\Form\CategoryType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvertType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class, ['description' => 'Titre de l\'annonce'])
            ->add('content', TextType::class, ['description' => 'Contenu de l\'annonce'])
            ->add('author', TextType::class, ['description' => 'Description de l\'annonce'])
            ->add('categories', CollectionType::class, [
                'entry_type'        => CategoryType::class,
                'allow_add'         => true,
                'error_bubbling'    => false,
                'description'       => 'Liste des catégories',
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
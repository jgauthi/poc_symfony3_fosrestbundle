<?php
namespace App\Form;

use Symfony\Component\Form\{
    AbstractType,
    Extension\Core\Type\CollectionType,
    Extension\Core\Type\TextType,
    FormBuilderInterface,
};
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvertTypeApi extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, ['description' => 'Advert title'])
            ->add('content', TextType::class, ['description' => 'Advert content'])
            ->add('author', TextType::class, ['description' => 'Advert author'])
            ->add('categories', CollectionType::class, [
                'entry_type'        => CategoryType::class,
                'allow_add'         => true,
                'error_bubbling'    => false,
                'description'       => 'Category list',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Advert',
            'csrf_protection' => false,
        ]);
    }
}

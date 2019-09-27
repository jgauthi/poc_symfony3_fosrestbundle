<?php
namespace App\Form;

use App\Entity\Application;
use Symfony\Component\Form\{
    AbstractType,
    Extension\Core\Type\ChoiceType,
    Extension\Core\Type\IntegerType,
    Extension\Core\Type\SubmitType,
    Extension\Core\Type\TextareaType,
    FormBuilderInterface
};
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, ['description' => 'Application content'])
            ->add('city', ChoiceType::class, [
                'choices' => array_combine(Application::CITY_AVAILABLE, Application::CITY_AVAILABLE),
                'description' => 'Application city',
            ])
            ->add('salaryClaim', IntegerType::class, ['description' => 'Application salary'])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Application',
        ]);
    }
}

<?php
namespace MyRestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CredentialsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('login', TextType::class, ['description' => 'Login utilisateur']);
        $builder->add('password', PasswordType::class, ['description' => 'Mot de passe utilisateur']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MyRestBundle\Entity\Credentials',
            'csrf_protection' => false
        ]);
    }
}

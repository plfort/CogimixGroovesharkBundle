<?php

namespace Cogipix\CogimixGroovesharkBundle\Form\Grooveshark;

use Symfony\Component\Validator\Constraints\Length;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LoginFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('login','text',
                array('label'=>'Login',
                        'constraints' => new Length(array('min' => 5)),))
        ->add('password','password',array('label'=>'Password'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

        $resolver->setDefaults(array(
            'validation_groups' => array('CogimixRegistration')

        ));
    }

    public function getName()
    {
        return 'cogimix_grooveshark_login';
    }
}

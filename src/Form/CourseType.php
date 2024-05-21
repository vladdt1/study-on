<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Символьный код',
                'constraints' => [
                    new NotBlank(message: 'Символьный код не может быть пустым'),
                    new Length(max: 255, maxMessage: 'Символьный код должен быть не более 255 символов')
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'p-1',
                ]
            ])
            ->add('title', TextType::class, [
                'label' => 'Название',
                'constraints' => [
                    new NotBlank(['message' => 'Название не может быть пустым']),
                    new Length(['max' => 55, 'maxMessage' => 'Название должно быть не более 55 символов'])
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'p-1',
                ]
            ])
            ->add('description', TextType::class, [
                'label' => 'Описание',
                'constraints' => [
                    new NotBlank(['message' => 'Описание не может быть пустым']),
                    new Length(['max' => 255, 'maxMessage' => 'Описание должно быть не более 255 символов'])
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'p-1',
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Тип курса',
                'choices' => [
                    'Бесплатный' => 0,
                    'Покупка' => 1,
                    'Аренда' => 2,
                ],
                'constraints' => [
                    new NotBlank(message: 'Тип курса не может быть пустым')
                ],
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'width: auto'
                ],
                'label_attr' => [
                    'class' => 'p-1',
                ]
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Стоимость',
                'currency' => 'RUB',
                'constraints' => [
                    new NotBlank(message: 'Стоимость не может быть пустой')
                ],
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'width: auto'
                ],
                'label_attr' => [
                    'class' => 'p-1',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}

<?php

namespace App\Entity\NewsItem;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsItemType extends AbstractType
{
    private $categories;

    public function __construct(ParameterBagInterface $params)
    {
        $this->categories = [];
        foreach ($params->get('news_categories') as $id => $category) {
            $this->categories[$category] = $id;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', ChoiceType::class, ['choices' => $this->categories])
            ->add('title')
            ->add('date', DateType::class, ['widget' => 'single_text'])
            ->add('end', DateType::class, ['widget' => 'single_text'])
            ->add('content');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => NewsItem::class]);
    }
}

<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\EntityDTO;
use Exception;
use Nette\Forms\Form;

class FormFactory extends Form
{
    public static function createForm(string $actionType, EntityDTO $entityDTO): Form
    {
        $form = new self($actionType . '_' . $entityDTO::getTableName() . '_' .  $entityDTO->getId());
        $form->setAction($entityDTO->getRoute()->getAbsPath());
        $form->addHidden('_' . $entityDTO::getTableName() . '__' . $entityDTO::getTableMainIdentifier(), $entityDTO->getId());

        return match ($actionType) {
            'softDelete' => self::softDeleteForm($form, $entityDTO),
            'upsert' => self::upsertForm($form, $entityDTO),
            default => throw new Exception("Requested type of form was not found")
        };
    }

    /**
     * @todo standalone classes by entity types
     */
    private static function upsertForm(Form $form, EntityDTO $entityDTO): Form
    {
        $content = $entityDTO->getContent();
        if ($content) {
            $form->addText('_content__title', '$.content.title')->setDefaultValue($content->title);
            $form->addText('_content__description', '$.content.description')->setDefaultValue($content->description);
            $form->addText('_content__text', '$.content.text')->setDefaultValue($content->text);
        }
        return $form;
    }

    private static function deleteForm(Form $form, EntityDTO $entityDTO): Form
    {
        return $form;
    }

    private static function softDeleteForm(Form $form, EntityDTO $entityDTO): Form
    {
        return $form;
    }
}

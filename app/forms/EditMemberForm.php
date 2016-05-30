<?php

namespace App\Forms;

use Nette;
use Nette\Utils\Html;
use Nette\Application\UI\Form;

/**
 * EditMemberForm used for editing members.
 */
class EditMemberForm extends Nette\Object {

    /**
     * Creates instance of the form.
     * 
     * @return Form
     */
    public function create() {
        $form = new Form;

        $form->addGroup('Osobní údaje');
        $form->addText('firstname', 'Jméno:')
                ->setRequired('Zadejte prosím jméno člena')
                ->addRule(Form::MAX_LENGTH, 'Jméno může mít nejvýše %d znaků', 20);

        $form->addText('midname', 'Střední jméno:')
                ->addRule(Form::MAX_LENGTH, 'Střední jméno může mít nejvýše %d znaků', 20);

        $form->addText('surname', 'Příjmení:')
                ->setRequired('Zadejte jméno člena')
                ->addRule(Form::MAX_LENGTH, 'Příjmení může mít nejvýše %d znaků', 30);

        $form->addSelect('gender', 'Pohlaví:', array('M' => 'Muž', 'F' => 'Žena',))
                ->setRequired('Vyberte pohlaví');

        // Birthdate is disabled, because of being permanent attribute
        $form->addText('birthdate', 'Datum narození:')
                ->setAttribute('class', 'datepicker')
                ->setRequired('Vyberte datum narození')
                ->setDisabled(true);

        // NID is disabled, because of being permanent attribute
        $form->addText('nid', 'Rodné číslo:')
                ->setRequired('Zedejte rodné číslo')
                ->addRule(Form::MAX_LENGTH, 'Rodné číslo může mít nejvýše %d znaků', 20)
                ->addRule(Form::MIN_LENGTH, 'Rodné číslo mít alespoň %d znaky', 10)
                ->setDisabled(true);

        $form->addText('mail', 'E-mailová adresa')
                ->addRule(Form::EMAIL, 'Nevalidní formát e-mailové adresy')
                ->addRule(Form::MAX_LENGTH, 'E-mailová adresa může mít nejvýše %d znaků', 60);

        $form->addText('phone', 'Telefonní číslo')
                ->addRule(Form::MAX_LENGTH, 'Telefonní číslo může mít nejvýše %d znaků', 20);

        $roles = array(
            'member' => 'člen',
            'competitor' => 'závodník',
            'trainer' => 'trenér',
            'coach' => 'kouč',
            'admin' => 'admin');
        $form->addMultiSelect('roles', 'Uživatelské role:', $roles)
                ->setRequired('Vyberte uživatelské role')
                ->setOption('description', Html::el('p')
                        ->setHtml('<i>Více rolí vyberete podržením klávesy <kbd>CTRL</kbd> a kliknutím na příslušné role</i>'));

        // Technical_grade is disabled and is manually enabled only for users
        // with appropriate roles, which are allowed to change technical_grades
        $form->addSelect('technical_grade', 'Technický stupeň', array('8.kyu', '7.kyu', '6.kyu', '5.kyu', '4.kyu', '3.kyu', '2.kyu', '1.kyu',
            '1.dan', '2.dan', '3.dan', '4.dan', '5.dan', '6.dan', '7.dan', '8.dan'));

        $form->addGroup("Trvalé bydliště");
        $adressText = "Vyplňte celou adresu bydliště";
        $form->addText('city', 'Město:')
                ->setRequired($adressText);
        $form->addText('address', 'Ulice a ČP:')
                ->setRequired($adressText);
        $form->addText('zipcode', 'PSČ:')
                ->setRequired($adressText)
                ->addFilter(function ($value) {
                    return str_replace(' ', '', $value);
                });

        $form->addSubmit('edit', 'Upravit členské údaje');
        \BootstrapRenderer::setBoostrapRendering($form);

        return $form;
    }

}

<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

/**
 * EditTrainerForm used for editing trainers.
 */
class EditTrainerForm extends Nette\Object {

    /**
     * Creates instance of the form.
     * 
     * @return Form
     */
    public function create() {

        $form = new Form;
        $form->addGroup('Trenérské informace');

        $form->addSelect('trainer_grade', 'Trenérská licence: ', array(1 => '1. třída', 2 => '2. třída', 3 => '3. třída'));
        $form->addText('licence_start', 'Platnost licence od:')->setAttribute('class', 'datepicker');
        $form->addText('licence_end', 'Platnost licence do:')->setAttribute('class', 'datepicker');


        $form->addSubmit('edit', 'Upravit trenérské údaje');
        \BootstrapRenderer::setBoostrapRendering($form, true);

        return $form;
    }

}

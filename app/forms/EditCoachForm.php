<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

/**
 * EditCoachForm used for editing coaches.
 */
class EditCoachForm extends Nette\Object {

    /**
     * Creates instance of the form.
     * 
     * @return Form
     */
    public function create() {
        $form = new Form;
        $form->addGroup('Informace kouče');

        $form->addText('coach_grade', 'Úroveň kouče: ')->setType('number')->setAttribute('min', 1)->setAttribute('max', 5);
        $form->addSelect('specialization', 'Specializace: ', array(0 => 'Žádná specializace', 1 => 'Kata', 2 => 'Kumite'));


        $form->addSubmit('edit', 'Upravit koučovy údaje');
        \BootstrapRenderer::setBoostrapRendering($form, true);

        return $form;
    }

}

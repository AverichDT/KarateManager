<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

/**
 * TrainingGroupForm used for creating and editing training groups.
 */
class TrainingGroupForm extends Nette\Object {

    /**
     * Creates instance of the form
     * 
     * @return Form
     */
    public function create() {
        $form = new Form;
        $form->addText('name', 'Název skupiny:')
                ->setMaxLength(45)
                ->setRequired();

        $form->addTextArea('description', 'Popis skupiny:')
                ->addRule(Form::MAX_LENGTH, 'Popisek je příliš dlouhý', 300);

        return $form;
    }

}

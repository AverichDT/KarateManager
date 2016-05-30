<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

/**
 * CompetitorsGroupForm used for creating and editing competitors groups.
 */
class CompetitorsGroupForm extends Nette\Object {

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

        $form->addText('performance_grade', 'Třída skupiny')
                ->setType('number')->setAttribute('min', 1)->setAttribute('max', 5);
        
        return $form;
    }

}

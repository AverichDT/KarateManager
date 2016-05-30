<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

/**
 * CompetitionForm used for creating and editing competitions.
 */
class CompetitionForm extends Nette\Object {

    /**
     * Creates instance of form
     * 
     * @return Form
     */
    public function create() {
        $form = new Form;
        $form->addText('title', 'Název soutěže:')
                ->setMaxLength(100)
                ->setRequired();

        $form->addTextArea('description', 'Popisek:')
                ->addRule(Form::MAX_LENGTH, 'Popisek je příliš dlouhý', 1000);

        $form->addText('place', 'Místo konání: ')->setAttribute('id', 'placeautocomplete')->setAttribute('class', 'placeautocomplete');

        $form->addText('start_time', 'Začátek soutěže: ')->setAttribute('class', 'datetimepicker');
        $form->addText('end_time', 'Konec soutěže: ')->setAttribute('class', 'datetimepicker');
        $form->addText('registration_deadline', 'Deadline registrací: ')->setAttribute('class', 'datetimepicker');
        $form->addText('competition_grade', 'Třída soutěže: ')->setType('number')->setAttribute('min', 1)->setAttribute('max', 5);

        
        

        return $form;
    }

}

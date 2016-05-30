<?php

namespace App\Forms;

use Nette;
use Nette\Utils\Html;
use Nette\Application\UI\Form;

/**
 * Form used for creating trainings
 */
class CreateTrainingForm extends Nette\Object {

    /**
     * Creates instance of form
     * 
     * @return Form
     */
    public function create() {
        $form = new Form;
        $form->addText('title', 'Název tréninku:')
                ->setMaxLength(100)
                ->setRequired();

        $form->addTextArea('description', 'Popisek:')
                ->addRule(Form::MAX_LENGTH, 'Popisek je příliš dlouhý', 800);

        $form->addText('place', 'Místo konání: ')->setAttribute('id', 'placeautocomplete')->setAttribute('class', 'placeautocomplete');

        $form->addText('start_time', 'Začátek tréninku: ')->setAttribute('class', 'datetimepicker');
        $form->addText('end_time', 'Konec tréninku: ')->setAttribute('class', 'datetimepicker');
        $form->addText('max_attendance', 'Maximální počet účastníků: ')->setType('number');
        $form->addSelect('min_technical_grade', 'Minimální technický stupeň', array('8.kyu', '7.kyu', '6.kyu', '5.kyu', '4.kyu', '3.kyu', '2.kyu', '1.kyu',
            '1.dan', '2.dan', '3.dan', '4.dan', '5.dan', '6.dan', '7.dan', '8.dan'));
        $form->addCheckbox('repeating', 'Opakující se trénink');
        $form->addText('repeating_interval', 'Opakovat po dnech:')
                ->setType('number')
                ->setOption('description', Html::el('p')
                        ->setHtml('<i>Nevyplňujte, pokud se trénink neopakuje.</i>'));
        $form->addText('repeating_end', 'Konec opakování: ')->setAttribute('class', 'datetimepicker')
                ->setOption('description', Html::el('p')
                        ->setHtml('<i>Nevyplňujte, pokud se trénink neopakuje.</i>'));

        $form->addSubmit('create', 'Přidat');

        \BootstrapRenderer::setBoostrapRendering($form);

        return $form;
    }

}

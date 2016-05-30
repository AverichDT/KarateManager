<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

/**
 * EditCompetitorForm used for editing competitors.
 */
class EditCompetitorForm extends Nette\Object {

    /**
     * Creates instance of the form.
     * 
     * @return Form
     */
    public function create() {
        $form = new Form;
        $form->addGroup('Závodnické informace');
        $form->addText('weight', 'Váha v kg:')
                ->setType('number')
                ->setAttribute('min', 20)->setAttribute('max', 150)->setAttribute('step', '0.5');

        $form->addText('height', 'Výška v cm:')
                ->setType('number')
                ->setAttribute('min', 60)->setAttribute('max', 250);

        $form->addSelect('specialization', 'Závodnická specializace', array(0 => 'Žádná specializace', 1 => 'Kata', 2 => 'Kumite'));

        $form->addCheckbox('cuma_stamp', 'Známka ČUBU');
        $form->addCheckbox('cka_stamp', 'Známka ČSK');

        $form->addText('performance_grade', 'Závodnická třída')
                ->setType('number')->setAttribute('min', 1)->setAttribute('max', 5);

        $form->addSubmit('edit', 'Upravit závodnické údaje');
        \BootstrapRenderer::setBoostrapRendering($form, true);

        return $form;
    }

}

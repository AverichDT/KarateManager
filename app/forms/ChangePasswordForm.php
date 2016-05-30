<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

/**
 * Form for changing password of user.
 */
class ChangePasswordForm extends Nette\Object {

    /**
     * Creates instance of form
     * 
     * @return Form
     */
    public function create() {
        $form = new Form;

        $form->addPassword('password', 'Heslo:')
                ->setRequired('Zvolte si heslo')
                ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 6);

        $form->addPassword('passwordVerify', 'Heslo pro kontrolu:')
                ->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
                ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);

        $form->addSubmit('change', 'Změnit heslo');


        \BootstrapRenderer::setBoostrapRendering($form);
        return $form;
    }

}

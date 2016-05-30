<?php

namespace App\Forms;

use Nette;
use Nette\Utils\Html;
use Nette\Application\UI\Form;

/**
 * EditUserForm used for editing user accounts.
 */
class EditUserForm extends Nette\Object {

    /**
     * Creates instance of the form.
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
        
        $roles = array(
            'member' => 'člen',
            'competitor' => 'závodník',
            'trainer' => 'trenér',
            'coach' => 'kouč',);
        $form->addMultiSelect('role', 'Uživatelské role:', $roles)
                ->setRequired('Vyberte uživatelské role')
                ->setOption('description', Html::el('p')
                        ->setHtml('<i>Více rolí vyberete podržením klávesy <kbd>CTRL</kbd> a kliknutím na příslušné role</i>'));


        $form->addSubmit('edit', 'Upravit');
        \BootstrapRenderer::setBoostrapRendering($form, true);

        return $form;
    }

}

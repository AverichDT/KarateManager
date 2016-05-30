<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


class SignFormFactory extends Nette\Object
{
	/** @var FormFactory */
	private $factory;

	/** @var User */
	private $user;


        /**
         * Constructs new SignFormFactory, which allows creation of form instances
         */
	public function __construct(FormFactory $factory, User $user)
	{
		$this->factory = $factory;
		$this->user = $user;
	}


	/**
         * Creates instance of the form
         * 
	 * @return Form
	 */
	public function create()
	{
		$form = $this->factory->create();
		$form->addText('username', 'Uživatelské jméno:')
			->setRequired('Prosím zadejte své uživatelské jméno.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Prosím zadejte heslo.');

		$form->addCheckbox('remember', 'Zůstat přihlášen');

		$form->addSubmit('send', 'Přihlásit');

		$form->onSuccess[] = array($this, 'formSucceeded');
                
//                \BootstrapRenderer::setBoostrapRendering($form);
		return $form;
	}


        /**
         * Logs user into the system in case of correct credentials.
         * Displays flashMessage if incorrect credentials.
         * 
         * @param Form $form submited SignForm
         * @param type $values submited values
         */
	public function formSucceeded(Form $form, $values)
	{
		if ($values->remember) {
			$this->user->setExpiration('14 days', FALSE);
		} else {
			$this->user->setExpiration(0, TRUE);
		}
		try {
			$this->user->login($values->username, $values->password);
//                        $this->user->setAuthorizator(new \Authorizator());
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError('The username or password you entered is incorrect.');
                        $form->getPresenter()->flashMessage('Zadané uživatelské jméno nebo heslo je nesprávné.', 'error');
		}
	}

}

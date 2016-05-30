<?php

namespace App\Presenters;

use Nette;
use App\Forms\SignFormFactory;

/**
 * Default Nette SignPresenter
 */
class SignPresenter extends BasePresenter
{
	/** @var SignFormFactory @inject */
	public $factory;


	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = $this->factory->create();
		$form->onSuccess[] = function ($form) {
			$form->getPresenter()->redirect('UserManagement:');
		};
		return $form;
	}


	public function actionOut()
	{
		$this->getUser()->logout();
	}

}

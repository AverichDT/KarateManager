<?php

namespace App\Presenters;

use Nette;
use App\Forms\SignFormFactory;

/**
 * Presenter for presenting homepage
 */
class HomepagePresenter extends BasePresenter {

    /** @var SignFormFactory @inject */
    public $factory;

    /**
     * Renders default Homepage view
     */
    public function renderDefault() {
        
    }

    /**
     * Creates instance of form for signing into system
     * 
     * @return Form sign in form
     */
    protected function createComponentSignInForm() {
        $form = $this->factory->create();
        $form->onSuccess[] = function ($form) {
            $form->getPresenter()->redirect('Profile:', $this->user->getId());
        };
        return $form;
    }

    /**
     * Action for logging out and redirecting back to homepage
     */
    public function actionOut() {
        $this->getUser()->logout();
        $this->redirect('Homepage:');
    }

}

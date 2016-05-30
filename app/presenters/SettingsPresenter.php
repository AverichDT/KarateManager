<?php

namespace App\Presenters;

use Nette;
use App\Forms\ChangePasswordForm,
    Nette\Application\UI\Form;
use App\Model\UserModel;

/**
 * Presenter for presenting settings page
 */
class SettingsPresenter extends BasePresenter {

    private $database;
    private $userModel;

    /**
     * Cretes new instance of presenter
     * 
     * @param Nette\Database\Context $database DB connection
     */
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
        $this->userModel = new UserModel($database);
    }

    /**
     * Checks requirements for accessing Settings page
     * 
     * @param type $element
     * @throws \Nette\Application\ForbiddenRequestException
     */
    public function checkRequirements($element) {
        parent::checkRequirements($element);
        if (!($this->user->isAllowed('Settings', 'view'))) {
            throw new \Nette\Application\ForbiddenRequestException;
        }
    }

    /**
     * Checks whether user is authorized to do specified action
     * 
     * @param type $action user action
     * @throws \Nette\Application\ForbiddenRequestException
     */
    private function checkAuhtorization($action) {
        if (!($this->user->isAllowed('Settings', $action))) {
            throw new \Nette\Application\ForbiddenRequestException;
        }
    }

    /**
     * Renders default view
     * 
     * @param int $id user's id
     */
    public function renderDefault($id) {
        $this->checkAuhtorization('manage');
        if ($id != $this->user->id && !$this->user->isInRole('admin')) {
            throw new \Nette\Application\ForbiddenRequestException;
        }
        if (!$this->userModel->get($id)) {
            throw new Nette\Application\BadRequestException;
        }
        $this->template->id = $id;
    }

    /**
     * Creates form for changing password
     * 
     * @return ChangePasswordForm
     */
    protected function createComponentChangePasswordForm() {
        $passwordForm = new ChangePasswordForm();
        $form = $passwordForm->create();
        $form->onSuccess[] = array($this, 'changePasswordSucceeded');
        $form->onSuccess[] = function () {
            $id = $this->getParameter('id');
            $this->flashMessage('Heslo úspěšně změněno.');
            $this->redirect('Profile:', $id);
        };
        return $form;
    }

    /**
     * Changes user's password on form submit
     * 
     * @param Form $form submitted form
     * @param array $values submitted values
     */
    public function changePasswordSucceeded(Form $form, $values) {
        $id = $this->getParameter('id');
        $this->userModel->changePassword($id, $values['password']);
    }

}

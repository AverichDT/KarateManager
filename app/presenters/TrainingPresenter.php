<?php

namespace App\Presenters;

use Nette,
    Nette\Application\UI\Form;
// Models
use App\Model\TrainingModel,
    App\Model\TrainingGroupModel,
    App\Model\MemberModel;
// Forms
use App\Forms\CreateTrainingForm;
use App\Forms\TrainingGroupForm;
use DateTime;

/**
 * Description of TrainingPresenter
 *
 * @author Petr
 */
class TrainingPresenter extends BasePresenter {

    private $database;
    private $trainingModel;
    private $groupModel;
    private $memberModel;

    /**
     * Checks requirements for accessing Training page
     * 
     * @param type $element
     * @throws \Nette\Application\ForbiddenRequestException
     */
    public function checkRequirements($element) {
        parent::checkRequirements($element);
        if (!($this->user->isAllowed('Training', 'view'))) {
            throw new \Nette\Application\ForbiddenRequestException;
        }
    }

    /**
     * Checks whether user is authorized to do specified action
     * 
     * @param type $action user action
     * @throws \Nette\Application\ForbiddenRequestException
     */
    private function checkAuthorization($action) {
        if (!($this->user->isAllowed('Training', $action))) {
            throw new \Nette\Application\ForbiddenRequestException;
        }
    }

    /**
     * Creates new instance of TrainingPresenter
     * 
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
        $this->trainingModel = new TrainingModel($database);
        $this->groupModel = new TrainingGroupModel($database);
        $this->memberModel = new MemberModel($database);
    }

    /**
     * Renders training create view
     */
    public function renderCreate() {
        $this->checkAuthorization('manage');
    }

    /**
     * Create instance of form for creating trainings
     *  
     * @return CreateTrainingForm
     */
    protected function createComponentCreateTrainingForm() {
        $createTrainingForm = new CreateTrainingForm();
        $form = $createTrainingForm->create();
        $form->onSuccess[] = array($this, 'createTrainingSucceeded');
        $form->onSuccess[] = function () {
            $this->flashMessage('Trénink(y) úspěšně přidán(y).');
            $this->redirect('Training:default');
        };
        return $form;
    }

    /**
     * Creates new training(s)
     * 
     * @param Form $form submitted form
     * @param type $values submitted values
     */
    public function createTrainingSucceeded(Form $form, $values) {
        $this->trainingModel->create($values);
    }

    /**
     * Renders default training view 
     */
    public function renderDefault() {
        $this->checkAuthorization('view');
        $this->template->member = $this->memberModel->getByUserId($this->user->id);
        if (!isset($this->template->trainings)) {
            $this->setCurrentDateToTemplate();
            $this->template->trainings = $this->trainingModel->getMonthTrainings($this->template->year, $this->template->month);
        }
    }

    /**
     * Handles AJAX requests for deleting trainings
     * 
     * @param int $id training's id
     * @param int $year currently viewed year
     * @param int $month currently viewed month
     */
    public function handleDelete($id, $year = null, $month = null, $series_id = null) {
        $this->checkAuthorization('manage');
        $this->trainingModel->delete($id, $series_id);
        $this->setDateToTemplate($year, $month);
        $this->template->trainings = $this->trainingModel->getMonthTrainings($this->template->year, $this->template->month);
        $this->redrawControl('trainings');
    }

    /**
     * Signs currently logged user for specified training(s)
     * 
     * @param type $id training's id
     * @param type $year currently displayed year
     * @param type $month currently displayed month
     * @param type $series_id training series id
     */
    public function handleSignMyself($id, $year, $month, $series_id = null) {
        $this->checkAuthorization('view');
        $member = $this->memberModel->getByUserId($this->user->id);
        $this->trainingModel->signUp($member->id, $id, $series_id);
        $this->setDateToTemplate($year, $month);
        $this->template->trainings = $this->trainingModel->getMonthTrainings($year, $month);
        $this->redrawControl('trainings');
    }

    /**
     * Unsigns currently logged user from specified training(s)
     * 
     * @param type $id training's id
     * @param type $year currently displayed year
     * @param type $month currently displayed month
     * @param type $series_id training series id
     */
    public function handleUnsignMyself($id, $year, $month, $series_id = null) {
        $this->checkAuthorization('view');
        $member = $this->memberModel->getByUserId($this->user->id);
        $this->trainingModel->unSign($member->id, $id, $series_id);
        $this->setDateToTemplate($year, $month);
        $this->template->trainings = $this->trainingModel->getMonthTrainings($year, $month);
        $this->redrawControl('trainings');
    }

    /**
     * Handles AJAX requests for showing previous month trainings
     * 
     * @param int $year calendar year
     * @param int $month calendar month
     */
    public function handlePreviousMonth($year, $month) {
        if ($month == 1) {
            $this->template->year = --$year;
            $this->template->month = 12;
        } else {
            $this->template->year = $year;
            $this->template->month = --$month;
        }
        $this->template->trainings = $this->trainingModel->getMonthTrainings($this->template->year, $this->template->month);
        $this->redrawControl('trainings');
    }

    /**
     * Handles AJAX requests for showing next month trainings
     * 
     * @param int $year calendar year
     * @param int $month calendar month
     */
    public function handleNextMonth($year, $month) {
        if ($month == 12) {
            $this->template->year = ++$year;
            $this->template->month = 1;
        } else {
            ++$month;
            $this->template->year = $year;
            $this->template->month = $month;
        }
        $this->template->trainings = $this->trainingModel->getMonthTrainings($this->template->year, $this->template->month);
        $this->redrawControl('trainings');
    }

    /**
     * Renders training groups creation view
     */
    public function renderCreateGroup() {
        
    }

    /**
     * Creates new instance of TrainingGroupForm for creating training groups
     * 
     * @return TrainingGroupForm
     */
    protected function createComponentCreateTrainingGroupForm() {
        $groupForm = new TrainingGroupForm();
        $form = $groupForm->create();
        $form->addSubmit('create', 'Vytvořit');
        \BootstrapRenderer::setBoostrapRendering($form);
        $form->onSuccess[] = array($this, 'createGroupSucceeded');
        $form->onSuccess[] = function() {
            $this->flashMessage('Skupina přidána.');
            $this->redirect('Training:groups');
        };


        return $form;
    }

    public function createGroupSucceeded(Form $form, $values) {
        $this->groupModel->create($values);
    }

    /**
     * Renders training groups edit view
     */
    public function renderEditGroup($id) {
        $this->checkAuthorization('manage');
        $this->template->id = $id;
        $this->template->group = $this->groupModel->get($id);
        if (!$this->template->group) {
            throw new Nette\Application\BadRequestException;
        }
    }

    /**
     * Creates new instance of TrainingGroupForm for editing training groups
     * 
     * @return TrainingGroupForm
     */
    protected function createComponentEditTrainingGroupForm() {
        $groupForm = new TrainingGroupForm();
        $form = $groupForm->create();
        $form->addSubmit('edit', 'Upravit');
        \BootstrapRenderer::setBoostrapRendering($form);
        $form->onSuccess[] = array($this, 'editGroupSucceeded');
        $form->onSuccess[] = function() {
            $this->flashMessage('Skupina upravena.');
            $this->redirect('Training:groups');
        };
        $id = $this->getParameter('id');
        $group = $this->groupModel->get($id);
        $form->setDefaults($group->toArray());

        return $form;
    }

    public function editGroupSucceeded(Form $form, $values) {
        $id = $this->getParameter('id');
        $this->groupModel->update($id, $values);
    }

    /**
     * Renders training groups view
     */
    public function renderGroups() {
        $this->checkAuthorization('manage');
        $this->template->groups = $this->groupModel->getAll();
    }

    /**
     * Handle AJAX requests for removing members from training groups
     * 
     * @param int $memberId member's id
     * @param int $groupId group's id
     */
    public function handleRemoveFromGroup($memberId, $groupId) {
        $this->checkAuthorization('manage');
        $this->groupModel->removeMemberFromGroup($memberId, $groupId);
        $this->setGroupMembersToTemplate($groupId);
        $this->redrawControl('membersTable');
    }

    /**
     * Handle AJAX requests for adding members to training groups
     * 
     * @param int $memberId members's id
     * @param int $groupId group's id
     */
    public function handleAddToGroup($memberId, $groupId) {
        $this->checkAuthorization('manage');
        $this->groupModel->addMemberToGroup($memberId, $groupId);
        $this->setGroupMembersToTemplate($groupId);
        $this->redrawControl('membersTable');
    }

    /**
     * Sets group's members to template
     * 
     * @param int $id group's id
     */
    private function setGroupMembersToTemplate($id) {
        $this->template->selectedGroup = $this->groupModel->get($id);
        $this->template->members = $this->groupModel->getMembers($id);
        $this->template->nonMembers = $this->groupModel->getNonMembers($id);
    }

    /**
     * Handles AJAX request for selecting training group
     * 
     * @param int $id group's id
     */
    public function handleSelectGroup($id) {
        $this->template->selectedGroup = $this->groupModel->get($id);
        $this->template->members = $this->groupModel->getMembers($id);
        $this->template->nonMembers = $this->groupModel->getNonMembers($id);
        $this->redrawControl('membersTable');
        $this->redrawControl('selectedGroup');
    }

    /**
     * Handles AJAX request for deleting training group
     * 
     * @param int $id group's id
     */
    public function handleDeleteGroup($id) {
        $this->checkAuthorization('manage');
        $this->groupModel->delete($id);
        $this->redrawControl('groups');
        $this->redrawControl('selectedGroup');
        $this->redrawControl('membersTable');
    }

    /**
     * Handles AJAX request for showing training group
     * 
     * @param int $groupId group's id
     * @param int $trainingId training's id
     */
    public function handleShowGroup($groupId, $trainingId) {
        $this->setGroupAttendantsToTemplate($groupId, $trainingId);
        $this->redrawControl('attendanceTable');
    }

    /**
     * Handles AJAX requests for training unsign
     * 
     * @param int $memberId member's id
     * @param int $trainingId training's id
     * @param int $groupId currently selected group
     */
    public function handleUnSign($memberId, $trainingId, $groupId = null, $series_id = null) {
        $this->checkAuthorization('view');
        $this->trainingModel->unSign($memberId, $trainingId, $series_id);
        if ($groupId == null) {
            $this->template->attendants = $this->trainingModel->getAttendants($trainingId);
            $this->template->nonAttendants = $this->trainingModel->getNonAttendants($trainingId);
            $this->redrawControl('attendanceTable');
        } else {
            $this->handleShowGroup($groupId, $trainingId);
        }
    }

    /**
     * Handles AJAX requests for training sign up
     * 
     * @param int $memberId members's id
     * @param int $trainingId training's id
     * @param int $groupId currently selected group
     */
    public function handleSignUp($memberId, $trainingId, $groupId = null, $series_id = null) {
        $this->checkAuthorization('view');
        $this->trainingModel->signUp($memberId, $trainingId, $series_id);
        if ($groupId == null) {
            $this->template->attendants = $this->trainingModel->getAttendants($trainingId);
            $this->template->nonAttendants = $this->trainingModel->getNonAttendants($trainingId);
            $this->redrawControl('attendanceTable');
        } else {
            $this->handleShowGroup($groupId, $trainingId);
        }
    }

    /**
     * Renders training attendance view
     * 
     * @param type $id training's id
     * @throws Nette\Application\BadRequestException
     */
    public function renderAttendance($id) {
        $this->checkAuthorization('manage');
        $this->template->id = $id;
        $this->template->training = $this->trainingModel->get($id);
        if (!$this->template->training) {
            throw new Nette\Application\BadRequestException;
        }
        if (!isset($this->template->attendants)) {
            $this->template->attendants = $this->trainingModel->getAttendants($id);
            $this->template->nonAttendants = $this->trainingModel->getNonAttendants($id);
        }
        $this->template->trainingGroups = $this->groupModel->getAll();
    }

    /**
     * Handles AJAX requests for training group sign up
     * 
     * @param type $groupId group's id
     * @param type $trainingId training's id
     * @param type $series_id training series id
     */
    public function handleGroupSignUp($groupId, $trainingId, $series_id = null) {
        $this->checkAuthorization('manage');
        $this->groupModel->trainingSignUp($groupId, $trainingId, $series_id);
        $this->setGroupAttendantsToTemplate($groupId, $trainingId);
        $this->redrawControl('attendanceTable');
    }

    /**
     * Handles AJAX requests for training group unsign
     * 
     * @param type $groupId group's id
     * @param type $trainingId training's id
     * @param type $series_id training series id
     */
    public function handleGroupUnSign($groupId, $trainingId, $series_id = null) {
        $this->checkAuthorization('manage');
        $this->groupModel->trainingUnSign($groupId, $trainingId, $series_id);
        $this->setGroupAttendantsToTemplate($groupId, $trainingId);
        $this->redrawControl('attendanceTable');
    }

    /**
     * Handles AJAX request for hiding group
     */
    public function handleHideGroup() {
        $this->redrawControl('attendanceTable');
    }

    /**
     * Calculates age from birthdate
     * 
     * @param DateTime $birthdate birth date
     * @return int age
     */
    public function getAge($birthdate) {
        return $this->memberModel->calculateAge($birthdate);
    }

    /**
     * Resolves technical grade enmuration into technical grade text
     * 
     * @param int $technicalGrade technical grade enum
     * @return string technical grade text
     */
    public function getTechnicalGradeText($technicalGrade) {
        return $this->memberModel->getTechnicalGradeText($technicalGrade);
    }

    /**
     * Counts number of members attending specified training
     * 
     * @param int $id training's id
     * @return int attendants count
     */
    public function getAttendingCount($id) {
        return $this->trainingModel->getCountOfAttendingMembers($id);
    }

    /**
     * Checks wheter currently logged user is attending specified training
     * 
     * @param type $trainingId training's id
     * @return boolean true if attending, false otherwise
     */
    public function isUserAttending($trainingId) {
        $member = $this->memberModel->getByUserId($this->user->id);
        return $this->trainingModel->isMemberAttending($member->id, $trainingId);
    }

    private function setGroupAttendantsToTemplate($groupId, $trainingId) {
        $this->template->selectedGroup = $this->groupModel->get($groupId);
        $this->template->attendants = $this->trainingModel->getGroupAttendants($groupId, $trainingId);
        $this->template->nonAttendants = $this->trainingModel->getGroupNonAttendants($groupId, $trainingId);
    }

    private function setDateToTemplate($year, $month) {
        if (is_null($year)) {
            $this->setCurrentDateToTemplate();
        } else {
            $this->template->year = $year;
            $this->template->month = $month;
        }
    }

    private function setCurrentDateToTemplate() {
        $date = new DateTime();
        $this->template->year = $date->format('Y');
        $this->template->month = $date->format('n');
    }

}

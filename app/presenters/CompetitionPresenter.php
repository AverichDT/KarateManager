<?php

namespace App\Presenters;

use Nette;
use App\Model\CompetititionModel,
    App\Model\CompetitionGroupModel,
    App\Forms\CompetitionForm,
    App\Model\CompetitorModel,
    App\Forms\CompetitorsGroupForm,
    Nette\Application\UI\Form;
use DateTime;

/**
 * CompetitionPresenter fomr presenting competitions
 *
 * @author Petr
 */
class CompetitionPresenter extends BasePresenter {

    private $database;
    private $competitionModel;
    private $competitorModel;
    private $groupModel;

    /**
     * Checks requirements for accessing Competition page
     * 
     * @param type $element
     * @throws \Nette\Application\ForbiddenRequestException
     */
    public function checkRequirements($element) {
        parent::checkRequirements($element);
        if (!($this->user->isAllowed('Competition', 'view'))) {
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
        if (!($this->user->isAllowed('Competition', $action))) {
            throw new \Nette\Application\ForbiddenRequestException;
        }
    }

    /**
     * Creates new instance of CompetitionPresneter
     * 
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
        $this->competitionModel = new CompetititionModel($database);
        $this->competitorModel = new CompetitorModel($database);
        $this->groupModel = new CompetitionGroupModel($database);
    }

    /**
     * Renders default competition view (overview)
     */
    public function renderDefault() {
        $this->checkAuthorization('view');
        if (!isset($this->template->competitions)) {
            $this->setCurrentDateToTemplate();
            $this->template->competitions = $this->competitionModel->getMonthCompetitions($this->template->year, $this->template->month);
        }
        if ($this->user->isInRole('competitor')) {
            $this->template->competitor = $this->competitorModel->getByUserId($this->user->id);
        }
    }

    /**
     * Renders comptition edit view
     * 
     * @param int $id competition's id
     * @throws Nette\Application\BadRequestException
     */
    public function renderEdit($id) {
        $this->checkAuthorization('manage');
        $this->template->id = $id;
        $this->template->competition = $this->competitionModel->get($id);
        if (!$this->template->competition) {
            throw new Nette\Application\BadRequestException;
        }
    }

    /**
     * Renderers competition participation view
     * 
     * @param int $id competition's id
     * @throws Nette\Application\BadRequestException
     */
    public function renderParticipation($id) {
        $this->checkAuthorization('manage');
        $this->template->id = $id;
        $this->template->competition = $this->competitionModel->get($id);
        if (!$this->template->competition) {
            throw new Nette\Application\BadRequestException;
        }
        if (!isset($this->template->participants)) {
            $this->template->participants = $this->competitionModel->getParticipants($id);
            $this->template->competitors = $this->competitionModel->getNonParticipants($id);
        }
        $this->template->competitionGroups = $this->groupModel->getAll();
    }

    /**
     * Renders competition groups view
     */
    public function renderGroups() {
        $this->checkAuthorization('manage');
        $this->template->groups = $this->groupModel->getAll();
    }

    /**
     * Renders competition create view
     */
    public function renderCreate() {
        $this->checkAuthorization('manage');
    }

    /**
     * Renders competition groups create view
     */
    public function renderCreateGroup() {
        $this->checkAuthorization('manage');
    }

    /**
     * Renders competition group edit view
     * 
     * @param int $id competition group's id
     * @throws Nette\Application\BadRequestException
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
     * Creates instance of form for competition group editing
     * 
     * @return CompetitorsGroupForm competitors group edition form
     */
    protected function createComponentEditCompetitorsGroupForm() {
        $createForm = new CompetitorsGroupForm();
        $form = $createForm->create();
        $form->addSubmit('update', 'Upravit');
        $form->onSuccess[] = array($this, 'editGroupSucceeded');
        $form->onSuccess[] = function() {
            $this->flashMessage('Soutěžní skupina úspěšně upravena');
            $this->redirect('Competition:groups');
        };
        $id = $this->getParameter('id');
        $group = $this->groupModel->get($id);
        $form->setDefaults($group->toArray());

        \BootstrapRenderer::setBoostrapRendering($form);
        return $form;
    }

    /**
     * Creates instace of form for competition group creating
     * 
     * @return CompetitorsGroupForm
     */
    protected function createComponentCreateCompetitorsGroupForm() {
        $createForm = new CompetitorsGroupForm();
        $form = $createForm->create();
        $form->addSubmit('create', 'Vytvořit');
        $form->onSuccess[] = array($this, 'createGroupSucceeded');
        $form->onSuccess[] = function() {
            $this->flashMessage('Soutěžní skupina úspěšně vytvořena');
            $this->redirect('Competition:groups');
        };

        \BootstrapRenderer::setBoostrapRendering($form);
        return $form;
    }

    /**
     * Creates instance of form for competition creating
     * 
     * @return CompetitionForm
     */
    protected function createComponentCreateCompetitionForm() {
        $createForm = new CompetitionForm();
        $form = $createForm->create();
        $form->addSubmit('create', 'Vytvořit');
        $form->onSuccess[] = array($this, 'createSucceeded');
        $form->onSuccess[] = function() {
            $this->redirect('Competition:');
        };

        \BootstrapRenderer::setBoostrapRendering($form);
        return $form;
    }

    /**
     * Creates competition group
     * 
     * @param Form $form submitted form
     * @param array $values submitted values
     */
    public function createGroupSucceeded(Form $form, $values) {
        $this->groupModel->create($values);
    }

    /**
     * Updates competition group with new values
     * 
     * @param Form $form submitted form
     * @param array $values submitted values
     */
    public function editGroupSucceeded(Form $form, $values) {
        $id = $this->getParameter('id');
        $this->groupModel->update($id, $values);
    }

    /**
     * Creates competition
     * 
     * @param Form $form submitted form
     * @param array $values submitted values
     */
    public function createSucceeded(Form $form, $values) {
        $this->competitionModel->create($values);
    }

    /**
     * Creates instance of form for competition editing
     * 
     * @return CompetitionForm
     */
    protected function createComponentEditCompetitionForm() {
        $editForm = new CompetitionForm();
        $form = $editForm->create();
        $form->onSuccess[] = array($this, 'editSucceeded');
        $form->onSuccess[] = function () {
            $this->redirect('Competition:');
        };
        $form->addSubmit('update', 'Upravit');
        if (isset($this->template->competition)) {
            $form->setDefaults($this->template->competition->toArray());
        }
        \BootstrapRenderer::setBoostrapRendering($form);
        return $form;
    }

    /**
     * Updates competition with new values
     * 
     * @param Form $form submitted form
     * @param array $values submitted values
     */
    public function editSucceeded(Form $form, $values) {
        $id = $this->getParameter('id');
        $this->competitionModel->update($values, $id);
    }

    /**
     * Handles AJAX requests for showing previous month competitions
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
        $this->template->competitions = $this->competitionModel->getMonthCompetitions($this->template->year, $this->template->month);
        $this->redrawControl('competitions');
    }

    /**
     * Handles AJAX requests for showing next month competitions
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
        $this->template->competitions = $this->competitionModel->getMonthCompetitions($this->template->year, $this->template->month);
        $this->redrawControl('competitions');
    }

    public function handleSignMyself($id, $year, $month) {
        $this->checkAuthorization('view');
        $competitor = $this->competitorModel->getByUserId($this->user->id);
        $this->competitionModel->signUp($competitor->id, $id);
        $this->setDateToTemplate($year, $month);
        $this->template->competitions = $this->competitionModel->getMonthCompetitions($year, $month);
        $this->redrawControl('competitions');
    }

    public function handleUnsignMyself($id, $year, $month) {
        $this->checkAuthorization('view');
        $competitor = $this->competitorModel->getByUserId($this->user->id);
        $this->competitionModel->unSign($competitor->id, $id);
        $this->setDateToTemplate($year, $month);
        $this->template->competitions = $this->competitionModel->getMonthCompetitions($year, $month);
        $this->redrawControl('competitions');
    }

    /**
     * Handles AJAX requests for deleting competitions
     * 
     * @param int $id competition's id
     * @param int $year currently viewed year
     * @param int $month currently viewed month
     */
    public function handleDelete($id, $year = null, $month = null) {
        $this->checkAuthorization('manage');
        $this->competitionModel->delete($id);
        $this->setDateToTemplate($year, $month);
        $this->template->competitions = $this->competitionModel->getMonthCompetitions($this->template->year, $this->template->month);
        $this->redrawControl('competitions');
    }

    /**
     * Handles AJAX requests for competition unsign
     * 
     * @param int $competitorId competitor's id
     * @param int $competitionId competition's id
     * @param int $groupId currently selected group
     */
    public function handleUnSign($competitorId, $competitionId, $groupId = null) {
        $this->checkAuthorization('view');
        $this->competitionModel->unSign($competitorId, $competitionId);
        if ($groupId == null) {
            $this->template->participants = $this->competitionModel->getParticipants($competitionId);
            $this->template->competitors = $this->competitionModel->getNonParticipants($competitionId);
            $this->redrawControl('participantsTable');
        } else {
            $this->handleShowGroup($groupId, $competitionId);
        }
    }

    /**
     * Handles AJAX requests for competition sing up
     * 
     * @param int $competitorId competitor's id
     * @param int $competitionId competition's id
     * @param int $groupId currently selected group
     */
    public function handleSignUp($competitorId, $competitionId, $groupId = null) {
        $this->checkAuthorization('view');
        $this->competitionModel->signUp($competitorId, $competitionId);
        if ($groupId == null) {
            $this->template->participants = $this->competitionModel->getParticipants($competitionId);
            $this->template->competitors = $this->competitionModel->getNonParticipants($competitionId);
            $this->redrawControl('participantsTable');
        } else {
            $this->handleShowGroup($groupId, $competitionId);
        }
    }

    /**
     * Handles AJAX request for selecting competition group
     * 
     * @param int $id group's id
     */
    public function handleSelectGroup($id) {
        $this->template->selectedGroup = $this->groupModel->get($id);
        $this->template->members = $this->groupModel->getMembers($id);
        $this->template->nonMembers = $this->groupModel->getNonMembers($id);
        $this->redrawControl('competitorsTable');
        $this->redrawControl('selectedGroup');
    }

    /**
     * Handle AJAX requests for removing competitors from competition groups
     * 
     * @param int $competitorId competitor's id
     * @param int $groupId group's id
     */
    public function handleRemoveFromGroup($competitorId, $groupId) {
        $this->checkAuthorization('manage');
        $this->groupModel->removeCompetitorFromGroup($competitorId, $groupId);
        $this->setGroupMembersToTemplate($groupId);
        $this->redrawControl('competitorsTable');
    }

    /**
     * Handle AJAX requests for adding competitors to competition groups
     * 
     * @param int $competitorId competitor's id
     * @param int $groupId group's id
     */
    public function handleAddToGroup($competitorId, $groupId) {
        $this->checkAuthorization('manage');
        $this->groupModel->addCompetitorToGroup($competitorId, $groupId);
        $this->setGroupMembersToTemplate($groupId);
        $this->redrawControl('competitorsTable');
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
     * Handles AJAX request for showing competition group
     * 
     * @param int $groupId group's id
     * @param int $competitionId competition's id
     */
    public function handleShowGroup($groupId, $competitionId) {
        $this->setGroupParticipantsToTemplate($groupId, $competitionId);
        $this->redrawControl('participantsTable');
    }

    /**
     * Handles AJAX requests for group sign up for competitions
     * 
     * @param int $groupId group's id
     * @param int $competitionId competition's id
     */
    public function handleGroupSignUp($groupId, $competitionId) {
        $this->checkAuthorization('manage');
        $this->groupModel->competitionSignUp($groupId, $competitionId);
        $this->setGroupParticipantsToTemplate($groupId, $competitionId);
        $this->redrawControl('participantsTable');
    }

    /**
     * Handles AJAX requests for group unsign from competitions
     * 
     * @param int $groupId group's id
     * @param int $competitionId competition's id
     */
    public function handleGroupUnSign($groupId, $competitionId) {
        $this->checkAuthorization('manage');
        $this->groupModel->competitionUnSign($groupId, $competitionId);
        $this->setGroupParticipantsToTemplate($groupId, $competitionId);
        $this->redrawControl('participantsTable');
    }

    private function setGroupParticipantsToTemplate($groupId, $competitionId) {
        $this->template->selectedGroup = $this->groupModel->get($groupId);
        $this->template->participants = $this->competitionModel->getGroupParticipants($groupId, $competitionId);
        $this->template->competitors = $this->competitionModel->getGroupNonParticipants($groupId, $competitionId);
    }

    /**
     * Handles AJAX request for deleting competition group
     * 
     * @param int $id group's id
     */
    public function handleDeleteGroup($id) {
        $this->checkAuthorization('manage');
        $this->groupModel->delete($id);
        $this->redrawControl('groups');
        $this->redrawControl('selectedGroup');
        $this->redrawControl('competitorsTable');
    }

    /**
     * Handles AJAX request for hiding group
     */
    public function handleHideGroup() {
        $this->redrawControl('participantsTable');
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

    /**
     * Calculates age from birth date
     * 
     * @param DateTime $birthDate birth date
     * @return int age
     */
    public function getAge($birthDate) {
        return \App\Model\MemberModel::calculateAge($birthDate);
    }

    /**
     * Creates WKF category string from competitor values
     * 
     * @param array $competitor competitor values
     * @return string WKF category string
     */
    public function getCompetitorCategory($competitor) {
        return \CompetitionCategories::getCategory($competitor->gender, $this->getAge($competitor->birthdate), $competitor->weight);
    }

    /**
     * Checks wheter currently logged user is attending specified competition
     * 
     * @param type $competitionId competition's id
     * @return boolean true if attending, false otherwise
     */
    public function isUserAttending($competitionId) {
        $competitor = $this->competitorModel->getByUserId($this->user->id);
        return $this->competitionModel->isCompetitorAttending($competitor->id, $competitionId);
    }

}

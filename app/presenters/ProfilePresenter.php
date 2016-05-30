<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\MemberModel;
use App\Model\CompetitorModel;
use App\Model\TrainerModel;
use App\Model\CoachModel;
use App\Forms;
use DateTime;

/**
 * Presenter for presenting profile page
 *
 * @author Petr
 */
class ProfilePresenter extends BasePresenter {

    /** @var Nette\Database\Context */
    private $database;
    private $memberModel;
    private $competitorModel;
    private $coachModel;
    private $trainerModel;

    /**
     * Creates new instance of ProfilePresenter
     * 
     * @param Nette\Database\Context $database DB connection
     */
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
        $this->memberModel = new MemberModel($database);
        $this->competitorModel = new CompetitorModel($database);
        $this->trainerModel = new TrainerModel($database);
        $this->coachModel = new CoachModel($database);
    }

    /**
     * Checks requirements for accessing Profile page
     * 
     * @param type $element
     * @throws \Nette\Application\ForbiddenRequestException
     */
    public function checkRequirements($element) {
        parent::checkRequirements($element);
        if (!($this->user->isAllowed('Profile', 'view'))) {
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
        if (!($this->user->isAllowed('Profile', $action))) {
            throw new \Nette\Application\ForbiddenRequestException;
        }
    }

    /**
     * Render's default profile view
     * 
     * @param int $id member's id
     * @throws Nette\Application\BadRequestException
     * @throws Nette\Application\ForbiddenRequestException
     */
    public function renderDefault($id) {
        $this->checkAuthorization('view');
        if (!$this->user->isInRole('admin') && !$this->user->isInRole('trainer') && !$this->user->isInRole('coach') && $this->user->id != $id) {
            throw new Nette\Application\ForbiddenRequestException;
        }
        $member = $this->memberModel->get($id);
        if (!$member) {
            throw new Nette\Application\BadRequestException;
        }
        $this->template->member = $member;
        $this->template->competitor = $this->competitorModel->getByMemberId($member->id);
        $this->template->coach = $this->coachModel->getByMemberId($member->id);
        $this->template->trainer = $this->trainerModel->getByMemberId($member->id);
        $this->template->age = $this->memberModel->calculateAge($member->birthdate);
        $this->template->technicalGrade = $this->memberModel->getTechnicalGradeText($member->technical_grade);
        if ($this->template->competitor) {
            $this->template->category = \CompetitionCategories::getCategory($member->gender, $this->template->age, $this->template->competitor->weight);
        }
        $this->template->roles = json_decode($member->roles);

        if (!isset($this->template->trainings)) {
            $date = new DateTime();
            $this->template->year = $year = $date->format('Y');
            $this->template->month = $month = $date->format('n');
            $this->template->trainings = $this->memberModel->getMonthTrainings($member->id, $year, $month);
            if ($this->template->competitor) {
                $this->template->competitions = $this->competitorModel->getMonthCompetitions($this->template->competitor->id, $year, $month);
            } else {
                $this->template->competitions = [];
            }
        }
    }

    /**
     * Handles AJAX requests for showing previous month events
     * 
     * @param int $year calendar year
     * @param int $month calendar month
     * @param int $memberId member's id
     * @param int $competitorId competitor's id
     */
    public function handlePreviousMonth($year, $month, $memberId, $competitorId = null) {
        if ($month == 1) {
            $this->template->year = --$year;
            $this->template->month = 12;
        } else {
            $this->template->year = $year;
            $this->template->month = --$month;
        }
        $this->template->trainings = $this->memberModel->getMonthTrainings($memberId, $year, $month);
        if ($competitorId) {
            $this->template->competitions = $this->competitorModel->getMonthCompetitions($competitorId, $year, $month);
        } else {
            $this->template->competitions = [];
        }
        $this->redrawControl('attendingEvents');
    }

    /**
     * Handles AJAX requests for showing next month events
     * 
     * @param int $year calendar year
     * @param int $month calendar month
     * @param int $memberId member's id
     * @param int $competitorId competitor's id
     */
    public function handleNextMonth($year, $month, $memberId, $competitorId = null) {
        if ($month == 12) {
            $this->template->year = ++$year;
            $this->template->month = 1;
        } else {
            ++$month;
            $this->template->year = $year;
            $this->template->month = $month;
        }
        $this->template->trainings = $this->memberModel->getMonthTrainings($memberId, $year, $month);
        if ($competitorId) {
            $this->template->competitions = $this->competitorModel->getMonthCompetitions($competitorId, $year, $month);
        } else {
            $this->template->competitions = [];
        }
        $this->redrawControl('attendingEvents');
    }

    /**
     * Render's edit profile view
     * 
     * @param int $id member's id
     * @throws Nette\Application\BadRequestException
     * @throws Nette\Application\ForbiddenRequestException
     */
    public function renderEdit($id) {
        $this->checkAuthorization('manage');
        if (!$this->user->isInRole('admin') && !$this->user->isInRole('trainer') && !$this->user->isInRole('coach') && $this->user->id != $id) {
            throw new Nette\Application\ForbiddenRequestException;
        }
        $this->template->member = $this->memberModel->get($id);
        if (!$this->template->member) {
            throw new Nette\Application\BadRequestException;
        }
        $this->template->roles = $this->memberModel->getMemberRoles($id);
    }

    /**
     * Creates instance of form for editing members
     * 
     * @return EditMemberForm
     */
    protected function createComponentEditMemberForm() {
        $editMemberForm = new Forms\EditMemberForm();
        $form = $editMemberForm->create();
        $id = $this->getParameter('id');
        $member = $this->memberModel->get($id);
        $memberArray = $member->toArray();
        $memberArray['roles'] = json_decode($memberArray['roles']);

        if (!$this->user->isInRole('admin')) {
            $form['technical_grade']->setDisabled(true);
            $form['roles']->setDisabled(true);
        }

        $form->setDefaults($memberArray);
        $form->onSuccess[] = array($this, "editMemberSucceeded");

        return $form;
    }

    /**
     * Updates member on form submit
     * 
     * @param Form $form submitted form
     * @param array $values submitted values
     */
    public function editMemberSucceeded(Form $form, $values) {
        $id = $this->getParameter('id');
        $this->memberModel->update($id, $values);
        $this->redirect('Profile:', $id);
    }

    /**
     * Creates form for editing competitor
     * 
     * @return EditCompetitorForm
     */
    protected function createComponentEditCompetitorForm() {
        $editCompetitorForm = new Forms\EditCompetitorForm();
        $form = $editCompetitorForm->create();
        $id = $this->getParameter('id');
        if (!$this->user->isInRole('coach') && !$this->user->isInRole('trainer') && !$this->user->isInRole('admin')) {
            $form['performance_grade']->setDisabled(true);
        }
        $form->setDefaults($this->competitorModel->getByMemberId($id));
        $form->onSuccess[] = array($this, "editCompetitorSucceeded");

        return $form;
    }

    /**
     * Updates competitor on form submit
     * 
     * @param Form $form submitted form
     * @param array $values submitted values
     */
    public function editCompetitorSucceeded(Form $form, $values) {
        $id = $this->getParameter('id');
        $this->competitorModel->update($id, $values);
        $this->redirect('Profile:', $id);
    }

    /**
     * Creates form for updating trainer
     * 
     * @return EditTrainerForm
     */
    protected function createComponentEditTrainerForm() {
        $editTrainerForm = new Forms\EditTrainerForm();
        $form = $editTrainerForm->create();
        $id = $this->getParameter('id');
        $form->setDefaults($this->trainerModel->getByMemberId($id));
        $form->onSuccess[] = array($this, "editTrainerSucceeded");

        return $form;
    }

    /**
     * Updates trainer on form submit
     * 
     * @param Form $form submitted form
     * @param type $values submitted values
     */
    public function editTrainerSucceeded(Form $form, $values) {
        $id = $this->getParameter('id');
        $this->trainerModel->update($id, $values);
        $this->redirect('Profile:', $id);
    }

    /**
     * Creates form for updating coach
     * 
     * @return EditCoachForm
     */
    protected function createComponentEditCoachForm() {
        $editCoachForm = new Forms\EditCoachForm();
        $form = $editCoachForm->create();
        $id = $this->getParameter('id');
        $form->setDefaults($this->coachModel->getByMemberId($id));
        $form->onSuccess[] = array($this, "editCoachSucceeded");

        return $form;
    }

    /**
     * Update coach on form submit
     * 
     * @param Form $form submitted form
     * @param array $values submitted values
     */
    public function editCoachSucceeded(Form $form, $values) {
        $id = $this->getParameter('id');
        $this->coachModel->update($id, $values);
        $this->redirect('Profile:', $id);
    }

}

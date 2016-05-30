<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use \App\Forms;
use App\Model;

/**
 * UserManagement presenter for presenting administration page
 */
class UserManagementPresenter extends BasePresenter {

    /** @var Nette\Database\Context */
    private $database;

    /** @var App\Model\MemberModel */
    private $memberModel;

    /** @var App\Model\CompetitorModel */
    private $competitorModel;

    /** @var App\Model\TrainerModel */
    private $trainerModel;

    /** @var App\Model\CoachModel */
    private $coachModel;

    /**
     * Creates new instance of presenter
     * 
     * @param Nette\Database\Context $database DB connection
     */
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
        $this->memberModel = new Model\MemberModel($database);
        $this->competitorModel = new Model\CompetitorModel($database);
        $this->trainerModel = new Model\TrainerModel($database);
        $this->coachModel = new Model\CoachModel($database);
    }

    /**
     * Checks requirements for accessing UserManagement page
     * 
     * @param type $element
     * @throws \Nette\Application\ForbiddenRequestException
     */
    public function checkRequirements($element) {
        parent::checkRequirements($element);
        if (!($this->user->isAllowed('UserManagement', 'view'))) {
            throw new \Nette\Application\ForbiddenRequestException;
        }
    }

    /**
     * Renders default view
     */
    public function renderDefault() {
        if (!($this->user->isAllowed('UserManagement', 'view'))) {
            throw new \Nette\Application\ForbiddenRequestException;
        }

        $this->template->members = $this->memberModel->getAllMembers();
        $this->template->competitors = $this->competitorModel->getAll();
        $this->template->trainers = $this->trainerModel->getAll();
        $this->template->coaches = $this->coachModel->getAll();
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
     * Returns WKF competitor's category text
     * 
     * @param array $competitor competitor's values
     * @return string WKF category text
     */
    public function getCompetitorCategory($competitor) {
        return $this->competitorModel->getCompetitorCategory($competitor);
    }

    /**
     * Resolves specialization number into specialization text
     * 
     * @param int $specialization
     * @return string specialization text
     */
    public function getSpecializationText($specialization) {
        $specText = "";
        switch ($specialization) {
            case 0:
                $specText = "bez specializace";
                break;
            case 1:
                $specText = "kata";
                break;
            case 2:
                $specText = "kumite";
                break;
        }
        return $specText;
    }

    /**
     * Creates form for member create
     * 
     * @return CreateMemberForm
     */
    protected function createComponentCreateMemberForm() {
        $createMemberForm = new Forms\CreateMemberForm();
        $form = $createMemberForm->create();
        $form->onSuccess[] = array($this, "createMemberSucceeded");
        $form->onSuccess[] = function() {
            $this->flashMessage('Uživatel byl úspěšně vytvořen.', 'info');
        };
        $form->onSuccess[] = function () {
            $this->redirect('UserManagement:');
        };
        return $form;
    }

    /**
     * Creates new member on form submit
     * 
     * @param Form $form submitted form
     * @param array $values submitted values
     */
    public function createMemberSucceeded(Form $form, $values) {
        $this->memberModel->createMember($values);
    }

    /**
     * Handles AJAX request for member delete
     * 
     * @param int $id member's id
     */
    public function handleDelete($id) {
        $this->memberModel->delete($id);
        if ($this->isAjax()) {
            $this->redrawControl("memberTables");
        } else {
            $this->redirect('UserManagement:');
        }
    }

}

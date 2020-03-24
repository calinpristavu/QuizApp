<?php

namespace QuizApp\Controller;

use Framework\Contracts\RendererInterface;
use Framework\Controller\AbstractController;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Http\Stream;
use QuizApp\Entity\QuizTemplate;
use QuizApp\Entity\User;
use QuizApp\Service\QuizTemplateService;
use QuizApp\Util\Paginator;

class QuizTemplateController extends AbstractController
{
    private $quizTemplateService;

    /**
     * UserController constructor.
     * @param RendererInterface $renderer
     * @param QuizTemplateService $questionInstanceService
     */
    public function __construct(RendererInterface $renderer, QuizTemplateService $questionInstanceService)
    {
        parent::__construct($renderer);
        $this->quizTemplateService = $questionInstanceService;
    }

    public function createQuiz(Request $request)
    {
        $name = $request->getParameter('name');
        $description = $request->getParameter('description');
        $questions = $request->getParameter('questions');
        $currentUserId = $this->quizTemplateService->getId();
        $this->quizTemplateService->saveQuiz($name, $description,$questions,$currentUserId);
        $location = 'Location: http://quizApp.com/admin-quizzes-listing?page=1';
        $body = Stream::createFromString("");

        return new Response($body, '1.1', '301', $location);
    }

    public function showQuizzes(Request $request, array $requestAttributes)
    {
        $resultsPerPage = 5;
        $quizName = $this->quizTemplateService->getFromParameter('quizName', $request, "");
        $userId = $this->quizTemplateService->getFromParameter('userId', $request, "");
        $currentPage = (int)$this->quizTemplateService->getFromParameter('page', $request, 1);
        $totalResults = (int)$this->quizTemplateService->getEntityNumberOfPagesByField(QuizTemplate::class, ['name' => $quizName, 'user_id' => $userId]);
        $quizzes = $this->quizTemplateService->getEntitiesByField(QuizTemplate::class, ['name' => $quizName, 'user_id' => $userId], $currentPage, $resultsPerPage);
        $users = $this->quizTemplateService->getEntitiesByField(User::class, ['role' => 'Admin'], 1, 99999999);

        $paginator = new Paginator($totalResults, $currentPage, $resultsPerPage);
        $paginator->setTotalPages($totalResults, $resultsPerPage);

        return $this->renderer->renderView("admin-quizzes-listing.phtml", [
            'quizName' => $quizName,
            'username'=>$this->quizTemplateService->getName(),
            'users'=>$users,
            'dropdownUserId' => $userId,
            'paginator' => $paginator,
            'quizzes' => $quizzes,
        ]);
    }

    public function editQuiz(Request $request, array $requestAttributes)
    {
        $name = $request->getParameter('name');
        $description = $request->getParameter('description');
        $questions = $request->getParameter('questions');
        $this->quizTemplateService->editQuiz($requestAttributes['id'], $name, $description,$questions);
        $location = 'Location: http://quizApp.com/admin-quizzes-listing?page=1';
        $body = Stream::createFromString("");

        return new Response($body, '1.1', '301', $location);
    }

    public function deleteQuiz(Request $request, array $requestAttributes)
    {
        $this->quizTemplateService->deleteQuiz($requestAttributes['id']);

        $location = 'Location: http://quizApp.com/admin-quizzes-listing?page=1';
        $body = Stream::createFromString("");

        return new Response($body, '1.1', '301', $location);
    }

    public function showQuizDetailsEdit(Request $request, array $requestAttributes)
    {
        $params = $this->quizTemplateService->getParams($requestAttributes['id']);
        $params['selectedQuestions'] = $this->quizTemplateService->getSelectedQuestions($requestAttributes['id']);
        $params['questions'] = $this->quizTemplateService->getQuestions();
        $params['username'] = $this->quizTemplateService->getName();
        $params['path'] = 'edit/' . $params['id'];

        return $this->renderer->renderView("admin-quiz-details.phtml", $params);
    }

    public function showQuizDetails()
    {
        $params = $this->quizTemplateService->getEmptyParams();
        $params['selectedQuestions'] = [];
        $params['questions'] = $this->quizTemplateService->getQuestions();
        $params['username'] = $this->quizTemplateService->getName();
        $params['path'] = 'create';

        return $this->renderer->renderView("admin-quiz-details.phtml", $params);
    }
}

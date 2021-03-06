<?php

namespace QuizApp\Controller;

use Framework\Contracts\RendererInterface;
use Framework\Controller\AbstractController;
use Framework\Http\Message;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Http\Stream;
use Psr\Http\Message\MessageInterface;
use QuizApp\Entity\QuestionTemplate;
use QuizApp\Service\QuestionTemplateService;
use QuizApp\Util\Paginator;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

/**
 * Class QuestionTemplateController
 * @package QuizApp\Controllers
 */
class QuestionTemplateController extends AbstractController
{

    /**
     * @var QuestionTemplateService
     */
    private $questionTemplateService;

    /**
     * UserController constructor.
     * @param RendererInterface $renderer
     * @param QuestionTemplateService $questionInstanceService
     */
    public function __construct(RendererInterface $renderer, QuestionTemplateService $questionInstanceService)
    {
        parent::__construct($renderer);
        $this->questionTemplateService = $questionInstanceService;
    }

    /**
     * This method creates a question and saves it in the database
     *
     * @param Request $request
     * @return Message|MessageInterface
     */
    public function createQuestion(Request $request): MessageInterface
    {
        $text = $request->getParameter('text');
        $type = $request->getParameter('type');
        $answer = $request->getParameter('answer');
        $this->questionTemplateService->saveQuestion($text, $type, $answer);
        $body = Stream::createFromString("");
        $response = new Response($body, '1.1', 301);

        return $response->withHeader('Location', '/admin-questions-listing');
    }

    /**
     * This method returns a Response with the questions searched by getQuestionsByText with the specified page and text
     *
     * @param Request $request
     * @param array $requestAttributes
     * @return Response
     */
    public function showQuestions(Request $request, array $requestAttributes): Response
    {
        $resultsPerPage = 5;
        $text = $this->questionTemplateService->getFromParameter('text', $request, "");
        $type = $this->questionTemplateService->getFromParameter('type', $request, "");
        $currentPage = (int)$this->questionTemplateService->getFromParameter('page', $request, 1);
        $totalResults = (int)$this->questionTemplateService->getEntityNumberOfPagesByField(QuestionTemplate::class, ['type' => $type, 'text' => $text]);
        $questions = $this->questionTemplateService->getEntitiesByField(QuestionTemplate::class, ['type' => $type, 'text' => $text], $currentPage, $resultsPerPage);

        $paginator = new Paginator($totalResults, $currentPage, $resultsPerPage);
        $paginator->setTotalPages($totalResults, $resultsPerPage);

        //TODO username modify after injecting the Session class in Controller
        return $this->renderer->renderView("admin-questions-listing.phtml", [
            'text' => $text,
            'username' => $this->questionTemplateService->getName(),
            'dropdownType' => $type,
            'paginator' => $paginator,
            'questions' => $questions,
        ]);
    }

    /**
     * This method edits a question with the attributes from the form
     *
     * @param Request $request
     * @param array $requestAttributes
     * @return Message|MessageInterface
     */
    public function editQuestion(Request $request, array $requestAttributes): MessageInterface
    {
        $text = $request->getParameter('text');
        $type = $request->getParameter('type');
        $answer = $request->getParameter('answer');
        $this->questionTemplateService->editQuestion($requestAttributes['id'], $text, $type, $answer);

        $body = Stream::createFromString("");
        $response = new Response($body, '1.1', 301);

        return $response->withHeader('Location', '/admin-questions-listing');
    }

    /**
     * This method deletes a question and redirects to /admin-questions-listing
     *
     * @param Request $request
     * @param array $requestAttributes
     * @return Message|MessageInterface
     */
    public function deleteQuestion(Request $request, array $requestAttributes): MessageInterface
    {
        $this->questionTemplateService->deleteQuestion($requestAttributes['id']);

        $location = 'Location: /admin-questions-listing';
        $body = Stream::createFromString("");
        $response = new Response($body, '1.1', 301);

        return $response->withHeader('Location', '/candidate-results');
    }

    /**
     * This method displays a pre-filled 'Edit Question' form
     *
     * @param Request $request
     * @param array $requestAttributes
     * @return Response
     */
    public function showQuestionDetailsEdit(Request $request, array $requestAttributes): Response
    {
        //TODO modify username after injecting the Session class in Controller
        //TODO move params in an array for renderView
        $params = $this->questionTemplateService->getParams($requestAttributes['id']);
        $params['username'] = $this->questionTemplateService->getName();
        $params['path'] = 'edit/' . $params['id'];

        return $this->renderer->renderView("admin-question-details.phtml", $params);
    }

    /**
     * This method displays a 'Create Question' form
     *
     * @return Response
     */
    public function showQuestionDetails(): Response
    {
        //TODO modify username after injecting the Session class in Controller
        //TODO move params in an array for renderView
        $params['username'] = $this->questionTemplateService->getName();
        $params['path'] = 'create';

        return $this->renderer->renderView("admin-question-details.phtml", $params);
    }
}

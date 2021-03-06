<?php

namespace QuizApp\Controller;

use Framework\Contracts\RendererInterface;
use Framework\Controller\AbstractController;
use Framework\Http\Message;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Http\Stream;
use Psr\Http\Message\MessageInterface;
use QuizApp\Entity\User;
use QuizApp\Repository\UserRepository;
use QuizApp\Service\UserService;
use QuizApp\Util\Paginator;
use ReallyOrm\Filter;
use ReallyOrm\Test\Repository\RepositoryManager;

/**
 * Class UserController
 * @package QuizApp\Controllers
 */
class UserController extends AbstractController
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var UserRepository
     */
    protected $userRepo;

    /**
     * UserController constructor.
     * @param RendererInterface $renderer
     * @param UserService $questionInstanceService
     * @param UserRepository $userRepo
     */
    public function __construct(RendererInterface $renderer, UserService $questionInstanceService, UserRepository $userRepo)
    {
        parent::__construct($renderer);
        $this->userService = $questionInstanceService;
        $this->userRepo = $userRepo;
    }

    /**
     * @return Response
     */
    public function showLogin(): Response
    {
        return $this->renderer->renderView("login.html", []);
    }

    /**
     * This method returns a Response with the users searched with the specified page and text
     *
     * @param Request $request
     * @param array $requestAttributes
     * @return Message|MessageInterface
     */
    public function showUsers(Request $request, array $requestAttributes): MessageInterface
    {
        //TODO modify after injecting the Session class in Controller
        $redirectToLogin = $this->verifySessionUserName($this->userService->getSession());
        if ($redirectToLogin) {
            return $redirectToLogin;
        }
        $resultsPerPage = 5;
        $email = $this->userService->getFromParameter('email', $request, "");
        $role = $this->userService->getFromParameter('role', $request, "");
        $filters = ['email' => $email, 'role' => $role];
        $orderBy = $this->userService->getFromParameter('orderBy', $request, "");
        $sortType = $this->userService->getFromParameter('sort', $request, "");
        //TODO remove casts and fix methods
        $currentPage = (int)$this->userService->getFromParameter('page', $request, 1);
        $totalResults = (int)$this->userService->getEntityNumberOfPagesByField(User::class, $filters);
        $filtersForEntity = new Filter($filters, $resultsPerPage, ($currentPage - 1) * $resultsPerPage, $orderBy, $sortType);
        $users = $this->userRepo->getFilteredEntities($filtersForEntity);

        $paginator = new Paginator($totalResults, $currentPage, $resultsPerPage);
        $paginator->setTotalPages($totalResults, $resultsPerPage);

        //TODO modify after injecting the Session class in Controller
        return $this->renderer->renderView("admin-users-listing.phtml", [
            'email' => $email,
            'username' => $this->userService->getName(),
            'dropdownRole' => $role,
            'paginator' => $paginator,
            'users' => $users,
            'orderBy' => $orderBy,
            'sortType' => $sortType,
        ]);
    }

    /**
     * This method returns a Response with the page ready for a create
     *
     * @return Response
     */
    public function showUserDetails(): Response
    {
        //TODO modify after injecting the Session class in Controller
        return $this->renderer->renderView("admin-user-details.phtml", [
            'username' => $this->userService->getName(),
            'path' => 'create'
        ]);
    }

    /**
     * This method creates an user and saves it in the database
     *
     * @param Request $request
     * @return Message|MessageInterface
     */
    public function createUser(Request $request): MessageInterface
    {
        $name = $request->getParameter('name');
        $email = $request->getParameter('email');
        $password = $request->getParameter('password');
        $role = $request->getParameter('role');
        $this->userService->saveUser($name, $email, $password, $role);
        $body = Stream::createFromString("");
        $response = new Response($body, '1.1', 301);

        return $response->withHeader('Location', '/admin-users-listing');
    }

    /**
     * This method returns a Response with the page ready for an edit
     *
     * @param Request $request
     * @param array $requestAttributes
     * @return Response
     */
    public function showUserDetailsEdit(Request $request, array $requestAttributes): Response
    {
        $params = $this->userService->getParams($requestAttributes['id']);

        //TODO modify after injecting the Session class in Controller
        return $this->renderer->renderView("admin-user-details.phtml", [
            'name' => $params['name'],
            'email' => $params['email'],
            'role' => $params['role'],
            'username' => $this->userService->getName(),
            'path' => 'edit/' . $params['id']
        ]);
    }

    /**
     * This method edits an user with the attributes from the form
     *
     * @param Request $request
     * @param array $requestAttributes
     * @return Message|MessageInterface
     */
    public function editUser(Request $request, array $requestAttributes): MessageInterface
    {
        $name = $request->getParameter('name');
        $email = $request->getParameter('email');
        $password = $request->getParameter('password');
        $role = $request->getParameter('role');
        $this->userService->editUser($requestAttributes['id'], $name, $email, $password, $role);
        $body = Stream::createFromString("");
        $response = new Response($body, '1.1', 301);

        return $response->withHeader('Location', '/admin-users-listing');
    }

    /**
     * This method deletes an user and redirects to /admin-users-listing
     *
     * @param Request $request
     * @param array $requestAttributes
     * @return Message|MessageInterface
     */
    public function deleteUser(Request $request, array $requestAttributes): MessageInterface
    {
        $this->userService->deleteUser($requestAttributes['id']);
        $body = Stream::createFromString("");
        $response = new Response($body, '1.1', 301);

        return $response->withHeader('Location', '/admin-users-listing');
    }

    //TODO add comment
    public function showExceptionsPage(): Response
    {
        return $this->renderer->renderView("exceptions-page.phtml", ['errorMessage' => 'Route not found!']);
    }
}

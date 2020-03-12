<?php

namespace QuizApp\Services;

use QuizApp\Entities\QuestionTemplate;
use QuizApp\Entities\QuizTemplate;
use QuizApp\Entities\User;
use QuizApp\Repository\QuestionTemplateRepository;

class QuizTemplateServices extends AbstractServices
{
    public function createQuestionList(array $questions)
    {
        $result = [];
        foreach ($questions as $question) {
            $result[] = $this->repoManager->getRepository(QuestionTemplate::class)->findOneBy(['text' => $question]);
        }

        return $result;
    }

    public function saveQuiz($name, $description, $questions, $userId)
    {
        $quiz = new QuizTemplate();
        $quiz->setName($name);
        $quiz->setDescription($description);
        $filters = ['name' => $name, 'description' => $description];
        $user = $this->repoManager->getRepository(User::class)->find($userId);
        $result = $this->repoManager->getRepository(QuizTemplate::class)->findOneBy($filters);
        $questionsFound = $this->createQuestionList($questions);
        if (!$result) {
            $this->repoManager->register($quiz);
            $this->repoManager->register($user);
            $this->repoManager->register($questionsFound[0]);
            $quiz->save();
            $this->repoManager->getRepository(QuizTemplate::class)->setForeignKeyId($user, $quiz);
            $this->repoManager->getRepository(QuizTemplate::class)->setEntitiesToTarget($quiz, $questionsFound);
        }
    }

    public function getEmptyParams()
    {
        return ['name' => '', 'description' => ''];
    }

    public function getQuizNumber(array $filters)
    {
        return $this->repoManager->getRepository(QuizTemplate::class)->getNumberOfEntities($filters);
    }

    public function getQuizzes(array $filters, $currentPage)
    {
        $quizzes = $this->repoManager->getRepository(QuizTemplate::class)->findBy($filters, [], ($currentPage - 1) * 5, 5);
        foreach ($quizzes as $quiz){
            $this->repoManager->register($quiz);
        }
        return $quizzes;
    }

    public function editQuiz($quizId, $name, $description, $questions)
    {
        $quiz = $this->repoManager->getRepository(QuizTemplate::class)->find($quizId);
        $quiz->setName($name);
        $quiz->setDescription($description);
        $questionsFound = $this->createQuestionList($questions);
        $this->repoManager->register($quiz);
        $this->repoManager->register($questionsFound[0]);
        $quiz->save();
        $this->repoManager->getRepository(QuizTemplate::class)->deleteQuestions($quiz->getId());
        $this->repoManager->getRepository(QuizTemplate::class)->setEntitiesToTarget($quiz, $questionsFound);
    }

    public function getParams($id)
    {
        $quiz = $this->repoManager->getRepository(QuizTemplate::class)->find($id);

        return ['id' => $quiz->getId(), 'name' => $quiz->getName(), 'description' => $quiz->getDescription()];
    }

    public function deleteQuiz($id)
    {
        $quiz = $this->repoManager->getRepository(QuizTemplate::class)->find($id);
        $this->repoManager->getRepository(QuizTemplate::class)->deleteQuestions($quiz->getId());
        return $this->repoManager->getRepository(QuizTemplate::class)->delete($quiz);
    }

    public function searchByText($text)
    {
        return $this->repoManager->getRepository(QuizTemplate::class)->searchByField('text', $text);
    }

    public function getQuestions()
    {
        return $this->repoManager->getRepository(QuestionTemplate::class)->findBy([], [], 0, 999999999999);
    }

    public function getSelectedQuestions($quizId)
    {
        return $this->repoManager->getRepository(QuizTemplate::class)->getSelectedQuestions($quizId);
    }

}
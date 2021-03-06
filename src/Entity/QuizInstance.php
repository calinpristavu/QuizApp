<?php

namespace QuizApp\Entity;

use ReallyOrm\Entity\AbstractEntity;

/**
 * Class QuizInstance
 * @package QuizApp\Entities
 */
class QuizInstance extends AbstractEntity
{
    /**
     * @var int
     * @ID
     * @ORM id
     */
    private $id;

    /**
     * @var int
     * @ORM score
     */
    private $score;

    /**
     * @var string
     * @ORM name
     */
    private $name;

    /**
     * @var int
     * @ORM is_saved
     */
    private $isSaved;

    /**
     * QuizInstance constructor.
     */
    public function __construct()
    {
        $this->id = null;
        $this->score = 0;
        $this->name = '';
        $this->isSaved = 0;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * @param int $score
     */
    public function setScore($score): void
    {
        $this->score = $score;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getIsSaved(): int
    {
        return $this->isSaved;
    }

    /**
     * @param int
     */
    public function setIsSaved($isSaved): void
    {
        $this->isSaved = $isSaved;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->getRepository()->getUserId($this->getId());
    }

}

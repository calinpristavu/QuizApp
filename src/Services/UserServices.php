<?php

namespace QuizApp\Services;

use Framework\Contracts\SessionInterface;
use QuizApp\Entities\User;
use ReallyOrm\Test\Repository\RepositoryManager;

class UserServices extends AbstractServices
{

    public function saveUser(string $name, string $email, string $password, string $role)
    {
        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setRole($role);
        $filters = ['email' => $email];
        $result = $this->repoManager->getRepository(User::class)->findOneBy($filters);
        if (!$result) {
            $this->repoManager->register($user);
            $user->save();
        }
    }

    public function getUsers(array $filters, int $currentPage)
    {
        return $this->repoManager->getRepository(User::class)->findBy($filters, [], ($currentPage - 1) * 5, 5);
    }

    public function getUserNumber(array $filters)
    {
        return $this->repoManager->getRepository(User::class)->getNumberOfEntities($filters);
    }

    public function editUser($id, $name, $email, $password, $role)
    {
        $user = $this->repoManager->getRepository(User::class)->find($id);
        $user->setName($name);
        $user->setEmail($email);
        if ($password !== "") {
            $user->setPassword($password);
        }
        $user->setRole($role);
        $filters = ['email' => $email];
        $result = $this->repoManager->getRepository(User::class)->findOneBy($filters);
        if ($result){
            $this->repoManager->register($user);
            $user->save();
        }
    }

    public function getParams(int $id)
    {
        $user = $this->repoManager->getRepository(User::class)->find($id);

        return ['id' => $user->getId(), 'name' => $user->getName(), 'email' => $user->getEmail(), 'password' => $user->getPassword(), 'role' => $user->getRole()];
    }

    public function deleteUser($id)
    {
        $user = $this->repoManager->getRepository(User::class)->find($id);

        return $this->repoManager->getRepository(User::class)->delete($user);
    }

    public function getEmptyParams()
    {
       return  ['id' => '', 'name' => '', 'email' => '', 'password' => '', 'role' => ''];
    }

}

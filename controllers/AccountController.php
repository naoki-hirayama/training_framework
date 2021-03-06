<?php

class AccountController extends Controller
{
    protected $auth_actions = ['index', 'signout', 'follow']; 

    public function signupAction()
    {
        if ($this->session->isAuthenticated()) {
            return $this->redirect('/account/detail');
        }

        return $this->render(array(
            'user_name' => '',
            'password' => '',
            '_token' => $this->generateCsrfToken('account/signup'),
        ));
    }
    //登録
    public function registerAction()
    {
        if ($this->session->isAuthenticated()) {
            return $this->redirect('/account/detail');
        }

        if (!$this->request->isPost()) {
            $this->foward404();
        }

        $token = $this->request->getPost('_token');
        if (!$this->checkCsrfToken('account/signup', $token)) {
            return $this->redirect('/account/signup');
        }

        $user_name = $this->request->getPost('user_name');
        $password = $this-> request->getPost('password');

        $errors = array();

        if (!strlen($user_name)) {
            $errors[] = 'ユーザーiDを入力してください';
        } else if (!preg_match('/^\w{3,20}$/', $user_name)) {
            $errors[] = 'ユーザーiDは半角英数字及びアンダースコアを3〜20文字以内で入力してください';
        } else if (!$this->db_manager->get('User')->isUniqueUserName($user_name)) {
            $errors[] = 'ユーザーidは既に使用されています';
        }

        if (!strlen($password)) {
            $errors[] = 'パスワードを入力してください';
        } else if (strlen($password < 4 || strlen($password) > 30)) {
            $errors[] = 'パスワード は４〜30字以内で入力してください';
        }

        if (count($errors) === 0) {
            $this->db_manager->get('User')->insert($user_name, $password);

            $this->session->setAuthenticated(true);

            $user = $this->db_manager->get('User')->fetchByUserName($user_name);
            $this->session->set('user', $user);

            return $this->redirect('/');
        }

        return $this->render(array(
            'user_name' => $user_name,
            'password'  => $password,
            'errors'    => $errors,
            '_token'    => $this->generateCsrfToken('account/signup'),
        ), 'signup');
    }

    public function indexAction()
    {
        $user = $this->session->get('user');
        // var_dump($_SESSION);
        // var_dump($user);
        $followings = $this->db_manager->get('user')->fetchAllFollowingsByUserId($user['id']);
        $messages = [];
        $errors = [];
        if ($this->request->isPost()) {
            //post送信された時
            $user_repository = $this->db_manager->get('User');

            $current_password = $this->request->getPost('current_password');
            $new_password = $this->request->getPost('new_password');
            $confirm_password = $this->request->getPost('confirm_password');
            $token = $this->request->getPost('_token');

            if (!$this->checkCsrfToken('account/signin', $token)) {
                return $this->redirect('/');
            }

            if ($user['password'] !== $user_repository->hashPassword($current_password)) {
                $errors[] = "パスワードが間違っています。";
            } else {
                if ($new_password !== $confirm_password) {
                    $errors[] = '確認パスワードが一致しません';
                } elseif (strlen($new_password) < 4 || strlen($new_password) > 30) {
                    $errors[] = 'パスワード は４〜30字以内で入力してください';
                }
            }

            if (count($errors) === 0) {
                $user_repository->changePassword($user['user_name'], $new_password);
                $user = $user_repository->fetchByUserName($user['user_name']);
                $this->session->set('user', $user);
                
                $messages[] = "変更しました";
            } 
        }
            return $this->render(array(
                'user'       => $user,
                'followings' => $followings,
                'messages'   => $messages,
                'errors'     => $errors,
                '_token'     => $this->generateCsrfToken('account/signin'),
            ));
    }

    public function signinAction()
    {
        if ($this->session->isAuthenticated()) {
            return $this->redirect('/account/detail');
        }

        return $this->render(array(
            'user_name' => '',
            'password'  => '',
            '_token'    => $this->generateCsrfToken('account/signin'),
        ));
    }
    //login
    public function authenticateAction()
    {
        if ($this->session->isAuthenticated()) {
            return $this->redirect('/account/detail');
        }

        if (!$this->request->isPost()) {
            $this->forward404();
        }

        $token = $this->request->getPost('_token');
        if (!$this->checkCsrfToken('account/signin', $token)) {
            return $this->redirect('/account/signin');
        }

        $user_name = $this->request->getPost('user_name');
        $password = $this->request->getPost('password');

        $errors = [];

        if (!strlen($user_name)) {
            $errors[] = 'ユーザーiDを入力してください';
        }

        if (!strlen($password)) {
            $errors[] = 'パスワードを入力してください';
        }

        if (count($errors) === 0) {
            $user_repository = $this->db_manager->get('User');
            $user = $user_repository->fetchByUserName($user_name);
            
            if (!$user || ($user['password'] !== $user_repository->hashPassword($password))) {
                $errors[] = 'ユーザーiDかパスワード が不正です';
            } else {
                $this->session->setAuthenticated(true);
                $this->session->set('user', $user);

                return $this->redirect('/');
            }
        }

        return $this->render(array(
            'user_name' => $user_name,
            'password'  => $password,
            'errors'    => $errors,
            '_token'    => $this->generateCsrfToken('account/signin'),
        ), 'signin');
    }

    public function signoutAction()
    {
        $this->session->clear();
        $this->session->setAuthenticated(false);

        return $this->redirect('/account/signin');
    }
    //フォロー
    public function followAction()
    {
        if (!$this->request->isPost()) {
            $this->forward404();
        }

        $following_name = $this->request->getPost('following_name');
        if (!$following_name) {
            $this->forward404();
        }

        $token = $this->request->getPost('_token');
        if (!$this->checkCsrfToken('account/follow', $token)) {
            return $this->redirect('/user/' . $following_name);
        }

        $follow_user = $this->db_manager->get('User')->fetchByUserName($following_name);

        if (!$follow_user) {
            $this->forward404();
        }

        $user = $this->session->get('user');

        $following_repository = $this->db_manager->get('Following');
        if ($user['id'] !== $follow_user['id'] && !$following_repository->isFollowing($user['id'], $follow_user['id'])) {
            $following_repository->insert($user['id'], $follow_user['id']);
        }

        return $this->redirect('/account/detail');
    }

}
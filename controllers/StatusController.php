<?php

class StatusController extends Controller
{
    protected $auth_actions = ['index', 'post'];

    public function indexAction($params)
    {
        $user = $this->session->get('user');
        //$statuses = $this->db_manager->get('Status')->fetchAllPersonalArchivesByUserId($user['id']);
        $total_records = $this->db_manager->get('Status')->fetchCountAllPersonalArchivesByUserId($user['id']);
        
        $max_pager_range = 4;
        $per_page_records = 5;
        if (isset( $params['page'])) {
            $page = $params['page'];
        } else {
            $page = 1;
        }

        //var_dump($_SESSION);

        $pager = new Pager($total_records, $max_pager_range, $per_page_records);
        $pager->setCurrentPage($page);
        $offset = $pager->getOffset();
        $per_page_records = $pager->getPerPageRecords();
        $statuses = $this->db_manager->get('Status')->fetchPerPagePersonalArchivesByUserIdAndOffsetAndLimit($user['id'], $offset, $per_page_records);

        return $this->render(array(
            'statuses' => $statuses,
            'body'     => '',
            '_token'   => $this->generateCsrfToken('status/post'),
            'pager'    => $pager,
        ));
    }

    public function postAction()
    {
        if (!$this->request->isPost()) {
            $this->forward404();
        }

        $token = $this->request->getPost('_token');
        if (!$this->checkCsrfToken('status/post', $token)) {
            return $this->redirect('/');
        }

        $body = $this->request->getPost('body');

        $errors = [];

        if (!strlen($body)) {
            $errors[] = 'ひとことを入力してください';
        } else if (mb_strlen($body) > 200) {
            $errors[] = 'ひとことは200字以内で入力してください';
        }

        if (count($errors) === 0) {
            $user = $this->session->get('user');
            $this->db_manager->get('status')->insert($user['id'], $body);

            return $this->redirect('/');
        }

        $user = $this->session->get('user');

        return $this->render(array(
            'errors'   => $errors,
            'body'     => $body,
            '_token'   => $this->generateCsrfToken('status/post'), 
        ), 'index');
    }

    function userAction($params)
    {
        $user = $this->db_manager->get('User')->fetchByUserName($params['user_name']);
        if (!$user) {
            $this->forward404();
        }

        $statuses = $this->db_manager->get('Status')->fetchAllByUserId($user['id']);

        $following = null;
        if ($this->session->isAuthenticated()) {
            $my = $this->session->get('user');
            if ($my['id'] !== $user['id']) {
                $following = $this->db_manager->get('Following')->isFollowing($my['id'], $user['id']);
            }
        }

        return $this->render(array(
            'user'     => $user,
            'statuses' => $statuses,
            'following' => $following,
            '_token' => $this->generateCsrfToken('account/follow'),
        ));
    }

    public function showAction($params)
    {
        $status = $this->db_manager->get('Status')->fetchByIdAndUserName($params['id'], $params['user_name']);

        if (!$status) {
            $this->forward404();
        }

        return $this->render(array('status' => $status));
    }

    public function signinAction()
    {
        if ($this->session->isAuthenticated()) {
            return $this->redirect('/account');
        }

        return $this->render(array(
            'user_name' => '',
            'password'  => '',
            '_token'    => $this->generateCsrfToken('account/signin'),
        ));
    }
}
<?php

class UserRepository extends DbRepository
{
    public function insert($user_name, $password)
    {
        $password = $this->hashPassword($password);
        $now = new DateTime();

        $sql = 'INSERT INTO user(user_name, password, created_at) VALUES(:user_name, :password, :created_at)';
        //・ユーザーのプロフ画像設定機能
        $stmt = $this->execute($sql, array(
            ':user_name' => $user_name,
            ':password' => $password,
            ':created_at' => $now->format('Y-m-d H:i:s'),
        ));
    }
    //パスワード 変更
    public function changePassword($user_name, $password)
    {
        $password = $this->hashPassword($password);
        $sql = 'UPDATE user SET password = :password WHERE user_name = :user_name';

        $stmt = $this->execute($sql, array(
            ':user_name' => $user_name,
            ':password' => $password,
        ));
    }

    public function hashPassword($password)
    {
        return sha1($password . 'Secretkey');
    }

    public function fetchByUserName($user_name)
    {  
        $sql = 'SELECT * FROM user WHERE user_name = :user_name';
        //pic
        return $this->fetch($sql, array(':user_name' => $user_name));
    }

    public function isUniqueUserName($user_name)
    {
        $sql = 'SELECT COUNT(id) as count FROM user WHERE user_name = :user_name';

        $row = $this->fetch($sql, array(':user_name' => $user_name));
        if ($row['count'] === '0') {
            return true;
        }
    }

    public function fetchAllFollowingsByUserId($user_id)
    {
        $sql = 'SELECT u.* FROM user u LEFT JOIN following f ON f.following_id = u.id WHERE f.user_id = :user_id';

        return $this->fetchAll($sql, array(':user_id' => $user_id));
    }
}
<?php

namespace app\models;

class User extends \yii\base\Object implements \yii\web\IdentityInterface
{
    public $id;
    public $username;
    public $password;
    public $authKey;
    public $accessToken;
    private static $users;

    /*
     * read file
     * and get all user info
     * $id - filtered parametr
     */
    static function readFileData($id = null, $token = null, $username = null)
    {
        if(file_exists(\Yii::getAlias("@app/runtime/users")))
        {
            $handle = @fopen(\Yii::getAlias('@app/runtime/users'), "r");
            if ($handle) {
                $count = 1;
                while (($buffer = fgets($handle, 4096)) !== false) {

                    $info = explode('|',$buffer);

                    $user = [
                        'id'=>$info[0],
                        'username'=>$info[1],
                        'password'=>$info[2],
                        'authKey'=>$info['3'],
                        'accessToken'=>$info['4']
                    ];

                    //if we have filter param we accepted
                    if($id && $user['id']==$id)
                    {
                        return $user;
                    }elseif($token && $user['accessToken']==$token){
                        return $user;
                    }elseif($username && $user['username']==$username){
                        return $user;
                    }
                    $count++;
                }
                fclose($handle);

                return false;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return new static(static::readFileData($id));
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return new static(static::readFileData(null,$token));
    }

    /**
     * Finds user by username
     *
     * @param  string      $username
     * @return static|null
     */
    public static function findByUsername($username)
    {

        return new static(static::readFileData(null,null, $username));
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }

}

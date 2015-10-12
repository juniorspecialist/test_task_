<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],

            ['username','checkAttempts'],
        ];
    }

    /*
     * validation attempts
     */
    public function checkAttempts(){

        //TODO проверить работу счётчика кол-ва попыток
        $count = Yii::$app->session->get("attempts");

        if($count==3){
            $this->addError("username", "Попробуйте ещё раз через ".(Yii::$app->session->get("attempts_last_time")). " сек.");
        }
        if($count<3)
        {
            if($count)
            {
                $count++;

                Yii::$app->session->set("attempts", $count);
                Yii::$app->session->set("attempts_last_time", time());
            }else{
                Yii::$app->session->set("attempts", 1);
                Yii::$app->session->set("attempts_last_time", time());
            }
        }

    }


    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Не верно указано имя пользователя или пароль.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            Yii::$app->session->set("attempts", 0);
            Yii::$app->session->set("attempts_last_time", time());

            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Имя пользователя',
            'password' => 'Пароль',
        ];
    }
}

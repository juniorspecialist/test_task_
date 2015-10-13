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

            ['username','checkAttempts'],

            // password is validated by validatePassword()
            ['password', 'validatePassword'],

            ['username','checkAttempts'],
        ];
    }

    /*
     * validation attempts
     */
    public function checkAttempts(){

        $count = Yii::$app->session->get("attempts");

        //проверим не был ли превышен лимит попыток неудачного входа
        if(!$this->hasErrors())
        {
            if($count==3)
            {
                //сверим время после последней попытки авторизации с текущим
                $delta = (time() - Yii::$app->session->get("attempts_last_time"));

                if($delta<300)
                {
                    $this->addError("username", "Попробуйте ещё раз через " . (300 - $delta) . " сек.");
                }else {
                    //если прошло более 5ти минут - сбрасываем счётчики неудачных попыток
                    //очистка меток попыток
                    Yii::$app->session->remove('attempts_last_time');
                    Yii::$app->session->remove('attempts');
                }
            }
        }else{
            if($count<3)
            {
                $count++;

                Yii::$app->session->set("attempts", $count);
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
            //очистка меток попыток
            Yii::$app->session->remove('attempts_last_time');
            Yii::$app->session->remove('attempts');

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

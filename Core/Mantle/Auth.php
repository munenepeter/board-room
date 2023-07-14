<?php

namespace BoardRoom\Core\Mantle;

use BoardRoom\Models\Employee; 
use BoardRoom\Core\Mantle\Session;

class Auth {

    public static function login(String $email, String $password) {


        $employee =  Employee::query("select employee_no, email, password  from employees where email = \"$email\"");

        if (empty($employee)) {
            logger("Info","Login: No account with {$email} email with {$password}");
            Session::make('_msg_error', "Wrong credentials, Please try again!");
            return redirectback();
        }
        $employee = (object)$employee[0];

    
        if (password_verify($password, $employee->password)) {

            logger("Info","Login: Logged in {$email} with {$password}");

            Session::make('loggedIn', true);
            Session::make('employee_no', $employee->employee_no);
            Session::make('email', $employee->email);

            Session::make('_msg_success', "Successfull login");
            return;
        } else {
            logger("Info","Login: Wrong Credentials; Email: {$email} Password: {$password}");
            Session::make('_msg_error', "Wrong credentials, Please try again!");
            return redirectback();
        }
    }
    public static function logout(String $employee) {
        
        Session::unset($employee);
        Session::make('loggedIn', false);

        logger("Info","Login: logged out $employee");
        Session::destroy();
    }
}

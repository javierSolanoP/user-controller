<?php
namespace App\Http\Controllers\Module_User\Class;

use App\Http\Controllers\Trait\MethodUser;

class Usuario {

    public function __construct(private $identification = '', 
                                private $name = '', 
                                private $last_name = '', 
                                private $email = '', 
                                private $password = '', 
                                private $confirmPassword = '',
                                private $telephone = ''
                                ){}

    //Se importa los metodos de usuario: 
    use MethodUser;
}
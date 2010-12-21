<?php

class KCaptchaComponent extends Object {

    function startup(&$controller) {
        $this->controller = $controller;
    }

    function render() {
        App::import('Vendor', 'kcaptcha/kcaptcha');
        $kcaptcha = new KCAPTCHA();
        
        $string = $kcaptcha->getKeyString();
                             														       
        $this->controller->Session->write('captcha', $string);
//        $this->controller->Session->write('cap', $this->controller);
    }

}

?>
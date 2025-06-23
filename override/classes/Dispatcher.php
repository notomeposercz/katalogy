<?php
/**
 * Override for Dispatcher to handle custom module routes
 */

class Dispatcher extends DispatcherCore
{
    public function getController()
    {
        $controller = parent::getController();
        
        // Handle our custom katalogy route
        if ($this->request_uri == 'katalogy-reklamnich-predmetu-ke-stazeni' || 
            $this->request_uri == '/katalogy-reklamnich-predmetu-ke-stazeni') {
            $_GET['fc'] = 'module';
            $_GET['module'] = 'katalogy';
            $_GET['controller'] = 'seznam';
            $this->front_controller = 'module';
            return 'module';
        }
        
        return $controller;
    }
}
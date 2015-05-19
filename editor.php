<?php

include_once ROOT_DIR . '/files.php';

/**
 * Plugin that provides a content editor
 *
 * @author Alexandre Kaspar
 */
class Editor {

    // for authentication
    private $user;
    private $edit_state;
    private $edit_template;

    public function __construct() {
        $this->edit_state = FALSE;
    }

    public function config_loaded($config){
        // user
        if(array_key_exists('auth-user', $config))
            $this->user = $config['auth-user'];
        else
            $this->user = FALSE;
        // template
        if(array_key_exists('editor-template', $config))
            $this->edit_template = $config['editor-template'];
        else
            $this->edit_template = 'editor.html';
    }

    public function request_url(&$route){
        // only go farther if we are logged (or no need to be logged)
        if($this->user !== FALSE){
            // we want a session to store logins
            if(session_status() === PHP_SESSION_NONE)
                session_start();

            // are we logged in with the valid user?
            if(!isset($_SESSION['user']) || $_SESSION['user'] !== $this->user){
                return;
            }
        }

        // action depending on parameters
        if(!isset($_REQUEST['edit']))
            return; // no need to do anything special
        // updating content
        if(isset($_POST['edit'])){
            $this->update($route);
        } else {
            $this->show($route);
        }
    }

    private function update($url) {
        $title = Request::get_parameter('title');
        $file = Files::resolve_page($url);
        $content = Request::get_parameter('content');

        // action
        $error = '';
        if($content === ''){
            // deleting file (only if it exists)
            if(file_exists($file))
                $error = unlink($file) ? '' : 'Error: could not delete file';
        } else {
            // creating / updating file
            if(strlen($content) !== @file_put_contents($file, $content))
                $error = 'Error: could not save changes ...';
        }
        die(json_encode(array(
            'content'   => $content,
            'url'       => $url,
            'error'     => $error
        )));
    }

    private function show($url) {
        $file = Files::resolve_page($url);

        $content = '';
        if(file_exists($file)){
            $content = file_get_contents($file);
        } else {
            // XXX we should check that we can create the file eventually
            $content = '/*
Title: ' . basename($filename) . '
Date: ' . date('Y/m/d') . '
*/';
        }
        $this->edit_state = array(
            'url'      => $url,
            'content'   => $content
        );
    }

    public function before_render(&$twig_vars, &$twig /*, &$template */) {
        // should we overwrite the rendering
        if($this->edit_state === FALSE)
            return;

        // override 404 header in case we need
        header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');

        // load special editor template
        $loader = $twig->getLoader();
        $loader->addPath(dirname(__FILE__));
        // render editor
        $args = array_merge($twig_vars, $this->edit_state);
        $base_dir = realpath(Files::base_dir());
        $this_dir = realpath(dirname(__FILE__));
        $args['editor_path'] = str_replace($base_dir, '', $this_dir);
        echo $twig->render($this->edit_template, $args);
        exit;
    }

}

?>

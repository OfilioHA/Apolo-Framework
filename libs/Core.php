<?php
    class Core{

        private $raw_url;
        private $url;
        private $currentController = "Main";
        private $currentMethod = 'index';
        private $params = [];

        public function __construct()
        {
            $this->getUrl();
            $this->getController();            
            $this->getMehod();
            $this->getParameters();
            var_dump($this);

            //Call Back pasando los parametros obtenidos.
            call_user_func_array(
                [
                    $this->currentController,
                    $this->currentMethod
                ],
                [
                    $this->params
                ]
            );
        }


        public function getUrl(){
            if(isset($_GET["url"])){
                //Retiramos los espacios en blanco.
                $url = rtrim($_GET["url"], "/");
                //Separamos los argumentos pasados por GET.
                $url = explode('?', $_GET["url"])[0];
                //Filtramos la  url en variables string y numeros.
                $this->raw_url = filter_var($url, FILTER_SANITIZE_URL);
                //Lo dividimos en un array.
                $this->url = explode("/", $url);
                //Limpiamos la URL del array GET
                unset($_GET["url"]);
            }
        }

        public function getController(){
            //Verificamos que venga el nombre de un controlador
            $controller_name = (!empty($this->url[0])) ? ucwords($this->url[0]) : $this->currentController;
            //Verificamos que exista el controlador, debe tener la primera letra en Mayusculas.
            $controller_name = $this->sanitizeControllerName($controller_name);
            if(file_exists("./controllers/$controller_name.php")){
                $this->currentController = $controller_name;
                //Creamos una instancia del Controlador solicitado.
                require_once "./controllers/$this->currentController.php";
                $this->currentController = new $this->currentController();
            }
        }

        private function sanitizeControllerName($string){
            //Limpiamos el nombre del controlador.
            $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
            $string = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $string);
            $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
            $string = preg_replace(array('~[^0-9a-z_]~i', '~[ -]+~'), ' ', $string);
            $string = str_replace(' ', '', $string);
            return trim($string, ' -');
        }

        public function getMehod(){
            //Verificamos que exista el metodo solicitado.
            //En caso que no exista, tomara el el metodo por defecto.
            if(isset($this->url[1])){
                if(method_exists($this->currentController, $this->url[1])){
                    $this->currentMethod = $this->url[1];
                }
            }
        }

        public function getParameters(){
            //Creamos una copia de la url
            $url = $this->url;
            //Obtenemos los parametros enviados por la url
            $parameters = (count($this->url) > 2) ? array_splice($url, 2) : [];
            //Combinamos todos las peticiones
            $this->params = array_merge($parameters, $_GET, $_POST);
        }
    }
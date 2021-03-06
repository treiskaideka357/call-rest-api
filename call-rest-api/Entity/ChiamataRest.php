<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 11/2/16
 * Time: 12:41 PM
 */

namespace Services\Bundle\Rest\Entity;

/**
 * This class permit to call rest resources
 *
 * Class ChiamataRest
 * @package ServicesBundle\Entity
 */
class ChiamataRest
{

    /**
     * Url to call
     *
     * @string
     */
    private $url;

    /**
     * Userid for the header
     *
     * @string
     */
    private $login;

    /**
     * Password for the header
     *
     * @string
     */
    private $password;

    /**
     * Who is calling
     *
     * @string
     */
    private $chiamante;

    /**
     * Here you can set your json
     *
     * @string
     */
    private $json;

    /**
     * http verrb
     *
     * @string
     */
    private $tipoChiamata;

    /**
     * Contain the information if needs to control success field or not in case it doesn't exist
     *
     * @boolean
     */
    private $controlSuccess=true;

    /**
     * Contains the httpcode received from the rest call
     *
     * @integer
     */
    private $httpcode;

    /**
     * This variable contain the name of the field containing message information from request
     *
     * @var string
     */
    private $nomeCampoMessage="message";

    /**
     * This variable contain the name of the field containing the result of the request
     *
     * @var string
     */
    private $nomeCampoSuccess="success";

    /**
     * This variable contains the ssl version
     *
     * @var string
     */
    private $sslVersion;

    /**
     *
     * Questo metodo effettua una chiamata rest con decodifica in output sotto autenticazione all'url passato, restituisce un array decodificato dal json di risposta, controlla anche se c'è stato un errore logico ed in caso solleva un'eccezione
     * Se viene passato un tipo chiamata questo può assumere i seguenti valori
     * GET
     * POST
     * PUT
     * DELETE
     *
     * This metod make a rest call and return an array, http verb permits are:
     * GET
     * POST
     * PUT
     * DELETE
     *
     * @return array
     * @throws \Exception
     */
    public function chiamataRestDecodificata() {

        //Dichiaro le variabili
        $url=$this->url;
        $login=$this->login;
        $password=$this->password;
        $chiamante=$this->chiamante;
        $json=$this->json;
        $tipoChiamata=$this->tipoChiamata;
        $ritorno="";
        $jsonDecodificato="";
        $messaggio=$this->nomeCampoMessage;
        $success=$this->nomeCampoSuccess;

        //Tolgo gli spazi
        $url = str_replace(" ","%20",$url);

        //Inizializzo la chiamata
        $ch = curl_init();

        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        //Imposto i valori
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!empty($this->sslVersion))
            curl_setopt($ch, CURLOPT_SSLVERSION, $this->sslVersion);

        if ($tipoChiamata!="FORM")
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $tipoChiamata);

        //Se è post o patch di default deve passargli un json
        if ($tipoChiamata=="POST" || $tipoChiamata=="PUT") {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($json))
            );
        }

        if ($tipoChiamata=="FORM") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }

        //Controllo se è stato passato un json anche alla chiamata delete
        if ($tipoChiamata=="DELETE" && !empty($json)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($json))
            );
        }

        //Se è stato passato un userid allora lo setto nell'header
        if (!empty($login)) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "$login:$password");
        }

        //Gli passo il json se valorizzato
        if (!empty($json)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        }

        //Effettuo la chiamata
        $ritorno=curl_exec($ch);

        // Check if an error occurred
        if(curl_errno($ch)) {
            curl_close($ch);
            throw new \Exception($ritorno);
            //throw new \Exception("Risposta negativa alla seguente chiamata:".$chiamante." Il messaggio di ritorno è:".$ritorno);
        }

        // Get HTTP response code
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //Salvo l'httpcode nel caso in cui il chiamante voglia leggerlo
        $this->httpcode=$code;

        //Chiudo la chiamata
        curl_close($ch);

        //Controllo se il codice è tra quelli ammessi (200,201,202)
        if ($code!=200 && $code!=201 && $code!=202)
            throw new \Exception($ritorno);
            //throw new \Exception("Risposta negativa alla seguente chiamata:".$chiamante." Il codice di ritorno è:".$code." e il messaggio:".$ritorno);


        //Decodifico in un array il json di ritorno
        $jsonDecodificato=json_decode($ritorno);

        //Controllo se è stato scelto di testare il success field
        if ($this->controlSuccess) {
            //Controllo se il campo success è true o false
            if (!$jsonDecodificato->$success) {
                throw new \Exception($jsonDecodificato->$messaggio);
                //throw new \Exception("Risposta negativa alla seguente chiamata:".$chiamante.". Le informazioni restituite dal Web Service sono le seguenti:".
                //    $jsonDecodificato->$messaggio);
            }
        }

        //Restituisco l'array relativo al json ricevuto
        return $jsonDecodificato;
    }

    /**
     *
     * Questo metodo effettua una chiamata rest sotto autenticazione all'url passato, restituisce un array decodificato dal json di risposta, controlla anche se c'è stato un errore logico ed in caso solleva un'eccezione
     * Se viene passato un tipo chiamata questo può assumere i seguenti valori
     * GET
     * POST
     * PUT
     *
     * This metod make a rest call and return a string containing the json received, http verb permits are:
     * GET
     * POST
     * PUT
     * DELETE
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function chiamataRest() {

        //Dichiaro le variabili
        $url=$this->url;
        $login=$this->login;
        $password=$this->password;
        $chiamante=$this->chiamante;
        $json=$this->json;
        $tipoChiamata=$this->tipoChiamata;
        $ritorno="";
        $jsonDecodificato="";
        $messaggio=$this->nomeCampoMessage;
        $success=$this->nomeCampoSuccess;

        //Tolgo gli spazi
        $url = str_replace(" ","%20",$url);

        //Inizializzo la chiamata
        $ch = curl_init();

        //Imposto i valori
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!empty($this->sslVersion))
            curl_setopt($ch, CURLOPT_SSLVERSION, $this->sslVersion);

        if ($tipoChiamata!="FORM")
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $tipoChiamata);

        //Se è post o patch di default deve passargli un json
        if ($tipoChiamata=="POST" || $tipoChiamata=="PUT") {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($json))
            );
        }

        if ($tipoChiamata=="FORM") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }

        //Controllo se è stato passato un json anche alla chiamata delete
        if ($tipoChiamata=="DELETE" && !empty($json)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($json))
            );
        }

        //Se è stato passato un userid allora lo setto nell'header
        if (!empty($login)) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "$login:$password");
        }

        //Gli passo il json se valorizzato
        if (!empty($json)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        }

        //Effettuo la chiamata
        $ritorno=curl_exec($ch);

        // Check if an error occurred
        if(curl_errno($ch)) {
            curl_close($ch);
            throw new \Exception($ritorno);
            //throw new \Exception("Risposta negativa alla seguente chiamata:".$chiamante." Il messaggio di ritorno è:".$ritorno);
        }

        // Get HTTP response code
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //Salvo l'httpcode nel caso in cui il chiamante voglia leggerlo
        $this->httpcode=$code;

        //Chiudo
        curl_close($ch);

        //Controllo se il codice è tra quelli ammessi (200,201,202)
        if ($code!=200 && $code!=201 && $code!=202)
            throw new \Exception($ritorno);
            //throw new \Exception("Risposta negativa alla seguente chiamata:".$chiamante." Il codice di ritorno è:".$code." e il messaggio:".$ritorno);

        //Controllo se è stato scelto di testare il success field
        if ($this->controlSuccess) {

            //Decodifico il json in un array per semplicità per accedere meglio alle proprietà successivamente
            $jsonDecodificato=json_decode($ritorno);

            //Controllo il campo success
            if (!$jsonDecodificato->$success) {
                throw new \Exception($jsonDecodificato->$messaggio);
                //throw new \Exception("Risposta negativa alla seguente chiamata:".$chiamante.". Le informazioni restituite dal Web Service sono le seguenti:".
                //    $jsonDecodificato->$messaggio);
            }
        }

        //Restituisco il json
        return $ritorno;
    }

    /**
     *
     * get url
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * get login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * set login
     *
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     *
     * get caller
     *
     * @return string
     */
    public function getChiamante()
    {
        return $this->chiamante;
    }

    /**
     * set caller
     *
     * @param string $chiamante
     */
    public function setChiamante($chiamante)
    {
        $this->chiamante = $chiamante;
    }

    /**
     * get json
     *
     * @return string
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * set json
     *
     * @param string $json
     */
    public function setJson($json)
    {
        $this->json = $json;
    }

    /**
     * get httpverb
     *
     * @return string
     */
    public function getTipoChiamata()
    {
        return $this->tipoChiamata;
    }

    /**
     * set httpverb
     *
     * @param string $tipoChiamata
     */
    public function setTipoChiamata($tipoChiamata)
    {
        $this->tipoChiamata = $tipoChiamata;
    }

    /**
     * get if control succes field
     *
     * @return boolean
     */
    public function getControlSuccess()
    {
        return $this->controlSuccess;
    }

    /**
     * set if control succes field
     *
     * @param boolean $controlSuccess
     */
    public function setControlSuccess($controlSuccess)
    {
        $this->controlSuccess = $controlSuccess;
    }

    /**
     * return httpcode of last request
     *
     * @return integer
     */
    public function getHttpcode()
    {
        return $this->httpcode;
    }

    /**
     * get value of field nomeCampoMessage
     *
     * @return string
     */
    public function getNomeCampoMessage()
    {
        return $this->nomeCampoMessage;
    }

    /**
     * set value of field nomeCampoMessage
     *
     * @param string $nomeCampoMessage
     */
    public function setNomeCampoMessage($nomeCampoMessage)
    {
        $this->nomeCampoMessage = $nomeCampoMessage;
    }

    /**
     * get value of field nomeCampoSuccess
     *
     * @return string
     */
    public function getNomeCampoSuccess()
    {
        return $this->nomeCampoSuccess;
    }

    /**
     * set value of field nomeCampoSuccess
     *
     * @param string $nomeCampoSuccess
     */
    public function setNomeCampoSuccess($nomeCampoSuccess)
    {
        $this->nomeCampoSuccess = $nomeCampoSuccess;
    }

    /**
     * @return string
     */
    public function getSslVersion()
    {
        return $this->sslVersion;
    }

    /**
     * @param string $sslVersion
     */
    public function setSslVersion($sslVersion)
    {
        $this->sslVersion = $sslVersion;
    }


}
<?php
/**
 * Class pour utiliser l'API de Jheberg
 *
 * @author S-K-P
 * @version 1.1
 */
class Jheberg
{
	/**
	 * Défini le serveur à utiliser par défaut
	 * @var string
	 * @access private
	 */
	private $defaultServer = "api";
	/**
	 * Contient l’url des serveurs qui peuvent être interrogé.
	 * @var array
	 * @access private
	 */
	private $server = array(
		"api" => "http://jheberg.net/api/",
		"upload" => null,
		);
	/**
	 * Contient les différents paramètres pour cURL
	 * @var array
	 * @access private
	 */
	private $curlOptions = array(
		"RETURNTRANSFER" => true,
		"HEADER" => false,
		"POST" => true,
		"POSTFIELDS" => null,
		);
	/**
	 * Contient le nom de la dernière methode executé
	 * @var string
	 * @access private
	 */
	private $lastAction = null;
	/**
	 * Contient le http code de la dernière requête retourné par l'API de Jheberg
	 * @var int
	 * @access private
	 */
	private $httpCode = 0;
	/**
	 * Contient les différentes erreurs
	 * @var array
	 * @access private
	 */
	private $error = array();
	/**
	 * Défini si l'utilisateur doit être utilisé lors de l'interrogation de l'API de Jheberg
	 * @var boolean
	 * @access private
	 */
	private $useUser = false;
	/**
	 * Contient les informations pour identifier un utilisateur
	 * @var array
	 * @access private
	 */
	private $user = array(
		"username" => null,
		"password" => null,
		"autoReset" => false,
		);
	/**
	 * Le constructeur vérifie si l'extension cURL est utilisable, dans le cas contraire une exception est levée.
	 */
	function __construct()
	{
		if(!is_callable("curl_init"))
		{
			throw new BadFunctionCallException("The curl extension is disabled");
		}
	}
	/**
	 * Défini le serveur d'upload à utiliser
	 * @param  boolean $newServer Si vaut true, même si un serveur est déjà défini, un nouveau sera défini
	 */
	public function generateUploadServer($newServer = false)
	{
		$this->setLastAction(__FUNCTION__);
		if($this->server["upload"] == null || $newServer)
		{
			$requestOptions = array(
				"command" => "get/server/",
				);
			$curlOptions = array(
				"POST" => false,
				);
			$infoServer = $this->ask($requestOptions, $curlOptions);
			!empty($infoServer->url) ? $this->server["upload"] = $infoServer->url."api/" : $this->newError("UPLOAD SERVER NO GENERATE");
		}
	}
	/**
	 * Upload un fichier
	 * @param  file $fileUpload
	 * @return mixed
	 */
	public function fileUpload($fileUpload)
	{
		$this->generateUploadServer();
		if(!$this->haveError())
		{
			$this->setLastAction(__FUNCTION__);
			$requestOptions = array(
				"server" => "upload",
				"command" => "upload/",
			);
			$datasPost = $this->buildPostUser();
			$datasPost["file"] = "@".realpath($fileUpload);
			$curlOptions = array(
				"POSTFIELDS" => $datasPost,
				);
			$infoFile = $this->ask($requestOptions, $curlOptions);
			if(!empty($infoFile))
			{
				return empty($infoFile->error) ? $infoFile : $this->newError("JHEBERG UPLOAD ERROR", $infoFile->error);
			}
			return $this->newError("ERROR FILE UPLOAD");
		}
		return null;
	}
	/**
	 * Upload un fichier à partir d'une URL
	 * @param  string $urlUpload      URL du fichier à upload
	 * @param  string $fileNameUpload Nom du fichier à upload
	 * @return mixed
	 */
	public function remoteUpload($urlUpload, $fileNameUpload = null)
	{
		$this->generateUploadServer();
		if(!$this->haveError())
		{
			$this->setLastAction(__FUNCTION__);
			$requestOptions = array(
				"server" => "upload",
				"command" => "remote/",
			);
			$datasPost = $this->buildPostUser();
			$datasPost["url"] = $urlUpload;
			if($fileNameUpload != null)
			{
				$datasPost["filename"] = $fileNameUpload;
			}
			$curlOptions = array(
				"POSTFIELDS" => $datasPost,
				);
			$infoFile = $this->ask($requestOptions, $curlOptions);
			if(!empty($infoFile))
			{
				return empty($infoFile->error) ? $infoFile : $this->newError("JHEBERG REMOTE ERROR", $infoFile->error);
			}
			return $this->newError("ERROR REMOTE UPLOAD");
		}
		return null;
	}
	/**
	 * Renvoie la disponibilité du fichier
	 * @param  string $idFile ID ou lien complet du fichier
	 * @return boolean
	 */
	public function validityLink($idFile)
	{
		$infoFile = $this->getInfoLink($idFile);
		$this->setLastAction(__FUNCTION__);
		return !empty($infoFile) ? true : $this->newError("MISSING FILE");
	}
	/**
	 * Renvoie les informations de base sur un fichier et renvoie la disponibilité sur les hébergeurs
	 * @param  string $idFile ID ou lien complet du fichier
	 * @return mixed
	 */
	public function getInfoLink($idFile)
	{
		$this->setLastAction(__FUNCTION__);
		$requestOptions = array(
			"command" => "verify/file/?id=$idFile",
			);
		$curlOptions = array(
			"POST" => false,
			);
		$infoFile = $this->ask($requestOptions, $curlOptions);
		return !empty($infoFile) ? $infoFile : $this->newError("MISSING FILE");
	}
	/**
	 * Récupère tous les fichiers d'un utilisateur
	 * @return mixed
	 */
	public function retrieveListFiles()
	{
		$this->setLastAction(__FUNCTION__);
		if($this->haveUser())
		{
			$requestOptions = array(
				"command" => "list/files/",
				);
			$datasPost = $this->buildPostUser();
			$curlOptions = array(
				"POSTFIELDS" => $datasPost,
				);
			return $this->ask($requestOptions, $curlOptions);
		}
		return $this->newError("NO USER FOR RETRIEVE LIST FILES");
	}
	/**
	 * Créer un dossier dans le gestionnaire de fichiers d'un utilisateur
	 * @param  string $nameDirectory Nom du dossier à créer
	 * @return mixed
	 */
	public function createDirectory($nameDirectory)
	{
		$this->setLastAction(__FUNCTION__);
		if($this->haveUser())
		{
			$requestOptions = array(
				"command" => "create/directory/",
				);
			$datasPost = $this->buildPostUser();
			$datasPost["directory"] = $nameDirectory;

			$curlOptions = array(
				"POSTFIELDS" => $datasPost,
				);
			$infoDirectory = $this->ask($requestOptions, $curlOptions);
			return !empty($infoDirectory) && $infoDirectory->status === 1 ? true : $this->newError("JHEBERG REMOTE ERROR", $infoDirectory->error);
		}
		return $this->newError("NO USER FOR CREATE DIRECTORY");
	}
	/**
	 * Ajoute un mot de passe sur un fichier
	 * @param  string $passwordFile Mot de passe à configurer
	 * @param  string $idFile       ID ou lien complet du fichier
	 * @return mixed
	 */
	public function putPasswordFile($passwordFile, $idFile)
	{
		$this->setLastAction(__FUNCTION__);
		if($this->haveUser())
		{
			$requestOptions = array(
				"command" => "set/password/",
				);
			$datasPost = $this->buildPostUser();
			$datasPost["file_password"] = $passwordFile;
			$datasPost["id"] = $idFile;
			$curlOptions = array(
				"POSTFIELDS" => $datasPost,
				);
			$this->ask($requestOptions, $curlOptions);
			return ($this->httpCode == 200);
		}
		return $this->newError("NO USER FOR PUT PASSWORD FILE");
	}
	/**
	 * Vérifie si un utilisateur a été défini
	 * @return boolean
	 */
	public function haveUser()
	{
		return ($this->user["username"] != null && $this->user["password"] != null);
	}
	/**
	 * Défini si l'utilisateur doit être utilisé
	 * @param boolean $useUser
	 */
	public function setUseUser($useUser)
	{
		$this->useUser = $useUser;
	}
	/**
	 * Défini les informations de l'utilisateur
	 * @param string  $username
	 * @param string  $password
	 * @param boolean $autoReset Défini si les informations devront être réinitialisées après utilisation
	 * @param boolean $useUser   Défini si les informations de l'utilisateur seront utilisées
	 */
	public function setUser($username, $password, $autoReset = false, $useUser = true)
	{
		$this->user = array(
			"username" => $username,
			"password" => $password,
			"autoReset" => $autoReset,
			);
		$this->setUseUser($useUser);
	}
	/**
	 * Défini les informations de l'utilisateur et fait une vérification
	 * @param string  $username
	 * @param string  $password
	 * @param boolean $autoReset Défini si les informations devront être réinitialisées après utilisation
	 * @param boolean $useUser   Défini si les informations de l'utilisateur seront utilisées
	 * @return boolean
	 */	
	public function setUserAndVerify($username, $password, $autoReset = false, $useUser = true)
	{
		$response = $this->verifyLogin($username, $password);
		$this->setUser($username, $password, $autoReset, $useUser);
		return $response;
	}
	/**
	 * Défini les informations de l'utilisateur seulement si la vérification est correct
	 * @param string  $username
	 * @param string  $password
	 * @param boolean $autoReset Défini si les informations devront être réinitialisées après utilisation
	 * @param boolean $useUser   Défini si les informations de l'utilisateur seront utilisées
	 * @return boolean
	 */	
	public function setUserIfVerify($username, $password, $autoReset = false, $useUser = true)
	{
		if($this->verifyLogin($username, $password))
		{
			$this->setUser($username, $password, $autoReset, $useUser);
			return true;
		}
		return false;
	}
	/**
	 * Réinitialise les informations de l'utilisateur
	 * @return void
	 */
	public function resetUser()
	{
		$this->setUser(null, null, false, false);
	}
	/**
	 * Vérifie si le nom de l'utilisateur et son mot de passe son correct.
	 * Détail : Si la fonction retrieveListFiles() à un http code de retour qui est égal à 200, on en déduit que les informations sont corrects
	 * @param  string $username
	 * @param  string $password
	 * @return boolean
	 * @access private
	 */
	private function verifyLogin($username, $password)
	{
		$this->setUser($username, $password, true, true);
		$this->retrieveListFiles();
		return $this->httpCode == 200;
	}
	/**
	 * Interroge l'API de Jheberg
	 * @param  array $requestOptions Contient les informations pour construire l'url
	 * @param  array $curlOptions    Contient les paramètres pour cURL
	 * @return mixed
	 * @access private
	 */
	private function ask($requestOptions, $curlOptions = array())
	{
		$url = $this->buildUrl($requestOptions);
		if($url != null)
		{
			$curlOptions = array_merge($this->curlOptions, $curlOptions);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, $curlOptions["RETURNTRANSFER"]);
			curl_setopt($ch, CURLOPT_HEADER, $curlOptions["HEADER"]);
			curl_setopt($ch, CURLOPT_POST, $curlOptions["POST"]);
			if($curlOptions["POSTFIELDS"] != null)
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, $curlOptions["POSTFIELDS"]);
			}
			$datas = curl_exec($ch);
			$curlInfo = curl_getinfo($ch);
			$this->httpCode = $curlInfo["http_code"];
			return ($datas !== false) ? json_decode($datas) : $this->newError("ERROR CURL");
		}
		return null;
	}
	/**
	 * Construit l'url qui devra être interrogé
	 * @param  array $options Contiendra "server" indique le server à interroger et "command" la commande
	 * @return string
	 * @access private
	 */
	private function buildUrl($options)
	{
		$server = isset($options["server"]) && array_key_exists($options["server"], $this->server) ? $this->server[$options["server"]] : $this->server[$this->defaultServer];
		$command = isset($options["command"]) ? $options["command"] : null;
		return ($server != null && $command != null) ? $server.$command : $this->newError("ERROR BUILD URL");
	}
	/**
	 * Construit les informations de l'utilisateur pour quelle puisse être envoyé en POST
	 * @param  array $currentDatasPost Contiendra éventuellement les anciennes valeurs à envoyer
	 * @return array
	 * @access private
	 */
	private function buildPostUser($currentDatasPost = null)
	{
		$datasPost = $currentDatasPost != null && is_array($currentDatasPost) ? $currentDatasPost : array();
		if($this->useUser)
		{
			$datasPost["username"] = $this->user["username"];
			$datasPost["password"] = $this->user["password"];
			if($this->user["autoReset"])
			{
				$this->resetUser();
			}
		}
		return $datasPost;
	}
	/**
	 * Défini la dernière methode exécuté
	 * @param string $lastAction
	 * @access private
	 */
	private function setLastAction($lastAction)
	{
		$this->lastAction = $lastAction;
		if(isset($this->error[$this->lastAction]))
		{
			unset($this->error[$this->lastAction]);
		}
	}
	/**
	 * Cree une nouvelle erreur 
	 * @param  string $internalMessage Message interne en cas d'erreur
	 * @param  string $jhebergMessage  Message retourné par l'API de Jheberg
	 * @return null
	 * @access private
	 */
	private function newError($internalMessage, $jhebergMessage = null)
	{
		if($this->lastAction != null)
		{
			$this->error[$this->lastAction] = array(
				"internalMessage" => $internalMessage,
				"jhebergMessage" => $jhebergMessage,
				"httpCode" => $this->httpCode,
				"completMessage" => $internalMessage." // ".$jhebergMessage." // http code ".$this->httpCode,
				);
		}
		return null;
	}
	/**
	 * Retourne l'état des erreurs
	 * @return boolean
	 */
	public function haveError()
	{
		return !empty($this->error[$this->lastAction]);
	}
	/**
	 * Retourne la dernière erreur
	 * @return array 
	 */
	public function getLastError()
	{
		return isset($this->error[$this->lastAction]) ? $this->error[$this->lastAction] : array();
	}
	/**
	 * Retourne toutes les erreurs
	 * @return array
	 */
	public function getAllError()
	{
		return $this->error;
	}
}
?>

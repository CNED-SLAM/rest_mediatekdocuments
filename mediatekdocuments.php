<?php
header('Content-Type: application/json');
include_once("Controle.php");
// Charger les variables d'environnement (user + pwd)
require 'vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load(); 
$expectedUser = $_ENV['AUTH_USER'] ?? '';
$expectedPw = $_ENV['AUTH_PW'] ?? '';

// créer l'objet d'accès au contrôleur
$controle = new Controle();

// Contrôle de l'authentification
$authUser = $_SERVER['PHP_AUTH_USER'];
$authPw = $_SERVER['PHP_AUTH_PW'];
if (!$authUser || ($authUser && !($authUser === $expectedUser && $authPw === $expectedPw))) {
    $controle->unauthorized();
   
}else{

    // récupération des données
    // Nom de la table au format string
    $table = filter_input(INPUT_GET, 'table', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ??
             filter_input(INPUT_POST, 'table', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    // id de l'enregistrement au format string
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ??
          filter_input(INPUT_POST, 'id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);    
    // nom et valeur des champs au format json
    $champs = filter_input(INPUT_GET, 'champs') ??
              filter_input(INPUT_POST, 'champs');
    // si une variable dans body donc reçu en post (champs)
    $data = file_get_contents('php://input');
    if (!is_null($data) && $data !== '') {
        parse_str($data, $parseData);
        if (!is_null($parseData['champs']) && $parseData['champs'] !== '') {
            $champs = $parseData['champs'];
        }
    }
    if (!is_null($champs) && $champs !== '') {
        // évite les injections de caractères spéciaux		  
        $champs = htmlspecialchars($champs, ENT_NOQUOTES);	     
        // conversion en tableau	
        $champs = json_decode($champs, true);
    }    

    // traitement suivant le verbe HTTP utilisé
    $method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    if ($method === 'GET') {
        $controle->get($table, $champs);
    } else if ($method === 'POST') {
        $controle->post($table, $champs);
    } else if ($method === 'PUT') {
        $controle->put($table, $id, $champs);
    } else if ($method === 'DELETE') {
        $controle->delete($table, $champs);
    }

}

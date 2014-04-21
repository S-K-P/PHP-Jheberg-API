PHP Jheberg API
=================

Class PHP pour utiliser l'API de Jheberg


## Exemple pour upload un fichier

```php
<?php
require "./Jheberg.php";
$jheberg = new Jheberg();
$infoUpload = $jheberg->fileUpload("./mon-fichier.doc");
if(!$jheberg->haveError())
{
	echo "Votre fichier est disponible en suivant ce lien : ".$infoUpload->url;
}
?>
```

## Exemple pour utiliser le remote upload

```php
<?php
require "./Jheberg.php";
$jheberg = new Jheberg();
$infoUpload = $jheberg->remoteUpload("http://www.exemple.com/mon-document.zip", "Mon Document");
if(!$jheberg->haveError())
{
	echo "Votre fichier est disponible en suivant ce lien : ".$infoUpload->url;
}
?>
```

## Exemple pour upload un fichier avec un utilisateur

```php
<?php
require "./Jheberg.php";
$jheberg = new Jheberg();
$jheberg->setUser("user", "password");
$infoUpload = $jheberg->fileUpload("./mon-fichier.doc");
if(!$jheberg->haveError())
{
	echo "Votre fichier est disponible en suivant ce lien : ".$infoUpload->url;
}
?>
```

## Exemple pour utiliser le remote upload avec un utilisateur

```php
<?php
require "./Jheberg.php";
$jheberg = new Jheberg();
$jheberg->setUser("user", "password");
$infoUpload = $jheberg->remoteUpload("http://www.exemple.com/mon-document.zip", "Mon Document");
if(!$jheberg->haveError())
{
	echo "Votre fichier est disponible en suivant ce lien : ".$infoUpload->url;
}
?>
```

## Exemple pour vérifier un lien

```php
<?php
require "./Jheberg.php";
$jheberg = new Jheberg();
//if($jheberg->validityLink("mon-fichier"))
if($jheberg->validityLink("http://jheberg.net/download/mon-fichier"))
{
	echo "Votre lien existe";
}
?>
```

## Exemple pour récupérer les informations d'un lien

```php
<?php
require "./Jheberg.php";
$jheberg = new Jheberg();
//if($jheberg->getInfoLink("mon-fichier"))
if($jheberg->getInfoLink("http://jheberg.net/download/mon-fichier"))
{
	echo "Votre lien existe";
}
?>
```

## Exemple pour créer un dossier

```php
<?php
require "./Jheberg.php";
$jheberg = new Jheberg();
$jheberg->setUser("user", "password");
if($jheberg->createDirectory("mon-dossier"))
{
	echo "Votre dossier a été créé";
}
//OR
$jheberg->createDirectory("mon-dossier");
if(!$jheberg->haveError())
{
	echo "Votre dossier a été créé";
}
?>
```

## Exemple pour mettre un mot de passe sur un fichier

```php
<?php
require "./Jheberg.php";
$jheberg = new Jheberg();
$jheberg->setUser("user", "password");
//if($jheberg->putPasswordFile("mon-password", "mon-fichier"))
if($jheberg->putPasswordFile("mon-password", "http://jheberg.net/download/mon-fichier"))
{
	echo "Votre fichier est sécurisé avec un mot de passe";
}
//OR
//$jheberg->putPasswordFile("mon-password", "mon-fichier");
$jheberg->putPasswordFile("mon-password", "http://jheberg.net/download/mon-fichier");
if(!$jheberg->haveError())
{
	echo "Votre fichier est sécurisé avec un mot de passe";
}
?>
```

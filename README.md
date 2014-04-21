PHP Jheberg API
=================

Class PHP pour utiliser l'API de Jheberg


# Exemple pour upload un fichier

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

# Exemple pour utiliser le remote upload

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

# Dictionnaire de données

## Utilisateurs (`user`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant de notre l'utilisateur|
|last_name|VARCHAR(255)|NOT NULL|Le nom de l'utilisateur|
|first_name|VARCHAR(255)|NOT NULL|Le prénom de l'utilisateur|
|email|VARCHAR(255)|NOT NULL|L'adresse mail de l'utilisateur|
|password|VARCHAR(255)|NOT NULL|Le mot de passe de l'utilisateur|
|role|VARCHAR(255)|NOT NULL|Le rôle de l'utilisateur|
|avatar|VARCHAR(255)|NULL|L'avatar de l'utilisateur|
|created_at|TIMESTAMP|DEFAULT CURRENT_TIMESTAMP|La date de création de l'utilisateur|
|updated_at|TIMESTAMP|NULL|La date de la dernière mise à jour de l'utilisateur|

## Voyages (`travel`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant du voyage|
|title|VARCHAR(255)|NOT NULL|Le titre du voyage|
|description|LONGTEXT|NOT NULL|La description du voyage|
|status|BOOLEAN|NOT NULL|Le status 0: en cours 1: terminé|
|picture_url|VARCHAR(255)|NULL|La photo de présentation du voyage|
|creation_date|DATE|DEFAULT CURRENT_TIMESTAMP|La date de début du voyage|
|created_at|TIMESTAMP|DEFAULT CURRENT_TIMESTAMP|La date de création du voyage|
|updated_at|TIMESTAMP|NULL|La date de la dernière mise à jour du voyage|
|creator|INT|NOT NULL|L'id de l'utilisateur qui à crée le voyage (relation)|

## Etapes (`step`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant de l'étape|
|title|VARCHAR(255)|NOT NULL|Le titre de l'étape|
|description|VARCHAR(255)|NOT NULL|La description de l'étape|
|latitude|FLOAT|NOT NULL|Latitude de l'étape|
|longitude|FLOAT|NOT NULL|Longitude de l'étape|
|like|INT|NULL|Nombre de like de l'étape|
|step_date|DATE|DEFAULT CURRENT_TIMESTAMP|La date de l'étape|
|created_at|TIMESTAMP|DEFAULT CURRENT_TIMESTAMP|La date de création de l'étape|
|updated_at|TIMESTAMP|NULL|La date de la dernière mise à jour de l'étape|
|travel_id|INT|NOT NULL|L'id du voyage (relation)|

## Commentaires (`comment`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant du commentaire|
|comment|VARCHAR(255)|NOT NULL|La description de l'étape|
|created_at|TIMESTAMP|DEFAULT CURRENT_TIMESTAMP|La date de création du commentaire|
|updated_at|TIMESTAMP|NULL|La date de la dernière mise à jour du commentaire|
|step_id|INT|NOT NULL|L'id de l'étape commentée (relation)|
|user_id|INT|NOT NULL|L'id de l'auteur du commantaire (relation)|

## Photos (`picture`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant de la photo|
|url|VARCHAR(255)|NOT NULL|L'url de la photo|
|created_at|TIMESTAMP|DEFAULT CURRENT_TIMESTAMP|La date de création de la photo|
|updated_at|TIMESTAMP|NULL|La date de la dernière mise à jour de la photo|
|step_id|INT|NOT NULL|L'id de l'étape à qui appartient la photo (relation)|

## Table pivot entre les voyages (travel) et les utilisateurs (user) pour les `followers`

|Champ|Type|Description|
|-|-|-|
|user_id|INT|relation entre les tables|
|travel_id|INT|relation entre les tables|

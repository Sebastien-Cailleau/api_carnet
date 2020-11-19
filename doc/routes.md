# Routes crées

| URL | HTTP Method | Controller | Method | Title | Content | Comment |
|--|--|--|--|--|--|--|
|`/`||`None`||`Page d'accueil`|`Texte de présentation et un bouton “se connecter”`||
|`/login`|`POST`|`SecurityController`|`api_login_checkactivation`|`connexion`|`formulaire de connexion`|`permet de se connecter`|
|`/register`|`POST`|`UserApiController`|`register`|`inscription`|`formulaire d'inscription`|`permet de se créer un compte`|
|`/visit`|`GET`|`VisitController`|`visit`|`visiteur`|`visite d'un voyage`|`possibilité de se connecter`|
|`/email-forgot-password`|`GET`|`SecurityController`|`emailForgotPassword`|`demande de modification de mot de passe oublié`|`formulaire de demande d'envoi d'email de modification de mot de passe`|`permet de recevoir un email de modification du mot de passe`|
|`/reset-password/{token}`|`POST`|`SecurityController`|`resetPassword`|`mot de passe oublié`|`formulaire de récupération de mot de passe`|`permet de modifier son mot de passe via un formulaire, {id} en fonction de l'email reçu`|

| URL | HTTP Method | Controller | Method | Title | Content | Comment |
|--|--|--|--|--|--|--|
|`/travels/create`|`POST`|`TravelApiController`|`add`|`nouveau voyage`|`formulaire de création de voyage`|`Titre et description du voyage`|
|`/travels/{id}`|`GET`|`TravelApiController`|`show`|`aperçu voyage`|`map avec aperçu du voyage`|`même chose si on est visiteur ( pas d'accès aux commentaires ), visiteur connecté ou créateur`|
|`/travels/{id}/update`|`PUT`|`TravelApiController`|`update`|`Modifier mon voyage`|`Formulaire de modification des informations du voyage`|`Modification des informations du voyage`|
|`/travels/list`|`GET`|`TravelApiController`|`list`|`liste des ses voyages`|`liste des voyages`|`Liste de l'ensemble de ses voyages`|
|`/travels/{id}/delete`|`DELETE`|`TravelApiController`|`delete`|`suppression d'un voyage`|`bouton de supression de voyage`|`Permet de supprimer un voyage dans sa totalité`|

| URL | HTTP Method | Controller | Method | Title | Content | Comment |
|--|--|--|--|--|--|--|
|`/travel/{id1}/step/{id2}`|`GET`|`StepApiController`|`show`|`étape`|`visualisation de l'étape`|`{id1} du voyage {id2} de l'étape`|
|`/travel/{id1}/update/{id2}`|`PUT`|`StepApiController`|`update`|`modification d'étape`|`formulaire de modification d'étape`|`{id1} du voyage {id2} de l'étape`|
|`/travel/{id}/add`|`POST`|`StepApiController`|`add`|`nouvelle étape`|`formulaire de création d'étape`|`formulaire d'une nouvelle étape`|
|`/travel/{id1}/delete/{id2}`|`DELETE`|`StepApiController`|`delete`|`suppression d'étape`|`bouton de suppression d'étape`|`{id1} du voyage {id2} de l'étape`|
|`/travel/{id}/comment/{id}`|`GET`|`StepApiController`|`comment`|`Ajouter un commentaire`|`Permet d'ajouter un commentaire`|`Ajout commentaire`|
|`/travel/{id}/like/{id}`|`GET`|`StepApiController`|`like`|`Aimer un post`|`Permet de liker un post`|`Ajout d'un like`|
|`/travel/{id}/unlike/{id}`|`GET`|`StepApiController`|`unlike`|`Ne plus aimer un post`|`Permet de déliker un post`|`Suppression d'un like`|
|`/travel/{id}/comment/{id}/delete`|`DELETE`|`StepApiController`|`deleteComment`|`Suppression d'un commentaire`|`Permet de supprimer un commentaire`|`Suppression de commentaire possible que par l'admin`|

| URL | HTTP Method | Controller | Method | Title | Content | Comment |
|--|--|--|--|--|--|--|
|`/travels/follow/list`|`GET`|`TravelsController`|`listFollow`|`liste des voyages suivis`|`liste des voyages amis`|`Liste de l'ensemble des voyages suivis`|
|`/travels/follow/add`|`POST`|`TravelsController`|`addFollow`|`formulaire d'ajout d'un voyage à suivre`|`ajout d'un voyage amis`|`Permet d'ajouter un voyage via un formulaire`|
|`/travels/follow/delete`|`DELETE`|`TravelsController`|`deleteFollow`|`bouton de suppression d'un voyage suivi`|`suppression d'un voyage amis`|`Permet de retirer un voyage de sa liste`|

| URL | HTTP Method | Controller | Method | Title | Content | Comment |
|--|--|--|--|--|--|--|
|`/user/{id}/logout`|`GET`|`SecurityController`|`logout`|`deconnexion`|`permet de se deconnecter`|
|`/user/{id}`|`GET`|`UserController`|`user`|`mon profil`|`accès aux informations utilisateur`|`gestion du compte`|
|`/user/{id}/update`|`PUT`|`UserController`|`update`|`Modifier mon profil`|`Modification des informations de l'utilisateur`|`gestion du compte`|
|`/user/{id}/delete`|`DELETE`|`UserController`|`delete`|`supprimer mon profil`|`supprime l'utilisateur`|`le compte peut être supprimé par son propriétaire ou un admin`|

| URL | HTTP Method | Controller | Method | Title | Content | Comment |
|--|--|--|--|--|--|--|
|`/contact`|`POST`|`MailerController`|`contact`|`contact`|`contact avec un administrateur`|`Moyen de contact avec les administrateurs`|
|`/presentation`|`GET`|`DefaultController`|`presentation`|`présentation de l'équipe`|`Présentation de l'équipe des créateurs du site`|`Présentation des 5 membres de l'équipe`|
|`/privacy`|`GET`|`DefaultController`|`privacy`|`mentions legales`|`accès aux mentions legales`|`Présentations des mentions légales, RGPD`|

| URL | HTTP Method | Controller | Method | Title | Content | Comment |
|--|--|--|--|--|--|--|
|`/admin/userlist/search`|`GET`|`AdminController`|`user`|`recherche d'un des utilisateurs`|`accès aux utilisateurs`|`recherche d'un utilisateur`|
|`/admin/userlist`|`GET`|`AdminController`|`userlist`|`liste de tous les utilisateurs`|`accès aux utilisateurs`|`liste des utilisateurs`|


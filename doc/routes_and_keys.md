# Routes et Clés

| URL | Non Optional Key | Optional Key | Return Keys|
|--|--|--|--|--|--|--|
|`/api/travel/{id}/step/{id2}`|`None`|`None`|`id`,`title`,  `description`,  `latitude`,`longitude`,  `stepLike`,`stepDate`,  `createdAt`,`updatedAt`,  `travel(object)`,`comments(object)`,`pictures(object)`|
|`/api/travel/{id}/add`|`title`,`description`,  `latitude`,`longitude`,  `step_date`,  `pictures(object)`|`None`|`id(of the new step)`|
|`/api/travel/{id}/update/{id2}`|`None`|`title`,`description`,`latitude`,`longitude`,  `step_date`,`step_like`,  `pictures-delete(url)`,`pictures-new(data and url)`|`id(of the updated step)`|
|`/api/travel/{id}/delete/{id2}`|`None`|`None`|`None`|
|`/api/travel/{id}/like/{id2}`|`None`|`None`|`None`|
|`/api/travel/{id}/unlike/{id2}`|`None`|`None`|`None`|
|`/api/travel/{id}/comment/{id2}`|`comment`|`None`|`id(of the new comment)`|
|`/api/travel/{id}/comment/{id2}/delete`|`None`|`None`|`None`|
|`/api/admin/userlist`|`None`|`None`|(`id`,`lastName`,`firstName`,`avatar`)=>for each user in database|
|`/api/admin/userlist/search`|`search`|`None`|(`id`,`lastName`,`firstName`,`avatar`)=>for each user in database who contain a part of the search in firstName or lastName |
|`/api/user/{id}`|`None`|`None`|`id`,`email`,  `roles(array)`,  `password (hashed)`,`lastName`,  `firstName`,`avatar`,  `createdAt`,`updatedAt`|
|`/api/user/{id}/update`|`None`|`password`,`lastName`,`firstName`,`longitude`, `avatar(data and url)`|`id(of the updated user)`|
|`/api/user/{id}`|`None`|`None`|`None`|

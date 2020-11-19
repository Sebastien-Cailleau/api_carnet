# Backup server

## Htaccess

```
# when compiling LESS/Sass/CoffeScript assets.
# Options +FollowSymlinks

# Disabling MultiViews prevents unwanted negotiation, e.g. "/index" should not resolve
# to the front controller "/index.php" but be rewritten to "/index.php/index".
<IfModule mod_negotiation.c>
    Options -MultiViews
</IfModule>

<IfModule mod_rewrite.c>
    RewriteEngine On

    # Determine the RewriteBase automatically and set it as environment variable.
    # If you are using Apache aliases to do mass virtual hosting or installed the
    # project in a subdirectory, the base path will be prepended to allow proper
    # resolution of the index.php file and to redirect to the correct URI. It will
    # work in environments without path prefix as well, providing a safe, one-size
    # fits all solution. But as you do not need it in this case, you can comment
    # the following 2 lines to eliminate the overhead.
    RewriteCond %{REQUEST_URI}::$0 ^(/.+)/(.*)::\2$
    RewriteRule .* - [E=BASE:%1]

    # Sets the HTTP_AUTHORIZATION header removed by Apache
    RewriteCond %{HTTP:Authorization} .+
    RewriteRule ^ - [E=HTTP_AUTHORIZATION:%0]

    # Redirect to URI without front controller to prevent duplicate content
    # (with and without `/index.php`). Only do this redirect on the initial
    # rewrite by Apache and not on subsequent cycles. Otherwise we would get an
    # endless redirect loop (request -> rewrite to front controller ->
    # redirect -> request -> ...).
    # So in case you get a "too many redirects" error or you always get redirected
    # to the start page because your Apache does not expose the REDIRECT_STATUS
    # environment variable, you have 2 choices:
    # - disable this feature by commenting the following 2 lines or
    # - use Apache >= 2.3.9 and replace all L flags by END flags and remove the
    #   following RewriteCond (best solution)
    RewriteCond %{ENV:REDIRECT_STATUS} =""
    RewriteRule ^index\.php(?:/(.*)|$) %{ENV:BASE}/$1 [R=301,L]

    # If the requested filename exists, simply serve it.
    # We only want to let Apache serve files and not directories.
    # Rewrite all other queries to the front controller.
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ %{ENV:BASE}/index.php [L]

    RewriteCond %{HTTP:Authorization} ^(.*)
    RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]

</IfModule>

<IfModule !mod_rewrite.c>
    <IfModule mod_alias.c>
        # When mod_rewrite is not available, we instruct a temporary redirect of
        # the start page to the front controller explicitly so that the website
        # and the generated links can still be used.
        RedirectMatch 307 ^/$ /index.php/
        # RedirectTemp cannot be used instead
    </IfModule>
</IfModule>
```

## dotenv.local

```
DATABASE_URL=mysql://hyperion:hyperionOclock2020@127.0.0.1:3306/carnet_db?serverVersion=8
MAILER_DSN=gmail://sebtoorop:zhcnqdduuvjhwtii@default
JWT_PASSPHRASE=C@rnetDeVoyageSecret
```

## VirtualhostAPI

```
<VirtualHost *:80>
        # The ServerName directive sets the request scheme, hostname and port that
        # the server uses to identify itself. This is used when creating
        # redirection URLs. In the context of virtual hosts, the ServerName
        # specifies what hostname must appear in the request's Host: header to
        # match this virtual host. For the default virtual host (this file) this
        # value is not decisive as it is used as a last resort host regardless.
        # However, you must set it for any further virtual host explicitly.
        #ServerName www.example.com

        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/html/projet-carnet-voyage/Back/api-carnet/public/
        ServerName www.api-carnet.com
        ServerAlias api-carnet.com
        # Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
        # error, crit, alert, emerg.
        # It is also possible to configure the loglevel for particular
        # modules, e.g.
        #LogLevel info ssl:warn

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined

        # For most configuration files from conf-available/, which are
        # enabled or disabled at a global level, it is possible to
        # include a line for only one particular virtual host. For example the
        # following line enables the CGI configuration for this host only
        # after it has been globally disabled with "a2disconf".
        #Include conf-available/serve-cgi-bin.conf      
        # Configuration du répertoire

        <Directory "/var/www/html/projet-carnet-voyage/Back/api-carnet/public/">
                # AllowOverride All permet d'autoriser la surcharge de la configuration d'Apache via .htaccess
                # Question de sécurité : on ne fait pas n'imoprte quoi avec la conf du serveur
                AllowOverride All
                Order allow,deny
                Allow from All
        </Directory>
</VirtualHost>

# vim: syntax=apache ts=4 sw=4 sts=4 sr noet
```

## virtual host front

```
<VirtualHost *:80>
        # The ServerName directive sets the request scheme, hostname and port that
        # the server uses to identify itself. This is used when creating
        # redirection URLs. In the context of virtual hosts, the ServerName
        # specifies what hostname must appear in the request's Host: header to
        # match this virtual host. For the default virtual host (this file) this
        # value is not decisive as it is used as a last resort host regardless.
        # However, you must set it for any further virtual host explicitly.
        #ServerName www.example.com

        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/html/projet-carnet-voyage/Front/dist
        Alias /carnet-de-voayage "/var/www/html/projet-carnet-voyage/Front/dist"
        ServerName www.carnet-de-voyage.com
        ServerAlias carnet-de-voyage.com
        # Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
        # error, crit, alert, emerg.
        # It is also possible to configure the loglevel for particular
        # modules, e.g.
        #LogLevel info ssl:warn

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined

        # For most configuration files from conf-available/, which are
        # enabled or disabled at a global level, it is possible to
        # include a line for only one particular virtual host. For example the
        # following line enables the CGI configuration for this host only
        # after it has been globally disabled with "a2disconf".
        #Include conf-available/serve-cgi-bin.conf      
        # Configuration du répertoire

        <Directory "/var/www/html/projet-carnet-voyage/Front/dist">
                # AllowOverride All permet d'autoriser la surcharge de la configuration d'Apache via .htaccess
                # Question de sécurité : on ne fait pas n'imoprte quoi avec la conf du serveur
                AllowOverride All
                Order allow,deny
                Allow from All
        </Directory>
</VirtualHost>
```

## Upload front 

```bash
scp -i ~/.ssh/testcle.pem -r /var/www/html/projet-carnet-voyage/Front/dist ubuntu@34.202.233.128:/var/www/html
```

## js

```js
    const buttonCopy = document.createElement('button');

    buttonCopy.setAttribute('onClick', 'copyMeOnClipboard');
    buttonCopy.setAttribute('type', 'button');
    buttonCopy.className = 'shareUrlCopy';
    buttonCopy.textContent = 'Copie moi';

    divElement.appendChild(buttonCopy);
```

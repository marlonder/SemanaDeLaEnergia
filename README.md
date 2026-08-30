# Entorno Docker para PHP — Proyecto Complexivo

Este entorno debe vivir en:

```
/home/marlon/Projects/Complexivo
```

## 1. Estructura del proyecto

```
/home/marlon/Projects/Complexivo
├── docker-compose.yml
├── .env                  <-- lo creas copiando .env.example
├── docker/
│   ├── php/
│   │   ├── Dockerfile
│   │   └── php.ini
│   └── nginx/
│       └── default.conf
└── src/                  <-- aquí va tu código (Laravel, Symfony, o PHP puro)
    └── index.php
```

## 2. Instalación paso a paso

```bash
# 1. Ubícate en la carpeta del proyecto
cd /home/marlon/Projects/Complexivo

# 2. Copia el archivo de entorno
cp .env.example .env

# 3. Averigua tu UID/GID y ajústalos en .env (evita problemas de permisos)
id -u
id -g
# Edita .env y coloca esos valores en UID y GID

# 4. Construye y levanta los contenedores
docker compose up -d --build

# 5. Verifica que todo esté corriendo
docker compose ps
```

## 3. Acceso a los servicios

| Servicio        | URL                          |
|------------------|-------------------------------|
| App web (Nginx)  | http://localhost:8080         |
| phpMyAdmin       | http://localhost:8081         |
| MySQL (host)     | localhost:3306                |

Con el `src/index.php` de prueba, al entrar a `http://localhost:8080` deberías ver la versión de PHP y el estado de conexión a MySQL.

## 4. Trabajando con PHP puro (configuración actual)

Por defecto, este entorno está configurado **sin framework**: `docker/nginx/default.conf` apunta directo a `/var/www/html` (o sea, tu carpeta `src/`). Simplemente crea tus archivos `.php` dentro de `src/` y ya son accesibles en `http://localhost:8080`.

## 5. Instalar un framework en el futuro (Laravel de ejemplo)

Si más adelante quieres usar **Laravel** (u otro framework con carpeta `public/`) dentro de `src/`:

```bash
# Entra al contenedor de PHP
docker compose exec app bash

# Dentro del contenedor, instala Laravel en una carpeta temporal
composer create-project laravel/laravel tmp-laravel

# Mueve el contenido a la raíz de src/ (ya montada como /var/www/html)
shopt -s dotglob
mv tmp-laravel/* .
rm -rf tmp-laravel
exit
```

Luego edita `src/.env` de Laravel con estos datos (coinciden con el `docker-compose.yml`):

```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=complexivo
DB_USERNAME=complexivo_user
DB_PASSWORD=secret
```

Y genera la key de la app:

```bash
docker compose exec app php artisan key:generate
```

> Importante: cuando instales el framework, edita `docker/nginx/default.conf` y cambia la línea `root /var/www/html;` por `root /var/www/html/public;` (ya está dejada comentada lista para descomentar). Luego reinicia el contenedor de nginx:
> ```bash
> docker compose restart webserver
> ```

## 6. Otros comandos útiles

```bash
# Ver logs
docker compose logs -f

# Entrar al contenedor de PHP (para composer, artisan, etc.)
docker compose exec app bash

# Entrar a MySQL por consola
docker compose exec db mysql -u root -p

# Apagar todo
docker compose down

# Apagar y borrar también los datos de la base de datos
docker compose down -v
```

## 7. Extensiones PHP incluidas

pdo, pdo_mysql, mysqli, mbstring, zip, exif, pcntl, bcmath, gd, xml, opcache — cubren la gran mayoría de frameworks PHP modernos (Laravel, Symfony, CodeIgniter, Slim, etc.). Si necesitas otra extensión, agrégala en `docker/php/Dockerfile` y reconstruye con `docker compose up -d --build`.

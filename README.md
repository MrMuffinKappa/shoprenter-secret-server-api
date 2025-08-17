
# Secret Server API

Ez a projekt egy egyszerű, de funkcionális Titok Szerver API megvalósítása, amely egy Junior PHP Fejlesztői pozícióra készült próba feladatként.

Az alkalmazás lehetővé teszi a felhasználók számára, hogy titkos üzeneteket tároljanak, amelyek egy véletlenszerűen generált URL-en keresztül érhetők el. A titkok csak korlátozott számú alkalommal tekinthetők meg, és lejárati idővel (TTL) is rendelkezhetnek, ami után automatikusan megsemmisülnek.



## Főbb funkciók
- Titok létrehozása megtekintési limittel és lejárati idővel (TTL).

- Egyedi, véletlenszerűen generált URL (hash) a titkok biztonságos megosztásához.

- A titkok a megtekintési limit elérése vagy a lejárati idő letelte után automatikusan elérhetetlenné válnak.

- Dinamikus válaszformátum: Az API képes JSON vagy XML formátumban válaszolni az Accept HTTP header alapján.

- Tiszta, PSR-12 kompatibilis, objektum-orientált kód.


## Felhasznált technológiák
- Backend: PHP 8.2+, [Laravel 11](https://www.laravel.com/)

- Adatbázis: SQLite (A fejlesztés egyszerűsítése érdekében, de a Laravel Eloquent ORM-nek köszönhetően könnyen cserélhető, pl. MySQL, PostgreSQL.)

- API Tesztelés: Insomnia

- Függőségek:

    - `mtownsend/response-xml` - XML válaszok egyszerű generálásához.
## Telepítés és beüzemelés

A projekt helyi környezetben való futtatásához kövesd az alábbi lépéseket:

#### Klónozd a repository-t:
```bash
  git clone https://github.com/MrMuffinKappa/shoprenter-secret-server-api.git
  cd shoprenter-secret-server-api
```

#### Telepítsd a Composer függőségeket:
```bash
  composer install
```

#### Hozd létre a `.env` fájlt:
```bash
  cp .env.example .env
```

#### Hozd létre az SQLite adatbázis fájlt:
```bash
  touch database/database.sqlite
```

#### Futtasd az adatbázis migrációkat:
```bash
  php artisan migrate
```

#### Indítsd el a fejlesztői szervert:
```bash
  php artisan serve
```
Az API mostantól a `http://127.0.0.1:8000` címen érhető el.


## API Használata

### Titok létrehozása
- **Végpont**: `POST /api/secret`
- **Leírás**: Új titkos üzenetet hoz létre az adatbázisban.
- **Request Body** (`application/json`):
```JSON
{
    "secret": "TNT - Titkos üzenet",
    "expireAfterViews": 5,
    "expireAfter": 60
}
```
 - `secret` (string, kötelező): A tárolandó titkos üzenet.
 - `expireAfterViews` (integer, kötelező, min: 1): Hányszor lehet megtekinteni a titkot, mielőtt törlődik.
 - `expireAfter` (integer, kötelező, min: 0): Hány perc múlva jár le a titok. Ha 0, akkor nincs időkorlát.

- **Sikeres válasz** (`201 Created`):
```JSON
{
    "hash": "a1b2c3d4e5f6",
    "secretText": "Ez egy nagyon titkos üzenet.",
    "createdAt": "2025-08-17T18:30:00.000000Z",
    "expiresAt": "2025-08-17T19:30:00.000000Z",
    "remainingViews": 5
}
```

- **Hibás válasz** (`405 Invalid Input`):
```JSON
{
    "secret": [
        "The secret field is required."
    ]
}
```

### Titok lekérése
- **Végpont**: `GET /api/secret/{hash}`
- **Leírás**: Lekéri a megadott `hash`-hez tartozó titkot. Minden sikeres lekérés csökkenti a `remainingViews` értékét eggyel.
- **URL Paraméter**:
    - `{hash}` (string, kötelező): A titok létrehozásakor kapott egyedi azonosító.

- **Sikeres válasz** (`200 OK`):
```JSON
{
    "hash": "a1b2c3d4e5f6",
    "secretText": "Ez egy nagyon titkos üzenet.",
    "createdAt": "2025-08-17T18:30:00.000000Z",
    "expiresAt": "2025-08-17T19:30:00.000000Z",
    "remainingViews": 4
}
```
- **Hibás válasz** (`404 Not Found`):
Akkor kapod, ha a titok:

- Nem létezik.

- Már lejárt.

- Elérte a maximális megtekintési limitet.


## Válaszformátumok (JSON/XML)

Az API a `Accept` HTTP header alapján határozza meg a válasz formátumát.

- JSON válasz kérése:
    `Accept: application/json`

- XML válasz kérése:
    `Accept: application/xml`

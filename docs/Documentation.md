# Konferenční systém – dokumentace


## Zadání
Webová aplikace pro konferenční systém. Nepřihlášený uživatel vidí publikované články a může se registrovat. Autoři mohou nahrávat a spravovat své příspěvky, recenzenti hodnotí přiřazené články a administrátoři spravují uživatele, přiřazují recenzenty a rozhodují o publikování.

## Použité technologie
- **PHP 8 + PDO:** backend, MVC controllery a modely, přístup do MySQL.
- **MySQL/MariaDB:** perzistence uživatelů, článků a recenzí.
- **HTML5 + Bootstrap 5:** UI, responzivní layout.
- **CSS v rámci Bootstrapu:** základní stylování.
- **JavaScript (Bootstrap bundle):** kolabující menu v navigaci.

## Struktura adresářů
- `index.php` – vstupní soubor a jednoduchý router podle parametru `page`.
- `app/Core/Database.php` – vytvoření PDO připojení.
- `app/Controllers/` – controllery (Home, Auth, Article, Review, Admin) volající modely a předávající data do pohledů.
- `app/Models/` – práce s databází (uživatelé, články, recenze).
- `app/Views/` – šablony `.phtml` + společné layouty `layouts/header.phtml`, `layouts/footer.phtml`.
- `uploads/` – úložiště nahraných PDF článků.
- `sql/install.sql` – skript pro vytvoření databáze a demo dat.
- `docs/Documentation.md` – tento dokument.

## Architektura
Aplikace používá jednoduché MVC:
- **Router:** `index.php` načte controller podle `$_GET['page']`.
- **Controller:** třídy v `app/Controllers` zpracují request, validují roli, volají model a vykreslí view pomocí metody `view()` z `Controller.php`.
- **Model:** třídy v `app/Models` provádějí SQL dotazy přes připravené PDO statementy.
- **View:** `.phtml` šablony obsahují HTML s drobným PHP a jsou obaleny společným layoutem s navigací a patičkou.

## Role a přístupová práva
- **autor:** přidává vlastní články (PDF), edituje a maže je do stavu publikace, vidí své položky.
- **recenzent:** vidí přiřazené články a zadává hodnocení (tři kritéria + komentář).
- **admin:** spravuje uživatele a jejich role, přiřazuje recenzenty, rozhoduje o publikování/odmítnutí.

## Bezpečnost
- Přihlášení používá `password_hash`/`password_verify` (bcrypt).
- Připravené dotazy s pojmenovanými parametry v modelech omezují SQL injection.
- Výstupní data (jména, názvy, abstrakty) jsou v šablonách escapována přes `htmlspecialchars`.
- Souborový upload kontroluje příponu PDF a ukládá do izolované složky `uploads/` s unikátním názvem.

## Instalace a spuštění
1. Vytvořte databázi MySQL `conference_db` a spusťte skript `sql/install.sql`.
2. Upravte přihlašovací údaje k DB v `app/Core/Database.php` (host, uživatel, heslo).
3. Ujistěte se, že složka `uploads/` je zapisovatelná pro webový server.
4. Spusťte PHP server v kořenovém adresáři, např.: `php -S localhost:8000`.
5. Otevřete `http://localhost:8000/index.php`.

### Výchozí uživatelské účty
- **admin / heslo:** `heslo123` (bcrypt v demo datech)
- **reviewer1 / heslo:** `heslo123`
- **author1 / heslo:** `heslo123`

## Seznam hlavních funkcí
- Registrace a přihlášení uživatele, uložení role do session.
- Vytváření článků s uploadem PDF a stavem workflow (`pending`, `accepted`, `rejected`).
- Přehled vlastních článků, editace a mazání do publikace.
- Admin dashboard s přiřazením recenzentů a změnou stavu článků.
- Recenzní formulář se třemi kritérii hodnocení a komentářem.
- Veřejný seznam publikovaných článků ke stažení.

## Splnění povinných požadavků (stav k revizi)
- **Technologie:** HTML5, CSS (Bootstrap), PHP, MySQL, PDO – splněno.
- **MVC + OOP:** controllery, modely a views oddělené – splněno.
- **Jeden vstupní bod:** `index.php` – splněno.
- **PDO + ochrana proti SQLi:** připravené dotazy – splněno.
- **Hashování hesel:** bcrypt – splněno.
- **XSS ochrana:** šablony escapují dynamická data – částečně, je vhodné zkontrolovat všechny výstupy.
- **Upload souborů:** PDF upload – splněno.
- **Responzivita:** Bootstrap 5 – splněno.
- **Role:** admin, reviewer, author – splněno (3 role).
- **Dokumentace + DB skripty:** tento soubor + `sql/install.sql` – doplněno.
- **Bez frameworku:** čisté PHP – splněno.
